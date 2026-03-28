@extends('layouts.app')

@section('header', 'Product Management')

@section('content')
<div x-data="{ 
    showCategoryModal: false,
    newCategoryName: '',
    showModal: @js($errors->any()),
    editing: @js(old('_method') === 'PUT'),
    baseUrl: '{{ url('/products') }}',
    formAction: @js(old('_method') === 'PUT' && old('id') ? url('/products/'.old('id')) : route('products.store')),
    product: {
        id: @js(old('id', '')),
        name: @js(old('name', '')),
        category_id: @js(old('category_id', '')),
        sku: @js(old('sku', '')),
        cost_price: @js(old('cost_price', '')),
        selling_price: @js(old('selling_price', '')),
        stock: @js(old('stock', '')),
        description: @js(old('description', '')),
        is_active: @js(old('is_active', '1') == '1'),
        is_recipe_based: @js(old('is_recipe_based', '') == '1'),
        ingredients: @js(old('ingredients', [])),
        variation_groups: @js(old('variation_groups', []))
    },
    imagePreview: null,
    categories: @js($categories),
    rawMaterials: @js($rawMaterials ?? []),
    variationGroupsList: @js($variationGroups ?? []),

    get totalHpp() {
        if (!this.product.is_recipe_based || !this.product.ingredients.length) return 0;
        return this.product.ingredients.reduce((sum, ing) => {
            const rm = this.rawMaterials.find(m => String(m.id) === String(ing.id));
            const cost = rm ? parseFloat(rm.cost_per_unit || 0) : 0;
            const qty = parseFloat(ing.quantity || 0);
            return sum + (qty * cost);
        }, 0);
    },

    get grossMargin() {
        const price = parseFloat(this.product.selling_price || 0);
        if (price <= 0) return null;
        return (((price - this.totalHpp) / price) * 100).toFixed(1);
    },

    get marginColor() {
        if (this.grossMargin === null) return 'text-gray-400';
        if (this.grossMargin < 30) return 'text-red-600';
        if (this.grossMargin <= 50) return 'text-orange-500';
        return 'text-green-600';
    },

    get marginBg() {
        if (this.grossMargin === null) return 'bg-gray-50 border-gray-100';
        if (this.grossMargin < 30) return 'bg-red-50 border-red-100';
        if (this.grossMargin <= 50) return 'bg-orange-50 border-orange-100';
        return 'bg-green-50 border-green-100';
    },

    ingredientCost(index) {
        const ing = this.product.ingredients[index];
        if (!ing) return 0;
        const rm = this.rawMaterials.find(m => String(m.id) === String(ing.id));
        const cost = rm ? parseFloat(rm.cost_per_unit || 0) : 0;
        const qty = parseFloat(ing.quantity || 0);
        return qty * cost;
    },

    formatRupiah(val) {
        return new Intl.NumberFormat('id-ID').format(Math.round(val));
    },
    
    openAddModal() {
        this.editing = false;
        this.formAction = '{{ route('products.store') }}';
        this.product = { id: '', name: '', category_id: '', sku: '', cost_price: '', selling_price: '', stock: '', description: '', is_active: true, is_recipe_based: false, ingredients: [], variation_groups: [] };
        this.imagePreview = null;
        this.showModal = true;
    },
    
    openEditModal(item) {
        this.editing = true;
        this.formAction = `${this.baseUrl}/${item.id}`;
        this.product = {
            ...item,
            is_recipe_based: item.is_recipe_based == 1,
            ingredients: item.raw_materials ? item.raw_materials.map(rm => ({
                id: String(rm.id),
                quantity: rm.pivot.quantity
            })) : [],
            variation_groups: item.variation_groups ? item.variation_groups.map(vg => String(vg.id)) : []
        };
        this.imagePreview = item.image_path ? `/storage/${item.image_path}` : null;
        this.showModal = true;
    },
    
    previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            this.imagePreview = URL.createObjectURL(file);
        }
    },
    
    async toggleActive(id) {
        try {
            const response = await fetch(`/products/${id}/toggle-active`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
        } catch (error) {
            console.error('Error toggling status:', error);
        }
    },

    async addCategory() {
        if (!this.newCategoryName.trim()) return;
        
        try {
            const response = await fetch('{{ route('categories.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: this.newCategoryName })
            });
            
            const data = await response.json();
            if (data.category) {
                this.categories.push(data.category);
                this.newCategoryName = '';
                this.showCategoryModal = false;
                Swal.fire({
                    title: 'Success!',
                    text: 'Category added successfully.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-3xl' }
                });
            }
        } catch (error) {
            console.error('Error adding category:', error);
        }
    }
}" @keydown.escape="showModal = false" class="space-y-8">

    <!-- Actions Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex flex-col md:flex-row md:items-center gap-4 flex-1">
            <div class="relative w-full md:w-96 group">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-smash-blue transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <form action="{{ route('products.index') }}" method="GET">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        class="block w-full pl-11 pr-4 py-3 border border-gray-200 rounded-2xl leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-smash-blue/10 focus:border-smash-blue sm:text-sm transition-all shadow-sm" 
                        placeholder="Search by name or SKU...">
                </form>
            </div>
            <div class="flex items-center gap-2 w-full md:w-auto">
                <div class="relative w-full md:w-56">
                    <select onchange="window.location.href = '?category_id=' + this.value + '&search={{ request('search') }}'"
                        class="appearance-none block w-full pl-4 pr-10 py-3 text-sm border-gray-200 focus:outline-none focus:ring-4 focus:ring-smash-blue/10 focus:border-smash-blue rounded-2xl transition-all shadow-sm bg-white font-medium text-gray-600">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
                <button @click="showCategoryModal = true" 
                    class="h-[46px] w-[46px] flex items-center justify-center text-smash-blue bg-blue-50 border border-blue-100/50 rounded-2xl hover:bg-smash-blue hover:text-white transition-all shadow-sm active:scale-90" 
                    title="Quick Add Category">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </button>
                <button @click="openAddModal()" 
                    class="h-[46px] w-[46px] flex items-center justify-center text-white bg-smash-blue border border-blue-100/50 rounded-2xl hover:bg-blue-50 hover:text-smash-blue transition-all shadow-sm active:scale-90" 
                    title="Add Product">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </button>
            </div>
        </div>    
    </div>

    <!-- Product Table -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-50 uppercase tracking-tight">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 md:px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Product Info</th>
                        <th class="px-4 md:px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Category</th>
                        <th class="px-4 md:px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Pricing</th>
                        <th class="px-4 md:px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Margin</th>
                        <th class="px-4 md:px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Stock</th>
                        <th class="px-4 md:px-8 py-5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Status</th>
                        <th class="px-4 md:px-8 py-5 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 bg-white">
                    @forelse($products as $prod)
                    <tr class="hover:bg-blue-50/20 transition-colors group">
                        <td class="px-4 md:px-8 py-5 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-14 w-14 flex-shrink-0">
                                    @if($prod->image_path)
                                        <img class="h-14 w-14 rounded-2xl object-cover border border-gray-100 shadow-sm group-hover:shadow-md transition-shadow" src="{{ asset('storage/' . $prod->image_path) }}" alt="{{ $prod->name }}">
                                    @else
                                        <div class="h-14 w-14 rounded-2xl bg-gray-50 flex items-center justify-center border border-gray-100 text-gray-300">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-[15px] font-bold text-gray-900 group-hover:text-smash-blue transition-colors">{{ $prod->name }}</div>
                                    <div class="text-[11px] font-bold text-gray-400 tracking-wider uppercase mt-0.5">{{ $prod->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 md:px-8 py-5 whitespace-nowrap">
                            <span class="inline-flex px-3 py-1 text-[11px] font-bold rounded-lg bg-blue-50 text-smash-blue border border-blue-100/50 uppercase">
                                {{ $prod->category->name }}
                            </span>
                        </td>
                        <td class="px-4 md:px-8 py-5 font-bold whitespace-nowrap">
                            <div class="text-[14px] text-gray-900 leading-tight">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</div>
                            <div class="text-[11px] text-gray-400 mt-1 uppercase">HPP: Rp {{ number_format($prod->calculateHpp(), 0, ',', '.') }}</div>
                        </td>
                        <td class="px-4 md:px-8 py-5 whitespace-nowrap">
                            @php
                                $margin = $prod->gross_margin;
                                if ($margin === null) {
                                    $marginClass = 'bg-gray-50 text-gray-400 border-gray-100';
                                } elseif ($margin < 30) {
                                    $marginClass = 'bg-red-50 text-red-600 border-red-100';
                                } elseif ($margin <= 50) {
                                    $marginClass = 'bg-orange-50 text-orange-600 border-orange-100';
                                } else {
                                    $marginClass = 'bg-green-50 text-green-600 border-green-100';
                                }
                            @endphp
                            <span class="inline-flex px-3 py-1 text-[11px] font-black rounded-lg border {{ $marginClass }} uppercase">
                                {{ $margin !== null ? $margin . '%' : 'N/A' }}
                            </span>
                        </td>
                        <td class="px-4 md:px-8 py-5 whitespace-nowrap">
                            @php
                                $stockClass = $prod->calculated_stock <= 0 ? 'bg-red-50 text-red-600 border-red-100' : ($prod->calculated_stock <= 10 ? 'bg-orange-50 text-orange-600 border-orange-100' : 'bg-green-50 text-green-600 border-green-100');
                            @endphp
                            <span class="inline-flex px-3 py-1 text-[11px] font-black rounded-lg border {{ $stockClass }} uppercase">
                                {{ $prod->calculated_stock }} Units
                            </span>
                        </td>
                        <td class="px-4 md:px-8 py-5 whitespace-nowrap">
                            <button @click="toggleActive({{ $prod->id }})" class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none ring-offset-2 focus:ring-2 focus:ring-smash-blue/30" 
                                :class="{'bg-smash-blue': {{ $prod->is_active ? 'true' : 'false' }}, 'bg-gray-200': !{{ $prod->is_active ? 'true' : 'false' }}}"
                                x-data="{ active: {{ $prod->is_active ? 'true' : 'false' }} }" @click="active = !active">
                                <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-lg transform ring-0 transition ease-in-out duration-200"
                                    :class="{'translate-x-5': active, 'translate-x-0': !active}"></span>
                            </button>
                        </td>
                        <td class="px-4 md:px-8 py-5 text-right font-medium whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="openEditModal({{ json_encode($prod) }})" class="p-2.5 text-gray-400 hover:text-smash-blue hover:bg-blue-50 rounded-xl transition-all border border-transparent hover:border-blue-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 00-2 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <form action="{{ route('products.index') }}/{{ $prod->id }}" method="POST" onsubmit="return confirm('Archive this product?')">
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
                        <td colspan="7" class="px-8 py-20 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <div class="h-20 w-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <p class="font-black text-gray-900 uppercase tracking-widest text-[13px]">No products found</p>
                                <p class="text-[11px] font-bold text-gray-400 mt-1 uppercase tracking-tight">Start by adding your first product to the system.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
        <div class="px-8 py-5 bg-gray-50/50 border-t border-gray-100">
            {{ $products->links() }}
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
                                    <h2 class="text-2xl font-black text-white uppercase tracking-tighter" id="slide-over-title" x-text="editing ? 'Edit Product' : 'Add New Product'"></h2>
                                    <div class="ml-3 h-7 flex items-center">
                                        <button type="button" @click="showModal = false" class="rounded-xl p-1 bg-white/10 text-white hover:bg-white/20 transition-all">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="mt-2 text-[12px] font-bold text-blue-100/80 uppercase tracking-widest italic">Product Catalog Management</p>
                            </div>
                            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                        </div>

                        <form :action="formAction" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col font-bold">
                            @csrf
                            <template x-if="editing">
                                <div>
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="id" x-model="product.id">
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

                                <!-- Image Upload -->
                                <div class="space-y-3">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest">Product Image</label>
                                    <div class="flex items-center space-x-6">
                                        <div class="w-28 h-28 rounded-3xl border-2 border-dashed border-gray-100 flex items-center justify-center overflow-hidden bg-gray-50/50 shadow-inner group">
                                            <template x-if="imagePreview">
                                                <img :src="imagePreview" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!imagePreview">
                                                <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </template>
                                        </div>
                                        <div class="flex-1">
                                            <input type="file" name="image" @change="previewImage($event)" class="hidden" id="product-image">
                                            <label for="product-image" class="inline-flex items-center px-6 py-3 border border-gray-100 rounded-2xl shadow-sm text-xs font-black text-gray-700 bg-white hover:bg-gray-50 cursor-pointer transition-all uppercase tracking-widest border-b-4 hover:border-b-2 hover:translate-y-0.5 active:translate-y-1">
                                                Change Image
                                            </label>
                                            <p class="mt-3 text-[10px] font-bold text-gray-400 uppercase tracking-tight">Requirement: JPG, PNG • Max 2MB</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Basic Info -->
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Product Name</label>
                                        <input type="text" name="name" x-model="product.name" required
                                            class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">SKU Code</label>
                                            <input type="text" name="sku" x-model="product.sku" required
                                                class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Category</label>
                                            <div class="relative">
                                                <select name="category_id" x-model="product.category_id" required
                                                    class="appearance-none block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30 font-bold text-gray-600">
                                                    <option value="">Select Category</option>
                                                    <template x-for="cat in categories" :key="cat.id">
                                                        <option :value="cat.id" x-text="cat.name"></option>
                                                    </template>
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

                                <!-- Pricing & Stock -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Cost Price (HPP)</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <span class="text-gray-400 font-black text-[11px]">Rp</span>
                                            </div>
                                            <input type="number" name="cost_price" x-model="product.cost_price" required
                                                :readonly="product.is_recipe_based"
                                                :class="product.is_recipe_based ? 'bg-gray-100 cursor-not-allowed' : 'bg-gray-50/30'"
                                                class="block w-full pl-10 pr-4 py-3 border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue text-sm transition-all shadow-sm">
                                        </div>
                                        <template x-if="product.is_recipe_based">
                                            <p class="mt-1 text-[10px] font-bold text-smash-blue uppercase tracking-widest">Auto-calculated from recipe</p>
                                        </template>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Selling Price</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <span class="text-gray-400 font-black text-[11px]">Rp</span>
                                            </div>
                                            <input type="number" name="selling_price" x-model="product.selling_price" required
                                                class="block w-full pl-10 pr-4 py-3 border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue text-sm transition-all bg-gray-50/30 shadow-sm border-smash-blue/20">
                                        </div>
                                    </div>
                                    <div class="col-span-2" x-show="!product.is_recipe_based">
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Stock Quantity</label>
                                        <input type="number" name="stock" x-model="product.stock" :required="!product.is_recipe_based"
                                            class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30">
                                    </div>
                                    <div class="col-span-2 space-y-4">
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 shadow-inner">
                                            <div>
                                                <div class="text-[14px] font-black text-gray-900 leading-none">Recipe Based</div>
                                                <div class="text-[10px] font-bold text-gray-400 uppercase mt-2 tracking-wide">Deduct raw material stock on sale</div>
                                            </div>
                                            <input type="hidden" name="is_recipe_based" value="0">
                                            <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                                <input type="checkbox" name="is_recipe_based" id="toggle-recipe" value="1" x-model="product.is_recipe_based"
                                                    class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 border-gray-200 appearance-none cursor-pointer focus:outline-none transition-all duration-300 transform"
                                                    :class="{'translate-x-6 border-smash-blue': product.is_recipe_based, 'translate-x-0 border-gray-200': !product.is_recipe_based}"/>
                                                <label for="toggle-recipe" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-200 cursor-pointer transition-colors duration-300"
                                                    :class="{'bg-smash-blue/40': product.is_recipe_based, 'bg-gray-200': !product.is_recipe_based}"></label>
                                            </div>
                                        </div>

                                        <!-- Recipe Builder -->
                                        <div x-show="product.is_recipe_based" x-transition x-effect="if (product.is_recipe_based && product.ingredients.length > 0) { product.cost_price = Math.round(totalHpp); }" class="space-y-3 p-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
                                            <h4 class="text-xs font-black text-smash-blue uppercase tracking-widest mb-4">Recipe Items</h4>
                                            
                                            <template x-for="(ingredient, index) in product.ingredients" :key="index">
                                                <div class="flex items-center gap-2">
                                                    <div class="flex-1">
                                                        <select :name="`ingredients[${index}][id]`" x-model="product.ingredients[index].id" :required="product.is_recipe_based"
                                                            class="appearance-none block w-full border-gray-200 rounded-xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-3 py-2 text-sm transition-all bg-gray-50/30 font-bold text-gray-600">
                                                            <option value="">Select Ingredient</option>
                                                            @foreach($rawMaterials as $rm)
                                                                <option value="{{ $rm->id }}">{{ $rm->name }} ({{ $rm->unit }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="w-20">
                                                        <input type="number" step="0.01" :name="`ingredients[${index}][quantity]`" x-model="product.ingredients[index].quantity" :required="product.is_recipe_based"
                                                            placeholder="Qty"
                                                            class="block w-full border-gray-200 rounded-xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-3 py-2 text-sm transition-all bg-gray-50/30">
                                                    </div>
                                                    <div class="w-28 text-right">
                                                        <span class="text-[11px] font-black text-gray-500" x-text="'Rp ' + formatRupiah(ingredientCost(index))"></span>
                                                    </div>
                                                    <button type="button" @click="product.ingredients.splice(index, 1)" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </div>
                                            </template>
                                            
                                            <button type="button" @click="product.ingredients.push({ id: '', quantity: '' })" class="mt-2 w-full py-2 border-2 border-dashed border-gray-200 rounded-xl text-xs font-black text-gray-400 hover:text-smash-blue hover:border-smash-blue/30 hover:bg-blue-50/50 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                Add Ingredient
                                            </button>

                                            <!-- HPP Cost Breakdown Card -->
                                            <template x-if="product.ingredients.length > 0">
                                                <div class="mt-4 p-4 rounded-2xl border" :class="marginBg">
                                                    <h5 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                                        HPP Cost Breakdown
                                                    </h5>
                                                    <div class="space-y-2">
                                                        <div class="flex items-center justify-between">
                                                            <span class="text-[11px] font-bold text-gray-500 uppercase">Total HPP</span>
                                                            <span class="text-sm font-black text-gray-900" x-text="'Rp ' + formatRupiah(totalHpp)"></span>
                                                        </div>
                                                        <div class="flex items-center justify-between">
                                                            <span class="text-[11px] font-bold text-gray-500 uppercase">Selling Price</span>
                                                            <span class="text-sm font-black text-gray-900" x-text="'Rp ' + formatRupiah(product.selling_price || 0)"></span>
                                                        </div>
                                                        <div class="pt-2 mt-2 border-t border-gray-200/50 flex items-center justify-between">
                                                            <span class="text-[11px] font-black uppercase tracking-widest" :class="marginColor">Gross Margin</span>
                                                            <span class="text-lg font-black" :class="marginColor" x-text="grossMargin !== null ? grossMargin + '%' : 'N/A'"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="flex items-center justify-between p-6 bg-gray-50 rounded-[2rem] border border-gray-100 shadow-inner">
                                    <div>
                                        <div class="text-[14px] font-black text-gray-900 leading-none">Active Visibility</div>
                                        <div class="text-[10px] font-bold text-gray-400 uppercase mt-2 tracking-wide">Show product in point of sale</div>
                                    </div>
                                    <input type="hidden" name="is_active" value="0">
                                    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                        <input type="checkbox" name="is_active" id="toggle-active" value="1" x-model="product.is_active"
                                            class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 border-gray-200 appearance-none cursor-pointer focus:outline-none transition-all duration-300 transform"
                                            :class="{'translate-x-6 border-smash-blue': product.is_active, 'translate-x-0 border-gray-200': !product.is_active}"/>
                                        <label for="toggle-active" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-200 cursor-pointer transition-colors duration-300"
                                            :class="{'bg-smash-blue/40': product.is_active, 'bg-gray-200': !product.is_active}"></label>
                                    </div>
                                </div>

                                <!-- Variation Groups -->
                                <div class="space-y-4">
                                    <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2 border-b border-gray-100 pb-2">Variasi Produk (Opsional)</h3>
                                    <div class="grid grid-cols-1 gap-3 max-h-48 overflow-y-auto pr-2">
                                        <template x-for="vg in variationGroupsList" :key="vg.id">
                                            <label class="flex items-center p-3 bg-white border border-gray-100 rounded-2xl cursor-pointer hover:border-smash-blue/30 transition-all shadow-sm group">
                                                <input type="checkbox" name="variation_groups[]" :value="vg.id" x-model="product.variation_groups"
                                                    class="w-5 h-5 rounded-md border-2 border-gray-200 text-smash-blue focus:ring-smash-blue focus:ring-offset-0 transition-colors cursor-pointer">
                                                <div class="ml-3 flex-1">
                                                    <span class="text-[12px] font-black text-gray-900 leading-none group-hover:text-smash-blue transition-colors" x-text="vg.name"></span>
                                                    <span class="text-[10px] font-bold text-gray-400 block mt-0.5"><span x-text="vg.options.length"></span> Opsi &bull; <span x-text="vg.type === 'single' ? 'Pilih 1 (Radio)' : 'Pilih Banyak (Checkbox)'"></span></span>
                                                </div>
                                            </label>
                                        </template>
                                        <template x-if="variationGroupsList.length === 0">
                                            <p class="text-[11px] font-bold text-gray-400 italic">Belum ada grup variasi yang dibuat.</p>
                                        </template>
                                    </div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Centang variasi yang akan aktif untuk produk ini di halaman POS Kasir.</p>
                                </div>
                            </div>

                            <div class="mt-auto px-8 py-8 border-t border-gray-50 flex items-center justify-between bg-white sticky bottom-0">
                                <button type="button" @click="showModal = false" class="text-[12px] font-black text-gray-400 hover:text-gray-900 transition-colors uppercase tracking-widest px-4">Close Info</button>
                                <button type="submit" class="px-10 py-4 bg-smash-blue text-white rounded-2xl font-black shadow-2xl shadow-smash-blue/30 hover:bg-blue-700 transition-all transform hover:-translate-y-1 active:scale-95 uppercase tracking-widest text-xs">
                                    Finalize Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Category Slide-over -->
    <div x-show="showCategoryModal" class="fixed inset-0 overflow-hidden z-[60] text-[13px]" aria-labelledby="category-slide-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="absolute inset-0 overflow-hidden">
            <!-- Overlay -->
            <div x-show="showCategoryModal" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showCategoryModal = false" aria-hidden="true"></div>

            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div x-show="showCategoryModal" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" 
                    class="relative w-screen max-w-md">
                    
                    <div class="h-full flex flex-col bg-white shadow-2xl overflow-hidden">
                        <div class="px-8 py-10 bg-smash-blue relative overflow-hidden">
                            <div class="relative z-10">
                                <div class="flex items-start justify-between">
                                    <h2 class="text-2xl font-black text-white uppercase tracking-tighter" id="category-slide-title">New Category</h2>
                                    <div class="ml-3 h-7 flex items-center">
                                        <button type="button" @click="showCategoryModal = false" class="rounded-xl p-1 bg-white/10 text-white hover:bg-white/20 transition-all">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="mt-2 text-[12px] font-bold text-blue-100/80 uppercase tracking-widest italic leading-none">Adding to Catalog System</p>
                            </div>
                            <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                        </div>

                        <div class="flex-1 flex flex-col font-bold p-8 space-y-8">
                            <div>
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Group Name</label>
                                <input type="text" x-model="newCategoryName" @keydown.enter="addCategory()" placeholder="e.g. Beverages, Snacks..."
                                    class="block w-full border-gray-100 rounded-2xl bg-gray-50 focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-4 text-sm transition-all font-bold placeholder-gray-300">
                                <p class="mt-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Tip: Keep category names short and clear for the best POS display experience.</p>
                            </div>
                            
                            <div class="mt-auto space-y-4 pt-10 border-t border-gray-50">
                                <button @click="addCategory()" 
                                    class="w-full py-5 bg-smash-blue text-white rounded-2xl font-black shadow-2xl shadow-smash-blue/20 hover:bg-blue-700 transition-all transform hover:-translate-y-1 active:scale-95 uppercase tracking-widest text-xs flex items-center justify-center gap-3">
                                    <span>Create Category</span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </button>
                                <button @click="showCategoryModal = false" class="w-full py-2 text-[11px] font-black text-gray-400 hover:text-gray-900 transition-colors uppercase tracking-widest">
                                    Discard Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
