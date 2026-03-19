@extends('layouts.app')

@section('header', 'Expenses & Wastages')

@section('content')
<div class="space-y-6" x-data="expenseForm()">

    {{-- Tabs Navigation --}}
    <div class="flex space-x-1 bg-gray-100/50 p-1.5 rounded-2xl w-max border border-gray-200">
        <a href="{{ route('expenses.index') }}" class="px-6 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('expenses.*') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
            Operating Expenses & Restock
        </a>
        <a href="{{ route('wastages.index') }}" class="px-6 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('wastages.*') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
            Wastage Tracking
        </a>
    </div>

    {{-- Summary & Actions --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
        <div class="flex gap-4">
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 min-w-[200px]">
                <p class="text-xs text-gray-500 font-medium mb-1 uppercase tracking-wider">Total Period</p>
                <h3 class="text-xl font-bold text-gray-900">Rp {{ number_format($totalPeriod, 0, ',', '.') }}</h3>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 min-w-[200px]">
                <p class="text-xs text-gray-500 font-medium mb-1 uppercase tracking-wider flex justify-between">
                    <span>This Month</span>
                    <span class="text-smash-blue font-bold">ALL</span>
                </p>
                <h3 class="text-xl font-bold text-smash-blue">Rp {{ number_format($totalMonth, 0, ',', '.') }}</h3>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 min-w-[200px]">
                <p class="text-xs text-gray-500 font-medium mb-1 uppercase tracking-wider flex justify-between">
                    <span>This Month</span>
                    <span class="text-orange-500 font-bold">RESTOCK</span>
                </p>
                <h3 class="text-xl font-bold text-orange-600">Rp {{ number_format($restockCurrentMonth, 0, ',', '.') }}</h3>
            </div>
        </div>

        <button @click="openModal = true" class="flex items-center px-5 py-2.5 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Record Expense
        </button>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5">
        <form action="{{ route('expenses.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description..." class="pl-10 w-full rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue">
                </div>
            </div>
            
            <div class="w-48">
                <select name="category_id" class="w-full rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->icon }} {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue">
                <span class="text-gray-400">-</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue">
            </div>

            <button type="submit" class="p-2.5 bg-gray-50 text-gray-600 rounded-xl hover:bg-gray-100 border border-gray-200 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
            </button>
            
            @if(request()->anyFilled(['search', 'category_id', 'date_from', 'date_to']))
                <a href="{{ route('expenses.index') }}" class="text-sm text-rose-500 hover:text-rose-700 font-medium">Clear</a>
            @endif
        </form>
    </div>

    {{-- Expense Table --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-4 md:px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Date</th>
                    <th class="px-4 md:px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Category</th>
                    <th class="px-4 md:px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Description</th>
                    <th class="px-4 md:px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Amount</th>
                    <th class="px-4 md:px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Payment / User</th>
                    <th class="px-4 md:px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($expenses as $exp)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $exp->expense_date->format('d M Y') }}
                    </td>
                    <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $exp->isRestock() ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800' }}">
                            <span class="mr-1">{{ $exp->category->icon ?? '' }}</span>
                            {{ $exp->category->name ?? 'Unknown' }}
                        </span>
                        @if($exp->isRestock())
                            <div class="mt-1 flex flex-col gap-0.5">
                                @foreach($exp->restockItems as $item)
                                    <span class="text-[10px] text-gray-500 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100 w-max">
                                        +{{ floatval($item->quantity) }} {{ $item->rawMaterial->unit ?? 'unit' }} {{ $item->rawMaterial->name ?? 'Unknown' }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="px-4 md:px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                        <p class="font-medium">{{ $exp->description }}</p>
                        @if($exp->notes)
                            <p class="text-xs text-gray-400 mt-0.5 max-w-xs whitespace-normal">{{ $exp->notes }}</p>
                        @endif
                    </td>
                    <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-bold {{ $exp->isRestock() ? 'text-orange-600' : 'text-gray-900' }}">
                            Rp {{ number_format($exp->amount, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="inline-flex items-center text-xs font-medium text-gray-500 mb-1">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                {{ $exp->payment_method }}
                            </span>
                            <span class="text-xs text-gray-400 flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                {{ $exp->user->name ?? 'System' }}
                            </span>
                        </div>
                    </td>
                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-right text-sm">
                        <div class="flex items-center justify-end space-x-2">
                            @if($exp->receipt_image)
                                <a href="{{ asset('storage/' . $exp->receipt_image) }}" target="_blank" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View Receipt">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </a>
                            @endif
                            <form action="{{ route('expenses.destroy', $exp) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this expense? {{ $exp->isRestock() ? 'WARNING: This will rollback the added material stock!' : '' }}');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">No expenses found</h3>
                        <p class="text-sm text-gray-500">Record a new expense to get started.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($expenses->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>

    {{-- Expense Modal --}}
    <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="openModal" class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" @click="openModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="openModal" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
                <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white px-6 py-6 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900" id="modal-title">Record New Expense</h3>
                        <button type="button" @click="openModal = false" class="text-gray-400 hover:text-gray-500 bg-gray-50 hover:bg-gray-100 rounded-full p-2 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="px-6 py-6 space-y-5">
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select name="expense_category_id" x-model="selectedCategory" class="w-full rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue bg-gray-50" required>
                                    <option value="" disabled selected>Select category...</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" data-is-restock="{{ $cat->is_restock ? '1' : '0' }}">
                                            {{ $cat->icon }} {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('expense_category_id') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" class="w-full rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue bg-gray-50" required>
                                @error('expense_date') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input type="text" name="description" value="{{ old('description') }}" placeholder="e.g., Bayar listrik bulan Februari" class="w-full rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue bg-gray-50" required>
                            @error('description') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount (Rp)</label>
                                <input type="number" name="amount" value="{{ old('amount') }}" min="0" placeholder="0" class="w-full rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue bg-gray-50 text-xl font-bold" required>
                                @error('amount') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                                <select name="payment_method" class="w-full rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue bg-gray-50">
                                    <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="Transfer" {{ old('payment_method') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                                    <option value="QRIS" {{ old('payment_method') == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                                </select>
                                @error('payment_method') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Dynamic Restock Items Section --}}
                        <div x-show="isRestockCategory()" class="bg-orange-50/50 border border-orange-100 rounded-2xl p-4 space-y-3" style="display: none;">
                            <h4 class="text-sm font-bold text-orange-800 flex items-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                Restock Materials (Auto-Updates Stock)
                            </h4>
                            
                            <template x-for="(item, index) in restockItems" :key="index">
                                <div class="flex gap-2 items-start bg-white p-2.5 rounded-xl border border-orange-100 shadow-sm relative">
                                    <div class="flex-1">
                                        <select :name="`restock_items[${index}][raw_material_id]`" x-model="item.id" class="w-full rounded-lg border-gray-200 text-xs focus:ring-orange-500 focus:border-orange-500" :required="isRestockCategory()" :disabled="!isRestockCategory()">
                                            <option value="" disabled selected>Select Material...</option>
                                            @foreach($rawMaterials as $rm)
                                                <option value="{{ $rm->id }}">{{ $rm->name }} ({{ $rm->unit }}) - Stock: {{ floatval($rm->stock) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="w-24">
                                        <input type="number" :name="`restock_items[${index}][quantity]`" x-model="item.qty" step="0.01" min="0.01" placeholder="Qty" class="w-full rounded-lg border-gray-200 text-xs focus:ring-orange-500 focus:border-orange-500" :required="isRestockCategory()" :disabled="!isRestockCategory()">
                                    </div>
                                    <div class="w-32">
                                        <input type="number" :name="`restock_items[${index}][unit_cost]`" x-model="item.cost" min="0" placeholder="Unit Cost (Rp)" class="w-full rounded-lg border-gray-200 text-xs focus:ring-orange-500 focus:border-orange-500" :required="isRestockCategory()" :disabled="!isRestockCategory()">
                                    </div>
                                    <button type="button" @click="removeItem(index)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg" x-show="restockItems.length > 1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </template>
                            
                            <button type="button" @click="addItem()" class="text-xs font-semibold text-orange-600 hover:text-orange-700 flex items-center mt-2 px-2">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Add another material
                            </button>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Receipt / Proof (Optional)</label>
                            <input type="file" name="receipt" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-smash-blue file:text-white hover:file:bg-blue-600 cursor-pointer">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                            <textarea name="notes" rows="2" class="w-full rounded-xl border-gray-200 text-sm focus:ring-smash-blue focus:border-smash-blue bg-gray-50"></textarea>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end space-x-3 rounded-b-3xl">
                        <button type="button" @click="openModal = false" :disabled="submitting" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors disabled:opacity-50">
                            Cancel
                        </button>
                        <button type="submit" @click="submitting = true" class="px-5 py-2.5 text-sm font-medium text-white bg-smash-blue rounded-xl hover:bg-blue-600 shadow-sm transition-colors disabled:opacity-50 flex items-center">
                            <span x-show="!submitting">Save Expense</span>
                            <span x-show="submitting" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function expenseForm() {
        return {
            openModal: {{ $errors->any() ? 'true' : 'false' }},
            submitting: false,
            selectedCategory: '{{ old('expense_category_id') }}',
            categoriesData: {!! json_encode($categories->mapWithKeys(fn($c) => [$c->id => $c->is_restock])->toArray()) !!},
            restockItems: [{ id: '', qty: '', cost: '' }],
            
            isRestockCategory() {
                if (!this.selectedCategory) return false;
                // Parse correctly whether string or int key
                return this.categoriesData[this.selectedCategory] == 1;
            },
            
            addItem() {
                this.restockItems.push({ id: '', qty: '', cost: '' });
            },
            
            removeItem(index) {
                if (this.restockItems.length > 1) {
                    this.restockItems.splice(index, 1);
                }
            }
        }
    }
</script>
@endsection
