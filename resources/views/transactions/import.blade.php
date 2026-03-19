@extends('layouts.app')

@section('header', 'Import Historical Transaction')

@section('content')
<div x-data="historicalImport()">
<div class="max-w-4xl mx-auto pb-12">
    <!-- Warning Banner -->
    <div class="bg-orange-50 border border-orange-100 rounded-3xl p-6 flex flex-col sm:flex-row sm:items-center space-y-4 sm:space-y-0 sm:space-x-6 mb-8 group transition-colors hover:bg-orange-100/50">
        <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-orange-500 shadow-sm flex-shrink-0 group-hover:scale-110 transition-transform">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div>
            <h4 class="text-sm font-black text-orange-900 uppercase tracking-widest">Historical Import Mode</h4>
            <p class="text-xs font-bold text-orange-700/80 mt-1 leading-relaxed">Transactions entered here are marked as backdated and <strong>will not deduct current inventory stock</strong>. They will appear in dashboards and financial reports based on the selected date.</p>
        </div>
    </div>

    <form @submit.prevent="showConfirmModal = true" id="importForm" class="space-y-8">
        @csrf

        <!-- 1. General Details -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest flex items-center">
                    <span class="w-6 h-6 rounded-lg bg-smash-blue/10 text-smash-blue flex items-center justify-center mr-3 text-xs">1</span>
                    General Details
                </h3>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-3">Transaction Date</label>
                    <input type="date" name="transaction_date" required max="{{ now()->toDateString() }}" class="w-full bg-gray-50 border-0 text-sm font-bold text-gray-900 rounded-xl focus:ring-2 focus:ring-smash-blue transition-shadow p-4" x-model="date" @change="validateDate">
                    @error('transaction_date') <span class="text-red-500 text-xs font-bold mt-2 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-3">Payment Method</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_method" value="Cash" class="peer sr-only" required>
                            <div class="p-4 rounded-xl border border-gray-100 bg-white text-center peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 transition-all font-bold text-sm text-gray-400">
                                Cash
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_method" value="QRIS" class="peer sr-only" required>
                            <div class="p-4 rounded-xl border border-gray-100 bg-white text-center peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:text-purple-700 transition-all font-bold text-sm text-gray-400">
                                QRIS
                            </div>
                        </label>
                    </div>
                    @error('payment_method') <span class="text-red-500 text-xs font-bold mt-2 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- 2. Items Sold -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest flex items-center">
                    <span class="w-6 h-6 rounded-lg bg-smash-blue/10 text-smash-blue flex items-center justify-center mr-3 text-xs">2</span>
                    Items Sold
                </h3>
                <button type="button" @click="addItem()" class="px-4 py-2 bg-blue-50 text-smash-blue text-xs font-black rounded-xl hover:bg-blue-100 transition-colors uppercase tracking-widest flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Add Item
                </button>
            </div>
            <div class="p-8">
                <!-- Data for JS -->
                <script>
                    window.availableProducts = {!! json_encode($products->map(function($p) {
                        return ['id' => $p->id, 'name' => $p->name, 'price' => $p->selling_price, 'is_active' => (bool)$p->is_active];
                    })) !!};
                </script>

                <div class="space-y-4">
                    <template x-for="(item, index) in items" :key="item.key">
                        <div class="flex items-start space-x-4 bg-gray-50 p-4 rounded-2xl group transition-all hover:shadow-md border border-transparent hover:border-gray-100">
                            <!-- Product Selector -->
                            <div class="flex-1">
                                <label class="sr-only">Product</label>
                                <select :name="`cart[${index}][id]`" x-model="item.id" @change="productSelected(index)" required class="w-full bg-white border-0 text-sm font-bold text-gray-900 rounded-xl focus:ring-2 focus:ring-smash-blue transition-shadow p-3">
                                    <option value="">Select Product...</option>
                                    <template x-for="prod in window.availableProducts" :key="prod.id">
                                        <option :value="prod.id" x-text="prod.name + (!prod.is_active ? ' (Inactive)' : '')"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div class="w-24">
                                <label class="sr-only">Qty</label>
                                <input type="number" :name="`cart[${index}][quantity]`" x-model.number="item.quantity" min="1" required class="w-full bg-white border-0 text-sm font-bold text-gray-900 rounded-xl focus:ring-2 focus:ring-smash-blue transition-shadow p-3 text-center" placeholder="Qty">
                            </div>

                            <!-- Custom Price Override -->
                            <div class="w-48">
                                <label class="sr-only">Historical Price</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-xs font-black text-gray-400">Rp</span>
                                    </div>
                                    <input type="number" :name="`cart[${index}][custom_price]`" x-model.number="item.price" min="0" required class="w-full bg-white border-0 text-sm font-bold text-gray-900 rounded-xl pl-9 focus:ring-2 focus:ring-smash-blue transition-shadow p-3" placeholder="Price">
                                </div>
                                <p class="text-[9px] font-bold text-gray-400 mt-1 pl-1">Historical Price</p>
                            </div>

                            <!-- Remove Button -->
                            <button type="button" @click="removeItem(index)" class="w-12 h-12 bg-red-50 text-red-500 rounded-xl flex items-center justify-center hover:bg-red-100 transition-colors flex-shrink-0 opacity-0 group-hover:opacity-100 focus:opacity-100" title="Remove item">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <div x-show="items.length === 0" class="text-center py-12 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                    <p class="text-sm font-bold text-gray-400">No items added yet. Click 'Add Item' to start.</p>
                </div>
            </div>
        </div>

        <!-- 3. Final Adjustments & Total -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest flex items-center">
                    <span class="w-6 h-6 rounded-lg bg-smash-blue/10 text-smash-blue flex items-center justify-center mr-3 text-xs">3</span>
                    Review & Total
                </h3>
            </div>
            <div class="p-8">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Notes & Discoutns -->
                    <div class="lg:w-1/2 space-y-6">
                        <div>
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-3">Order Notes (Optional)</label>
                            <textarea name="notes" rows="2" class="w-full bg-gray-50 border-0 text-sm font-bold text-gray-900 rounded-xl focus:ring-2 focus:ring-smash-blue transition-shadow p-4 resize-none" placeholder="E.g., Special event catering..."></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-3">Discount Type</label>
                                <select name="discount_type" x-model="discountType" class="w-full bg-gray-50 border-0 text-sm font-bold text-gray-900 rounded-xl focus:ring-2 focus:ring-smash-blue transition-shadow p-4">
                                    <option value="">None</option>
                                    <option value="nominal">Nominal (Rp)</option>
                                    <option value="percentage">Percentage (%)</option>
                                </select>
                            </div>
                            <div x-show="discountType !== ''">
                                <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-3">Discount Value</label>
                                <input type="number" name="discount_value" x-model.number="discountValue" min="0" class="w-full bg-gray-50 border-0 text-sm font-bold text-gray-900 rounded-xl focus:ring-2 focus:ring-smash-blue transition-shadow p-4">
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center space-x-3 cursor-pointer p-4 rounded-xl border border-gray-100 bg-gray-50 hover:bg-gray-100/50 transition-colors">
                                <input type="checkbox" name="apply_tax" value="1" x-model="applyTax" class="w-5 h-5 text-smash-blue bg-white border-gray-300 rounded focus:ring-smash-blue focus:ring-offset-gray-50">
                                <span class="text-sm font-black text-gray-700 uppercase tracking-widest">Apply 10% Tax</span>
                            </label>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="lg:w-1/2 bg-gray-900 rounded-3xl p-8 text-white relative overflow-hidden flex flex-col justify-end">
                        <div class="absolute -right-20 -top-20 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl"></div>
                        
                        <div class="space-y-4 mb-8 relative z-10">
                            <div class="flex justify-between items-center text-sm">
                                <span class="font-bold text-gray-400">Subtotal</span>
                                <span class="font-black" x-text="formatCurrency(subtotal)"></span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-red-400" x-show="discountAmount > 0">
                                <span class="font-bold">Discount</span>
                                <span class="font-black" x-text="'- ' + formatCurrency(discountAmount)"></span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-blue-400" x-show="applyTax">
                                <span class="font-bold">Tax (10%)</span>
                                <span class="font-black" x-text="'+ ' + formatCurrency(taxAmount)"></span>
                            </div>
                        </div>
                        
                        <div class="pt-6 border-t border-gray-800 relative z-10">
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Total to Record</p>
                                    <div class="text-4xl font-black tracking-tighter" x-text="formatCurrency(totalAmount)">Rp 0</div>
                                </div>
                                <button type="submit" :disabled="items.length === 0 || !date || isSubmitting" class="px-8 py-4 bg-smash-blue text-white text-sm font-black rounded-2xl shadow-xl shadow-blue-500/20 hover:bg-blue-700 transition-all uppercase tracking-widest active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                                    <span x-show="!isSubmitting">Import Now</span>
                                    <span x-show="isSubmitting">Processing...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Confirmation Modal -->
