@extends('layouts.app')

@section('header', 'Raw Materials')

@section('content')
<div x-data="{ 
    showModal: @js($errors->any()),
    editing: @js(old('_method') === 'PUT'),
    baseUrl: '{{ url('/raw-materials') }}',
    formAction: @js(old('_method') === 'PUT' && old('id') ? url('/raw-materials/'.old('id')) : route('raw-materials.store')),
    rawMaterial: {
        id: @js(old('id', '')),
        name: @js(old('name', '')),
        sku: @js(old('sku', '')),
        unit: @js(old('unit', 'pcs')),
        stock: @js(old('stock', '')),
        low_stock_threshold: @js(old('low_stock_threshold', '10')),
        cost_per_unit: @js(old('cost_per_unit', ''))
    },
    
    openAddModal() {
        this.editing = false;
        this.formAction = '{{ route('raw-materials.store') }}';
        this.rawMaterial = { id: '', name: '', sku: '', unit: 'pcs', stock: '', low_stock_threshold: '10', cost_per_unit: '' };
        this.showModal = true;
    },
    
    openEditModal(item) {
        this.editing = true;
        this.formAction = `${this.baseUrl}/${item.id}`;
        this.rawMaterial = { ...item };
        this.showModal = true;
    }
}" @keydown.escape="showModal = false" class="space-y-8">

    <!-- Actions Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <form action="{{ route('raw-materials.index') }}" method="GET" class="w-full sm:w-96 relative group">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-smash-blue transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or sku..." 
                   class="block w-full pl-10 pr-3 py-3 border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue text-sm transition-all bg-gray-50/50">
        </form>

        <button @click="openAddModal()" class="w-full sm:w-auto px-6 py-3 bg-smash-blue text-white rounded-2xl font-black shadow-lg shadow-smash-blue/30 hover:bg-blue-700 transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Add Raw Material
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-100 rounded-2xl flex items-center gap-3">
            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <p class="text-sm font-bold text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 md:px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Name & SKU</th>
                        <th class="px-4 md:px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Harga Beli</th>
                        <th class="px-4 md:px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Stock Level</th>
                        <th class="px-4 md:px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Status</th>
                        <th class="px-4 md:px-6 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 bg-white">
                    @forelse ($rawMaterials as $material)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-black text-gray-900">{{ $material->name }}</div>
                                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-widest mt-0.5">{{ $material->sku }}</div>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-black text-gray-900">Rp {{ number_format($material->cost_per_unit, 0, ',', '.') }}</div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">per {{ $material->unit }}</div>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-lg font-black text-gray-900">{{ floatval($material->stock) }}</span>
                                    <span class="text-xs font-bold text-gray-500">{{ $material->unit }}</span>
                                </div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Alert at: {{ floatval($material->low_stock_threshold) }}</div>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                @if($material->stock_status === 'Out of Stock')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-red-100 text-red-700">Out of Stock</span>
                                @elseif($material->stock_status === 'Low Stock')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-orange-100 text-orange-700">Low Stock</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-green-100 text-green-700">In Stock</span>
                                @endif
                            </td>
                            <td class="px-4 md:px-6 py-4 text-right whitespace-nowrap">
                                <button @click="openEditModal({{ $material->toJson() }})" class="p-2 text-gray-400 hover:text-smash-blue hover:bg-blue-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <form action="{{ route('raw-materials.destroy', $material) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this raw material?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                </div>
                                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">No raw materials found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rawMaterials->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $rawMaterials->links() }}
            </div>
        @endif
    </div>

    <!-- Slide-over Modal -->
    <div x-show="showModal" class="fixed inset-0 overflow-hidden z-50 text-[13px]" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="absolute inset-0 overflow-hidden">
            <div x-show="showModal" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showModal = false" aria-hidden="true"></div>

            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div x-show="showModal" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" 
                    class="relative w-screen max-w-md">
                    
                    <div class="h-full flex flex-col bg-white shadow-2xl overflow-y-scroll">
                        <div class="px-8 py-10 bg-smash-blue relative overflow-hidden">
                            <div class="relative z-10">
                                <div class="flex items-start justify-between">
                                    <h2 class="text-2xl font-black text-white uppercase tracking-tighter" id="slide-over-title" x-text="editing ? 'Edit Raw Material' : 'Add Raw Material'"></h2>
                                    <div class="ml-3 h-7 flex items-center">
                                        <button type="button" @click="showModal = false" class="rounded-xl p-1 bg-white/10 text-white hover:bg-white/20 transition-all">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="mt-2 text-[12px] font-bold text-blue-100/80 uppercase tracking-widest italic">Inventory Management</p>
                            </div>
                            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                        </div>

                        <form :action="formAction" method="POST" class="flex-1 flex flex-col font-bold">
                            @csrf
                            <template x-if="editing">
                                <div>
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="id" x-model="rawMaterial.id">
                                </div>
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

                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Material Name</label>
                                        <input type="text" name="name" x-model="rawMaterial.name" required placeholder="e.g. Bun, Patty, Cheese"
                                            class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">SKU Code</label>
                                            <input type="text" name="sku" x-model="rawMaterial.sku" required placeholder="e.g. RM-001"
                                                class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Unit</label>
                                            <div class="relative">
                                                <select name="unit" x-model="rawMaterial.unit" required
                                                    class="appearance-none block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30 font-bold text-gray-600">
                                                    <option value="pcs">pcs</option>
                                                    <option value="gram">gram</option>
                                                    <option value="ml">ml</option>
                                                    <option value="kg">kg</option>
                                                    <option value="liter">liter</option>
                                                </select>
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cost Per Unit -->
                                <div>
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Harga Beli (Cost Per Unit)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-gray-400 font-black text-[11px]">Rp</span>
                                        </div>
                                        <input type="number" step="0.01" name="cost_per_unit" x-model="rawMaterial.cost_per_unit" required
                                            class="block w-full pl-10 pr-4 py-3 border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue text-sm transition-all bg-gray-50/30 shadow-sm">
                                    </div>
                                    <p class="mt-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Harga per <span x-text="rawMaterial.unit || 'unit'"></span></p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Current Stock</label>
                                        <input type="number" step="0.01" name="stock" x-model="rawMaterial.stock" required
                                            class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Low Alert Level</label>
                                        <input type="number" step="0.01" name="low_stock_threshold" x-model="rawMaterial.low_stock_threshold" required
                                            class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30 border-orange-100">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto px-8 py-8 border-t border-gray-50 flex items-center justify-between bg-white sticky bottom-0">
                                <button type="button" @click="showModal = false" class="text-[12px] font-black text-gray-400 hover:text-gray-900 transition-colors uppercase tracking-widest px-4">Skip</button>
                                <button type="submit" class="px-10 py-4 bg-smash-blue text-white rounded-2xl font-black shadow-2xl shadow-smash-blue/30 hover:bg-blue-700 transition-all transform hover:-translate-y-1 active:scale-95 uppercase tracking-widest text-xs">
                                    Save Record
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
