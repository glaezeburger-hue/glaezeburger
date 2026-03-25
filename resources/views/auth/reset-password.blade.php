<x-guest-layout>
    <main class="flex h-screen w-full flex-col md:flex-row overflow-hidden">
        <!-- Left Panel: Expression Side (60%) -->
        <section class="hidden md:flex md:w-[60%] bg-smash-blue relative overflow-hidden items-center justify-center p-12">
            <div class="absolute inset-0 opacity-20 pointer-events-none">
                <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-white blur-[120px] rounded-full"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-blue-300 blur-[120px] rounded-full"></div>
            </div>
            
            <div class="relative z-10 text-center max-w-xl animate-fade-in-up">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white/10 backdrop-blur-xl rounded-2xl mb-8 border border-white/20 shadow-2xl">
                    <span class="text-white text-5xl font-black tracking-tighter italic">G</span>
                </div>
                <div class="uppercase tracking-[0.3em] text-white/50 text-sm font-bold mb-4">Security Protocol</div>
                <h1 class="text-5xl lg:text-7xl font-black text-white leading-[1.1] tracking-tighter mb-6 uppercase italic">
                    Credential <br/>Update.
                </h1>
                <p class="text-xl text-white/70 font-medium leading-relaxed max-w-md mx-auto italic">
                    Finalize your identity synchronization with the secure terminal network.
                </p>
            </div>

            <div class="absolute bottom-12 left-12 right-12 flex justify-between items-end">
                <div class="flex flex-col gap-2">
                    <div class="h-1 w-24 bg-white/20 rounded-full"></div>
                    <div class="h-1 w-12 bg-white/20 rounded-full"></div>
                </div>
                <div class="text-[10px] text-white/30 uppercase tracking-[0.3em] font-mono">
                    Security Interface v2.1
                </div>
            </div>
        </section>

        <!-- Right Panel: Utility Side (40%) -->
        <section class="w-full md:w-[40%] bg-white flex items-center justify-center p-8 md:p-12">
            <div class="w-full max-w-[420px] animate-fade-in">
                <div class="mb-10 text-center md:text-left">
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">Security Credentials</h2>
                    <p class="text-gray-500 text-sm font-medium">Set your new access tokens to finalize recovery.</p>
                </div>

                <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email Address -->
                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-wider text-gray-400 ml-1" for="email">Identity Key</label>
                        <input id="email" name="email" type="email" :value="old('email', $request->email)" required autocomplete="username"
                            class="w-full bg-gray-50 border border-gray-100 rounded-xl py-4 px-5 text-gray-900 font-bold focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue transition-all outline-none"/>
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-red-600 font-bold" />
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-wider text-gray-400 ml-1" for="password">New Access Token</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="••••••••••••"
                            class="w-full bg-gray-50 border border-gray-100 rounded-xl py-4 px-5 text-gray-900 font-bold focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue transition-all outline-none"/>
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-red-600 font-bold" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-wider text-gray-400 ml-1" for="password_confirmation">Confirm Token</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="••••••••••••"
                            class="w-full bg-gray-50 border border-gray-100 rounded-xl py-4 px-5 text-gray-900 font-bold focus:ring-4 focus:ring-smash-blue/5 focus:border-smash-blue transition-all outline-none"/>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs text-red-600 font-bold" />
                    </div>

                    <div class="pt-2">
                        <button class="w-full bg-smash-blue hover:bg-blue-700 text-white font-black py-4 px-6 rounded-xl flex items-center justify-center gap-3 transition-all active:scale-[0.98] shadow-xl shadow-smash-blue/20 uppercase tracking-[0.2em] text-xs" type="submit">
                            Sync Credentials
                            <span class="material-symbols-outlined text-lg">sync</span>
                        </button>
                    </div>
                </form>

                <div class="mt-12 text-center md:text-left border-t border-gray-50 pt-10">
                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.3em] leading-relaxed italic">
                        © 2024 GLAEZE DIGITAL ARCHITECT. <br>
                        SECURITY TERMINAL UPDATE SYSTEM.
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