<div x-cloak x-show="showConfirmModal" class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="showConfirmModal = false"></div>
    <div class="bg-white rounded-3xl p-8 max-w-sm w-full relative z-10 shadow-2xl transform transition-all text-center">
        <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
        </div>
        <h3 class="text-xl font-black text-gray-900 mb-2">Confirm Import</h3>
        <p class="text-sm font-bold text-gray-500 mb-6">You are about to import <span class="text-gray-900 font-black x-text='items.length'></span> items for <span class="text-gray-900 font-black x-text='date'></span> totaling <span class="text-smash-blue font-black x-text='formatCurrency(totalAmount)'></span>.</p>
        
        <div class="flex flex-col space-y-3">
            <button type="button" @click="submitImport()" :disabled="isSubmitting" class="w-full py-3.5 bg-smash-blue text-white text-sm font-black rounded-xl hover:bg-blue-700 transition-colors flex items-center justify-center disabled:opacity-50">
                <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="isSubmitting ? 'Processing...' : 'Yes, Import Data'"></span>
            </button>
            <button type="button" @click="showConfirmModal = false" :disabled="isSubmitting" class="w-full py-3.5 bg-gray-50 text-gray-500 text-sm font-black rounded-xl hover:bg-gray-100 transition-colors">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div x-cloak x-show="showSuccessModal" class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
    <div class="bg-white rounded-3xl p-8 max-w-sm w-full relative z-10 shadow-2xl transform transition-all text-center">
        <div class="w-16 h-16 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <h3 class="text-xl font-black text-gray-900 mb-2">Import Successful!</h3>
        <p class="text-sm font-bold text-gray-500 mb-6">Invoice <span class="text-gray-900 font-black x-text='successInvoice'></span> has been recorded.</p>
        
        <div class="flex flex-col space-y-3">
            <a href="{{ route('transactions.index') }}" class="w-full flex items-center justify-center py-3.5 bg-smash-blue text-white text-sm font-black rounded-xl hover:bg-blue-700 transition-colors">
                View Order History
            </a>
            <button type="button" @click="resetForm()" class="w-full py-3.5 bg-gray-50 text-gray-500 text-sm font-black rounded-xl hover:bg-gray-100 transition-colors">
                Import Another
            </button>
        </div>
    </div>
