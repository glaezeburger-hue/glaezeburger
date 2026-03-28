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
                showVariationModal: false,
                activeProduct: null,
                activeVariations: {},
                variationError: '',
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
                lastCashReceived: 0,
                lastChangeAmount: 0,
                audioCtx: null,

                // Bluetooth Printer State
                printer: null,
                printerConnected: false,
                printerConnecting: false,
                printerDropdownOpen: false,
                printerName: 'No Printer',
                lastPrinterName: localStorage.getItem('lastPrinterName') || null,
                showPrinterSafetyModal: false,
                pairedPrinters: [],
                autoPrintAfterPay: true,
                savedLogo: localStorage.getItem('printerLogoDataUrl') || null,
                isPrintingReceipt: false,
                printProgress: 0,

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
                    if (product.variation_groups && product.variation_groups.length > 0) {
                        this.openVariationModal(product);
                        return;
                    }
                    this.processAddToCart(product, null);
                },

                processAddToCart(product, variations) {
                    const cartKey = this.generateCartItemKey(product, variations);
                    const existingIndex = this.cart.findIndex(item => item.cartKey === cartKey);
                    const availability = this.checkStockAvailability(product, 1);
                    
                    if (existingIndex > -1) {
                        if (availability.canAdd) {
                            this.cart[existingIndex].quantity++;
                            this.playAddSound();
                            this.showVariationModal = false;
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
                                ...JSON.parse(JSON.stringify(product)), 
                                selling_price: parseFloat(product.selling_price),
                                quantity: 1, 
                                notes: '',
                                cartKey: cartKey,
                                variations: variations
                            });
                            this.playAddSound();
                            this.showVariationModal = false;
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

                generateCartItemKey(product, variations) {
                    if (!variations || Object.keys(variations).length === 0) return product.id.toString();
                    
                    let keyStr = product.id.toString() + '-';
                    const groupIds = Object.keys(variations).sort();
                    
                    groupIds.forEach(gid => {
                        keyStr += `g${gid}:`;
                        const selected = [...variations[gid].selected].sort();
                        if(selected.length > 0) {
                            keyStr += selected.join(',') + '-';
                        } else {
                            keyStr += 'none-';
                        }
                    });
                    
                    return keyStr;
                },

                openVariationModal(product) {
                    this.activeProduct = JSON.parse(JSON.stringify(product));
                    this.activeVariations = {};
                    this.variationError = '';
                    
                    if(this.activeProduct.variation_groups) {
                        this.activeProduct.variation_groups.forEach(group => {
                            this.activeVariations[group.id] = {
                                type: group.type,
                                is_required: group.is_required,
                                selected: []
                            };
                            
                            if(group.options) {
                                group.options.forEach(opt => {
                                    // Make sure active status is checked if needed, but for now just check is_default
                                    if(opt.is_default && opt.is_active) {
                                        this.activeVariations[group.id].selected.push(opt.id);
                                    }
                                });
                            }
                        });
                    }
                    
                    this.showVariationModal = true;
                },

                get variationSubtotal() {
                    if(!this.activeProduct || !this.activeProduct.variation_groups) return 0;
                    let total = parseFloat(this.activeProduct.selling_price);
                    
                    this.activeProduct.variation_groups.forEach(group => {
                        const selected = this.activeVariations[group.id]?.selected || [];
                        selected.forEach(optId => {
                            const opt = group.options.find(o => o.id === optId);
                            if(opt && opt.price_modifier) {
                                total += parseFloat(opt.price_modifier);
                            }
                        });
                    });
                    
                    return total;
                },

                toggleVariationOption(groupId, optionId) {
                    this.variationError = '';
                    const groupState = this.activeVariations[groupId];
                    if (!groupState) return;

                    if (groupState.type === 'single') {
                        groupState.selected = [optionId];
                    } else {
                        const idx = groupState.selected.indexOf(optionId);
                        if (idx > -1) {
                            groupState.selected.splice(idx, 1);
                        } else {
                            groupState.selected.push(optionId);
                        }
                    }
                },

                confirmVariationSelection() {
                    for(const groupId in this.activeVariations) {
                        const groupState = this.activeVariations[groupId];
                        const groupData = this.activeProduct.variation_groups.find(g => g.id == groupId);
                        if(groupState.is_required && groupState.selected.length === 0) {
                            this.variationError = `Ops! Pilih setidaknya satu opsi untuk ${groupData.name}.`;
                            return;
                        }
                    }
                    
                    this.processAddToCart(this.activeProduct, JSON.parse(JSON.stringify(this.activeVariations)));
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
                    return this.cart.reduce((sum, item) => {
                        let itemPrice = parseFloat(item.selling_price);
                        if (item.variations && item.variation_groups) {
                            item.variation_groups.forEach(group => {
                                const selected = item.variations[group.id]?.selected || [];
                                selected.forEach(optId => {
                                    const opt = group.options.find(o => o.id == optId);
                                    if (opt && opt.price_modifier) {
                                        itemPrice += parseFloat(opt.price_modifier);
                                    }
                                });
                            });
                        }
                        return sum + (itemPrice * item.quantity);
                    }, 0);
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

                async loadPairedPrinters() {
                    try {
                        if (navigator.bluetooth && navigator.bluetooth.getDevices) {
                            const devices = await navigator.bluetooth.getDevices();
                            this.pairedPrinters = devices.map(d => ({ id: d.id, name: d.name || 'Unknown Printer' }));
                        }
                    } catch (e) {
                        console.warn('Could not load paired printers', e);
                    }
                },

                async processCheckout(skipPrinterCheck = false) {
                    if (!this.printerConnected && !skipPrinterCheck) {
                        await this.loadPairedPrinters();
                        this.showPrinterSafetyModal = true;
                        return; // Halt checkout and wait for modal action
                    }

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

                                // Store ID and transient payment data for re-printing later
                                this.lastTransactionId = data.data.transaction_id;
                                this.lastCashReceived = this.cashReceived;
                                this.lastChangeAmount = this.changeAmount;

                                // Automatically trigger receipt print in popup
                                this.printReceipt(this.lastTransactionId, {
                                    cash_received: this.lastCashReceived,
                                    change_amount: this.lastChangeAmount
                                });

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
                
                async resetPosState() {
                    this.cart = [];
                    this.cashReceived = 0;
                    this.discountType = null;
                    this.discountValue = 0;
                    this.applyTax = false;
                    this.showCheckout = false;
                    this.showQrisModal = false;
                    this.paymentReference = '';
                    this.appliedVoucher = null;
                    this.voucherInput = '';
                    this.voucherError = '';
                    this.searchQuery = '';
                    this.selectedCategory = '';
                    this.paymentMethod = 'Cash';

                    // Soft-refresh stock data via AJAX (keeps Bluetooth alive)
                    try {
                        const res = await fetch('{{ route("pos.refresh-stock") }}');
                        const freshData = await res.json();
                        this.allProducts = freshData.products;
                        this.rawMaterials = freshData.rawMaterials;
                        this.filterProducts();
                    } catch (e) {
                        console.warn('Stock refresh failed, falling back to page reload', e);
                        location.reload();
                    }
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
                
                async printReceipt(transactionId, extraData = {}) {
                    // 1. Try Bluetooth Direct Print first
                    if (this.printerConnected && this.printer) {
                        try {
                            this.isPrintingReceipt = true;
                            this.printProgress = 0;

                            const response = await fetch(`{{ url('/transactions') }}/${transactionId}/receipt-data`);
                            const data = await response.json();
                            
                            // Merge extra data (cashReceived, changeAmount) from Alpine state
                            Object.assign(data, extraData);
                            // Attach logo if exists
                            data.logo_url = this.savedLogo;
                            
                            const printResult = await this.printer.printReceipt(data, (percent) => {
                                this.printProgress = percent;
                            });

                            this.isPrintingReceipt = false;
                            this.printProgress = 0;

                            if (printResult.success) {
                                return; // Successfully printed via Bluetooth
                            } else {
                                console.warn('Bluetooth print failed, falling back to browser print', printResult.error);
                            }
                        } catch (e) {
                            console.error('Error fetching receipt data', e);
                            this.isPrintingReceipt = false;
                            this.printProgress = 0;
                        }
                    }

                    // 2. Fallback to Browser Print Window
                    const width = 300; 
                    const height = 500;
                    const left = (screen.width / 2) - (width / 2);
                    const top = (screen.height / 2) - (height / 2);
                    
                    window.open(
                        `{{ url('/transactions') }}/${transactionId}/receipt`,
                        'ReceiptWindow',
                        `width=${width},height=${height},top=${top},left=${left},scrollbars=yes,status=no,resizable=yes`
                    );
                },

                // --- Printer Methods ---
                async connectPrinter() {
                    try {
                        this.printerConnecting = true;
                        
                        if (!this.printer) {
                            this.printer = new BluetoothPrinter();
                        }
                        
                        await this.printer.connect();
                        
                        this.printerConnected = true;
                        this.printerName = this.printer.device.name || 'Bluetooth Printer';
                        
                        Swal.fire({
                            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                            icon: 'success', title: 'Printer Connected'
                        });
                        
                        if (this.showPrinterSafetyModal) {
                            this.showPrinterSafetyModal = false;
                            this.processCheckout(true);
                        }
                        
                    } catch (error) {
                        console.error("Printer connection failed:", error);
                        Swal.fire({
                            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                            icon: 'error', title: 'Connection Failed', text: error.message
                        });
                        this.printerConnected = false;
                        this.printerName = 'No Printer';
                    } finally {
                        this.printerConnecting = false;
                        if (this.printerConnected) {
                            this.lastPrinterName = this.printerName;
                        }
                    }
                },

                async connectToSpecificPrinter(deviceId) {
                    try {
                        this.printerConnecting = true;
                        
                        if (!this.printer) {
                            this.printer = new BluetoothPrinter();
                        }

                        const result = await this.printer.autoConnect(deviceId);

                        if (result.success) {
                            this.printerConnected = true;
                            this.printerName = result.name;
                            this.lastPrinterName = result.name;
                            
                            Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'success', title: 'Printer Terhubung Otomatis' });
                            
                            if (this.showPrinterSafetyModal) {
                                this.showPrinterSafetyModal = false;
                                // Lanjutkan checkout
                                this.processCheckout(this.printerConnected); // Lolos check karena printerConnected = true
                            }
                        } else {
                            throw new Error(result.error || "Gagal terhubung ke printer.");
                        }
                    } catch (error) {
                        console.error("Direct connect failed:", error);
                        Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'error', title: 'Koneksi Gagal', text: error.message });
                        this.printerConnected = false;
                        this.printerName = 'No Printer';
                    } finally {
                        this.printerConnecting = false;
                    }
                },

                async autoConnectPrinter() {
                    try {
                        this.printerConnecting = true;
                        
                        if (!this.printer) {
                            this.printer = new BluetoothPrinter();
                        }
                        
                        const result = await this.printer.autoConnect();
                        
                        if (result.success) {
                            this.printerConnected = true;
                            this.printerName = result.name;
                            this.lastPrinterName = result.name;
                            Swal.fire({
                                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                                icon: 'success', title: 'Printer Terhubung Otomatis'
                            });
                        } else {
                            throw new Error(result.error);
                        }
                    } catch (error) {
                        console.error("Auto-connect failed:", error);
                        Swal.fire({
                            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                            icon: 'error', title: 'Gagal Re-connect', text: error.message
                        });
                        this.printerConnected = false;
                        this.printerName = 'No Printer';
                    } finally {
                        this.printerConnecting = false;
                    }
                },

                async disconnectPrinter() {
                    if (this.printer) {
                        await this.printer.disconnect();
                    }
                    this.printerConnected = false;
                    this.printerName = 'No Printer';
                },

                handleLogoUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.savedLogo = e.target.result;
                        localStorage.setItem('printerLogoDataUrl', this.savedLogo);
                    };
                    reader.readAsDataURL(file);
                },

                clearLogo() {
                    this.savedLogo = null;
                    localStorage.removeItem('printerLogoDataUrl');
                },

                async testPrint() {
                    if (!this.printerConnected) return;
                    
                    this.isPrintingReceipt = true;
                    this.printProgress = 0;
                    
                    const testData = {
                        invoice_number: 'TEST-001',
                        created_at: new Date().toLocaleString('id-ID'),
                        cashier_name: 'Test Cashier',
                        items: [
                            { quantity: 1, product: { name: 'Smash Burger (Test)' }, selling_price: 35000, subtotal: 35000 },
                            { quantity: 2, product: { name: 'Fries (Test)' }, selling_price: 15000, subtotal: 30000 }
                        ],
                        subtotal: 65000,
                        discount_amount: 0,
                        tax_amount: 6500,
                        grand_total: 71500,
                        payment_method: 'Cash',
                        cash_received: 100000,
                        change_amount: 28500,
                        logo_url: this.savedLogo
                    };
                    const result = await this.printer.printReceipt(testData, (percent) => {
                        this.printProgress = percent;
                    });
                    
                    this.isPrintingReceipt = false;
                    this.printProgress = 0;

                    if (result.success) {
                        Swal.fire({ icon: 'success', title: 'Test Print OK!', text: 'Struk test berhasil dicetak.', timer: 2000, showConfirmButton: false, customClass: { popup: 'rounded-2xl' } });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Test Print Gagal', text: result.error, confirmButtonColor: '#0A56C8' });
                    }
                },

                printLastReceipt() {
                    if (this.lastTransactionId) {
                        this.printReceipt(this.lastTransactionId, {
                            cash_received: this.lastCashReceived,
                            change_amount: this.lastChangeAmount
                        });
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

                    <!-- Shift Controls & Printer Dropdown in Header -->
                    <div class="flex items-center space-x-3">
                        <!-- Auto-Reconnect Reminder Prompt -->
                        <template x-if="lastPrinterName && !printerConnected">
                            <button @click="autoConnectPrinter()" 
                                :disabled="printerConnecting"
                                class="px-4 py-2 bg-yellow-400 text-yellow-900 border border-yellow-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-yellow-500 transition-all flex items-center gap-2 shadow-sm animate-pulse disabled:opacity-50 disabled:animate-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <span>Re-connect to <span x-text="lastPrinterName"></span>?</span>
                            </button>
                        </template>

                        <!-- Printer Indicator & Dropdown -->
                        <div class="relative" @click.away="printerDropdownOpen = false">
                            <button @click="printerDropdownOpen = !printerDropdownOpen" 
                                class="relative p-2.5 rounded-xl bg-gray-50 text-gray-400 hover:text-smash-blue hover:bg-blue-50 transition-all border border-transparent hover:border-blue-100 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                
                                <span :class="{
                                        'bg-red-500': !printerConnected && !printerConnecting,
                                        'bg-green-500': printerConnected,
                                        'bg-yellow-400 animate-pulse': printerConnecting
                                    }"
                                    class="absolute top-1 right-1 block h-2.5 w-2.5 rounded-full ring-2 ring-white transition-colors"></span>
                            </button>

                            <!-- Dropdown Popover -->
                            <div x-show="printerDropdownOpen"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-1"
                                x-cloak
                                class="absolute right-0 mt-3 w-72 bg-white rounded-2xl shadow-2xl shadow-gray-200/80 border border-gray-100 z-50 overflow-hidden">

                                <!-- Header -->
                                <div class="px-5 pt-5 pb-4">
                                    <div class="flex items-center gap-3 mb-1">
                                        <div :class="printerConnected ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'"
                                            class="p-2 rounded-xl transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.25 7.034V3.375"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-gray-900 uppercase tracking-wider">Printer</p>
                                            <p class="text-[11px] text-gray-400 font-medium" x-text="printerConnected ? printerName : 'Not Connected'"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-t border-gray-50 px-5 py-4 space-y-3">
                                    <template x-if="!printerConnected">
                                        <button @click="connectPrinter()"
                                            :disabled="printerConnecting"
                                            :class="printerConnecting ? 'opacity-60 cursor-wait' : 'hover:bg-blue-700'"
                                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-smash-blue text-white rounded-xl text-[11px] font-black uppercase tracking-widest transition-all">
                                            <template x-if="printerConnecting">
                                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                            </template>
                                            <template x-if="!printerConnecting">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m9.193-9.193l1.757-1.757a4.5 4.5 0 016.364 6.364l-4.5 4.5a4.5 4.5 0 01-7.244-1.242"/>
                                                </svg>
                                            </template>
                                            <span x-text="printerConnecting ? 'Connecting...' : 'Connect Printer'"></span>
                                        </button>
                                    </template>

                                    <template x-if="printerConnected">
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-2 px-3 py-2 bg-green-50 rounded-xl border border-green-100">
                                                <span class="relative flex h-2.5 w-2.5">
                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                                                </span>
                                                <span class="text-[11px] font-bold text-green-700">Connected</span>
                                            </div>

                                            <button @click="testPrint()"
                                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-50 text-gray-700 rounded-xl text-[11px] font-black uppercase tracking-widest hover:bg-gray-100 border border-gray-100 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                                </svg>
                                                Cetak Test Struk
                                            </button>

                                            <button @click="disconnectPrinter()"
                                                class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-white text-red-500 rounded-xl text-[11px] font-bold uppercase tracking-widest hover:bg-red-50 border border-red-200 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                Disconnect
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <!-- Printer Logo Upload -->
                                <div class="border-t border-gray-50 px-5 py-4">
                                    <label class="block text-[11px] font-bold text-gray-700 mb-2">Printer Logo (Optional)</label>
                                    <div class="flex items-center space-x-2">
                                        <input type="file" @change="handleLogoUpload" accept="image/*" class="hidden" id="logo-upload">
                                        <button @click="document.getElementById('logo-upload').click()"
                                            class="flex-1 px-4 py-2.5 bg-gray-50 text-gray-700 rounded-xl text-[11px] font-black uppercase tracking-widest hover:bg-gray-100 border border-gray-100 transition-all">
                                            <template x-if="savedLogo"><span>Change Logo</span></template>
                                            <template x-if="!savedLogo"><span>Upload Logo</span></template>
                                        </button>
                                        <template x-if="savedLogo">
                                            <button @click="clearLogo()" class="p-2.5 bg-red-50 text-red-500 rounded-xl hover:bg-red-100 border border-red-100 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </template>
                                    </div>
                                    <template x-if="savedLogo">
                                        <p class="text-[10px] text-gray-400 mt-2">Logo loaded. Will be printed on receipts.</p>
                                    </template>
                                </div>

                                <!-- Auto-print Toggle -->
                                <div class="border-t border-gray-50 px-5 py-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-[11px] font-bold text-gray-700">Auto-print setelah bayar</p>
                                            <p class="text-[10px] text-gray-400">Cetak struk otomatis</p>
                                        </div>
                                        <button @click="autoPrintAfterPay = !autoPrintAfterPay" type="button"
                                            :class="autoPrintAfterPay ? 'bg-smash-blue' : 'bg-gray-200'"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                                            <span :class="autoPrintAfterPay ? 'translate-x-5' : 'translate-x-0'"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

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

        <!-- Variation Selection Modal -->
        <div x-show="showVariationModal" class="fixed inset-0 z-[60] overflow-hidden flex items-center justify-center" style="display: none;">
            <!-- Backdrop -->
            <div x-show="showVariationModal" x-transition.opacity @click="showVariationModal = false" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

            <!-- Modal Content -->
            <div x-show="showVariationModal" 
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-8 scale-95"
                class="relative w-full max-w-lg mx-4 bg-white rounded-3xl shadow-2xl flex flex-col max-h-[90vh]">
                
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0 bg-gray-50/50 rounded-t-3xl">
                    <div>
                        <h2 class="text-lg font-black text-gray-900 uppercase tracking-tighter" x-text="activeProduct?.name || 'Pilih Variasi'"></h2>
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-widest mt-0.5">Customization Required</p>
                    </div>
                    <button @click="showVariationModal = false" class="p-2 text-gray-400 hover:text-gray-900 bg-white border border-gray-100 hover:border-gray-200 rounded-xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                    <template x-if="variationError">
                        <div class="p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center gap-3">
                            <svg class="h-5 w-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="text-[11px] font-black text-red-800 uppercase tracking-widest" x-text="variationError"></span>
                        </div>
                    </template>

                    <template x-if="activeProduct?.variation_groups" x-for="group in activeProduct.variation_groups" :key="group.id">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-[13px] font-black text-gray-900 uppercase tracking-widest flex items-center gap-2">
                                    <span x-text="group.name"></span>
                                    <template x-if="group.is_required">
                                        <span class="px-1.5 py-0.5 bg-red-50 text-red-500 text-[9px] rounded uppercase">Wajib</span>
                                    </template>
                                </h3>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest bg-gray-50 px-2 py-1 rounded-lg" x-text="group.type === 'single' ? 'Pilih 1' : 'Pilih Banyak'"></span>
                            </div>

                            <div class="grid grid-cols-1 gap-2">
                                <template x-for="opt in group.options" :key="opt.id">
                                    <template x-if="opt.is_active">
                                        <label class="flex items-center justify-between p-3 border rounded-xl cursor-pointer transition-all hover:bg-gray-50 group/opt"
                                            :class="activeVariations[group.id]?.selected.includes(opt.id) ? 'border-smash-blue bg-blue-50/30' : 'border-gray-100'">
                                            <div class="flex items-center gap-3">
                                                <!-- Radio/Checkbox visual -->
                                                <div class="flex-shrink-0 flex items-center justify-center transition-colors"
                                                    :class="[
                                                        group.type === 'single' ? 'w-5 h-5 rounded-full border-2' : 'w-5 h-5 rounded-[4px] border-2',
                                                        activeVariations[group.id]?.selected.includes(opt.id) ? 'border-smash-blue bg-smash-blue text-white' : 'border-gray-300'
                                                    ]">
                                                    <svg x-show="activeVariations[group.id]?.selected.includes(opt.id)" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                </div>
                                                <span class="text-sm font-black text-gray-700 uppercase tracking-tight" x-text="opt.name"></span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <template x-if="opt.price_modifier > 0">
                                                    <span class="text-[11px] font-black text-smash-blue" x-text="'+' + formatPrice(opt.price_modifier)"></span>
                                                </template>
                                            </div>
                                            <!-- Hidden input -->
                                            <input type="checkbox" class="hidden" @change="toggleVariationOption(group.id, opt.id)" :checked="activeVariations[group.id]?.selected.includes(opt.id)">
                                        </label>
                                    </template>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Modal Footer -->
                <div class="p-6 border-t border-gray-100 bg-white shrink-0 rounded-b-3xl">
                    <button @click="confirmVariationSelection()" class="w-full flex items-center justify-between px-6 py-4 bg-smash-blue text-white rounded-2xl font-black shadow-2xl shadow-smash-blue/30 hover:bg-blue-700 transition-all transform hover:-translate-y-1 active:scale-95 uppercase tracking-widest">
                        <span>Tambah Ke Cart</span>
                        <div class="flex items-center gap-2 bg-black/20 px-3 py-1.5 rounded-xl">
                            <span class="text-[12px]" x-text="formatPrice(variationSubtotal)"></span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </div>
                    </button>
                    <button @click="showVariationModal = false" class="w-full mt-3 py-3 text-[11px] font-black text-gray-400 hover:text-gray-900 uppercase tracking-widest transition-colors">Batal</button>
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
            <header class="p-5 bg-white border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex items-center space-x-3">
                    <button @click="cartOpen = false" class="md:hidden p-2 -ml-2 text-gray-400 hover:text-gray-900 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <div class="flex items-baseline space-x-2">
                        <h2 class="text-lg font-black text-gray-900 leading-none tracking-tighter uppercase">Order</h2>
                        <span x-show="cart.length > 0" x-text="'(' + cart.length + ')'" class="text-sm font-black text-smash-blue"></span>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button x-show="cart.length > 0" @click="clearCart()" class="p-2 text-red-500 bg-red-50 hover:bg-red-100 rounded-xl transition-colors" title="Clear Cart">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                    <button :disabled="!lastTransactionId" @click="printLastReceipt()" :class="!lastTransactionId ? 'opacity-30 cursor-not-allowed' : 'hover:bg-blue-50 hover:text-smash-blue'" class="p-2 text-gray-500 bg-gray-50 rounded-xl transition-colors" title="Print Last Receipt">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    </button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3">
                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="bg-white rounded-2xl p-3 border border-gray-100 shadow-sm flex items-start space-x-3">
                        <div class="h-12 w-12 rounded-xl bg-gray-50 flex-shrink-0 overflow-hidden">
                            <template x-if="item.image_path">
                                <img :src="`{{ asset('storage') }}/${item.image_path}`" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!item.image_path">
                                <div class="w-full h-full flex items-center justify-center text-gray-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0 pt-0.5">
                            <h4 class="text-[13px] font-black text-gray-900 uppercase tracking-tight line-clamp-1 leading-none" x-text="item.name"></h4>
                            
                            <!-- Variations Badges -->
                            <template x-if="item.variations && Object.keys(item.variations).length > 0">
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <template x-for="groupId in Object.keys(item.variations)" :key="groupId">
                                        <template x-for="optId in item.variations[groupId].selected" :key="optId">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-blue-50 text-smash-blue text-[9px] font-black uppercase tracking-wider">
                                                <span x-text="item.variation_groups.find(g => g.id == groupId)?.options.find(o => o.id == optId)?.short_name || item.variation_groups.find(g => g.id == groupId)?.options.find(o => o.id == optId)?.name"></span>
                                            </span>
                                        </template>
                                    </template>
                                </div>
                            </template>

                            <p class="text-smash-blue font-black text-[11px] mt-1" x-text="formatPrice(item.selling_price)"></p>
                            <input type="text" x-model="item.notes" placeholder="Notes (e.g. Extra Spicy)" class="mt-1.5 w-full text-[10px] font-bold text-gray-500 bg-gray-50 border border-gray-100 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-smash-blue/20 placeholder-gray-300 transition-all uppercase tracking-widest">
                        </div>
                        <div class="flex flex-col items-end space-y-2">
                            <div class="flex items-center bg-gray-50 rounded-xl p-0.5 border border-gray-100">
                                <button @click="decreaseQty(index)" class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-smash-blue transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"/></svg>
                                </button>
                                <span class="w-6 text-center text-[11px] font-black text-gray-900" x-text="item.quantity"></span>
                                <button @click="increaseQty(index)" class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-smash-blue transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                            <button @click="removeItem(index)" class="p-1 text-gray-300 hover:text-red-500 transition-colors" title="Remove item">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
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
            <div class="p-5 bg-white border-t border-gray-100 space-y-4" x-data="{ showAdvancedOptions: false }">
                <div class="flex justify-between items-center text-sm font-black uppercase tracking-widest text-gray-800">
                    <span>Subtotal</span>
                    <span x-text="formatPrice(subTotal)"></span>
                </div>

                <!-- Always Visible Summaries (When applicable and advanced is hidden) -->
                <template x-if="!showAdvancedOptions && (appliedVoucher !== null || discountAmount > 0 || taxAmount > 0)">
                    <div class="space-y-2 border-t border-dashed border-gray-100 pt-3">
                        <div x-show="appliedVoucher !== null" class="flex justify-between items-center text-[10px] font-black text-green-600 uppercase tracking-widest">
                            <span>Vch (<span x-text="appliedVoucher?.code"></span>)</span>
                            <span x-text="'- ' + formatPrice(appliedVoucher?.discount_amount)"></span>
                        </div>
                        <div x-show="discountAmount > 0" class="flex justify-between items-center text-[10px] font-black text-red-500 uppercase tracking-widest">
                            <span>Disc</span>
                            <span x-text="'- ' + formatPrice(discountAmount)"></span>
                        </div>
                        <div x-show="taxAmount > 0" class="flex justify-between items-center text-[10px] font-black text-gray-500 uppercase tracking-widest">
                            <span>PB1 (10%)</span>
                            <span x-text="'+ ' + formatPrice(taxAmount)"></span>
                        </div>
                    </div>
                </template>

                <!-- Advanced Options Toggle Button -->
                <button @click="showAdvancedOptions = !showAdvancedOptions" 
                    class="w-full py-1.5 flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:text-smash-blue hover:bg-blue-50 transition-colors">
                    <span class="text-[9px] font-black uppercase tracking-widest" x-text="showAdvancedOptions ? 'Close Options' : 'Add Promo / Discount / Tax'"></span>
                    <svg class="w-3 h-3 ml-1 transition-transform" :class="showAdvancedOptions ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                <!-- Expandable Options Section -->
                <div x-show="showAdvancedOptions" x-transition.opacity class="space-y-3 pt-2">
                    <!-- Voucher Section -->
                    <div class="flex space-x-2">
                        <input type="text" x-model="voucherInput" :disabled="appliedVoucher !== null" @keydown.enter.prevent="applyVoucher()" placeholder="Promo Code..." class="flex-1 w-full border bg-gray-50/50 border-gray-100 rounded-xl px-3 py-2 text-[11px] focus:outline-none focus:ring-2 focus:ring-smash-blue/20 focus:border-smash-blue uppercase font-black text-gray-700 disabled:opacity-50 transition-all">
                        <button x-show="appliedVoucher === null" @click="applyVoucher()" :disabled="!voucherInput || isApplyingVoucher || cart.length === 0" class="px-4 py-2 bg-smash-blue text-white rounded-xl text-[10px] font-black uppercase tracking-widest disabled:opacity-50 hover:bg-blue-700 transition-all flex items-center shrink-0">
                            <span x-show="!isApplyingVoucher">Apply</span>
                            <span x-show="isApplyingVoucher" class="animate-pulse">...</span>
                        </button>
                        <button x-show="appliedVoucher !== null" @click="removeVoucher()" class="px-3 py-2 bg-red-50 text-red-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-100 transition-all flex items-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <p x-show="voucherError" x-text="voucherError" class="text-[9px] font-bold text-red-500 mt-0.5 uppercase tracking-widest"></p>

                    <!-- Add Discount Toggle -->
                    <button @click="showDiscountModal = true" class="w-full flex items-center justify-between p-2.5 rounded-xl border border-gray-200 hover:border-smash-blue hover:bg-blue-50 transition-colors group bg-gray-50/50">
                        <div class="flex items-center space-x-2">
                            <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-smash-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 group-hover:text-smash-blue">Add Discount</span>
                        </div>
                        <span x-show="discountAmount > 0" class="text-[10px] font-black text-red-500 uppercase tracking-widest" x-text="'- ' + formatPrice(discountAmount)"></span>
                    </button>

                    <!-- PB1 Tax Toggle -->
                    <label class="w-full flex items-center justify-between p-2.5 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer bg-gray-50/50">
                        <div class="flex items-center space-x-2">
                            <div class="relative flex items-center">
                                <input type="checkbox" x-model="applyTax" class="peer sr-only">
                                <div class="w-10 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-smash-blue"></div>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-600">PB1 Tax (10%)</span>
                        </div>
                        <span x-show="taxAmount > 0" class="text-[10px] font-black text-gray-500 uppercase tracking-widest" x-text="'+ ' + formatPrice(taxAmount)"></span>
                    </label>
                </div>

                <div class="flex justify-between items-end border-t border-gray-100 pt-4">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">Grand Total</p>
                        <p class="text-3xl font-black text-smash-blue tracking-tighter mt-1 leading-none" x-text="formatPrice(grandTotal)"></p>
                    </div>
                </div>
                
                <button @click="openCheckout()" :disabled="cart.length === 0"
                    class="w-full py-5 mt-2 bg-smash-blue text-white rounded-3xl font-black text-sm uppercase tracking-widest shadow-lg shadow-smash-blue/30 hover:bg-blue-700 transition-all transform hover:-translate-y-1 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
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
        <div x-show="showQrisModal" class="fixed inset-0 flex items-center justify-center p-4 hidden md:flex" style="z-index: 100000; padding-right: 420px; display: none;" x-cloak>
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
                class="relative bg-white shadow-2xl flex flex-col w-full max-w-md max-h-[90vh] overflow-hidden"
                style="border-radius: 3rem;">
                
                <!-- Branded Header -->
                <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-white shrink-0">
                    <h3 class="text-lg font-black text-gray-900 uppercase tracking-widest">Pembayaran QRIS</h3>
                    <button @click="showQrisModal = false; resetPosState()" class="text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <!-- Scrollable Body -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6 scrollbar-hide bg-white">
                    <!-- Branded Amount Container -->
                    <div class="bg-gray-50 rounded-[1rem] p-3 space-y-1 text-center">
                        <p class="text-4xl font-black text-smash-blue tracking-tighter" x-text="formatPrice(qrisTotalAmount)"></p>
                        <div class="inline-block px-2 py-1 bg-white border border-gray-200 rounded-full mt-2">
                             <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest" x-text="qrisInvoiceNumber"></span>
                        </div>
                    </div>

                    <!-- Centered QR -->
                    <div class="flex flex-col items-center space-y-4">
                        <template x-if="qrisLoading">
                            <div class="bg-gray-50 flex flex-col items-center justify-center space-y-3 border-2 border-dashed border-gray-200" style="width: 200px; height: 200px; border-radius: 2.5rem;">
                                <svg class="animate-spin h-6 w-6 text-smash-blue" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"></path></svg>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Memuat QRIS...</p>
                            </div>
                        </template>
                        
                        <div id="qris-code-container" 
                            x-show="!qrisLoading && qrisString"
                            class="bg-white p-2 shadow-xl border border-gray-100 flex items-center justify-center"
                            style="border-radius: 1.5rem;">
                        </div>
                    </div>

                    <!-- Branded Input -->
                    <div class="space-y-3">
                        <input type="text" x-model="paymentReference" placeholder="E.G: REF-12345 / BUDI" 
                            class="w-full px-6 py-4 bg-gray-50 border-2 border-transparent focus:bg-white focus:border-smash-blue rounded-3xl font-black text-xs text-gray-900 transition-all focus:outline-none uppercase tracking-widest placeholder-gray-300">
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="p-8 border-t border-gray-100 bg-white shrink-0">
                    <button @click="confirmQrisPayment()" :disabled="isProcessing" 
                        class="w-full py-3 bg-smash-blue text-white rounded-2xl font-black text-base uppercase tracking-widest shadow-2xl shadow-smash-blue/30 hover:bg-blue-700 transition-all transform hover:-translate-y-1 active:scale-95 disabled:opacity-50 disabled:transform-none flex items-center justify-center">
                        <span x-show="!isProcessing">KONFIRMASI BERHASIL</span>
                        <span x-show="isProcessing" class="flex items-center">
                            <svg class="animate-spin h-6 w-6 text-white mr-3" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"></path></svg>
                            MEMPROSES...
                        </span>
                    </button>
                    
                    <button @click="showQrisModal = false; resetPosState()" :disabled="isProcessing" 
                        class="w-full mt-4 text-gray-400 hover:text-red-500 text-[10px] font-black uppercase tracking-widest transition-all">
                        BATAL / KEMBALI
                    </button>
                </div>
            </div>
        </div>

        <!-- Custom Safety Printer Modal -->
        <div x-show="showPrinterSafetyModal" class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 120000; padding-right: 420px; display: none;" x-cloak>
            <div x-show="showPrinterSafetyModal" x-transition.opacity class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showPrinterSafetyModal = false"></div>
            
            <div x-show="showPrinterSafetyModal" 
                x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                @click.stop
                class="relative bg-white shadow-2xl flex flex-col w-full max-w-sm overflow-hidden" style="border-radius: 2.5rem;">
                
                <div class="p-6 text-center pt-8">
                    <div class="mx-auto w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mb-5 border-[6px] border-white shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 uppercase tracking-widest leading-none mb-2">Printer Terputus</h3>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest leading-relaxed px-4">Pilih printer yang tersedia atau lanjutkan tanpa mecetak struk pembayaran.</p>
                </div>

                <div class="px-6 pb-2">
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl p-2 space-y-1 max-h-[160px] overflow-y-auto">
                        <template x-for="p in pairedPrinters" :key="p.id">
                            <button @click="connectToSpecificPrinter(p.id)" :disabled="printerConnecting" class="w-full text-left px-4 py-3 bg-white hover:bg-smash-blue hover:text-white rounded-xl text-[11px] font-black uppercase tracking-widest border border-gray-100 shadow-sm transition-all group flex items-center justify-between">
                                <span x-text="p.name"></span>
                                <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-200 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </button>
                        </template>

                        <button @click="connectPrinter()" :disabled="printerConnecting" class="w-full text-center px-4 py-3 bg-white hover:bg-gray-100 text-smash-blue rounded-xl text-[11px] font-black uppercase tracking-widest border border-gray-100 border-dashed transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Scan Printer Baru
                        </button>
                    </div>
                </div>

                <div class="p-6 bg-white space-y-3">
                    <button @click="showPrinterSafetyModal = false; processCheckout(true)" :disabled="printerConnecting" class="w-full py-4 bg-gray-900 text-white font-black text-xs uppercase tracking-widest hover:bg-gray-800 transition-all flex items-center justify-center gap-2" style="border-radius: 1.25rem;">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Lanjutkan Tanpa Struk
                    </button>
                    <button @click="showPrinterSafetyModal = false" :disabled="printerConnecting" class="w-full py-4 bg-red-50 text-red-500 font-black text-xs uppercase tracking-widest hover:bg-red-100 transition-all" style="border-radius: 1.25rem;">
                        Batal
                    </button>
                </div>
            </div>
        </div>

        <!-- Printing Progress Overlay -->
        <div x-show="isPrintingReceipt" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-2xl shadow-2xl w-80 p-6 flex flex-col items-center text-center transform scale-100 animate-in zoom-in-95 duration-200">
                <div class="relative w-16 h-16 mb-4 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full border-4 border-gray-100"></div>
                    <!-- Semi-circle spinner -->
                    <svg class="absolute inset-0 w-full h-full text-blue-600 animate-spin" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="46" fill="transparent" stroke="currentColor" stroke-width="8" stroke-dasharray="200" stroke-linecap="round"></circle>
                    </svg>
                    <i class="fas fa-print text-xl text-blue-600"></i>
                </div>
                
                <h3 class="text-lg font-bold text-gray-800 mb-1">Mencetak Struk...</h3>
                <p class="text-sm text-gray-500 font-medium mb-5">Harap tunggu, mengirim data ke printer</p>
                
                <!-- Progress Bar inside overlay -->
                <div class="w-full bg-gray-100 rounded-full h-2.5 mb-2 overflow-hidden shadow-inner flex justify-start">
                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300 ease-out relative" :style="{ width: printProgress + '%' }">
                        <!-- Shimmer effect -->
                        <div class="absolute top-0 inset-x-0 h-full bg-white/30 animate-pulse"></div>
                    </div>
                </div>
                
                <div class="flex justify-between w-full text-xs font-bold text-gray-400">
                    <span>Sending payload</span>
                    <span x-text="printProgress + '%'" class="text-blue-600">0%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Web Bluetooth ESC/POS Service -->
    <script src="{{ asset('js/bluetooth-printer.js') }}"></script>
</body>
</html>
