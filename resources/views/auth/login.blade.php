<x-guest-layout>
    <div class="flex h-screen overflow-hidden font-sans">
        <!-- Branding Side (Left) -->
        <div class="hidden lg:flex lg:w-[60%] relative h-full flex-col justify-between p-20 overflow-hidden bg-gray-900">
            <!-- Background Image -->
            <img src="{{ asset('images/branding/login-hero.png') }}" class="absolute inset-0 w-full h-full object-cover opacity-60 scale-105 hover:scale-100 transition-transform duration-[10s] ease-linear">
            
            <!-- Overlay Gradient -->
            <div class="absolute inset-0 bg-gradient-to-tr from-gray-900 via-gray-900/40 to-transparent"></div>

            <!-- Branding Content -->
            <div class="relative z-10 flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center gap-4 group">
                    <div class="w-16 h-16 bg-smash-blue rounded-[2rem] flex items-center justify-center shadow-2xl shadow-smash-blue/40 transform -rotate-6 group-hover:rotate-0 transition-transform duration-500">
                        <span class="text-white font-black text-3xl italic">G</span>
                    </div>
                    <div>
                        <h1 class="text-white font-black text-3xl tracking-tighter leading-none italic uppercase">GLÆZE</h1>
                        <p class="text-blue-100/60 font-black text-[10px] tracking-widest uppercase mt-1">Gourmet Burger Specialist</p>
                    </div>
                </div>

                <!-- Hero Text -->
                <div class="mt-auto max-w-lg">
                    <h2 class="text-6xl font-black text-white leading-[0.9] tracking-tighter italic uppercase animate-fade-in-up">
                        Ultimate <br>
                        <span class="text-smash-blue decoration-wavy underline-offset-8">Burger</span> <br>
                        Experience.
                    </h2>
                    <p class="text-gray-400 font-bold text-sm mt-8 max-w-sm leading-relaxed tracking-wide italic">
                        Enterprise Point of Sale System <br>
                        <span class="text-gray-500 font-medium">Precision, Speed, and Premium Management.</span>
                    </p>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="relative z-10 pt-10 border-t border-white/10 mt-10 flex items-center justify-between">
                <p class="text-[10px] font-black uppercase text-gray-500 tracking-[0.3em]">Version 2.4.0-Platinum</p>
                <div class="flex gap-4">
                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span class="text-[10px] font-black uppercase text-green-500 tracking-widest">System Operational</span>
                </div>
            </div>
        </div>

        <!-- Form Side (Right) -->
        <div class="flex-1 h-full flex flex-col justify-center items-center bg-white p-8 md:p-12 relative overflow-hidden">
            <!-- Mobile Background Hint -->
            <div class="lg:hidden absolute inset-0 -z-10 bg-gray-50">
                <div class="absolute top-0 right-0 w-64 h-64 bg-smash-blue/5 rounded-full blur-[100px]"></div>
                <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-100/10 rounded-full blur-[120px]"></div>
            </div>

            <div class="w-full max-w-md">
                <!-- Mobile Branding -->
                <div class="lg:hidden flex flex-col items-center mb-12">
                    <div class="w-20 h-20 bg-smash-blue rounded-3xl flex items-center justify-center shadow-xl shadow-smash-blue/20 mb-6">
                        <span class="text-white font-black text-4xl">G</span>
                    </div>
                </div>

                <div class="mb-10 text-center lg:text-left">
                    <h3 class="text-3xl font-black text-gray-900 tracking-tighter italic uppercase">Welcome Back</h3>
                    <p class="text-gray-400 font-bold text-[11px] uppercase tracking-widest mt-3 flex items-center justify-center lg:justify-start gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Secure POS Authentication
                    </p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email Address -->
                    <div class="space-y-2">
                        <label for="email" class="block text-[11px] font-black uppercase tracking-widest text-gray-500 px-1">Identity Key</label>
                        <div class="relative group">
                            <input id="email" class="block w-full rounded-[1.5rem] border-gray-100 bg-gray-50/50 py-4 px-6 text-[14px] font-bold text-gray-900 focus:bg-white focus:border-smash-blue focus:ring-4 focus:ring-smash-blue/5 transition-all duration-300 placeholder:text-gray-300" 
                                type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@smaesh.com" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-[10px] text-red-500 font-bold uppercase tracking-widest italic" />
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-center px-1">
                            <label for="password" class="block text-[11px] font-black uppercase tracking-widest text-gray-500">Access Token</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-[10px] font-black uppercase text-gray-400 hover:text-smash-blue transition-colors tracking-widest italic">Secret?</a>
                            @endif
                        </div>
                        <div class="relative group">
                            <input id="password" class="block w-full rounded-[1.5rem] border-gray-100 bg-gray-50/50 py-4 px-6 text-[14px] font-bold text-gray-900 focus:bg-white focus:border-smash-blue focus:ring-4 focus:ring-smash-blue/5 transition-all duration-300 placeholder:text-gray-300"
                                            type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-[10px] text-red-500 font-bold uppercase tracking-widest italic" />
                    </div>

                    <!-- Options -->
                    <div class="flex items-center justify-between px-2 pt-2">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                            <div class="relative flex items-center">
                                <input id="remember_me" type="checkbox" class="rounded-[0.5rem] border-gray-200 text-smash-blue shadow-sm focus:ring-smash-blue focus:ring-offset-0 w-5 h-5 transition-all cursor-pointer" name="remember">
                            </div>
                            <span class="ms-3 text-[11px] font-black text-gray-400 group-hover:text-gray-900 transition-colors uppercase tracking-widest">Keep me active</span>
                        </label>
                    </div>

                    <div class="mt-10 pt-4">
                        <button type="submit" class="w-full flex justify-center items-center gap-3 px-8 py-5 bg-gray-900 text-white rounded-[1.5rem] text-[12px] font-black tracking-[0.2em] uppercase hover:bg-smash-blue hover:shadow-[0_20px_50px_rgba(14,165,233,0.3)] hover:-translate-y-1 transition-all duration-500 focus:outline-none focus:ring-4 focus:ring-smash-blue/20 group">
                            <span>Execute Connection</span>
                            <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </button>
                    </div>
                </form>

                <p class="mt-12 text-center text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">
                    &copy; {{ date('Y') }} GLAEZE Burger Group <br>
                    <span class="text-gray-300">Secure Architecture &bull; V.2.4</span>
                </p>
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</x-guest-layout>