</div>
</div>

<script>
    function historicalImport() {
        return {
            date: '',
            items: [
                { key: Date.now(), id: '', quantity: 1, price: 0 }
            ],
            discountType: '',
            discountValue: 0,
            showConfirmModal: false,
            showSuccessModal: false,
            isSubmitting: false,
            successInvoice: '',
            errors: {},

            validateDate() {
                const selected = new Date(this.date);
                const today = new Date();
                today.setHours(23, 59, 59, 999);
                if (selected > today) {
                    alert('You cannot import transactions for future dates.');
                    this.date = '';
                }
            },

            addItem() {
                this.items.push({ key: Date.now(), id: '', quantity: 1, price: 0 });
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },

            productSelected(index) {
                const item = this.items[index];
                const product = window.availableProducts.find(p => p.id == item.id);
                if (product) {
                    item.price = product.price; // Auto-fill current selling price
                }
            },

            get subtotal() {
                return this.items.reduce((sum, item) => sum + ((parseFloat(item.price) || 0) * (parseInt(item.quantity) || 0)), 0);
            },

            get discountAmount() {
                if (this.discountType === 'percentage') {
                    return this.subtotal * ((parseFloat(this.discountValue) || 0) / 100);
                } else if (this.discountType === 'nominal') {
                    return parseFloat(this.discountValue) || 0;
                }
                return 0;
            },

            get taxAmount() {
                if (this.applyTax) {
                    return Math.max(0, this.subtotal - this.discountAmount) * 0.10;
                }
                return 0;
            },

            get totalAmount() {
                return Math.max(0, this.subtotal - this.discountAmount) + this.taxAmount;
            },

            formatCurrency(amount) {
                return 'Rp ' + Number(Math.round(amount)).toLocaleString('id-ID');
            },

            resetForm() {
                this.date = '';
                this.items = [{ key: Date.now(), id: '', quantity: 1, price: 0 }];
                this.discountType = '';
                this.discountValue = 0;
                this.applyTax = false;
                this.showSuccessModal = false;
                this.errors = {};
            },
            
            async submitImport() {
                this.isSubmitting = true;
                this.errors = {};
                
                try {
                    const form = document.getElementById('importForm');
                    const formData = new FormData(form);
                    
                    const response = await fetch("{{ route('transactions.import.store') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    const result = await response.json();
                    
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = result.errors;
                            this.showConfirmModal = false;
                            
                            // Collect errors into a string
                            let errStr = Object.values(this.errors).flat().join("\n");
                            alert("Validation Errors:\n" + errStr);
                        } else {
                            alert(result.message || "An error occurred");
                        }
                    } else {
                        this.showConfirmModal = false;
                        this.successInvoice = result.invoice_number;
                        this.showSuccessModal = true;
                    }
                } catch (e) {
                    console.error(e);
                    alert("A network error occurred.");
                } finally {
                    this.isSubmitting = false;
                }
            }
        }
    }
</script>
@endsection
