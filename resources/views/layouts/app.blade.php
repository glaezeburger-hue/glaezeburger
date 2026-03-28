<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'GLÆZE Burger POS') }}</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- EasyQRCodeJS -->
    <script src="https://cdn.jsdelivr.net/npm/easyqrcodejs@4.6.1/dist/easy.qrcode.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('partials.sidebar', ['isOverlay' => false])

        <!-- Main Content -->
        <main class="flex-1 relative overflow-y-auto focus:outline-none bg-blue-50/20">
            <!-- Header (Mobile/Utility) -->
            <header class="bg-white/80 backdrop-blur-md border-b border-gray-200 py-4 px-8 sticky top-0 z-50 flex items-center justify-between">
                <div class="md:hidden flex items-center space-x-4">
                    <button @click="sidebarOpen = true" class="p-2 -ml-2 mr-2 text-gray-400 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div class="w-8 h-8 bg-smash-blue rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-lg">G</span>
                    </div>
                    <span class="font-extrabold text-lg tracking-tight text-smash-blue">GLÆZE Burger</span>
                </div>
                <div class="hidden md:block">
                    <h2 class="text-xl font-extrabold text-gray-900">@yield('header')</h2>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative p-2 rounded-xl border border-gray-100 bg-gray-50/50">
                        <span class="absolute top-2.5 right-2.5 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                </div>
            </header>

            <div class="py-8 px-4 md:px-8">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6 p-4 bg-green-50 border border-green-100 rounded-2xl flex items-center justify-between shadow-sm">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs font-black text-green-800 uppercase tracking-widest">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-green-800/50 hover:text-green-800 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center justify-between shadow-sm">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="text-xs font-black text-red-800 uppercase tracking-widest">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-red-800/50 hover:text-red-800 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @yield('scripts')
    
    <!-- Global Feature Loaders -->
    @include('partials.global-printer')
    
</body>
</html>
