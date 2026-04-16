@extends('layouts.app')

@section('header', 'Manajemen Add-ons')

@section('content')
<div x-data="{ 
    showModal: @js($errors->any()),
    editing: @js(old('_method') === 'PUT'),
    defaultEndpoint: '{{ route('addons.store') }}',
    baseEndpoint: '{{ url('/addons') }}',
    formAction: @js(old('_method') === 'PUT' && old('id') ? url('/addons/'.old('id')) : route('addons.store')),
    rawMaterials: @js($rawMaterials),
    addon: {
        id: @js(old('id', '')),
        name: @js(old('name', '')),
        selling_price: @js(old('selling_price', 0)),
        is_active: @js(old('is_active', true)),
        ingredients: @js(old('ingredients', []))
    },

    getHppTarget() {
        let hpp = 0;
        this.addon.ingredients.forEach(ing => {
            let rm = this.rawMaterials.find(r => r.id == ing.id);
            if(rm && ing.quantity) {
                hpp += parseFloat(rm.cost_per_unit) * parseFloat(ing.quantity);
            }
        });
        return hpp;
    },

    getMargin() {
        let sp = parseFloat(this.addon.selling_price) || 0;
        let hpp = this.getHppTarget();
        if(sp <= 0) return 0;
        return ((sp - hpp) / sp) * 100;
    },

    addIngredient() {
        this.addon.ingredients.push({ id: '', quantity: 0 });
    },
    removeIngredient(index) {
        this.addon.ingredients.splice(index, 1);
    },
    openAddModal() {
        this.editing = false;
        this.formAction = this.defaultEndpoint;
        this.addon = { 
            id: '', name: '', selling_price: 0, is_active: true, 
            ingredients: [] 
        };
        this.showModal = true;
    },
    openEditModal(item) {
        this.editing = true;
        this.formAction = `${this.baseEndpoint}/${item.id}`;
        this.addon = {
            id: item.id,
            name: item.name,
            selling_price: parseFloat(item.selling_price),
            is_active: item.is_active ? true : false,
            ingredients: item.raw_materials ? item.raw_materials.map(rm => ({
                id: rm.id,
                quantity: parseFloat(rm.pivot.quantity)
            })) : []
        };
        this.showModal = true;
    }
}" @keydown.escape="showModal = false" class="space-y-8">

    <!-- Actions Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <form action="{{ route('addons.index') }}" method="GET" class="w-full sm:w-96 relative group">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-smash-blue transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama add-on..." 
                   class="block w-full pl-10 pr-3 py-3 border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue text-sm transition-all bg-gray-50/50">
        </form>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <form action="{{ route('addons.sync-costs') }}" method="POST">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-5 py-3 bg-white border border-gray-200 text-gray-700 rounded-2xl font-black hover:bg-gray-50 transition-all shadow-sm flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Sync HPP
                </button>
            </form>
            <button @click="openAddModal()" class="w-full sm:w-auto px-6 py-3 bg-smash-blue text-white rounded-2xl font-black shadow-lg shadow-smash-blue/30 hover:bg-blue-700 transition-all flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Buat Add-on
            </button>
        </div>
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
                        <th class="px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Nama Add-on</th>
                        <th class="px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Pricing</th>
                        <th class="px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Bahan Baku</th>
                        <th class="px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Status</th>
                        <th class="px-6 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 bg-white">
                    @forelse ($addons as $item)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-black text-gray-900">{{ $item->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <div class="text-sm font-black text-green-600">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</div>
                                    <div class="text-[11px] font-bold text-gray-500">HPP: Rp {{ number_format($item->cost_price, 0, ',', '.') }}</div>
                                    @php $margin = $item->gross_margin; @endphp
                                    <div class="text-[10px] font-black uppercase tracking-widest {{ $margin >= 50 ? 'text-green-500' : ($margin >= 30 ? 'text-blue-500' : 'text-red-500') }}">
                                        Margin: {{ $margin }}%
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 max-w-sm">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($item->rawMaterials as $rm)
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-gray-50 border border-gray-100 text-[10px] font-bold text-gray-600">
                                            {{ $rm->name }} ({{ floatval($rm->pivot->quantity) }} {{ $rm->unit }})
                                        </span>
                                    @empty
                                        <span class="text-xs font-bold text-gray-400 italic">Tidak ada bahan baku</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $item->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <button @click="openEditModal({{ $item->toJson() }})" class="p-2 text-gray-400 hover:text-smash-blue hover:bg-blue-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <form action="{{ route('addons.destroy', $item) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus add-on ini?');">
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
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                </div>
                                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Belum ada add-ons</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($addons->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $addons->links() }}
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
                        <div class="px-8 py-10 bg-smash-blue relative overflow-hidden shrink-0">
                            <div class="relative z-10">
                                <div class="flex items-start justify-between">
                                    <h2 class="text-2xl font-black text-white uppercase tracking-tighter" id="slide-over-title" x-text="editing ? 'Edit Add-on' : 'Buat Add-on'"></h2>
                                    <div class="ml-3 h-7 flex items-center">
                                        <button type="button" @click="showModal = false" class="rounded-xl p-1 bg-white/10 text-white hover:bg-white/20 transition-all">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                        </div>

                        <form :action="formAction" method="POST" class="flex-1 flex flex-col font-bold">
                            @csrf
                            <template x-if="editing">
                                <div>
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="id" x-model="addon.id">
                                </div>
                            </template>

                            <div class="px-8 py-8 space-y-6 flex-1">
                                <!-- Basic Info -->
                                <div>
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Add-on</label>
                                    <input type="text" name="name" x-model="addon.name" required placeholder="e.g. Extra Cheese"
                                        class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-white shadow-sm">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Harga Jual</label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 font-bold">Rp</span>
                                            <input type="number" name="selling_price" x-model="addon.selling_price" required min="0" placeholder="0"
                                                class="block w-full pl-10 pr-4 py-3 border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue text-sm transition-all bg-white shadow-sm font-bold">
                                        </div>
                                    </div>

                                    <div class="flex items-center pt-6">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="is_active" value="1" x-model="addon.is_active" class="sr-only">
                                            <div class="w-11 h-6 rounded-full transition-all duration-300 relative border-2 border-transparent"
                                                 :class="addon.is_active ? 'bg-green-500' : 'bg-gray-200'">
                                                <div class="absolute top-[2px] left-[2px] bg-white border-gray-300 border rounded-full h-4 w-4 transition-all duration-300"
                                                     :class="addon.is_active ? 'translate-x-5 border-white' : 'translate-x-0'"></div>
                                            </div>
                                            <span class="ml-3 text-[12px] font-black tracking-widest uppercase text-gray-700">Aktif</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Live Cost Info -->
                                <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Estimasi HPP</span>
                                        <div class="text-sm font-black text-blue-700" x-text="'Rp ' + Number(getHppTarget()).toLocaleString('id-ID')"></div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] font-black uppercase tracking-widest" :class="getMargin() >= 50 ? 'text-green-500' : (getMargin() >= 30 ? 'text-blue-500' : 'text-red-500')">Gross Margin</span>
                                        <div class="text-sm font-black" :class="getMargin() >= 50 ? 'text-green-600' : (getMargin() >= 30 ? 'text-blue-600' : 'text-red-600')" x-text="Number(getMargin()).toFixed(1) + '%'"></div>
                                    </div>
                                </div>

                                <!-- Ingredients Builder -->
                                <div>
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Resep / Bahan Baku</h3>
                                        <button type="button" @click="addIngredient()" class="text-[10px] font-black text-smash-blue bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-smash-blue hover:text-white transition-colors uppercase tracking-widest flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                            Tambah Bahan
                                        </button>
                                    </div>

                                    <div class="space-y-3">
                                        <template x-for="(ing, index) in addon.ingredients" :key="index">
                                            <div class="flex gap-2 p-3 bg-gray-50 border border-gray-100 rounded-2xl transition-all shadow-sm">
                                                <div class="flex-1">
                                                    <select :name="'ingredients['+index+'][id]'" x-model="ing.id" required
                                                        class="appearance-none block w-full border-gray-200 rounded-xl focus:border-smash-blue px-3 py-2 text-xs transition-all bg-white font-bold text-gray-700">
                                                        <option value="">Pilih Bahan Baku...</option>
                                                        <template x-for="rm in rawMaterials" :key="rm.id">
                                                            <option :value="rm.id" x-text="rm.name + ' (' + rm.unit + ')'"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div class="w-24">
                                                    <input type="number" step="0.01" :name="'ingredients['+index+'][quantity]'" x-model="ing.quantity" required placeholder="Qty" min="0"
                                                        class="block w-full border-gray-200 rounded-xl focus:border-smash-blue px-3 py-2 text-xs transition-all bg-white font-bold text-center">
                                                </div>
                                                <button type="button" @click="removeIngredient(index)" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-colors shrink-0">
                                                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <p class="mt-3 text-[10px] font-medium text-gray-400 italic">Bahan baku akan dipotong dari stok saat Checkout POS.</p>
                                </div>
                            </div>

                            <div class="mt-auto px-8 py-6 border-t border-gray-50 flex items-center justify-between bg-white sticky bottom-0 z-20">
                                <button type="button" @click="showModal = false" class="text-[12px] font-black text-gray-400 hover:text-gray-900 transition-colors uppercase tracking-widest px-4">Batal</button>
                                <button type="submit" class="w-full sm:w-auto px-6 py-4 bg-smash-blue text-white rounded-2xl font-black shadow-xl shadow-smash-blue/30 hover:bg-blue-700 transition-all text-xs">
                                    Simpan Add-on
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
