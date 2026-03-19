@extends('layouts.app')

@section('header', 'Voucher Management')

@section('content')
<div x-data="{ 
    showModal: @js($errors->any() && !old('_method')),
    editing: @js(old('_method') === 'PUT'),
    voucher: {
        id: @js(old('id', '')),
        code: @js(old('code', '')),
        reward_type: @js(old('reward_type', 'percentage')),
        reward_value: @js(old('reward_value', '0')),
        free_product_id: @js(old('free_product_id', '')),
        min_purchase: @js(old('min_purchase', '0')),
        max_discount: @js(old('max_discount', '')),
        quota: @js(old('quota', '')),
        valid_from: @js(old('valid_from', '')),
        valid_until: @js(old('valid_until', '')),
        is_active: @js(old('is_active', '1') == '1')
    },
    products: @js($products),
    
    openAddModal() {
        this.editing = false;
        this.voucher = { id: '', code: '', reward_type: 'percentage', reward_value: '0', free_product_id: '', min_purchase: '0', max_discount: '', quota: '', valid_from: '', valid_until: '', is_active: true };
        this.showModal = true;
    },
    
    openEditModal(item) {
        this.editing = true;
        this.voucher = { ...item };
        // Format dates for datetime-local input
        if (this.voucher.valid_from) {
            this.voucher.valid_from = this.voucher.valid_from.substring(0, 16);
        }
        if (this.voucher.valid_until) {
            this.voucher.valid_until = this.voucher.valid_until.substring(0, 16);
        }
        this.showModal = true;
    }
}" @keydown.escape="showModal = false" class="space-y-8">

    <!-- Actions Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex flex-col md:flex-row md:items-center gap-4 flex-1">
            <div class="relative w-full md:w-96 group">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-smash-blue transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </span>
                <input type="text" class="block w-full pl-11 pr-4 py-3 border border-gray-200 rounded-2xl leading-5 bg-gray-50/50 cursor-not-allowed text-gray-400 sm:text-sm shadow-sm" placeholder="Vouchers display chronologically..." disabled>
            </div>
        </div>
        
        <button @click="openAddModal()" 
            class="inline-flex items-center justify-center px-8 py-3.5 border border-transparent text-sm font-black rounded-2xl shadow-xl shadow-smash-blue/20 text-white bg-smash-blue hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-smash-blue/20 transition-all transform hover:-translate-y-0.5 active:scale-95">
            <svg class="w-5 h-5 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Generate Voucher
        </button>
    </div>

    <!-- Voucher Table -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-50 uppercase tracking-tight">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 md:px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Code</th>
                        <th class="px-4 md:px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Reward</th>
                        <th class="px-4 md:px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Usage/Quota</th>
                        <th class="px-4 md:px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Validity</th>
                        <th class="px-4 md:px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Status</th>
                        <th class="px-4 md:px-8 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 bg-white">
                    @forelse($vouchers as $v)
                    <tr class="hover:bg-blue-50/20 transition-colors group">
                        <td class="px-4 md:px-8 py-5 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 bg-blue-50 rounded-xl flex items-center justify-center text-smash-blue border border-blue-100/50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-[15px] font-black text-gray-900 group-hover:text-smash-blue transition-colors">{{ $v->code }}</div>
                                    <div class="text-[10px] font-bold text-gray-400 mt-0.5 tracking-widest uppercase">Min. Spend Rp{{ number_format($v->min_purchase, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 md:px-8 py-5 font-bold whitespace-nowrap">
                            @if($v->reward_type === 'percentage')
                                <div class="text-[14px] text-gray-900 leading-tight">{{ floatval($v->reward_value) }}% OFF</div>
                                @if($v->max_discount)
                                    <div class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest">Max Rp{{ number_format($v->max_discount, 0, ',', '.') }}</div>
                                @endif
                            @elseif($v->reward_type === 'nominal')
                                <div class="text-[14px] text-gray-900 leading-tight">Rp{{ number_format($v->reward_value, 0, ',', '.') }} OFF</div>
                            @elseif($v->reward_type === 'free_item')
                                <div class="text-[14px] text-gray-900 leading-tight">FREE ITEM</div>
                                <div class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest line-clamp-1 truncate w-40">{{ $v->freeProduct ? $v->freeProduct->name : 'Unknown Product' }}</div>
                            @endif
                        </td>
                        <td class="px-4 md:px-8 py-5 font-bold whitespace-nowrap">
                            <div class="text-[14px] text-gray-900">{{ $v->used_count }} / {{ $v->quota ?? '∞' }}</div>
                        </td>
                        <td class="px-4 md:px-8 py-5 whitespace-nowrap">
                            <div class="text-[11px] font-black text-gray-500 uppercase tracking-widest">
                                @if($v->valid_from || $v->valid_until)
                                    <div>{{ $v->valid_from ? \Carbon\Carbon::parse($v->valid_from)->format('d/m/y H:i') : 'ANY' }}</div>
                                    <div class="text-gray-400 -my-0.5">TO</div>
                                    <div>{{ $v->valid_until ? \Carbon\Carbon::parse($v->valid_until)->format('d/m/y H:i') : 'ANY' }}</div>
                                @else
                                    <span class="text-smash-blue">Always Valid</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 md:px-8 py-5 whitespace-nowrap">
                            @php
                                $statusClass = $v->is_active ? 'bg-green-50 text-green-600 border-green-100' : 'bg-red-50 text-red-600 border-red-100';
                                $statusText = $v->is_active ? 'Active' : 'Inactive';
                            @endphp
                            <span class="inline-flex px-3 py-1 text-[11px] font-black rounded-lg border {{ $statusClass }} uppercase">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td class="px-4 md:px-8 py-5 text-right font-medium whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="openEditModal({{ json_encode($v) }})" class="p-2.5 text-gray-400 hover:text-smash-blue hover:bg-blue-50 rounded-xl transition-all border border-transparent hover:border-blue-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 00-2 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <form action="{{ route('vouchers.destroy', $v) }}" method="POST" onsubmit="return confirm('Delete this voucher?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all border border-transparent hover:border-red-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-20 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <div class="h-20 w-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                    </svg>
                                </div>
                                <p class="font-black text-gray-900 uppercase tracking-widest text-[13px]">No vouchers found</p>
                                <p class="text-[11px] font-bold text-gray-400 mt-1 uppercase tracking-tight">Create your first promo code to boost sales.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($vouchers->hasPages())
        <div class="px-8 py-5 bg-gray-50/50 border-t border-gray-100">
            {{ $vouchers->links() }}
        </div>
        @endif
    </div>

    <!-- Slide-over Modal -->
    <div x-show="showModal" class="fixed inset-0 overflow-hidden z-50 text-[13px]" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="absolute inset-0 overflow-hidden">
            <!-- Overlay -->
            <div x-show="showModal" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showModal = false" aria-hidden="true"></div>

            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div x-show="showModal" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" 
                    class="relative w-screen max-w-md">
                    
                    <div class="h-full flex flex-col bg-white shadow-2xl overflow-y-scroll">
                        <div class="px-8 py-10 bg-smash-blue relative overflow-hidden">
                            <div class="relative z-10">
                                <div class="flex items-start justify-between">
                                    <h2 class="text-2xl font-black text-white uppercase tracking-tighter" id="slide-over-title" x-text="editing ? 'Edit Voucher' : 'Generate Voucher'"></h2>
                                    <div class="ml-3 h-7 flex items-center">
                                        <button type="button" @click="showModal = false" class="rounded-xl p-1 bg-white/10 text-white hover:bg-white/20 transition-all">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="mt-2 text-[12px] font-bold text-blue-100/80 uppercase tracking-widest italic">Promo Code Configuration</p>
                            </div>
                            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                        </div>

                        <form :action="editing ? `/vouchers/${voucher.id}` : '{{ route('vouchers.store') }}'" method="POST" class="flex-1 flex flex-col font-bold">
                            @csrf
                            <template x-if="editing">
                                <input type="hidden" name="_method" value="PUT">
                            </template>

                            <div class="px-8 py-10 space-y-8">
                                @if($errors->any())
                                    <div class="p-4 bg-red-50 border border-red-100 rounded-2xl">
                                        <div class="flex">
                                            <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            <div class="ml-3">
                                                <h3 class="text-xs font-black text-red-800 uppercase tracking-widest">Validation Errors</h3>
                                                <ul class="mt-2 text-[11px] text-red-700 font-bold list-disc list-inside">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Basic Info -->
                                <div>
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Voucher Code</label>
                                    <input type="text" name="code" x-model="voucher.code" required style="text-transform:uppercase" placeholder="e.g. GRANDLAUNCH"
                                        class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30 uppercase">
                                </div>

                                <!-- Reward Data -->
                                <div class="p-5 bg-blue-50/50 rounded-2xl border border-blue-100 space-y-6">
                                    <div class="flex items-center space-x-2 mb-2 text-smash-blue">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                                        <span class="text-[11px] font-black uppercase tracking-widest">Reward Configuration</span>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">Reward Type</label>
                                        <select name="reward_type" x-model="voucher.reward_type" required
                                            class="appearance-none block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-white font-bold text-gray-600">
                                            <option value="percentage">Percentage Discount (%)</option>
                                            <option value="nominal">Nominal Discount (Rp)</option>
                                            <option value="free_item">Free Item</option>
                                        </select>
                                    </div>

                                    <template x-if="voucher.reward_type !== 'free_item'">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">Value</label>
                                                <input type="number" name="reward_value" x-model="voucher.reward_value"
                                                    class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-white">
                                            </div>
                                            <template x-if="voucher.reward_type === 'percentage'">
                                                <div>
                                                    <label class="block text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">Max Disc. (Rp)</label>
                                                    <input type="number" name="max_discount" x-model="voucher.max_discount" placeholder="(Optional)"
                                                        class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-white">
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="voucher.reward_type === 'free_item'">
                                        <div>
                                            <label class="block text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">Select Free Product</label>
                                            <select name="free_product_id" x-model="voucher.free_product_id"
                                                class="appearance-none block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-white font-bold text-gray-600">
                                                <option value="">Select a product...</option>
                                                <template x-for="prod in products" :key="prod.id">
                                                    <option :value="prod.id" x-text="prod.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </template>
                                </div>

                                <!-- Rules -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Min. Purchase (Rp)</label>
                                        <input type="number" name="min_purchase" x-model="voucher.min_purchase" placeholder="0"
                                            class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Quota limit</label>
                                        <input type="number" name="quota" x-model="voucher.quota" placeholder="Unlimited"
                                            class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30">
                                    </div>
                                </div>

                                <!-- Dates -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Valid From</label>
                                        <input type="datetime-local" name="valid_from" x-model="voucher.valid_from"
                                            class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-xs transition-all bg-gray-50/30 text-gray-600">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Valid Until</label>
                                        <input type="datetime-local" name="valid_until" x-model="voucher.valid_until"
                                            class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-xs transition-all bg-gray-50/30 text-gray-600">
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="flex items-center justify-between p-6 bg-gray-50 rounded-[2rem] border border-gray-100 shadow-inner">
                                    <div>
                                        <div class="text-[14px] font-black text-gray-900 leading-none">Voucher Status</div>
                                        <div class="text-[10px] font-bold text-gray-400 uppercase mt-2 tracking-wide">Enable or disable this code</div>
                                    </div>
                                    <input type="hidden" name="is_active" value="0">
                                    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                        <input type="checkbox" name="is_active" id="toggle-active" value="1" x-model="voucher.is_active"
                                            class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 border-gray-200 appearance-none cursor-pointer focus:outline-none transition-all duration-300 transform"
                                            :class="{'translate-x-6 border-smash-blue': voucher.is_active, 'translate-x-0 border-gray-200': !voucher.is_active}"/>
                                        <label for="toggle-active" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-200 cursor-pointer transition-colors duration-300"
                                            :class="{'bg-smash-blue/40': voucher.is_active, 'bg-gray-200': !voucher.is_active}"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto px-8 py-8 border-t border-gray-50 flex items-center justify-between bg-white sticky bottom-0">
                                <button type="button" @click="showModal = false" class="text-[12px] font-black text-gray-400 hover:text-gray-900 transition-colors uppercase tracking-widest px-4">Cancel</button>
                                <button type="submit" class="px-10 py-4 bg-smash-blue text-white rounded-2xl font-black shadow-2xl shadow-smash-blue/30 hover:bg-blue-700 transition-all transform hover:-translate-y-1 active:scale-95 uppercase tracking-widest text-xs">
                                    Save Voucher
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
