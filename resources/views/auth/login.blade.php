<x-guest-layout>
    <main class="flex h-screen w-full flex-col md:flex-row overflow-hidden" x-data="{ showPassword: false }">
        <!-- Left Panel: Expression Side (60%) -->
        <section class="hidden md:flex md:w-[60%] bg-smash-blue relative overflow-hidden items-center justify-center p-12">
            <!-- Decorative background elements -->
            <div class="absolute inset-0 opacity-20 pointer-events-none">
                <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-white blur-[120px] rounded-full"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-blue-300 blur-[120px] rounded-full"></div>
            </div>
            
            <div class="relative z-10 text-center max-w-xl animate-fade-in-up">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white/10 backdrop-blur-xl rounded-2xl mb-8 border border-white/20 shadow-2xl">
                    <span class="text-white text-5xl font-black tracking-tighter italic">G</span>
                </div>
                <div class="uppercase tracking-[0.3em] text-white/50 text-sm font-bold mb-4">Glaeze Digital Architect</div>
                <h1 class="text-5xl lg:text-7xl font-black text-white leading-[1.1] tracking-tighter mb-6 uppercase italic">
                    Welcome to <br/>The SMÆSH ST.
                </h1>
                <p class="text-xl text-white/70 font-medium leading-relaxed max-w-md mx-auto italic">
                    Securely manage your burger business terminal from anywhere with high-fidelity encryption.
                </p>
            </div>

            <!-- Aesthetic Accent: Layout planes -->
            <div class="absolute bottom-12 left-12 right-12 flex justify-between items-end">
                <div class="flex flex-col gap-2">
                    <div class="h-1 w-24 bg-white/20 rounded-full"></div>
                    <div class="h-1 w-12 bg-white/20 rounded-full"></div>
                </div>
                <div class="text-[10px] text-white/30 uppercase tracking-[0.3em] font-mono">
                    Terminal System v{{ config('app.version', '4.0.22') }}
                </div>
            </div>
        </section>

        <!-- Right Panel: Utility Side (40%) -->
        <section class="w-full md:w-[40%] bg-white flex items-center justify-center p-8 md:p-12 relative">
            <div class="w-full max-w-[420px] animate-fade-in">
                <!-- Mobile Branding -->
                <div class="md:hidden flex flex-col items-center mb-10 text-center">
                    <div class="w-16 h-16 bg-smash-blue rounded-2xl flex items-center justify-center shadow-lg mb-4">
                        <span class="text-white font-black text-3xl italic">G</span>
                    </div>
                </div>

                <div class="mb-10 text-center md:text-left">
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">Sign In to Your Account</h2>
                    <p class="text-gray-500 text-sm font-medium">Enter your credentials to access the secure terminal.</p>
                </div>

                <x-auth-session-status class="mb-6" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Identity Key -->
                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-wider text-gray-400 ml-1" for="email">Identity Key</label>
                        <input id="email" name="email" type="email" :value="old('email')" required autofocus
                            class="w-full bg-gray-50 border border-gray-100 rounded-xl py-4 px-5 text-gray-900 font-bold placeholder:text-gray-300 focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue transition-all outline-none" 
                            placeholder="u_arch_key@glaeze.com"/>
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-red-600 font-bold" />
                    </div>

                    <!-- Access Token -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-center ml-1">
                            <label class="text-xs font-black uppercase tracking-wider text-gray-400" for="password">Access Token</label>
                            @if (Route::has('password.request'))
                                <a class="text-[11px] font-bold text-smash-blue hover:underline uppercase tracking-widest italic" href="{{ route('password.request') }}">Secret?</a>
                            @endif
                        </div>
                        <div class="relative">
                            <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required
                                class="w-full bg-gray-50 border border-gray-100 rounded-xl py-4 px-5 text-gray-900 font-bold placeholder:text-gray-300 focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue transition-all outline-none tracking-normal" 
                                placeholder="••••••••••••"/>
                            <button type="button" @click="showPassword = !showPassword" 
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 hover:text-smash-blue transition-colors">
                                <span class="material-symbols-outlined text-[20px]" x-text="showPassword ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-red-600 font-bold" />
                    </div>

                    <!-- Remember & Action -->
                    <div class="flex items-center justify-between py-2">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                            <input id="remember_me" type="checkbox" name="remember" class="w-5 h-5 rounded border-gray-200 text-smash-blue shadow-sm focus:ring-smash-blue focus:ring-offset-0 cursor-pointer">
                            <span class="ms-3 text-[11px] font-black text-gray-400 group-hover:text-gray-900 transition-colors uppercase tracking-[0.2em]">Keep Active</span>
                        </label>
                    </div>

                    <button class="w-full bg-smash-blue hover:bg-blue-700 text-white font-black py-4 px-6 rounded-xl flex items-center justify-center gap-3 transition-all active:scale-[0.98] shadow-xl shadow-smash-blue/20 uppercase tracking-[0.2em] text-xs" type="submit">
                        Launch Connection
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>
                </form>

                <div class="mt-12 text-center md:text-left border-t border-gray-50 pt-10">
                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.3em] leading-relaxed italic">
                        © 2024 GLAEZE DIGITAL ARCHITECT. <br>
                        SECURE TERMINAL ACCESS SYSTEM.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-fade-in-up { animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-fade-in { animation: fadeIn 1s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>
</x-guest-layout>
