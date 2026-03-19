<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-blue-50/20">
        <div class="w-full sm:max-w-md mt-6 px-10 py-12 bg-white shadow-xl shadow-blue-900/5 sm:rounded-3xl border border-gray-100">
            
            <div class="flex flex-col items-center justify-center mb-8">
                <div class="w-14 h-14 bg-smash-blue rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200 mb-4">
                    <span class="text-white font-extrabold text-3xl">G</span>
                </div>
                <h2 class="text-smash-blue font-black text-2xl tracking-tighter leading-none mt-2">GLÆZE BURGER POS</h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-4 text-center">Authorized Personnel Only</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div class="space-y-1">
                    <label for="email" class="block text-[11px] font-black uppercase tracking-widest text-gray-500">Email Address</label>
                    <input id="email" class="block w-full rounded-xl border-gray-200 focus:border-smash-blue focus:ring focus:ring-blue-100 transition-shadow text-sm font-semibold py-3 px-4 text-gray-900" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="enter your email" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-red-500 font-semibold" />
                </div>

                <!-- Password -->
                <div class="mt-6 space-y-1">
                    <label for="password" class="block text-[11px] font-black uppercase tracking-widest text-gray-500">Password</label>
                    <input id="password" class="block w-full rounded-xl border-gray-200 focus:border-smash-blue focus:ring focus:ring-blue-100 transition-shadow text-sm font-semibold py-3 px-4 text-gray-900"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" placeholder="••••••••" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-red-500 font-semibold" />
                </div>

                <!-- Remember Me -->
                <div class="block mt-6">
                    <label for="remember_me" class="inline-flex items-center group cursor-pointer">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-smash-blue shadow-sm focus:ring-smash-blue focus:ring-offset-0 w-4 h-4" name="remember">
                        <span class="ms-3 text-sm font-bold text-gray-500 group-hover:text-gray-900 transition-colors">Remember me on this device</span>
                    </label>
                </div>

                <div class="mt-8">
                    <button type="submit" class="w-full flex justify-center items-center px-6 py-4 bg-smash-blue text-white rounded-xl text-sm font-black tracking-widest uppercase hover:bg-blue-700 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-smash-blue">
                        Sign In Securely
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
