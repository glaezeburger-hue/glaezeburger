<x-guest-layout>
    <div class="w-full max-w-sm">
        <!-- Brand Header -->
        <div class="flex flex-col items-center mb-10">
            <div class="w-20 h-20 bg-smash-blue rounded-[2rem] flex items-center justify-center shadow-2xl shadow-smash-blue/20 mb-6 transform -rotate-3">
                <span class="text-white font-black text-4xl italic">G</span>
            </div>
            <h1 class="text-gray-900 font-black text-4xl tracking-tighter leading-none italic uppercase">GLÆZE</h1>
            <p class="text-gray-400 font-bold text-[10px] tracking-widest uppercase mt-3 px-4 py-1.5 bg-gray-100/50 rounded-full">Security Reset</p>
        </div>

        <!-- Reset Password Card -->
        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-blue-900/5 border border-gray-100/50 p-10 relative overflow-hidden">
            <div class="mb-8 text-center">
                <h3 class="text-xl font-black text-gray-900 tracking-tighter italic uppercase">Update Credentials</h3>
                <p class="mt-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">
                    Set your new access tokens below to regain system entry.
                </p>
            </div>

            <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div class="space-y-2">
                    <label for="email" class="block text-[11px] font-black uppercase tracking-widest text-gray-400 px-1">Identity Key</label>
                    <input id="email" class="block w-full rounded-[1.2rem] border-gray-100 bg-gray-50/50 py-4 px-6 text-[14px] font-bold text-gray-900 focus:bg-white focus:border-smash-blue/30 focus:ring-4 focus:ring-smash-blue/5 transition-all outline-none ring-0" type="email" name="email" :value="old('email', $request->email)" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-[10px] text-red-500 font-bold uppercase italic tracking-widest" />
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <label for="password" class="block text-[11px] font-black uppercase tracking-widest text-gray-400 px-1">New Access Token</label>
                    <input id="password" class="block w-full rounded-[1.2rem] border-gray-100 bg-gray-50/50 py-4 px-6 text-[14px] font-bold text-gray-900 focus:bg-white focus:border-smash-blue/30 focus:ring-4 focus:ring-smash-blue/5 transition-all outline-none ring-0" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-[10px] text-red-500 font-bold uppercase italic tracking-widest" />
                </div>

                <!-- Confirm Password -->
                <div class="space-y-2">
                    <label for="password_confirmation" class="block text-[11px] font-black uppercase tracking-widest text-gray-400 px-1">Verify Token</label>
                    <input id="password_confirmation" class="block w-full rounded-[1.2rem] border-gray-100 bg-gray-50/50 py-4 px-6 text-[14px] font-bold text-gray-900 focus:bg-white focus:border-smash-blue/30 focus:ring-4 focus:ring-smash-blue/5 transition-all outline-none ring-0" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-[10px] text-red-500 font-bold uppercase italic tracking-widest" />
                </div>

                <div class="pt-4 flex flex-col gap-4">
                    <button type="submit" class="w-full flex justify-center items-center px-8 py-4 bg-smash-blue text-white rounded-xl text-[12px] font-black tracking-[0.2em] uppercase hover:bg-smash-blue/90 hover:shadow-xl transition-all duration-300">
                        Update Credentials
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
