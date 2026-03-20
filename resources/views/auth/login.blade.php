<x-guest-layout>
    <div class="w-full max-w-sm">
        <!-- Brand Header -->
        <div class="flex flex-col items-center mb-10">
            <div class="w-20 h-20 bg-smash-blue rounded-[2rem] flex items-center justify-center shadow-2xl shadow-smash-blue/20 mb-6 transform -rotate-3">
                <span class="text-white font-black text-4xl italic">G</span>
            </div>
            <h1 class="text-gray-900 font-black text-4xl tracking-tighter leading-none italic uppercase">GLÆZE</h1>
            <p class="text-gray-400 font-bold text-[10px] tracking-widest uppercase mt-3 px-4 py-1.5 bg-gray-100/50 rounded-full">Secure Personnel Access</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-blue-900/5 border border-gray-100/50 p-10 relative overflow-hidden">
            <!-- Subtle Background Accent -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-smash-blue/5 rounded-full blur-[60px]"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-100/10 rounded-full blur-[60px]"></div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-6 text-xs font-bold text-green-600 uppercase tracking-widest text-center" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div class="space-y-2">
                    <label for="email" class="block text-[11px] font-black uppercase tracking-widest text-gray-400 px-1">Identity Key</label>
                    <input id="email" class="block w-full rounded-[1.2rem] border-gray-100 bg-gray-50/50 py-4 px-6 text-[14px] font-bold text-gray-900 focus:bg-white focus:border-smash-blue/30 focus:ring-4 focus:ring-smash-blue/5 transition-all duration-300 placeholder:text-gray-300 ring-0 outline-none" 
                        type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="enter email" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-[10px] text-red-500 font-bold uppercase tracking-widest italic" />
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center px-1">
                        <label for="password" class="block text-[11px] font-black uppercase tracking-widest text-gray-400">Access Token</label>
                    </div>
                    <input id="password" class="block w-full rounded-[1.2rem] border-gray-100 bg-gray-50/50 py-4 px-6 text-[14px] font-bold text-gray-900 focus:bg-white focus:border-smash-blue/30 focus:ring-4 focus:ring-smash-blue/5 transition-all duration-300 placeholder:text-gray-300 ring-0 outline-none"
                        type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-[10px] text-red-500 font-bold uppercase tracking-widest italic" />
                </div>

                <!-- Remember Me & Reset -->
                <div class="flex items-center justify-between px-1">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                        <input id="remember_me" type="checkbox" class="rounded-[0.5rem] border-gray-200 text-smash-blue shadow-sm focus:ring-smash-blue focus:ring-offset-0 w-5 h-5 transition-all cursor-pointer" name="remember">
                        <span class="ms-3 text-[11px] font-black text-gray-400 group-hover:text-gray-900 transition-colors uppercase tracking-widest">Keep Active</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-[10px] font-black uppercase text-gray-300 hover:text-smash-blue transition-colors tracking-widest italic">Secret?</a>
                    @endif
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full flex justify-center items-center gap-3 px-8 py-4 bg-smash-blue text-white rounded-xl text-[12px] font-black tracking-[0.25em] uppercase hover:bg-smash-blue/90 hover:shadow-xl hover:shadow-smash-blue/20 hover:-translate-y-1 transition-all duration-500 focus:outline-none focus:ring-4 focus:ring-smash-blue/20">
                        Connect Session
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <p class="mt-10 text-center text-[10px] font-bold text-gray-300 uppercase tracking-[0.2em] italic">
            &copy; {{ date('Y') }} GLAEZE Burger Group <br>
            <span class="text-gray-200">Terminal V.2.4 &bull; Global Network</span>
        </p>
    </div>
</x-guest-layout>
