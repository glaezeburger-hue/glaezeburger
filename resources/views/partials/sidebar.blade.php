@php
    $isOverlay = $isOverlay ?? false;
@endphp

<!-- Sidebar Backdrop -->
<div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm {{ $isOverlay ? '' : 'md:hidden' }}"></div>

<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transition-transform duration-300 ease-in-out flex flex-col {{ $isOverlay ? '' : 'md:relative md:translate-x-0' }}">
    
    <!-- Close Button for Overlay -->
    <button @click="sidebarOpen = false" class="absolute top-4 right-4 p-2 text-gray-400 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors {{ $isOverlay ? '' : 'md:hidden' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>

    <div class="p-6 mb-2">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-smash-blue rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                <span class="text-white font-extrabold text-xl">G</span>
            </div>
            <div>
                <h2 class="text-smash-blue font-black text-xl tracking-tighter leading-none">GLÆZE BURGER</h2>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
        @if(auth()->check() && auth()->user()->role === 'owner')
        <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Main Menu</p>
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-smash-blue text-white shadow-md shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-smash-blue' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Dashboard
        </a>
        <a href="{{ route('products.index') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('products.*') ? 'bg-smash-blue text-white shadow-md shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-smash-blue' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            Products
        </a>
        <a href="{{ route('raw-materials.index') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('raw-materials.*') ? 'bg-smash-blue text-white shadow-md shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-smash-blue' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            Raw Materials
        </a>
        <a href="{{ route('variations.index') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('variations.*') ? 'bg-smash-blue text-white shadow-md shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-smash-blue' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
            </svg>
            Variations
        </a>
        <a href="{{ route('addons.index') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('addons.*') ? 'bg-smash-blue text-white shadow-md shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-smash-blue' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add-ons
        </a>
        <a href="{{ route('vouchers.index') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('vouchers.*') ? 'bg-smash-blue text-white shadow-md shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-smash-blue' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
            </svg>
            Vouchers
        </a>
        <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-smash-blue text-white shadow-md shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-smash-blue' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            Kelola Akun
        </a>
        @endif
        
        <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-6 mb-2">Operations</p>
        @if(auth()->check() && in_array(auth()->user()->role, ['owner', 'cashier']))
        <a href="{{ route('pos.index') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('pos.index') ? 'bg-smash-blue text-white shadow-md shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-smash-blue' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            Point of Sale
        </a>
        <a href="{{ route('pos.shift.history') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('pos.shift.history') || request()->routeIs('pos.shift.show') ? 'bg-smash-blue text-white shadow-md shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-smash-blue' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Riwayat Shift
        </a>
        @endif
        @if(auth()->check() && in_array(auth()->user()->role, ['owner', 'kitchen']))
        <a href="{{ route('kds.index') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('kds.*') ? 'bg-smash-blue text-white shadow-md shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-smash-blue' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            Kitchen Display
        </a>
        @endif
        @if(auth()->check() && auth()->user()->role === 'owner')
        <a href="{{ route('transactions.index') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('transactions.index') || request()->routeIs('transactions.show') ? 'bg-smash-blue text-white shadow-md shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-smash-blue' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Order History
        </a>
        <a href="{{ route('transactions.import') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('transactions.import') ? 'bg-smash-blue text-white shadow-md shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-smash-blue' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            Import Data
        </a>
        @endif

        <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-6 mb-2">Finance</p>
        @if(auth()->check() && auth()->user()->role === 'owner')
        <a href="{{ route('finance.dashboard') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('finance.*') ? 'bg-smash-blue text-white shadow-md shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-smash-blue' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            Financial Report
        </a>
        <a href="{{ route('expenses.index') }}" class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('expenses.*') || request()->routeIs('wastages.*') ? 'bg-smash-blue text-white shadow-md shadow-blue-200' : 'text-gray-500 hover:bg-blue-50 hover:text-smash-blue' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Expenses & Wastages
        </a>
        @endif
    </nav>

    <div class="p-4 border-t border-gray-100">
        @if(auth()->check())
        <div class="flex flex-col space-y-3 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm p-3">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-smash-blue font-bold text-sm uppercase">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="flex-1 overflow-hidden">
                    <p class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-tight truncate">{{ ucfirst(auth()->user()->role) }}</p>
                </div>
            </div>
            @if(in_array(auth()->user()->role, ['owner', 'cashier']) && request()->routeIs('pos.index'))
            <a href="{{ route('pos.shift.index') }}" 
                class="w-full flex items-center justify-center px-3 py-2 text-xs font-bold text-orange-600 bg-orange-50 hover:bg-orange-100 hover:text-orange-700 rounded-xl transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Manage Shift
            </a>
            @endif
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center px-3 py-2 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 hover:text-red-700 rounded-xl transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
        @endif
    </div>
</aside>
