@extends('layouts.app')

@section('header', 'Variasi Produk')

@section('content')
<div x-data="{ 
    showModal: @js($errors->any()),
    editing: @js(old('_method') === 'PUT'),
    defaultEndpoint: '{{ route('variations.store') }}',
    baseEndpoint: '{{ url('/variations') }}',
    formAction: @js(old('_method') === 'PUT' && old('id') ? url('/variations/'.old('id')) : route('variations.store')),
    group: {
        id: @js(old('id', '')),
        name: @js(old('name', '')),
        type: @js(old('type', 'single')),
        is_required: @js(old('is_required', false)),
        options: @js(old('options', [['id' => '', 'name' => '', 'short_name' => '', 'price_modifier' => 0, 'is_default' => false]]))
    },

    addOption() {
        this.group.options.push({ id: '', name: '', short_name: '', price_modifier: 0, is_default: false });
    },
    removeOption(index) {
        if(this.group.options.length > 1) {
            this.group.options.splice(index, 1);
        } else {
            alert('Minimal harus ada 1 opsi variasi.');
        }
    },
    setDefault(index) {
        if (this.group.type === 'single' && this.group.options[index].is_default) {
            this.group.options.forEach((opt, i) => {
                if (i !== index) opt.is_default = false;
            });
        }
    },
    handleTypeChange() {
        if (this.group.type === 'single') {
            let foundDefault = false;
            this.group.options.forEach(opt => {
                if (opt.is_default) {
                    if (foundDefault) opt.is_default = false;
                    else foundDefault = true;
                }
            });
        }
    },
    openAddModal() {
        this.editing = false;
        this.formAction = this.defaultEndpoint;
        this.group = { 
            id: '', name: '', type: 'single', is_required: false, 
            options: [{ id: '', name: '', short_name: '', price_modifier: 0, is_default: false }] 
        };
        this.showModal = true;
    },
    openEditModal(item) {
        this.editing = true;
        this.formAction = `${this.baseEndpoint}/${item.id}`;
        this.group = {
            id: item.id,
            name: item.name,
            type: item.type,
            is_required: item.is_required ? true : false,
            options: item.options.length > 0 ? item.options.map(o => ({
                id: o.id,
                name: o.name,
                short_name: o.short_name || '',
                price_modifier: parseFloat(o.price_modifier),
                is_default: o.is_default ? true : false
            })) : [{ id: '', name: '', short_name: '', price_modifier: 0, is_default: false }]
        };
        this.showModal = true;
    }
}" @keydown.escape="showModal = false" class="space-y-8">

    <!-- Actions Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <form action="{{ route('variations.index') }}" method="GET" class="w-full sm:w-96 relative group">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-smash-blue transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama grup variasi..." 
                   class="block w-full pl-10 pr-3 py-3 border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue text-sm transition-all bg-gray-50/50">
        </form>

        <button @click="openAddModal()" class="w-full sm:w-auto px-6 py-3 bg-smash-blue text-white rounded-2xl font-black shadow-lg shadow-smash-blue/30 hover:bg-blue-700 transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Buat Grup Variasi
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
                        <th class="px-4 md:px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Nama Grup</th>
                        <th class="px-4 md:px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Tipe & Atribut</th>
                        <th class="px-4 md:px-6 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Daftar Opsi</th>
                        <th class="px-4 md:px-6 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 bg-white">
                    @forelse ($variationGroups as $group)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-black text-gray-900">{{ $group->name }}</div>
                                <div class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-widest">
                                    {{ $group->options->count() }} Opsi Tersedia
                                </div>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-2 items-start">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $group->type === 'single' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                        {{ $group->type === 'single' ? 'Radio (Pilih 1)' : 'Checkbox (Banyak)' }}
                                    </span>
                                    @if($group->is_required)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-red-100 text-red-700">Wajib Diisi</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-gray-100 text-gray-500">Opsional</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 md:px-6 py-4 max-w-sm">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($group->options as $opt)
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-gray-50 border border-gray-100 text-[10px] font-bold text-gray-600">
                                            @if($opt->is_default) <svg class="w-3 h-3 text-smash-blue mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> @endif
                                            {{ $opt->name }} 
                                            @if($opt->price_modifier > 0)
                                                <span class="text-green-600 ml-1">+{{ number_format($opt->price_modifier, 0, ',', '.') }}</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-right whitespace-nowrap">
                                <button @click="openEditModal({{ $group->toJson() }})" class="p-2 text-gray-400 hover:text-smash-blue hover:bg-blue-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <form action="{{ route('variations.destroy', $group) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus grup variasi ini secara permanen? Produk yang memiliki grup ini juga akan terpengaruh.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                </div>
                                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Belum ada grup variasi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($variationGroups->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $variationGroups->links() }}
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
                                    <h2 class="text-2xl font-black text-white uppercase tracking-tighter" id="slide-over-title" x-text="editing ? 'Edit Grup Variasi' : 'Buat Grup Variasi'"></h2>
                                    <div class="ml-3 h-7 flex items-center">
                                        <button type="button" @click="showModal = false" class="rounded-xl p-1 bg-white/10 text-white hover:bg-white/20 transition-all">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="mt-2 text-[12px] font-bold text-blue-100/80 uppercase tracking-widest italic">Product Customization Builder</p>
                            </div>
                            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                        </div>

                        <form :action="formAction" method="POST" class="flex-1 flex flex-col font-bold">
                            @csrf
                            <template x-if="editing">
                                <div>
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="id" x-model="group.id">
                                </div>
                            </template>

                            <div class="px-8 py-8 space-y-8 flex-1">
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

                                <!-- Group Settings -->
                                <div class="bg-gray-50/50 p-6 rounded-3xl border border-gray-100 space-y-6">
                                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Pengaturan Grup</h3>
                                    
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Grup</label>
                                        <input type="text" name="name" x-model="group.name" required placeholder="e.g. Level Pedas, Topping Tambahan"
                                            class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-white shadow-sm">
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Tipe Pilihan</label>
                                            <div class="relative">
                                                <select name="type" x-model="group.type" @change="handleTypeChange()" required
                                                    class="appearance-none block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-white shadow-sm font-bold text-gray-700">
                                                    <option value="single">Single (Radio)</option>
                                                    <option value="multiple">Multiple (Checkbox)</option>
                                                </select>
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <p class="mt-2 text-[10px] font-medium text-gray-400">Pilih 1 vs Pilih Banyak.</p>
                                        </div>

                                        <div class="flex items-center pt-6">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="is_required" value="1" x-model="group.is_required" class="sr-only">
                                                <div class="w-11 h-6 rounded-full transition-all duration-300 relative border-2 border-transparent"
                                                     :class="group.is_required ? 'bg-red-500' : 'bg-gray-200'">
                                                    <div class="absolute top-[2px] left-[2px] bg-white border-gray-300 border rounded-full h-4 w-4 transition-all duration-300"
                                                         :class="group.is_required ? 'translate-x-5 border-white' : 'translate-x-0'"></div>
                                                </div>
                                                <span class="ml-3 text-[12px] font-black tracking-widest uppercase transition-colors"
                                                      :class="group.is_required ? 'text-red-500' : 'text-gray-500'">Required di POS</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Options Builder -->
                                <div>
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Daftar Opsi Variasi</h3>
                                        <button type="button" @click="addOption()" class="text-[10px] font-black text-smash-blue bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-smash-blue hover:text-white transition-colors uppercase tracking-widest flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                            Tambah Opsi
                                        </button>
                                    </div>

                                    <div class="space-y-3">
                                        <template x-for="(opt, index) in group.options" :key="index">
                                            <div class="flex flex-col gap-2 p-3 bg-white border border-gray-100 rounded-2xl transition-all shadow-sm">
                                                
                                                <input type="hidden" :name="'options['+index+'][id]'" x-model="opt.id">
                                                
                                                <!-- Top Row: Name and Remove -->
                                                <div class="flex items-center justify-between gap-2 border-b border-gray-50 pb-2">
                                                    <div class="flex-1">
                                                        <input type="text" :name="'options['+index+'][name]'" x-model="opt.name" required placeholder="Nama Opsi"
                                                            class="block w-full border-transparent focus:border-transparent focus:ring-0 px-2 py-1 text-sm font-bold placeholder-gray-300 bg-transparent">
                                                    </div>
                                                    <button type="button" @click="removeOption(index)" class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-xl transition-colors">
                                                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                    </button>
                                                </div>

                                                <!-- Bottom Row: Short Name, Price, Default -->
                                                <div class="flex items-center justify-between gap-2 pt-1">
                                                    <!-- Short Name -->
                                                    <div class="flex-1">
                                                        <label class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Nama Struk</label>
                                                        <input type="text" :name="'options['+index+'][short_name]'" x-model="opt.short_name" placeholder="Opt"
                                                            class="block w-full border-gray-100 rounded-lg focus:border-smash-blue focus:ring-smash-blue px-2 py-1.5 text-[11px] font-bold bg-gray-50/50">
                                                    </div>

                                                    <!-- Price Modifier -->
                                                    <div class="flex-1">
                                                        <label class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Harga (+)</label>
                                                        <div class="relative">
                                                            <span class="absolute inset-y-0 left-0 pl-2 lg:pl-2.5 flex items-center pointer-events-none text-gray-400 font-bold text-[10px] md:text-[11px]">Rp</span>
                                                            <input type="number" :name="'options['+index+'][price_modifier]'" x-model="opt.price_modifier" placeholder="0" min="0"
                                                                class="block w-full pl-6 md:pl-7 lg:pl-8 pr-1 py-1.5 border-gray-100 rounded-lg focus:border-smash-blue focus:ring-smash-blue text-[11px] lg:text-[12px] font-bold bg-gray-50/50">
                                                        </div>
                                                    </div>

                                                    <!-- Default Checkbox -->
                                                    <div class="flex flex-col items-center justify-center pl-2">
                                                        <label class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Set Default</label>
                                                        <label class="flex items-center cursor-pointer">
                                                            <input type="checkbox" :name="'options['+index+'][is_default]'" value="1" x-model="opt.is_default" @change="setDefault(index)" class="sr-only peer">
                                                            <div class="w-5 h-5 rounded border-2 border-gray-200 peer-checked:bg-smash-blue peer-checked:border-smash-blue flex items-center justify-center transition-colors relative">
                                                                <svg x-show="opt.is_default" class="w-3 h-3 text-white absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>

                                            </div>
                                        </template>
                                    </div>
                                    <p class="mt-3 text-[10px] font-medium text-gray-400">Gunakan *Struk* untuk format ringkas di mesin kasir (Maks: 50 char). Harga dihitung sebagai tambahan (Base + Modifier).</p>
                                </div>
                            </div>

                            <div class="mt-auto px-8 py-6 border-t border-gray-50 flex items-center justify-between bg-white sticky bottom-0 z-20">
                                <button type="button" @click="showModal = false" class="text-[12px] font-black text-gray-400 hover:text-gray-900 transition-colors uppercase tracking-widest px-4">Batal</button>
                                <button type="submit" class="w-full sm:w-auto px-6 py-4 bg-smash-blue text-white rounded-2xl font-black shadow-xl shadow-smash-blue/30 hover:bg-blue-700 transition-all text-xs">
                                    Simpan Grup Variasi
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
