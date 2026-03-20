<x-guest-layout>
    <div class="flex h-screen overflow-hidden font-sans">
        <!-- Branding Side (Left) -->
        <div class="hidden lg:flex lg:w-[60%] relative h-full flex-col justify-between p-20 overflow-hidden bg-gray-900">
            <img src="{{ asset('images/branding/login-hero.png') }}" class="absolute inset-0 w-full h-full object-cover opacity-40">
            <div class="absolute inset-0 bg-gradient-to-tr from-gray-900 via-gray-900/40 to-transparent"></div>

            <div class="relative z-10 flex flex-col h-full text-white">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-smash-blue rounded-[2rem] flex items-center justify-center shadow-2xl shadow-smash-blue/40 transform -rotate-6">
                        <span class="text-white font-black text-3xl italic">G</span>
                    </div>
                    <div>
                        <h1 class="text-white font-black text-3xl tracking-tighter leading-none italic uppercase">GLÆZE</h1>
                        <p class="text-blue-100/60 font-black text-[10px] tracking-widest uppercase mt-1">Gourmet Burger Specialist</p>
                    </div>
                </div>

                <div class="mt-auto max-w-lg">
                    <h2 class="text-5xl font-black leading-[0.9] tracking-tighter italic uppercase">
                        Account <br>
                        <span class="text-smash-blue text-4xl">Recovery Center.</span>
                    </h2>
                </div>
            </div>
        </div>

        <!-- Form Side (Right) -->
        <div class="flex-1 h-full flex flex-col justify-center items-center bg-white p-8 md:p-12 relative overflow-hidden">
            <div class="w-full max-w-md">
                <div class="mb-10 text-center lg:text-left">
                    <h3 class="text-3xl font-black text-gray-900 tracking-tighter italic uppercase">Forgot Password?</h3>
                    <p class="mt-4 text-[11px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">
                        No problem. Just let us know your email address and we will email you a password reset link.
                    </p>
                </div>

                <x-auth-session-status class="mb-4 text-xs font-bold text-green-600 uppercase tracking-widest" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf
                    <div class="space-y-2">
                        <label for="email" class="block text-[11px] font-black uppercase tracking-widest text-gray-500">Registered Email</label>
                        <input id="email" class="block w-full rounded-[1.5rem] border-gray-100 bg-gray-50/50 py-4 px-6 text-[14px] font-bold text-gray-900 focus:bg-white focus:border-smash-blue focus:ring-4 focus:ring-smash-blue/5 transition-all" type="email" name="email" :value="old('email')" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-[10px] text-red-500 font-bold uppercase italic tracking-widest" />
                    </div>

                    <div class="pt-6 flex flex-col gap-4">
                        <button type="submit" class="w-full flex justify-center items-center px-8 py-5 bg-gray-900 text-white rounded-[1.5rem] text-[12px] font-black tracking-[0.2em] uppercase hover:bg-smash-blue hover:shadow-xl transition-all duration-300">
                            {{ __('Send Reset Link') }}
                        </button>
                        <a href="{{ route('login') }}" class="text-center text-[11px] font-black text-gray-400 hover:text-gray-900 transition-colors uppercase tracking-widest italic">
                            &larr; Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
