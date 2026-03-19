<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Cashier — GLÆZE Burger</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/easyqrcodejs@4.6.1/dist/easy.qrcode.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#F8FAFC] font-sans antialiased overflow-hidden">
    <script>
        function posApp() {
            return {
                allProducts: @js($products),
                filteredProducts: @js($products),
                categories: @js($categories),
                rawMaterials: @js($rawMaterials),
                cart: [],
                sidebarOpen: false,
                cartOpen: false,
                searchQuery: '',
                selectedCategory: '',
                voucherInput: '',
                appliedVoucher: null,
                isApplyingVoucher: false,
                voucherError: '',
                showCheckout: false,
                showDiscountModal: false,
                showQrisModal: false,
                qrisString: '',
                qrisLoading: false,
                qrisTransactionId: null,
                qrisInvoiceNumber: null,
                qrisTotalAmount: 0,
                paymentReference: '',
                paymentMethod: 'Cash',
                cashReceived: 0,
                discountType: null, // 'percentage' | 'nominal' | null
                discountValue: 0,
                applyTax: false,
                isProcessing: false,
                lastTransactionId: null,
                audioCtx: null,



                init() {
                    this.filterProducts();
                    // Setup watcher to auto-fill cash when QRIS is selected
                    this.$watch('paymentMethod', method => {
                        if (method === 'QRIS') {
                            this.cashReceived = this.grandTotal;
                        } else if (method === 'Cash' && this.cashReceived === this.grandTotal) {
                            this.cashReceived = 0; 
                        }
                    });
                },

                filterProducts() {
                    this.filteredProducts = this.allProducts.filter(p => {
                        const matchesSearch = p.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                            p.sku.toLowerCase().includes(this.searchQuery.toLowerCase());
                        const matchesCat = this.selectedCategory === '' || p.category_id == this.selectedCategory;
                        return matchesSearch && matchesCat;
                    });
                },

                setCategory(id) {
                    this.selectedCategory = id;
                    this.filterProducts();
                },

                addToCart(product) {
                    const existingIndex = this.cart.findIndex(item => item.id === product.id);
                    const availability = this.checkStockAvailability(product, 1);
                    
                    if (existingIndex > -1) {
                        if (availability.canAdd) {
                            this.cart[existingIndex].quantity++;
                            this.playAddSound();
                        } else {
                            Swal.fire({ 
                                title: 'Insufficient Stock', 
                                text: availability.message, 
                                icon: 'warning',
                                confirmButtonColor: '#0A56C8'
                            });
                        }
                    } else {
                        if (availability.canAdd) {
                            this.cart.push({ 
                                ...product, 
                                selling_price: parseFloat(product.selling_price), // Force number
                                quantity: 1, 
                                notes: '' 
                            });
                            this.playAddSound();
                        } else {
                            Swal.fire({ 
                                title: 'Out of Stock', 
                                text: availability.message, 
                                icon: 'error',
                                confirmButtonColor: '#0A56C8'
                            });
                        }
                    }
                },

                increaseQty(index) {
                    const item = this.cart[index];
                    const product = this.allProducts.find(p => p.id === item.id);
                    const availability = this.checkStockAvailability(product, 1);

                    if (availability.canAdd) {
                        this.cart[index].quantity++;
                        this.playAddSound();
                    } else {
                        Swal.fire({ 
                            title: 'Limit Reached', 
                            text: availability.message, 
                            icon: 'warning'
                        });
                    }
                },

                checkStockAvailability(product, additionalQty) {
                    // 1. If not recipe based, check direct stock
                    if (!product.is_recipe_based) {
                        const inCart = this.cart.find(c => c.id === product.id)?.quantity || 0;
                        const canAdd = (inCart + additionalQty) <= product.stock;
                        return { 
                            canAdd: canAdd, 
                            message: canAdd ? '' : `Only ${product.stock} units available in total.` 
                        };
                    }

                    // 2. If recipe based, check each raw material
                    for (const ingredient of product.raw_materials) {
                        const materialId = ingredient.id;
                        const reqPerUnit = ingredient.pivot.quantity;
                        
                        // Calculate total current consumption of this material in cart
                        let currentConsumpt = 0;
                        this.cart.forEach(item => {
                            const p = this.allProducts.find(prod => prod.id === item.id);
                            if (p && p.is_recipe_based) {
                                const ing = p.raw_materials.find(i => i.id === materialId);
                                if (ing) {
                                    currentConsumpt += (ing.pivot.quantity * item.quantity);
                                }
                            }
                        });

                        const material = this.rawMaterials.find(m => m.id === materialId);
                        const initialStock = material ? parseFloat(material.stock) : 0;
                        const totalReqIfAdded = currentConsumpt + (reqPerUnit * additionalQty);

                        if (totalReqIfAdded > initialStock) {
                            const remaining = initialStock - currentConsumpt;
                            const possibleAdd = Math.floor(remaining / reqPerUnit);
                            return {
                                canAdd: false,
                                message: `Insufficient ${ingredient.name}. Remaining: ${remaining} ${ingredient.unit}. You can only make ${possibleAdd} more units.`
                            };
                        }
                    }

                    return { canAdd: true, message: '' };
                },

                getLiveStock(product) {
                    if (!product.is_recipe_based) {
                        const inCart = this.cart.find(c => c.id === product.id)?.quantity || 0;
                        return Math.max(0, product.stock - inCart);
                    }

                    const possibleQuantities = [];
                    product.raw_materials.forEach(ingredient => {
                        const materialId = ingredient.id;
                        const reqPerUnit = ingredient.pivot.quantity;

                        let currentConsumpt = 0;
                        this.cart.forEach(item => {
                            const p = this.allProducts.find(prod => prod.id === item.id);
                            if (p && p.is_recipe_based) {
                                const ing = p.raw_materials.find(i => i.id === materialId);
                                if (ing) {
                                    currentConsumpt += (ing.pivot.quantity * item.quantity);
                                }
                            }
                        });

                        const material = this.rawMaterials.find(m => m.id === materialId);
                        const initialStock = material ? parseFloat(material.stock) : 0;
                        const remainingMaterial = initialStock - currentConsumpt;

                        possibleQuantities.push(Math.floor(remainingMaterial / reqPerUnit));
                    });

                    return Math.max(0, Math.min(...possibleQuantities));
                },

                decreaseQty(index) {
                    if (this.cart[index].quantity > 1) {
                        this.cart[index].quantity--;
                    } else {
                        this.removeItem(index);
                    }
                },

                removeItem(index) {
                    this.cart.splice(index, 1);
                },

                clearCart() {
                    Swal.fire({
                        title: 'Clear Order Cart?',
                        text: "All items and notes will be removed.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#9ca3af',
                        confirmButtonText: 'Yes, clear it'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.cart = [];
                            this.cashReceived = 0;
                            this.discountType = null;
                            this.discountValue = 0;
                            this.applyTax = false;
                            this.removeVoucher();
                        }
                    });
                },

                removeVoucher() {
                    this.voucherInput = '';
                    this.appliedVoucher = null;
                    this.voucherError = '';
                },


                
                async applyVoucher() {
                    if (!this.voucherInput || this.cart.length === 0) return;
                    
                    this.isApplyingVoucher = true;
                    this.voucherError = '';
                    
                    try {
                        const response = await fetch('{{ route('pos.vouchers.apply') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                voucher_code: this.voucherInput,
                                cart_subtotal: this.subTotal,
                                cart_items: this.cart.map(item => ({ id: item.id }))
                            })
                        });
                        
                        const data = await response.json();
                        if (data.success) {
                            this.appliedVoucher = {
                                code: data.voucher_code,
                                discount_amount: data.discount_amount
                            };
                            this.playSuccessSound();
                        } else {
                            this.voucherError = data.message;
                        }
                    } catch (e) {
                        this.voucherError = 'Connection error processing voucher.';
                    } finally {
                        this.isApplyingVoucher = false;
                    }
                },

                get subTotal() {
                    return this.cart.reduce((sum, item) => sum + (parseFloat(item.selling_price) * item.quantity), 0);
                },

                get discountAmount() {
                    const type = this.discountType;
                    const val = parseFloat(this.discountValue) || 0;
                    if (!type || val <= 0) return 0;
                    
                    let amount = 0;
                    if (type === 'percentage') {
                        amount = this.subTotal * (val / 100);
                    } else {
                        amount = val; // nominal
                    }
                    return Math.min(amount, this.subTotal); // Cap to subtotal
                },

                get netSales() {
                    let manualDisc = this.discountAmount;
                    let voucherDisc = this.appliedVoucher ? parseFloat(this.appliedVoucher.discount_amount) : 0;
                    
                    let totalDiscounts = manualDisc + voucherDisc;
                    if (totalDiscounts > this.subTotal) {
                        totalDiscounts = this.subTotal;
                    }
                    return Math.max(0, this.subTotal - totalDiscounts);
                },

                get taxAmount() {
                    return this.applyTax ? Math.round(this.netSales * 0.10) : 0;
                },

                get grandTotal() {
                    return this.netSales + this.taxAmount;
                },

                get changeAmount() {
                    return this.cashReceived - this.grandTotal;
                },

                formatPrice(price) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(price).replace('IDR', 'Rp');
                },

                openCheckout() {
                    this.showCheckout = true;
                },

                async processCheckout() {
                    this.isProcessing = true;
                    try {
                        const response = await fetch('{{ route('pos.checkout') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                payment_method: this.paymentMethod,
                                cart: this.cart.map(item => ({ 
                                    id: item.id, 
                                    quantity: item.quantity,
                                    notes: item.notes || null 
                                })),
                                apply_tax: this.applyTax,
                                discount_type: this.discountType,
                                discount_value: this.discountValue || 0,
                                voucher_code: this.appliedVoucher ? this.appliedVoucher.code : null
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            if (this.paymentMethod === 'QRIS') {
                                this.lastTransactionId = data.data.transaction_id;
                                this.qrisTransactionId = data.data.transaction_id;
                                this.qrisInvoiceNumber = data.data.invoice_number;
                                this.qrisTotalAmount = data.data.total_amount;
                                
                                this.showCheckout = false;
                                this.openQrisPayment(data.data.total_amount);
                            } else {
                                this.playSuccessSound();

                                // Store ID for re-printing later
                                this.lastTransactionId = data.data.transaction_id;

                                // Automatically trigger receipt print in popup
                                this.printReceipt(this.lastTransactionId);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Order Complete!',
                                    html: `<div class="p-4 space-y-2 uppercase tracking-widest text-xs font-black">
                                            <p class="text-gray-400">Invoice: <span class="text-gray-900">${data.data.invoice_number}</span></p>
                                            <p class="text-gray-400">Total: <span class="text-smash-blue">${this.formatPrice(data.data.total_amount)}</span></p>
                                            <p class="text-gray-400">Method: <span class="px-2 py-0.5 bg-gray-100 rounded text-gray-900">${data.data.payment_method}</span></p>
                                        </div>`,
                                    confirmButtonText: 'FINISH & NEW ORDER',
                                    confirmButtonColor: '#0A56C8',
                                    customClass: {
                                        popup: 'rounded-[3rem]',
                                        confirmButton: 'rounded-2xl px-8 py-3 uppercase tracking-widest font-black text-xs'
                                    }
                                }).then(() => {
                                    this.resetPosState();
                                });
                            }
                        } else {
                            throw new Error(data.message || 'Payment failed.');
                        }
                    } catch (error) {
                        Swal.fire({ icon: 'error', title: 'Error', text: error.message, confirmButtonColor: '#0A56C8' });
                    } finally {
                        this.isProcessing = false;
                    }
                },
                
                resetPosState() {
                    this.cart = [];
                    this.cashReceived = 0;
                    this.discountType = null;
                    this.discountValue = 0;
                    this.applyTax = false;
                    this.showCheckout = false;
                    this.showQrisModal = false;
                    this.paymentReference = '';
                    location.reload();
                },

                async openQrisPayment(amount) {
                    this.showQrisModal = true;
                    this.qrisLoading = true;
                    this.qrisString = '';
                    
                    const qrContainer = document.getElementById("qris-code-container");
                    if (qrContainer) qrContainer.innerHTML = '';
                    
                    try {
                        const response = await fetch('{{ route('pos.qris.generate') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ amount: amount })
                        });
                        
                        const data = await response.json();
                        if (data.success) {
                            this.qrisString = data.qris_string;
                            this.renderQrisCode(this.qrisString);
                            this.playTone(800, 'sine', 0.1); 
                        } else {
                            throw new Error(data.message || 'Failed to generate QRIS.');
                        }
                    } catch (error) {
                        Swal.fire({ icon: 'error', title: 'Error', text: error.message, confirmButtonColor: '#0A56C8' });
                    } finally {
                        this.qrisLoading = false;
                    }
                },
                
                renderQrisCode(text) {
                    const container = document.getElementById("qris-code-container");
                    if (!container) return;
                    container.innerHTML = '';
                    
                    new QRCode(container, {
                        text: text,
                        width: 200,
                        height: 200,
                        colorDark : "#000000",
                        colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.H,
                        PI: '#0A56C8', 
                        logo: "{{ asset('images/qris-logo.png') }}",
                        logoWidth: 50,
                        logoHeight: 50,
                        logoBackgroundColor: '#ffffff',
                        logoBackgroundTransparent: false
                    });
                },
                
                async confirmQrisPayment() {
                    if (!this.qrisTransactionId) return;
                    
                    this.isProcessing = true;
                    try {
                        const response = await fetch(`{{ url('/transactions') }}/${this.qrisTransactionId}/payment`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                payment_reference: this.paymentReference
                            })
                        });
                        
                        const data = await response.json();
                        if (data.success) {
                            this.playSuccessSound();
                            this.printReceipt(this.qrisTransactionId);
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Payment Confirmed!',
                                html: `<div class="p-4 space-y-2 uppercase tracking-widest text-xs font-black">
                                        <p class="text-gray-400">Invoice: <span class="text-gray-900">${this.qrisInvoiceNumber}</span></p>
                                        <p class="text-gray-400">Total: <span class="text-smash-blue">${this.formatPrice(this.qrisTotalAmount)}</span></p>
                                        <p class="text-gray-400">Method: <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-gray-900">QRIS (Confirmed)</span></p>
                                        <p class="text-gray-400">Ref: <span class="text-gray-900">${this.paymentReference || '-'}</span></p>
                                       </div>`,
                                confirmButtonText: 'FINISH & NEW ORDER',
                                confirmButtonColor: '#0A56C8',
                                customClass: {
                                    popup: 'rounded-[3rem]',
                                    confirmButton: 'rounded-2xl px-8 py-3 uppercase tracking-widest font-black text-xs'
                                }
                            }).then(() => {
                                this.resetPosState();
                            });
                        } else {
                            throw new Error(data.message || 'Confirmation failed.');
                        }
                    } catch (error) {
                        Swal.fire({ icon: 'error', title: 'Error', text: error.message, confirmButtonColor: '#0A56C8' });
                    } finally {
                        this.isProcessing = false;
                    }
                },
                
                printReceipt(transactionId) {
                    const width = 300; // ~58mm typical receipt printer pop-out width
                    const height = 500;
                    const left = (screen.width / 2) - (width / 2);
                    const top = (screen.height / 2) - (height / 2);
                    
                    window.open(
                        `{{ url('/transactions') }}/${transactionId}/receipt`,
                        'ReceiptWindow',
                        `width=${width},height=${height},top=${top},left=${left},scrollbars=yes,status=no,resizable=yes`
                    );
                },

                printLastReceipt() {
                    if (this.lastTransactionId) {
                        this.printReceipt(this.lastTransactionId);
                    }
                },

                initAudio() {
                    if (!this.audioCtx) {
                        this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    }
                },

                playTone(frequency, type, duration) {
                    this.initAudio();
                    if (this.audioCtx.state === 'suspended') this.audioCtx.resume();
                    const oscillator = this.audioCtx.createOscillator();
                    const gainNode = this.audioCtx.createGain();
                    oscillator.type = type;
                    oscillator.frequency.setValueAtTime(frequency, this.audioCtx.currentTime);
                    gainNode.gain.setValueAtTime(0.05, this.audioCtx.currentTime); // low volume
                    gainNode.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + duration);
                    oscillator.connect(gainNode);
                    gainNode.connect(this.audioCtx.destination);
                    oscillator.start();
                    oscillator.stop(this.audioCtx.currentTime + duration);
                },

                playAddSound() {
                    this.playTone(800, 'sine', 0.1);
                    setTimeout(() => this.playTone(1200, 'sine', 0.15), 50);
                },

                playSuccessSound() {
                    this.playTone(400, 'sine', 0.1);
                    setTimeout(() => this.playTone(600, 'sine', 0.1), 100);
                    setTimeout(() => this.playTone(1000, 'sine', 0.3), 200);
                }
            };
        }
    </script>
    <div x-data="posApp()" class="h-screen flex flex-col md:flex-row overflow-hidden relative">
        
        <!-- Left Panel: Product Area (70%) -->
        <div class="flex-1 flex flex-col h-full bg-white border-r border-gray-100 overflow-hidden">
            <!-- POS Header -->
            <header class="p-6 border-b border-gray-50 flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="p-2.5 rounded-xl bg-gray-50 text-gray-400 hover:text-smash-blue hover:bg-blue-50 transition-all border border-transparent hover:border-blue-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-2xl font-black text-gray-900 tracking-tighter uppercase leading-none">CASHIER</h1>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">GLÆZE BURGER POS SYSTEM</p>
                        </div>
                    </div>
                    <div class="relative w-72 group">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-300 group-focus-within:text-smash-blue transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input type="text" x-model="searchQuery" @input="filterProducts()"
                            class="block w-full pl-11 pr-4 py-3 border border-gray-100 rounded-2xl bg-gray-50/50 placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue sm:text-sm transition-all shadow-inner" 
                            placeholder="Find products by name or SKU...">
                    </div>

                    <!-- Shift Controls in Header -->
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('pos.shift.index') }}" class="px-4 py-2 bg-blue-50 text-smash-blue border border-blue-100 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-100 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Manage Shift
                        </a>
                    </div>
                </div>

                <!-- Category Chips -->
                <div class="flex items-center space-x-3 overflow-x-auto pb-2 scrollbar-hide">
                    <button @click="setCategory('')"
                        :class="selectedCategory === '' ? 'bg-smash-blue text-white shadow-lg shadow-blue-200 border-smash-blue' : 'bg-gray-50 text-gray-500 hover:bg-blue-50 hover:text-smash-blue border-transparent'"
                        class="px-6 py-2.5 rounded-xl text-[11px] font-black uppercase tracking-widest border transition-all whitespace-nowrap">
                        All Items
                    </button>
                    <template x-for="cat in categories" :key="cat.id">
                        <button @click="setCategory(cat.id)"
                            :class="selectedCategory === cat.id ? 'bg-smash-blue text-white shadow-lg shadow-blue-200 border-smash-blue' : 'bg-gray-50 text-gray-500 hover:bg-blue-50 hover:text-smash-blue border-transparent'"
                            class="px-6 py-2.5 rounded-xl text-[11px] font-black uppercase tracking-widest border transition-all whitespace-nowrap">
                            <span x-text="cat.name"></span>
                        </button>
                    </template>
                </div>
            </header>

            <!-- Product Grid -->
            <div class="flex-1 overflow-y-auto p-8 bg-blue-50/10">
                <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)" 
                            class="bg-white rounded-[2rem] p-4 border border-gray-100 shadow-sm hover:shadow-2xl hover:shadow-blue-100 transition-all cursor-pointer group transform hover:-translate-y-1 active:scale-95">
                            <div class="relative aspect-square rounded-3xl overflow-hidden bg-gray-50 mb-4 border border-gray-50">
                                <template x-if="product.image_path">
                                    <img :src="`{{ asset('storage') }}/${product.image_path}`" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                </template>
                                <template x-if="!product.image_path">
                                    <div class="w-full h-full flex items-center justify-center text-gray-200">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </template>
                                <div class="absolute top-3 right-3">
                                    <span :class="getLiveStock(product) <= 5 ? 'bg-orange-500' : 'bg-smash-blue'"
                                        class="px-3 py-1 text-[10px] font-black text-white rounded-lg shadow-lg shadow-black/10 uppercase tracking-widest transition-colors">
                                        <span x-text="getLiveStock(product)"></span> <span x-text="getLiveStock(product) === 0 ? 'Wait' : 'Stock'"></span>
                                    </span>
                                </div>
                            </div>
                            <h3 class="text-sm font-black text-gray-900 group-hover:text-smash-blue transition-colors line-clamp-1 uppercase tracking-tight" x-text="product.name"></h3>
                            <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase" x-text="product.category.name"></p>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-base font-black text-smash-blue" x-text="formatPrice(product.selling_price)"></span>
                                <div class="p-2 rounded-xl bg-gray-50 group-hover:bg-smash-blue group-hover:text-white transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Empty State -->
                <div x-show="filteredProducts.length === 0" class="h-full flex flex-col items-center justify-center py-20">
                    <div class="h-24 w-24 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <p class="font-black text-gray-900 text-lg uppercase tracking-widest leading-none">No products matching</p>
                    <p class="text-sm font-bold text-gray-400 mt-2 uppercase tracking-tight">Try adjusting your filters or search keywords.</p>
                </div>
            </div>
        </div>

        <!-- Mobile Cart FAB -->
        <button x-show="!cartOpen && cart.length > 0" @click="cartOpen = true" x-transition
            class="md:hidden fixed bottom-6 left-1/2 -translate-x-1/2 bg-smash-blue text-white px-6 py-4 rounded-full shadow-2xl shadow-smash-blue/30 font-black uppercase tracking-widest text-sm z-40 flex items-center space-x-3 active:scale-95 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span x-text="cart.length + ' Items'"></span>
            <span class="bg-white/20 px-2 py-0.5 rounded text-xs" x-text="formatPrice(grandTotal)"></span>
        </button>

        <!-- Cart Backdrop for Mobile -->
        <div x-cloak x-show="cartOpen" x-transition.opacity @click="cartOpen = false" class="md:hidden fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm"></div>

        <!-- Right Panel: Cart & Checkout (30%) -->
        <div :class="cartOpen ? 'translate-x-0 shadow-2xl' : 'translate-x-full md:translate-x-0'" 
             class="fixed md:relative inset-y-0 right-0 z-50 w-full sm:w-96 md:w-[420px] bg-gray-50/50 flex flex-col h-full transition-transform duration-300 ease-in-out border-l border-gray-100">
            <header class="p-6 bg-white border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex items-center space-x-3">
                    <button @click="cartOpen = false" class="md:hidden p-2 -ml-2 text-gray-400 hover:text-gray-900 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <h2 class="text-xl font-black text-gray-900 leading-none tracking-tighter uppercase">Order Cart</h2>
                    <span x-text="cart.length + ' Items'" class="px-3 py-1 bg-blue-50 text-smash-blue text-[10px] font-black rounded-lg uppercase tracking-widest hidden sm:inline-block"></span>
                </div>
                <div class="flex items-center space-x-2">
                    <button x-show="cart.length > 0" @click="clearCart()" class="text-[10px] font-black uppercase tracking-widest text-red-400 hover:text-red-500 flex items-center space-x-1 p-2 bg-red-50 hover:bg-red-100 rounded-lg transition-colors border border-red-100 hover:border-red-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        <span class="hidden xl:inline">Clear</span>
                    </button>
                    <button x-show="lastTransactionId" @click="printLastReceipt()" class="text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-smash-blue flex items-center space-x-1 p-2 bg-gray-50 hover:bg-blue-50 rounded-lg transition-colors border border-gray-100 hover:border-blue-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span class="hidden xl:inline">Print Last</span>
                    </button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto px-6 py-8 space-y-4">
                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="bg-white rounded-3xl p-4 border border-gray-100 shadow-sm flex items-center space-x-4">
                        <div class="h-16 w-16 rounded-2xl bg-gray-50 flex-shrink-0 overflow-hidden">
                            <template x-if="item.image_path">
                                <img :src="`{{ asset('storage') }}/${item.image_path}`" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!item.image_path">
                                <div class="w-full h-full flex items-center justify-center text-gray-200">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-black text-gray-900 uppercase tracking-tight line-clamp-1" x-text="item.name"></h4>
                            <p class="text-smash-blue font-black text-xs mt-1" x-text="formatPrice(item.selling_price)"></p>
                            <input type="text" x-model="item.notes" placeholder="Notes (e.g. Extra Spicy)" class="mt-2 w-full text-[10px] font-bold text-gray-500 bg-gray-50 border border-gray-100 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-smash-blue/20 placeholder-gray-300 transition-all uppercase tracking-widest">
                        </div>
                        <div class="flex items-center bg-gray-50 rounded-2xl p-1 shrink-0 border border-gray-100">
                            <button @click="decreaseQty(index)" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-smash-blue transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"/></svg>
                            </button>
                            <span class="w-8 text-center text-xs font-black text-gray-900" x-text="item.quantity"></span>
                            <button @click="increaseQty(index)" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-smash-blue transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                        <button @click="removeItem(index)" class="p-2.5 text-red-300 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </template>

                <!-- Empty Cart -->
                <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center py-20 text-center">
                    <div class="h-20 w-20 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <p class="font-black text-gray-300 uppercase tracking-widest leading-none">Your cart is empty</p>
                </div>
            </div>

            <!-- Total Section -->
            <div class="p-8 bg-white border-t border-gray-100 space-y-6">
                <div class="space-y-3">
                    <div class="flex justify-between text-gray-500 font-bold uppercase tracking-widest text-[11px]">
                        <span>Subtotal</span>
                        <span x-text="formatPrice(subTotal)"></span>
                    </div>

                    <!-- Voucher Section -->
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400">Promo Code</label>
                        <div class="flex space-x-2">
                            <input type="text" x-model="voucherInput" :disabled="appliedVoucher !== null" @keydown.enter.prevent="applyVoucher()" placeholder="Enter code..." class="flex-1 w-full border bg-gray-50/50 border-gray-100 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-4 focus:ring-smash-blue/10 focus:border-smash-blue uppercase font-black text-gray-700 disabled:opacity-50 transition-all shadow-inner">
                            <button x-show="appliedVoucher === null" @click="applyVoucher()" :disabled="!voucherInput || isApplyingVoucher || cart.length === 0" class="px-5 py-2 bg-smash-blue text-white rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-50 hover:transition-all active:scale-95 flex items-center justify-center min-w-[80px]">
                                <span x-show="!isApplyingVoucher">Apply</span>
                                <span x-show="isApplyingVoucher" class="animate-pulse">...</span>
                            </button>
                            <button x-show="appliedVoucher !== null" @click="removeVoucher()" class="px-5 py-2 bg-red-50 text-red-500 hover:text-red-600 border border-red-100 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-100 transition-all active:scale-95 flex items-center justify-center min-w-[80px]">
                                Remove
                            </button>
                        </div>
                        <p x-show="voucherError" x-text="voucherError" class="text-[10px] font-bold text-red-500 mt-1 uppercase tracking-widest"></p>
                    </div>

                    <div x-show="appliedVoucher !== null" class="flex justify-between items-center p-3 bg-green-50/50 rounded-2xl border border-green-100">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-[10px] font-black uppercase tracking-widest text-green-700">Code: <span x-text="appliedVoucher?.code"></span></span>
                        </div>
                        <span class="text-[11px] font-black text-green-600 uppercase tracking-widest" x-text="'- ' + formatPrice(appliedVoucher?.discount_amount)"></span>
                    </div>

                    <!-- Add Discount Toggle -->
                    <button @click="showDiscountModal = true" class="w-full flex items-center justify-between p-3 rounded-2xl border border-dashed border-gray-300 hover:border-smash-blue hover:bg-blue-50 transition-colors group">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-smash-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-500 group-hover:text-smash-blue">Add Discount</span>
                        </div>
                        <span x-show="discountAmount > 0" class="text-[11px] font-black text-red-500 uppercase tracking-widest" x-text="'- ' + formatPrice(discountAmount)"></span>
                    </button>

                    <!-- PB1 Tax Toggle -->
                    <label class="w-full flex items-center justify-between p-3 rounded-2xl border border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer">
                        <div class="flex items-center space-x-3">
                            <div class="relative flex items-center">
                                <input type="checkbox" x-model="applyTax" class="peer sr-only">
                                <div class="w-10 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-smash-blue"></div>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-600">PB1 Tax (10%)</span>
                        </div>
                        <span x-show="taxAmount > 0" class="text-[11px] font-black text-gray-500 uppercase tracking-widest" x-text="'+ ' + formatPrice(taxAmount)"></span>
                    </label>

                </div>
                <div class="flex justify-between items-end border-t border-gray-50 pt-6">
                    <div>
                        <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-none">Grand Total</p>
                        <p class="text-3xl font-black text-smash-blue tracking-tighter mt-2" x-text="formatPrice(grandTotal)"></p>
                    </div>
                </div>
                <button @click="openCheckout()" :disabled="cart.length === 0"
                    class="w-full py-5 bg-smash-blue text-white rounded-3xl font-black text-base uppercase tracking-widest shadow-2xl shadow-smash-blue/30 hover:bg-blue-700 transition-all transform hover:-translate-y-1 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                    PAY NOW
                </button>
            </div>
        </div>

        <!-- Checkout Slide-over Modal -->
        <div x-show="showCheckout" class="fixed inset-0 overflow-hidden z-[100]" style="display: none;">
            <div class="absolute inset-0 overflow-hidden">
                <div x-show="showCheckout" x-transition.opacity class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showCheckout = false"></div>
                <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                    <div x-show="showCheckout" x-transition:enter="transform transition ease-in-out duration-500" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" 
                        class="w-screen max-w-md">
                        <div class="h-full flex flex-col bg-white shadow-2xl">
                            <div class="p-8 bg-smash-blue text-white">
                                <div class="flex items-center justify-between mb-2">
                                    <h2 class="text-3xl font-black uppercase tracking-tighter leading-none">Checkout</h2>
                                    <button @click="showCheckout = false" class="p-2 hover:bg-white/10 rounded-xl transition-all">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <p class="text-blue-100 font-bold uppercase tracking-widest text-[11px]">Finalize Transaction</p>
                            </div>

                            <div class="flex-1 overflow-y-auto p-8 space-y-10">
                                <div>
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-4">Select Payment Method</label>
                                    <div class="grid grid-cols-2 gap-4 text-center">
                                        <button @click="paymentMethod = 'Cash'" 
                                            :class="paymentMethod === 'Cash' ? 'border-smash-blue bg-blue-50 text-smash-blue ring-4 ring-smash-blue/10' : 'border-gray-100 bg-white text-gray-500 hover:border-smash-blue/30'"
                                            class="p-6 rounded-[2.5rem] border-2 transition-all group flex flex-col items-center gap-3">
                                            <div :class="paymentMethod === 'Cash' ? 'bg-smash-blue text-white shadow-lg' : 'bg-gray-50 text-gray-400'" class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all">
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                            </div>
                                            <span class="font-black uppercase tracking-widest text-[12px]">CASH</span>
                                        </button>
                                        <button @click="paymentMethod = 'QRIS'"
                                            :class="paymentMethod === 'QRIS' ? 'border-smash-blue bg-blue-50 text-smash-blue ring-4 ring-smash-blue/10' : 'border-gray-100 bg-white text-gray-500 hover:border-smash-blue/30'"
                                            class="p-6 rounded-[2.5rem] border-2 transition-all group flex flex-col items-center gap-3">
                                            <div :class="paymentMethod === 'QRIS' ? 'bg-smash-blue text-white shadow-lg' : 'bg-gray-50 text-gray-400'" class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all">
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                                </svg>
                                            </div>
                                            <span class="font-black uppercase tracking-widest text-[12px]">QRIS</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Cash Calculator -->
                                <div x-show="paymentMethod === 'Cash'" x-transition x-cloak class="bg-blue-50/50 rounded-[2.5rem] p-8 border border-blue-100/50 space-y-6">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-[11px] font-black text-smash-blue uppercase tracking-widest">Cash Calculator</h3>
                                        <span class="px-2 py-1 bg-white border border-blue-100 rounded-lg text-[9px] font-black text-smash-blue uppercase tracking-widest">Auto Change</span>
                                    </div>
                                    
                                    <!-- Quick Cash -->
                                    <div class="grid grid-cols-2 gap-3 text-[11px] font-black tracking-widest uppercase">
                                        <button @click="cashReceived = grandTotal" class="py-3 px-2 bg-white border border-blue-100 rounded-2xl hover:bg-smash-blue hover:text-white transition-all text-smash-blue shadow-sm">Uang Pas</button>
                                        <button @click="cashReceived = 20000" class="py-3 px-2 bg-white border border-blue-100 rounded-2xl hover:bg-smash-blue hover:text-white transition-all text-gray-600 shadow-sm">20.000</button>
                                        <button @click="cashReceived = 50000" class="py-3 px-2 bg-white border border-blue-100 rounded-2xl hover:bg-smash-blue hover:text-white transition-all text-gray-600 shadow-sm">50.000</button>
                                        <button @click="cashReceived = 100000" class="py-3 px-2 bg-white border border-blue-100 rounded-2xl hover:bg-smash-blue hover:text-white transition-all text-gray-600 shadow-sm">100.000</button>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Cash Received</label>
                                            <div class="relative">
                                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-gray-400">Rp</span>
                                                <input type="number" x-model.number="cashReceived" class="w-full pl-11 pr-4 py-4 bg-white border-2 border-transparent focus:border-smash-blue rounded-3xl font-black text-lg text-gray-900 shadow-sm transition-all focus:outline-none" placeholder="0">
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-center py-4 border-t border-blue-100/50">
                                            <span class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Change</span>
                                            <span class="text-3xl font-black tracking-tighter" :class="changeAmount < 0 ? 'text-red-500' : 'text-green-500'" x-text="formatPrice(Math.max(0, changeAmount))"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Summary -->
                                <div class="bg-gray-50 rounded-[2.5rem] p-8 space-y-6">
                                    <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Order Summary</h3>
                                    <div class="space-y-3">
                                        <template x-for="item in cart" :key="item.id">
                                            <div class="flex justify-between items-center text-[13px] font-bold">
                                                <span class="text-gray-900" x-text="item.quantity + 'x ' + item.name"></span>
                                                <span class="text-gray-500" x-text="formatPrice(item.selling_price * item.quantity)"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="border-t border-gray-200 pt-4 space-y-3">
                                        <div class="flex justify-between items-center text-[11px] font-bold text-gray-500 uppercase tracking-widest">
                                            <span>Subtotal</span>
                                            <span x-text="formatPrice(subTotal)"></span>
                                        </div>
                                        <div x-show="discountAmount > 0" class="flex justify-between items-center text-[11px] font-bold text-red-500 uppercase tracking-widest">
                                            <span>Discount</span>
                                            <span x-text="'- ' + formatPrice(discountAmount)"></span>
                                        </div>
                                        <div x-show="taxAmount > 0" class="flex justify-between items-center text-[11px] font-bold text-gray-500 uppercase tracking-widest">
                                            <span>PB1 Tax (10%)</span>
                                            <span x-text="'+ ' + formatPrice(taxAmount)"></span>
                                        </div>
                                    </div>
                                    <div class="border-t border-gray-200 pt-6 flex justify-between items-baseline">
                                        <span class="font-black text-gray-900 uppercase tracking-widest text-xs">Grand Total</span>
                                        <span class="text-3xl font-black text-smash-blue tracking-tighter" x-text="formatPrice(grandTotal)"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-8 border-t border-gray-100 bg-white">
                                <button @click="processCheckout()" :disabled="isProcessing || cart.length === 0 || (paymentMethod === 'Cash' && (cashReceived === '' || cashReceived < grandTotal))"
                                    class="w-full py-5 bg-smash-blue text-white rounded-3xl font-black text-base uppercase tracking-widest shadow-2xl shadow-smash-blue/30 hover:bg-blue-700 transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center space-x-3 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                                    <template x-if="!isProcessing">
                                        <div class="flex items-center">
                                            <span>COMPLETE ORDER</span>
                                            <svg class="w-6 h-6 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                        </div>
                                    </template>
                                    <template x-if="isProcessing">
                                        <div class="flex items-center">
                                            <svg class="animate-spin h-6 w-6 text-white mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <span>PROCESSING...</span>
                                        </div>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('partials.sidebar', ['isOverlay' => true])

        <!-- Discount Slide-over Modal -->
        <div x-show="showDiscountModal" class="fixed inset-0 overflow-hidden z-[110]" style="display: none;" x-cloak>
            <div class="absolute inset-0 overflow-hidden">
                <div x-show="showDiscountModal" x-transition.opacity class="absolute inset-0 bg-gray-900/30 backdrop-blur-sm" @click="showDiscountModal = false"></div>
                <div class="fixed inset-y-0 right-0 max-w-sm w-full flex">
                    <div x-show="showDiscountModal" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" 
                        class="w-full h-full bg-white shadow-2xl flex flex-col">
                        
                        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-lg font-black text-gray-900 uppercase tracking-widest">Set Discount</h3>
                            <button @click="showDiscountModal = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        
                        <div class="p-6 space-y-6 flex-1">
                            <div>
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-4">Discount Type</label>
                                <div class="grid grid-cols-2 gap-2 p-1 bg-gray-100 rounded-2xl">
                                    <button @click="discountType = 'percentage'; discountValue = 0" :class="discountType === 'percentage' ? 'bg-white shadow-sm text-smash-blue' : 'text-gray-500 hover:text-gray-700'" class="py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all">Percentage %</button>
                                    <button @click="discountType = 'nominal'; discountValue = 0" :class="discountType === 'nominal' ? 'bg-white shadow-sm text-smash-blue' : 'text-gray-500 hover:text-gray-700'" class="py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all">Nominal Rp</button>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Discount Value</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-gray-400" x-text="discountType === 'percentage' ? '%' : 'Rp'"></span>
                                    <input type="number" x-model.number="discountValue" class="w-full pl-12 pr-4 py-4 bg-gray-50 border-2 border-transparent focus:bg-white focus:border-smash-blue rounded-3xl font-black text-lg text-gray-900 transition-all focus:outline-none" placeholder="0">
                                </div>
                            </div>

                            <button @click="discountType = null; discountValue = 0; showDiscountModal = false" class="w-full py-4 bg-red-50 text-red-500 rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-red-100 transition-all">Remove Discount</button>
                        </div>

                        <div class="p-6 border-t border-gray-100">
                            <button @click="showDiscountModal = false" class="w-full py-4 bg-smash-blue text-white rounded-3xl font-black text-sm uppercase tracking-widest hover:transition-all">Done</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fully Branded QRIS Modal (v5) -->
        <div x-show="showQrisModal" class="fixed inset-0 z-[100000] flex items-center justify-center p-4 md:pr-[420px]" style="display: none;" x-cloak>
            <!-- Heavy Backdrop for Focus -->
            <div x-show="showQrisModal" x-transition.opacity 
                class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" 
                @click="if(!isProcessing){ showQrisModal = false; resetPosState(); }">
            </div>
            
            <!-- Modal Body: Sidebar Consistency -->
            <div x-show="showQrisModal" 
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.stop
                class="relative bg-white rounded-[3rem] shadow-2xl flex flex-col w-full max-w-[420px] max-h-[90vh] overflow-hidden"
                style="width: 420px;">
                
                <!-- Branded Header (Matches Discount Modal) -->
                <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-white shrink-0">
                    <h3 class="text-lg font-black text-gray-900 uppercase tracking-widest">Pembayaran QRIS</h3>
                    <button @click="showQrisModal = false" class="text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <!-- Scrollable Body -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6 scrollbar-hide bg-white">
                    
                    <!-- Branded Amount Container (Matches Order Summary) -->
                    <div class="bg-gray-50 rounded-[1rem] p-3 space-y-1 text-center">
                        <p class="text-4xl font-black text-smash-blue tracking-tighter" x-text="formatPrice(qrisTotalAmount)"></p>
                        <div class="inline-block px-2 py-1 bg-white border border-gray-200 rounded-full mt-2">
                             <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest" x-text="qrisInvoiceNumber"></span>
                        </div>
                    </div>

                    <!-- Centered QR -->
                    <div class="flex flex-col items-center space-y-4">
                        <template x-if="qrisLoading">
                            <div class="w-[200px] h-[200px] bg-gray-50 rounded-[2.5rem] flex flex-col items-center justify-center space-y-3 border-2 border-dashed border-gray-200">
                                <svg class="animate-spin h-6 w-6 text-smash-blue" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"></path></svg>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Memuat QRIS...</p>
                            </div>
                        </template>
                        
                        <div id="qris-code-container" 
                            x-show="!qrisLoading && qrisString"
                            class="bg-white p-2 rounded-[1.5rem] shadow-xl border border-gray-100 flex items-center justify-center">
                        </div>
                    </div>

                    <!-- Branded Input -->
                    <div class="space-y-3">
                        <input type="text" x-model="paymentReference" placeholder="E.G: REF-12345 / BUDI" 
                            class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent focus:bg-white focus:border-smash-blue rounded-3xl font-black text-xs text-gray-900 transition-all focus:outline-none uppercase tracking-widest placeholder-gray-300">
                    </div>
                </div>

                <!-- Footer Action (Exact Sidebar Match) -->
                <div class="p-8 border-t border-gray-100 bg-white shrink-0">
                    <button @click="confirmQrisPayment()" :disabled="isProcessing" 
                        class="w-full py-3 bg-smash-blue text-white rounded-2xl font-black text-base uppercase tracking-widest shadow-2xl shadow-smash-blue/30 hover:bg-blue-700 transition-all transform hover:-translate-y-1 active:scale-95 disabled:opacity-50 disabled:transform-none flex items-center justify-center">
                        <span x-show="!isProcessing">KONFIRMASI BERHASIL</span>
                        <span x-show="isProcessing" class="flex items-center">
                            <svg class="animate-spin h-6 w-6 text-white mr-3" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"></path></svg>
                            MEMPROSES...
                        </span>
                    </button>
                    
                    <button @click="showQrisModal = false" :disabled="isProcessing" 
                        class="w-full mt-4 text-gray-400 hover:text-red-500 text-[10px] font-black uppercase tracking-widest transition-all">
                        BATAL / KEMBALI
                    </button>
                </div>

            </div>
        </div>
    </div>

    </div>

</body>
</html>
