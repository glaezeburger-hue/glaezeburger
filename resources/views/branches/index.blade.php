@extends('layouts.app')
@section('header', 'Manajemen Cabang')

@section('content')
<div x-data="branchManager()" class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">Manajemen Cabang</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola semua cabang outlet GLÆZE Burger</p>
        </div>
        <button @click="showModal = true; resetForm()" class="inline-flex items-center px-5 py-2.5 bg-smash-blue text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Tambah Cabang
        </button>
    </div>

    {{-- Branch Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($branches as $branch)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-extrabold text-lg shadow-md {{ $branch->is_active ? 'bg-gradient-to-br from-blue-500 to-indigo-600' : 'bg-gray-400' }}">
                            {{ strtoupper(substr($branch->code, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">{{ $branch->name }}</h3>
                            <span class="inline-block mt-0.5 px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider {{ $branch->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-lg">{{ $branch->code }}</span>
                </div>

                <div class="space-y-2 text-sm text-gray-500">
                    @if($branch->address)
                    <div class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>{{ $branch->address }}{{ $branch->city ? ', ' . $branch->city : '' }}</span>
                    </div>
                    @endif
                    @if($branch->phone)
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        <span>{{ $branch->phone }}</span>
                    </div>
                    @endif
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="flex space-x-4 text-xs text-gray-400">
                        <span>{{ $branch->users_count }} staff</span>
                        <span>{{ $branch->transactions_count }} transaksi</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button @click="editBranch({{ $branch->toJson() }})" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <form method="POST" action="{{ route('branches.toggle', $branch) }}" class="inline">
                            @csrf
                            <button type="submit" class="p-2 rounded-lg transition-colors {{ $branch->is_active ? 'text-gray-400 hover:text-red-600 hover:bg-red-50' : 'text-gray-400 hover:text-green-600 hover:bg-green-50' }}" title="{{ $branch->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                @if($branch->is_active)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            <p class="font-semibold">Belum ada cabang</p>
        </div>
        @endforelse
    </div>

    {{-- Create/Edit Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                <h3 class="text-lg font-extrabold text-gray-900 mb-6" x-text="editMode ? 'Edit Cabang' : 'Tambah Cabang Baru'"></h3>
                
                <form :action="editMode ? '{{ url('branches') }}/' + form.id : '{{ route('branches.store') }}'" method="POST">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Cabang *</label>
                                <input type="text" name="name" x-model="form.name" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400" placeholder="Centra Niaga Square">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Kode (maks 10) *</label>
                                <input type="text" name="code" x-model="form.code" required maxlength="10" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm uppercase focus:ring-2 focus:ring-blue-200 focus:border-blue-400" placeholder="CNS">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Alamat</label>
                            <input type="text" name="address" x-model="form.address" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400" placeholder="Jl. Contoh No. 123">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Kota</label>
                                <input type="text" name="city" x-model="form.city" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400" placeholder="Cikarang Utara">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Telepon</label>
                                <input type="text" name="phone" x-model="form.phone" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400" placeholder="0812xxxxxxxx">
                            </div>
                        </div>
                        
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pt-2">Pengaturan Struk</p>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Header Struk</label>
                            <input type="text" name="receipt_header" x-model="form.receipt_header" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400" placeholder="Street Smash Burger">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Footer Struk</label>
                            <input type="text" name="receipt_footer" x-model="form.receipt_footer" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400" placeholder="Follow & Tag Us">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Instagram</label>
                                <input type="text" name="receipt_instagram" x-model="form.receipt_instagram" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400" placeholder="@glaezeburger">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">TikTok</label>
                                <input type="text" name="receipt_tiktok" x-model="form.receipt_tiktok" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400" placeholder="@glaezeburger">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
                        <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-smash-blue rounded-xl shadow-md shadow-blue-200 hover:bg-blue-700 transition-colors" x-text="editMode ? 'Simpan Perubahan' : 'Tambah Cabang'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function branchManager() {
    return {
        showModal: false,
        editMode: false,
        form: {
            id: null, name: '', code: '', address: '', city: '', phone: '',
            receipt_header: '', receipt_footer: '', receipt_instagram: '', receipt_tiktok: ''
        },
        resetForm() {
            this.editMode = false;
            this.form = { id: null, name: '', code: '', address: '', city: '', phone: '', receipt_header: '', receipt_footer: '', receipt_instagram: '', receipt_tiktok: '' };
        },
        editBranch(branch) {
            this.editMode = true;
            this.form = { ...branch };
            this.showModal = true;
        }
    };
}
</script>
@endsection
