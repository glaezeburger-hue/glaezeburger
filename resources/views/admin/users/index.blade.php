@extends('layouts.app')

@section('header', 'Kelola Akun')

@section('content')
<div x-data="{ 
    showModal: @js($errors->any()),
    editing: @js(old('_method') === 'PUT'),
    baseUrl: '{{ url('/users') }}',
    formAction: @js(old('_method') === 'PUT' && old('id') ? url('/users/'.old('id')) : route('admin.users.store')),
    user: {
        id: @js(old('id', '')),
        name: @js(old('name', '')),
        email: @js(old('email', '')),
        role: @js(old('role', 'cashier')),
        status: @js(old('status', '1') == '1')
    },

    openCreateModal() {
        this.editing = false;
        this.user = { id: '', name: '', email: '', role: 'cashier', status: true };
        this.formAction = '{{ route('admin.users.store') }}';
        this.showModal = true;
    },

    openEditModal(userData) {
        this.editing = true;
        this.user = { 
            id: userData.id, 
            name: userData.name, 
            email: userData.email, 
            role: userData.role, 
            status: userData.status == 1 
        };
        this.formAction = `${this.baseUrl}/${userData.id}`;
        this.showModal = true;
    }
}" @keydown.escape="showModal = false" class="space-y-8">

    <!-- Actions Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex flex-col md:flex-row md:items-center gap-4 flex-1">
            <!-- Search -->
            <div class="relative w-full md:w-96 group">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-smash-blue transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <form action="{{ route('admin.users.index') }}" method="GET">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        class="block w-full pl-11 pr-4 py-3 border border-gray-200 rounded-2xl leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-smash-blue/10 focus:border-smash-blue sm:text-sm transition-all shadow-sm" 
                        placeholder="Search by name or email...">
                </form>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-2 w-full md:w-auto flex-1">
                <div class="relative flex-1 md:w-48">
                    <select onchange="window.location.href = '?role=' + this.value + '&search={{ request('search') }}&status={{ request('status') }}'"
                        class="appearance-none block w-full pl-4 pr-10 py-3 text-sm border-gray-200 focus:outline-none focus:ring-4 focus:ring-smash-blue/10 focus:border-smash-blue rounded-2xl transition-all shadow-sm bg-white font-medium text-gray-600 uppercase">
                        <option value="">All Roles</option>
                        <option value="owner" {{ request('role') == 'owner' ? 'selected' : '' }}>Owner</option>
                        <option value="cashier" {{ request('role') == 'cashier' ? 'selected' : '' }}>Cashier</option>
                        <option value="kitchen" {{ request('role') == 'kitchen' ? 'selected' : '' }}>Kitchen</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>

                <div class="relative flex-1 md:w-48">
                    <select onchange="window.location.href = '?status=' + this.value + '&search={{ request('search') }}&role={{ request('role') }}'"
                        class="appearance-none block w-full pl-4 pr-10 py-3 text-sm border-gray-200 focus:outline-none focus:ring-4 focus:ring-smash-blue/10 focus:border-smash-blue rounded-2xl transition-all shadow-sm bg-white font-medium text-gray-600 uppercase">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <button @click="openCreateModal()" 
            class="px-6 py-3 bg-smash-blue text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-lg shadow-smash-blue/30 hover:bg-blue-700 transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center justify-center gap-2 h-[46px] shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
            </svg>
            <span class="hidden sm:inline">Add New User</span>
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-100 rounded-2xl flex items-center gap-3">
            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <p class="text-xs font-black text-green-800 uppercase tracking-widest">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 uppercase tracking-tight">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-6 py-5 text-left text-[11px] font-black text-gray-400 tracking-widest whitespace-nowrap">User Info</th>
                        <th class="px-6 py-5 text-left text-[11px] font-black text-gray-400 tracking-widest whitespace-nowrap">Role</th>
                        <th class="px-6 py-5 text-center text-[11px] font-black text-gray-400 tracking-widest whitespace-nowrap">Status</th>
                        <th class="px-6 py-5 text-left text-[11px] font-black text-gray-400 tracking-widest whitespace-nowrap">Last Login</th>
                        <th class="px-6 py-5 text-right text-[11px] font-black text-gray-400 tracking-widest whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 bg-white">
                    @forelse($users as $u)
                    <tr class="hover:bg-blue-50/30 transition-colors group">
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-xl bg-blue-100 flex items-center justify-center text-smash-blue font-black text-[15px] border border-blue-200">
                                    {{ substr($u->name, 0, 1) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-[14px] font-black text-gray-900 leading-none group-hover:text-smash-blue transition-colors">{{ $u->name }}</div>
                                    <div class="text-[11px] font-bold text-gray-400 mt-1 lowercase tracking-normal">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap">
                            @php
                                $roleClass = $u->role === 'owner' ? 'bg-purple-100 text-purple-700 border-purple-200' : 
                                            ($u->role === 'cashier' ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-orange-100 text-orange-700 border-orange-200');
                            @endphp
                            <span class="inline-flex px-2.5 py-1 text-[10px] font-black rounded-lg border {{ $roleClass }} uppercase tracking-widest">
                                {{ $u->role }}
                            </span>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-center">
                            @if($u->status)
                                <span class="px-2.5 py-1 bg-green-100 text-green-700 border border-green-200 text-[10px] font-black rounded-lg tracking-widest">ACTIVE</span>
                            @else
                                <span class="px-2.5 py-1 bg-red-100 text-red-700 border border-red-200 text-[10px] font-black rounded-lg tracking-widest">INACTIVE</span>
                            @endif
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap">
                            <span class="text-[11px] font-bold text-gray-500 tracking-widest">
                                {{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'NEVER' }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-1">
                                <button @click="openEditModal({{ json_encode($u) }})" class="p-2 text-gray-400 hover:text-smash-blue hover:bg-blue-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button type="button" @click="confirmDelete('{{ route('admin.users.destroy', $u->id) }}')" class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest">No users found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $users->links() }}
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
                <div x-show="showModal" x-transition:enter="transform transition ease-in-out duration-500" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" 
                    class="relative w-screen max-w-md">
                    
                    <div class="h-full flex flex-col bg-white shadow-2xl overflow-y-scroll">
                        <!-- Modal Header -->
                        <div class="px-8 py-10 bg-smash-blue relative overflow-hidden">
                            <div class="relative z-10">
                                <div class="flex items-start justify-between">
                                    <h2 class="text-2xl font-black text-white uppercase tracking-tighter" x-text="editing ? 'Edit User' : 'Add New User'"></h2>
                                    <div class="ml-3 h-7 flex items-center">
                                        <button type="button" @click="showModal = false" class="rounded-xl p-1 bg-white/10 text-white hover:bg-white/20 transition-all">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="mt-2 text-[12px] font-bold text-blue-100/80 uppercase tracking-widest leading-none">User Access Management Center</p>
                            </div>
                            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                        </div>

                        <!-- Form Content -->
                        <form :action="formAction" method="POST" class="flex-1 flex flex-col font-bold">
                            @csrf
                            <template x-if="editing">
                                <input type="hidden" name="_method" value="PUT">
                            </template>
                            <input type="hidden" name="id" x-model="user.id">

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
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Full Name</label>
                                        <input type="text" name="name" x-model="user.name" required
                                            class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30 font-bold text-gray-900 border-gray-100">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Email Address</label>
                                        <input type="email" name="email" x-model="user.email" required 
                                            class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30 font-bold text-gray-900 border-gray-100 tracking-normal">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Access Role</label>
                                        <div class="relative">
                                            <select name="role" x-model="user.role" required 
                                                class="appearance-none block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30 font-bold text-gray-900 border-gray-100 uppercase">
                                                <option value="owner">OWNER</option>
                                                <option value="cashier">CASHIER</option>
                                                <option value="kitchen">KITCHEN</option>
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">
                                            Password <template x-if="editing"><span class="text-rose-400 font-medium normal-case tracking-normal">(leave blank to keep current)</span></template>
                                        </label>
                                        <input type="password" name="password" :required="!editing" minlength="8"
                                            class="block w-full border-gray-200 rounded-2xl focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue px-4 py-3 text-sm transition-all bg-gray-50/30 font-bold text-gray-900 border-gray-100 tracking-normal">
                                    </div>
                                </div>

                                <!-- Status (Only for edit) -->
                                <template x-if="editing">
                                    <div class="flex items-center justify-between p-6 bg-gray-50 rounded-[2rem] border border-gray-100 shadow-inner">
                                        <div>
                                            <div class="text-[14px] font-black text-gray-900 leading-none">Account Status</div>
                                            <div class="text-[10px] font-bold text-gray-400 uppercase mt-2 tracking-wide" x-text="user.status ? 'Account is active & allowed to login' : 'Account is inactive & blocked from login'"></div>
                                        </div>
                                        <input type="hidden" name="status" :value="user.status ? 1 : 0">
                                        <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                            <input type="checkbox" id="toggle-status" value="1" x-model="user.status"
                                                class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 border-gray-200 appearance-none cursor-pointer focus:outline-none transition-all duration-300 transform"
                                                :class="{'translate-x-6 border-smash-blue': user.status, 'translate-x-0 border-gray-200': !user.status}"/>
                                            <label for="toggle-status" @click="user.status = !user.status" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-200 cursor-pointer transition-colors duration-300"
                                                :class="{'bg-smash-blue/40': user.status, 'bg-gray-200': !user.status}"></label>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Footer Actions -->
                            <div class="mt-auto px-8 py-8 border-t border-gray-50 flex items-center justify-between bg-white sticky bottom-0">
                                <button type="button" @click="showModal = false" class="text-[12px] font-black text-gray-400 hover:text-gray-900 transition-colors uppercase tracking-widest px-4">Close Info</button>
                                <button type="submit" class="px-10 py-4 bg-smash-blue text-white rounded-2xl font-black shadow-2xl shadow-smash-blue/30 hover:bg-blue-700 transition-all transform hover:-translate-y-1 active:scale-95 uppercase tracking-widest text-xs">
                                    <span x-text="editing ? 'Update Account' : 'Finalize Account'"></span>
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

@section('scripts')
<script>
    function confirmDelete(url) {
        Swal.fire({
            title: 'ARCHIVE ACCOUNT?',
            text: "User will be SOFT DELETED. Identity remains in past invoices but they cannot login anymore.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0ea5e9',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'YES, ARCHIVE!',
            cancelButtonText: 'CANCEL',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-[2rem] border-none shadow-2xl font-bold uppercase tracking-tight',
                title: 'text-2xl font-black text-gray-900',
                confirmButton: 'rounded-xl px-6 py-3 font-black text-[10px] uppercase tracking-widest',
                cancelButton: 'rounded-xl px-6 py-3 font-black text-[10px] uppercase tracking-widest'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        })
    }
</script>
@endsection
