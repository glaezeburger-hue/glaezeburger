@extends('layouts.app')

@section('header', 'Order History')

@section('content')
<div x-data="{
    showDetail: false,
    detail: null,
    loading: false,

    async openDetail(id) {
        this.loading = true;
        this.showDetail = true;
        try {
            const res = await fetch(`{{ url('/transactions') }}/${id}`);
            this.detail = await res.json();
        } catch (e) {
            console.error(e);
        } finally {
            this.loading = false;
        }
    },

    closeDetail() {
        this.showDetail = false;
        this.detail = null;
    },

    formatPrice(val) {
        return 'Rp ' + Number(val).toLocaleString('id-ID');
    },

    async deleteTransaction(id, invoice) {
        const result = await Swal.fire({
            title: 'Delete Transaction?',
            text: `Are you sure you want to delete ${invoice}? This will permanently delete the record and RESTORE product stock levels.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'YES, DELETE & RESTORE STOCK',
            cancelButtonText: 'CANCEL',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-[2rem]',
                confirmButton: 'rounded-xl px-6 py-3 uppercase tracking-widest font-black text-[10px]',
                cancelButton: 'rounded-xl px-6 py-3 uppercase tracking-widest font-black text-[10px]'
            }
        });

        if (result.isConfirmed) {
            Swal.showLoading();
            try {
                const response = await fetch(`{{ url('/transactions') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false,
                        customClass: { popup: 'rounded-[2rem]' }
                    }).then(() => {
                        window.location.reload();
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete transaction. Please try again.',
                    customClass: { popup: 'rounded-[2rem]' }
                });
            }
        }
    },

    async confirmPayment(id, invoice) {
        const result = await Swal.fire({
            title: 'Confirm Payment?',
            text: `Mark ${invoice} as PAID? Make sure the funds have been received.`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#0A56C8',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'YES, CONFIRM PAYMENT',
            cancelButtonText: 'CANCEL',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-[2rem]',
                confirmButton: 'rounded-xl px-6 py-3 uppercase tracking-widest font-black text-[10px]',
                cancelButton: 'rounded-xl px-6 py-3 uppercase tracking-widest font-black text-[10px]'
            }
        });

        if (result.isConfirmed) {
            Swal.showLoading();
            try {
                const response = await fetch(`{{ url('/transactions') }}/${id}/payment`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false,
                        customClass: { popup: 'rounded-[2rem]' }
                    }).then(() => {
                        window.location.reload();
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to confirm payment.',
                    customClass: { popup: 'rounded-[2rem]' }
                });
            }
        }
    }
}">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Revenue -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
            <div class="relative">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="p-2.5 bg-blue-50 rounded-xl">
                        <svg class="w-5 h-5 text-smash-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Revenue</p>
                </div>
                <p class="text-2xl font-black text-gray-900 tracking-tight">Rp {{ number_format($summaryRevenue, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-green-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
            <div class="relative">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="p-2.5 bg-green-50 rounded-xl">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Orders</p>
                </div>
                <p class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($summaryCount) }}</p>
            </div>
        </div>

        <!-- Average Order -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-orange-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
            <div class="relative">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="p-2.5 bg-orange-50 rounded-xl">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Average Order</p>
                </div>
                <p class="text-2xl font-black text-gray-900 tracking-tight">Rp {{ number_format($summaryAvg, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap items-end gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Search Invoice</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="INV-20260226-..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-100 bg-gray-50/50 rounded-xl text-sm font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-smash-blue/20 focus:border-smash-blue transition-all">
                </div>
            </div>

            <!-- Payment Method -->
            <div class="min-w-[140px]">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Payment</label>
                <select name="payment_method" class="w-full py-2.5 px-3 border border-gray-100 bg-gray-50/50 rounded-xl text-sm font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-smash-blue/20 focus:border-smash-blue appearance-none cursor-pointer">
                    <option value="">All</option>
                    <option value="Cash" {{ request('payment_method') === 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="QRIS" {{ request('payment_method') === 'QRIS' ? 'selected' : '' }}>QRIS</option>
                </select>
            </div>

            <!-- Source -->
            <div class="min-w-[140px]">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Source</label>
                <select name="source" class="w-full py-2.5 px-3 border border-gray-100 bg-gray-50/50 rounded-xl text-sm font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-smash-blue/20 focus:border-smash-blue appearance-none cursor-pointer">
                    <option value="">All</option>
                    <option value="POS" {{ request('source') === 'POS' ? 'selected' : '' }}>POS</option>
                    <option value="Imported" {{ request('source') === 'Imported' ? 'selected' : '' }}>Imported</option>
                </select>
            </div>

            <!-- Status -->
            <div class="min-w-[140px]">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Status</label>
                <select name="status" class="w-full py-2.5 px-3 border border-gray-100 bg-gray-50/50 rounded-xl text-sm font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-smash-blue/20 focus:border-smash-blue appearance-none cursor-pointer">
                    <option value="">All</option>
                    <option value="Belum" {{ request('status') === 'Belum' ? 'selected' : '' }}>Pending</option>
                    <option value="Sudah" {{ request('status') === 'Sudah' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <!-- Date From -->
            <div class="min-w-[160px]">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full py-2.5 px-3 border border-gray-100 bg-gray-50/50 rounded-xl text-sm font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-smash-blue/20 focus:border-smash-blue">
            </div>

            <!-- Date To -->
            <div class="min-w-[160px]">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full py-2.5 px-3 border border-gray-100 bg-gray-50/50 rounded-xl text-sm font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-smash-blue/20 focus:border-smash-blue">
            </div>

            <!-- Buttons -->
            <div class="flex items-center space-x-2">
                <button type="submit" class="px-5 py-2.5 bg-smash-blue text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-blue-700 transition-all shadow-md shadow-blue-200 active:scale-95">
                    Filter
                </button>
                <a href="{{ route('transactions.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-500 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-200 transition-all active:scale-95">
                    Reset
                </a>
            </div>
        </form>

        <!-- Period Shortcuts -->
        <div class="flex items-center space-x-2 mt-4 pt-4 border-t border-gray-50">
            <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest mr-2">Quick:</span>
            <a href="{{ route('transactions.index', ['period' => 'today']) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg border transition-all {{ request('period') === 'today' ? 'bg-smash-blue text-white border-smash-blue' : 'bg-gray-50 text-gray-500 border-gray-100 hover:border-blue-200 hover:text-smash-blue' }}">Today</a>
            <a href="{{ route('transactions.index', ['period' => '7days']) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg border transition-all {{ request('period') === '7days' ? 'bg-smash-blue text-white border-smash-blue' : 'bg-gray-50 text-gray-500 border-gray-100 hover:border-blue-200 hover:text-smash-blue' }}">Last 7 Days</a>
            <a href="{{ route('transactions.index', ['period' => '30days']) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg border transition-all {{ request('period') === '30days' ? 'bg-smash-blue text-white border-smash-blue' : 'bg-gray-50 text-gray-500 border-gray-100 hover:border-blue-200 hover:text-smash-blue' }}">Last 30 Days</a>
            <a href="{{ route('transactions.index') }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg border transition-all {{ !request('period') && !request('search') && !request('payment_method') && !request('status') && !request('date_from') ? 'bg-smash-blue text-white border-smash-blue' : 'bg-gray-50 text-gray-500 border-gray-100 hover:border-blue-200 hover:text-smash-blue' }}">All Time</a>
        </div>
    </div>

    <!-- Transaction Table -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-50 uppercase tracking-tight">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 md:px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Invoice</th>
                        <th class="px-4 md:px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Date & Time</th>
                        <th class="px-4 md:px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Cashier</th>
                        <th class="px-4 md:px-6 py-5 text-center text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Items</th>
                        <th class="px-4 md:px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Payment</th>
                        <th class="px-4 md:px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Status</th>
                        <th class="px-4 md:px-6 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Total</th>
                        <th class="px-4 md:px-6 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 bg-white">
                    @forelse($transactions as $txn)
                    <tr class="hover:bg-blue-50/20 transition-colors group">
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-black text-gray-900 tracking-tight">{{ $txn->invoice_number }}</span>
                                @if($txn->is_imported)
                                    <span class="px-2 py-0.5 text-[9px] font-black bg-amber-50 text-amber-600 border border-amber-100 rounded-md uppercase tracking-widest">Imported</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-700">{{ $txn->created_at->format('d M Y') }}</div>
                            <div class="text-[11px] font-bold text-gray-400 mt-0.5">{{ $txn->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-smash-blue text-[10px] font-black uppercase">
                                    {{ substr($txn->user->name ?? '?', 0, 1) }}
                                </div>
                                <span class="text-sm font-bold text-gray-700">{{ $txn->user->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 md:px-6 py-4 text-center whitespace-nowrap">
                            <span class="inline-flex px-2.5 py-1 text-[11px] font-black rounded-lg bg-gray-50 text-gray-600 border border-gray-100">
                                {{ $txn->items->count() }}
                            </span>
                        </td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                            @if($txn->payment_method === 'QRIS')
                                <span class="inline-flex items-center px-3 py-1 text-[10px] font-black rounded-lg bg-purple-50 text-purple-600 border border-purple-100 uppercase tracking-widest">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    QRIS
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 text-[10px] font-black rounded-lg bg-green-50 text-green-600 border border-green-100 uppercase tracking-widest">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    Cash
                                </span>
                            @endif
                        </td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
                                <!-- Payment Status -->
                                @if($txn->payment_status === 'Paid')
                                    <span class="inline-flex items-center px-2 py-0.5 text-[9px] font-black rounded-md bg-green-50 text-green-600 border border-green-100 uppercase tracking-widest w-fit">
                                        PAID
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 text-[9px] font-black rounded-md bg-orange-50 text-orange-600 border border-orange-100 uppercase tracking-widest w-fit animate-pulse">
                                        UNPAID
                                    </span>
                                @endif

                                <!-- Order Status -->
                                @if($txn->order_status === 'Sudah')
                                    <span class="inline-flex px-2 py-0.5 text-[9px] font-black rounded-md bg-blue-50 text-smash-blue border border-blue-100 uppercase tracking-widest w-fit">
                                        COMPLETED
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 text-[9px] font-black rounded-md bg-gray-50 text-gray-400 border border-gray-100 uppercase tracking-widest w-fit">
                                        PENDING
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 md:px-6 py-4 text-right whitespace-nowrap">
                            <span class="text-sm font-black text-gray-900">Rp {{ number_format($txn->total_amount, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-4 md:px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-1">
                                <!-- Confirm Payment (If Unpaid) -->
                                @if($txn->payment_status === 'Pending')
                                <button @click="confirmPayment({{ $txn->id }}, '{{ $txn->invoice_number }}')" 
                                    class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-xl transition-all border border-transparent hover:border-green-100" title="Confirm Payment">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </button>
                                @endif

                                <!-- Delete Button (Owner Only) -->
                                @if(auth()->user()->role === 'owner')
                                <button @click="deleteTransaction({{ $txn->id }}, '{{ $txn->invoice_number }}')" 
                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all border border-transparent hover:border-red-100" title="Delete Transaction">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                @endif

                                <!-- Complete Order Button -->
                                @if($txn->order_status === 'Belum')
                                <form action="{{ route('transactions.status', $txn) }}" method="POST" class="inline-block" onsubmit="return confirm('Mark this order as complete?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-orange-500 hover:bg-orange-50 rounded-xl transition-all border border-transparent hover:border-orange-100" title="Complete Order">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif

                                <button @click="openDetail({{ $txn->id }})" class="p-2 text-gray-400 hover:text-smash-blue hover:bg-blue-50 rounded-xl transition-all border border-transparent hover:border-blue-100" title="View Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <button @click.prevent="$dispatch('print-receipt', {{ $txn->id }})" class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-xl transition-all border border-transparent hover:border-green-100" title="Bluetooth Print">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-20 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <div class="h-20 w-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="font-black text-gray-900 uppercase tracking-widest text-[13px]">No transactions found</p>
                                <p class="text-[11px] font-bold text-gray-400 mt-1 uppercase tracking-tight">Adjust your filters or start processing orders.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

    <!-- Detail Slide-over Modal -->
    <div x-cloak x-show="showDetail" class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0">
            <!-- Backdrop -->
            <div x-show="showDetail" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @click="closeDetail()" class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>

            <!-- Panel -->
            <div class="fixed inset-y-0 right-0 max-w-lg w-full flex">
                <div x-show="showDetail" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                    class="w-full bg-white shadow-2xl flex flex-col">

                    <!-- Header -->
                    <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div>
                            <div class="flex items-center space-x-2">
                                <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Order Detail</h3>
                                <span x-show="detail?.is_imported" class="px-2 py-0.5 text-[9px] font-black bg-amber-50 text-amber-600 border border-amber-100 rounded-md uppercase tracking-widest" style="display: none;">Imported</span>
                            </div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-1" x-text="detail?.invoice_number || '...'"></p>
                        </div>
                        <button @click="closeDetail()" class="p-2 text-gray-400 hover:text-gray-900 bg-white hover:bg-gray-100 rounded-xl transition-colors border border-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="flex-1 overflow-y-auto">
                        <!-- Loading state -->
                        <template x-if="loading">
                            <div class="h-full flex items-center justify-center">
                                <div class="text-center">
                                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-smash-blue mx-auto mb-4"></div>
                                    <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">Loading...</p>
                                </div>
                            </div>
                        </template>

                        <!-- Content -->
                        <template x-if="!loading && detail">
                            <div class="p-8 space-y-6">
                                <!-- Meta Info -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Date & Time</p>
                                        <p class="text-sm font-bold text-gray-900" x-text="detail.created_at"></p>
                                    </div>
                                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Cashier</p>
                                        <p class="text-sm font-bold text-gray-900" x-text="detail.cashier"></p>
                                    </div>
                                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Payment</p>
                                        <p class="text-sm font-bold text-gray-900" x-text="detail.payment_method"></p>
                                    </div>
                                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status</p>
                                        <span :class="detail.order_status === 'Sudah' ? 'text-green-600 bg-green-50 border-green-100' : 'text-orange-600 bg-orange-50 border-orange-100'"
                                            class="inline-flex px-2.5 py-1 text-[10px] font-black rounded-lg border uppercase tracking-widest"
                                            x-text="detail.order_status === 'Sudah' ? 'Completed' : 'Pending'"></span>
                                    </div>
                                </div>

                                <!-- Items Table -->
                                <div>
                                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Order Items</h4>
                                    <div class="bg-gray-50 rounded-2xl border border-gray-100 overflow-hidden">
                                        <table class="min-w-full divide-y divide-gray-100">
                                            <thead>
                                                <tr class="bg-gray-100/50">
                                                    <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Product</th>
                                                    <th class="px-4 py-3 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Qty</th>
                                                    <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Price</th>
                                                    <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                <template x-for="(item, idx) in detail.items" :key="idx">
                                                    <tr>
                                                        <td class="px-4 py-3">
                                                            <p class="text-xs font-bold text-gray-900 uppercase" x-text="item.product_name"></p>
                                                            
                                                            <!-- Variations Display -->
                                                            <template x-if="item.variations && item.variations.length > 0">
                                                                <div class="mt-1 flex flex-col gap-0.5">
                                                                    <template x-for="v in item.variations">
                                                                        <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest bg-gray-100 rounded px-1.5 py-0.5 inline-block w-fit" x-text="'+ ' + v.option"></span>
                                                                    </template>
                                                                </div>
                                                            </template>

                                                            <p x-show="item.notes" class="text-[10px] font-bold text-gray-400 mt-0.5 italic normal-case" x-text="'* ' + item.notes"></p>
                                                        </td>
                                                        <td class="px-4 py-3 text-center text-xs font-black text-gray-700" x-text="item.quantity"></td>
                                                        <td class="px-4 py-3 text-right text-xs font-bold text-gray-600" x-text="formatPrice(item.price)"></td>
                                                        <td class="px-4 py-3 text-right text-xs font-black text-gray-900" x-text="formatPrice(item.subtotal)"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Financial Breakdown -->
                                <div class="bg-gray-50 rounded-2xl border border-gray-100 p-5 space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="font-bold text-gray-500 uppercase tracking-widest text-[11px]">Subtotal</span>
                                        <span class="font-bold text-gray-700" x-text="formatPrice(detail.subtotal)"></span>
                                    </div>
                                    <template x-if="detail.discount_amount > 0">
                                        <div class="flex justify-between text-sm">
                                            <span class="font-bold text-red-500 uppercase tracking-widest text-[11px]">
                                                Discount
                                                <span x-show="detail.discount_type === 'percentage'" x-text="'(' + detail.discount_value + '%)'"></span>
                                            </span>
                                            <span class="font-bold text-red-500" x-text="'- ' + formatPrice(detail.discount_amount)"></span>
                                        </div>
                                    </template>
                                    <template x-if="detail.voucher_discount_amount > 0">
                                        <div class="flex justify-between text-sm">
                                            <span class="font-bold text-purple-500 uppercase tracking-widest text-[11px]">
                                                Voucher
                                                <span x-show="detail.voucher_code" x-text="'(' + detail.voucher_code + ')'"></span>
                                            </span>
                                            <span class="font-bold text-purple-500" x-text="'- ' + formatPrice(detail.voucher_discount_amount)"></span>
                                        </div>
                                    </template>
                                    <template x-if="detail.tax_amount > 0">
                                        <div class="flex justify-between text-sm">
                                            <span class="font-bold text-gray-500 uppercase tracking-widest text-[11px]">Tax (PB1 10%)</span>
                                            <span class="font-bold text-gray-700" x-text="'+ ' + formatPrice(detail.tax_amount)"></span>
                                        </div>
                                    </template>
                                    <div class="flex justify-between pt-3 border-t border-gray-200">
                                        <span class="font-black text-gray-900 uppercase tracking-widest text-xs">Grand Total</span>
                                        <span class="font-black text-xl text-smash-blue" x-text="formatPrice(detail.total_amount)"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Footer -->
                    <template x-if="detail">
                        <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/50">
                            <button @click="$dispatch('print-receipt', detail.id)"
                                class="w-full flex items-center justify-center px-6 py-3.5 bg-gray-900 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-800 transition-all active:scale-95">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Print Bluetooth Receipt
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
