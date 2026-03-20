@extends('layouts.app')

@section('header', 'Detail Shift')

@section('content')
<div class="space-y-6 animate-fadeIn pb-12">
    
    <!-- Actions & Meta Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('pos.shift.history') }}" class="group flex items-center gap-2 px-4 py-2 bg-white border border-gray-100 hover:bg-gray-50 text-gray-500 rounded-xl transition-all shadow-sm">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div class="h-8 w-px bg-gray-100"></div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] leading-none">Shift Record</p>
                <p class="text-xs font-black text-gray-800 uppercase mt-1">ID #{{ $shift->id }} • {{ $shift->closed_at->format('d M Y, H:i') }}</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3 px-5 py-3 bg-white border border-gray-100 rounded-2xl shadow-sm">
            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-smash-blue text-[10px] font-black">
                {{ substr($shift->user->name, 0, 1) }}
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none">Cashier On Duty</p>
                <p class="text-[11px] font-black text-gray-900 uppercase mt-1">{{ $shift->user->name }}</p>
            </div>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Saldo Awal -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:border-blue-200 transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-50/50 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Saldo Awal</p>
            <p class="text-2xl font-black text-gray-900 mt-2 relative z-10">Rp {{ number_format($summary['opening_balance'], 0, ',', '.') }}</p>
        </div>

        <!-- Sales (Cash) -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:border-green-200 transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-green-50/50 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Sales (Cash)</p>
            <p class="text-2xl font-black text-green-600 mt-2 relative z-10">+Rp {{ number_format($summary['total_cash_sales'], 0, ',', '.') }}</p>
        </div>

        <!-- Sales (QRIS) -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:border-purple-200 transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-purple-50/50 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Sales (QRIS)</p>
            <p class="text-2xl font-black text-purple-600 mt-2 relative z-10">+Rp {{ number_format($summary['total_qris_sales'], 0, ',', '.') }}</p>
        </div>

        <!-- Mutasi Kas (Net) -->
        @php $pettyNet = $summary['total_cash_in'] - $summary['total_cash_out']; @endphp
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:border-cyan-200 transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 {{ $pettyNet >= 0 ? 'bg-cyan-50/50' : 'bg-red-50/50' }} rounded-full group-hover:scale-125 transition-transform duration-500"></div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Mutasi Kas (Net)</p>
            <p class="text-2xl font-black {{ $pettyNet >= 0 ? 'text-cyan-600' : 'text-red-600' }} mt-2 relative z-10">
                {{ $pettyNet >= 0 ? '+' : '-' }}Rp {{ number_format(abs($pettyNet), 0, ',', '.') }}
            </p>
        </div>

        <!-- Total Omzet -->
        <div class="bg-smash-blue rounded-2xl p-6 shadow-[0_8px_25px_-4px_rgba(10,86,200,0.3)] border border-blue-400 transition-all group relative overflow-hidden text-white">
             <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
            <p class="text-[10px] font-black text-white/50 uppercase tracking-widest relative z-10">Total Omzet</p>
            <p class="text-2xl font-black text-white mt-2 relative z-10">Rp {{ number_format($summary['total_cash_sales'] + $summary['total_qris_sales'], 0, ',', '.') }}</p>
        </div>

        <!-- Target Saldo Akhir -->
        <div class="bg-gray-900 rounded-2xl p-6 shadow-[0_8px_25px_-4px_rgba(17,24,39,0.3)] border border-gray-800 transition-all group relative overflow-hidden text-white">
             <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/5 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
            <p class="text-[10px] font-black text-white/40 uppercase tracking-widest relative z-10">Expected Cash</p>
            <p class="text-2xl font-black text-white mt-2 relative z-10">Rp {{ number_format($summary['expected_balance'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Physical Cash Details -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] p-8">
                <h4 class="font-black text-sm tracking-widest uppercase mb-6">Hasil Penutupan</h4>
                
                <div class="p-6 bg-gray-50/50 border border-gray-100 rounded-2xl space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Cash Diinput</span>
                        <span class="text-xs font-black text-gray-900">{{ number_format($shift->closing_balance, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Selisih</span>
                        @if($shift->difference > 0)
                            <span class="text-xs font-black text-blue-600">+ {{ number_format($shift->difference, 0, ',', '.') }}</span>
                        @elseif($shift->difference < 0)
                            <span class="text-xs font-black text-red-600">- {{ number_format(abs($shift->difference), 0, ',', '.') }}</span>
                        @else
                            <span class="text-xs font-black text-gray-400 uppercase">PASS</span>
                        @endif
                    </div>
                </div>

                @if($shift->notes)
                <div class="mt-6">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block ml-1">Catatan</label>
                    <div class="p-4 bg-orange-50/30 border border-orange-100/50 rounded-xl text-[11px] font-bold text-orange-800 leading-relaxed italic">
                        "{{ $shift->notes }}"
                    </div>
                </div>
                @endif
            </div>

            <!-- Time Stats -->
            <div class="bg-smash-blue rounded-2xl p-8 text-white relative overflow-hidden group">
                 <h4 class="font-black text-sm tracking-widest uppercase text-white/90 mb-6">Operasional</h4>
                 <div class="grid grid-cols-2 gap-12">
                     <div class="space-y-1">
                         <p class="text-[9px] font-black text-white/30 uppercase tracking-[0.2em]">Buka Shift</p>
                         <p class="text-sm font-black">{{ $shift->opened_at->format('H:i') }}</p>
                         <p class="text-[10px] font-bold text-white/20 uppercase tracking-tight">{{ $shift->opened_at->format('d M Y') }}</p>
                     </div>
                     <div class="space-y-1">
                         <p class="text-[9px] font-black text-white/30 uppercase tracking-[0.2em]">Tutup Shift</p>
                         <p class="text-sm font-black">{{ $shift->closed_at->format('H:i') }}</p>
                         <p class="text-[10px] font-bold text-white/20 uppercase tracking-tight">{{ $shift->closed_at->format('d M Y') }}</p>
                     </div>
                 </div>
                 <div class="pt-6 mt-6 border-t border-white/5 text-center relative z-10">
                    <p class="text-[9px] font-black text-white/30 uppercase tracking-[0.2em]">Total Durasi</p>
                    <p class="text-lg font-black mt-1">
                        {{ $shift->opened_at->diffInHours($shift->closed_at) }}<span class="text-[10px] font-bold text-white/40 ml-1">JAM</span> 
                        {{ $shift->opened_at->diff($shift->closed_at)->format('%I') }}<span class="text-[10px] font-bold text-white/40 ml-1">MENIT</span>
                    </p>
                 </div>
            </div>
        </div>

        <!-- Detail Activity -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Transactions -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                    <h4 class="font-black text-sm tracking-widest uppercase">Daftar Transaksi</h4>
                    <span class="px-2.5 py-1 bg-blue-50 text-smash-blue text-[9px] font-black rounded uppercase tracking-widest border border-blue-100/50">
                        {{ $transactions->count() }} Orders
                    </span>
                </div>
                <div class="overflow-x-auto max-h-[350px]">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/30 sticky top-0 z-10 border-b border-gray-50">
                                <th class="px-8 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Invoice</th>
                                <th class="px-8 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Time</th>
                                <th class="px-8 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest">Method</th>
                                <th class="px-8 py-4 text-[9px] font-black text-gray-400 uppercase tracking-widest text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($transactions as $trx)
                            <tr class="hover:bg-gray-50/20 transition-colors">
                                <td class="px-8 py-4 text-[11px] font-black text-gray-900 tracking-tighter uppercase">{{ $trx->invoice_number }}</td>
                                <td class="px-8 py-4 text-[11px] font-bold text-gray-400">{{ $trx->created_at->format('H:i') }}</td>
                                <td class="px-8 py-4">
                                    <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest {{ $trx->payment_method === 'Cash' ? 'bg-green-50 text-green-600' : 'bg-purple-50 text-purple-600' }}">
                                        {{ $trx->payment_method }}
                                    </span>
                                </td>
                                <td class="px-8 py-4 text-right text-[11px] font-black text-gray-900">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cash Movements -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                    <h4 class="font-black text-sm tracking-widest uppercase">Mutasi Kas</h4>
                    <span class="px-2.5 py-1 bg-gray-50 text-gray-400 text-[9px] font-black rounded uppercase tracking-widest border border-gray-100">
                        {{ $movements->count() }} Movements
                    </span>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($movements as $m)
                    <div class="flex items-center justify-between p-4 bg-gray-50/30 border border-gray-100 rounded-2xl transition-all hover:bg-gray-50 group">
                         <div class="flex items-center gap-4">
                             <div class="w-10 h-10 rounded-xl {{ $m->type === 'in' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-rose-50 text-rose-600 border-rose-100' }} border flex items-center justify-center transition-all group-hover:scale-105">
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     @if($m->type === 'in')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                     @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                                     @endif
                                 </svg>
                             </div>
                             <div>
                                 <p class="text-[11px] font-black text-gray-800 uppercase tracking-tight italic">{{ $m->reason }}</p>
                                 <div class="flex items-center gap-2 mt-0.5">
                                     <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ $m->created_at->format('H:i') }}</span>
                                     <span class="text-[9px] font-bold text-gray-200">|</span>
                                     <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest">{{ $m->user->name }}</span>
                                 </div>
                             </div>
                         </div>
                         <div class="text-right">
                             <p class="text-xs font-black {{ $m->type === 'in' ? 'text-green-600' : 'text-rose-600' }} tracking-tight">
                                {{ $m->type === 'in' ? '+' : '-' }} {{ number_format($m->amount, 0, ',', '.') }}
                             </p>
                         </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-gray-300">
                        <p class="text-[9px] font-black uppercase tracking-[0.2em] italic">No cash movements recorded</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
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
