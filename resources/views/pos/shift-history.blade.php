@extends('layouts.app')

@section('header', 'Riwayat Shift')

@section('content')
<div class="space-y-6 animate-fadeIn">
    
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Shifts -->
        <div class="bg-white rounded-2xl p-5 md:p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-blue-200 hover:shadow-[0_8px_25px_-4px_rgba(59,130,246,0.1)] transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-50/50 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
            <p class="text-gray-500 text-[10px] md:text-[11px] font-bold uppercase tracking-widest leading-none mt-1">Total Shift Selesai</p>
            <p class="text-3xl font-black text-gray-900 mt-1 relative z-10">{{ $stats->total_shifts }}</p>
        </div>

        <!-- Total Selisih -->
        <div class="bg-white rounded-2xl p-5 md:p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-blue-200 hover:shadow-[0_8px_25px_-4px_rgba(59,130,246,0.1)] transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 {{ $stats->total_diff >= 0 ? 'bg-cyan-50/50' : 'bg-red-50/50' }} rounded-full group-hover:scale-125 transition-transform duration-500"></div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Total Selisih Kas</p>
            <p class="text-2xl font-black {{ $stats->total_diff >= 0 ? 'text-cyan-600' : 'text-red-600' }} mt-1 relative z-10">
                {{ $stats->total_diff >= 0 ? '+' : '-' }}Rp {{ number_format(abs($stats->total_diff), 0, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- Shifts Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Waktu Tutup</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Kasir</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Saldo Akhir</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Selisih</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($shifts as $shift)
                    <tr class="hover:bg-gray-50/30 transition-colors group">
                        <td class="px-8 py-6">
                            <p class="text-xs font-black text-gray-900 uppercase tracking-tighter">{{ $shift->closed_at->format('d M Y') }}</p>
                            <p class="text-[10px] font-bold text-gray-400 uppercase mt-0.5 tracking-widest">{{ $shift->closed_at->format('H:i') }}</p>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-smash-blue font-black text-[10px]">
                                    {{ substr($shift->user->name, 0, 1) }}
                                </div>
                                <span class="text-[11px] font-black text-gray-700 uppercase tracking-tight">{{ $shift->user->name }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <span class="text-xs font-black text-gray-900 tracking-tight">Rp {{ number_format($shift->closing_balance, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            @if($shift->difference > 0)
                                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[9px] font-black uppercase tracking-widest">
                                    + {{ number_format($shift->difference, 0, ',', '.') }}
                                </span>
                            @elseif($shift->difference < 0)
                                <span class="px-3 py-1 bg-red-50 text-red-600 rounded-lg text-[9px] font-black uppercase tracking-widest">
                                    - {{ number_format(abs($shift->difference), 0, ',', '.') }}
                                </span>
                            @else
                                <span class="px-3 py-1 bg-gray-50 text-gray-400 rounded-lg text-[9px] font-black uppercase tracking-widest">
                                    PASS
                                </span>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-center">
                            <a href="{{ route('pos.shift.show', $shift) }}" class="inline-flex items-center px-5 py-2.5 bg-gray-900 hover:bg-black text-white rounded-xl text-[9px] font-black uppercase tracking-[0.15em] transition-all group-hover:scale-105 shadow-sm active:scale-95">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mb-4 border border-gray-100">
                                    <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em]">Belum ada riwayat shift</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($shifts->hasPages())
        <div class="p-8 border-t border-gray-50">
            {{ $shifts->links() }}
        </div>
        @endif
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
    animation: fadeIn 0.5s ease-out forwards;
}
</style>
@endsection
