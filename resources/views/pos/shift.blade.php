@extends('layouts.app')

@section('header', 'Shift Management')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8" x-data="shiftApp()">

    @if(!$activeShift)
        <!-- STATE: OPEN SHIFT -->
        <div class="min-h-[70vh] flex flex-col items-center justify-center py-12 px-4">
            <div class="max-w-md w-full">
                <!-- Outer Glow Decoration -->
                <div class="relative">
                    <div class="absolute -inset-4 bg-gradient-to-tr from-smash-blue/10 to-blue-500/10 rounded-[3.5rem] blur-2xl"></div>
                    
                    <div class="relative bg-white rounded-[3rem] shadow-[0_32px_64px_-16px_rgba(10,86,200,0.12)] border border-gray-100 p-12 text-center space-y-10 group">
                        <!-- Icon Container -->
                        <div class="w-24 h-24 bg-gradient-to-br from-blue-50 to-white rounded-[2rem] flex items-center justify-center mx-auto shadow-inner border border-blue-50/50 transform group-hover:scale-110 transition-transform duration-500">
                            <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-sm">
                                <svg class="w-8 h-8 text-smash-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <h3 class="text-4xl font-black text-gray-900 tracking-tighter uppercase">Buka Shift</h3>
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-widest leading-relaxed">
                                Masukkan saldo kas awal laci<br/>untuk memulai operasional.
                            </p>
                        </div>

                        <div class="space-y-6">
                            <div class="relative group/input">
                                <div class="absolute left-0 inset-y-0 pl-7 flex items-center pointer-events-none">
                                </div>
                                <input type="number" 
                                       x-model="openingBalance"
                                       placeholder="0"
                                       class="w-full pl-20 pr-8 py-7 bg-gray-50/50 border-gray-100 rounded-3xl text-3xl font-black text-gray-900 focus:bg-white focus:ring-8 focus:ring-smash-blue/5 focus:border-smash-blue transition-all placeholder-gray-200" />
                                <div class="absolute inset-0 rounded-3xl border-2 border-transparent group-focus-within/input:border-smash-blue/10 pointer-events-none"></div>
                            </div>

                            <button @click="openShift()" 
                                    :disabled="loading"
                                    class="w-full relative overflow-hidden group/btn bg-smash-blue hover:bg-blue-700 text-white py-6 rounded-3xl font-black uppercase tracking-[0.2em] text-sm shadow-2xl shadow-blue-200 transition-all active:scale-[0.98] disabled:opacity-50">
                                <div class="absolute inset-0 bg-white/10 translate-y-full group-hover/btn:translate-y-0 transition-transform duration-300"></div>
                                <span class="relative" x-show="!loading">MULAI SHIFT SEKARANG</span>
                                <span class="relative" x-show="loading">MEMPROSES...</span>
                            </button>
                            
                            <!-- User Info Tag -->
                            <div class="inline-flex items-center px-4 py-2 bg-gray-50 rounded-full border border-gray-100 mx-auto">
                                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse mr-3"></div>
                                <span class="text-[10px] text-gray-500 font-black uppercase tracking-widest">
                                    Operator: <span class="text-gray-900">{{ auth()->user()->name }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- STATE: SHIFT MANAGEMENT (CLOSE SHIFT & PETTY CASH) -->
        <div class="flex flex-col gap-8 mb-10 pb-4">
            <!-- Main Summary Cards (Full Width Layout) -->
            <!-- Main Summary Cards (Responsive Grid) -->
            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-6">
                <!-- Saldo Awal -->
                <div class="bg-white rounded-3xl p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-blue-200 transition-all group relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-3">
                            <h3 class="text-gray-400 text-[10px] font-black uppercase tracking-widest">Saldo Awal</h3>
                        </div>
                        <div class="text-xl font-black text-gray-900 tracking-tight">Rp {{ number_format($activeShift->opening_balance, 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Sales Cash -->
                <div class="bg-white rounded-3xl p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-green-200 transition-all group relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-green-50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-3 text-green-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-400">Sales (Cash)</h3>
                        </div>
                        <div class="text-xl font-black text-green-600 tracking-tight">+ {{ number_format($summary['total_cash_sales'], 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Sales QRIS -->
                <div class="bg-white rounded-3xl p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-purple-200 transition-all group relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-purple-50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-3 text-purple-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-400">Sales (QRIS)</h3>
                        </div>
                        <div class="text-xl font-black text-purple-600 tracking-tight">+ {{ number_format($summary['total_qris_sales'], 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Petty Cash Net -->
                <div class="bg-white rounded-3xl p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-blue-200 transition-all group relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-3">
                            <h3 class="text-gray-400 text-[10px] font-black uppercase tracking-widest">Petty Cash (Net)</h3>
                        </div>
                        <div class="text-xl font-black tracking-tight" :class="pettyCashNet >= 0 ? 'text-blue-600' : 'text-orange-600'">
                            <span x-text="pettyCashNet >= 0 ? '+' : ''"></span>
                            <span x-text="formatRupiah(pettyCashNet)"></span>
                        </div>
                    </div>
                </div>

                <!-- Total Omzet -->
                <div class="bg-white rounded-3xl p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-smash-blue transition-all group relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-3 text-smash-blue">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-400">Total Omzet</h3>
                        </div>
                        <div class="text-xl font-black text-smash-blue tracking-tight">Rp {{ number_format($summary['total_cash_sales'] + $summary['total_qris_sales'], 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Target Saldo Akhir -->
                <div class="bg-gradient-to-br from-blue-600 to-smash-blue rounded-3xl p-6 shadow-xl shadow-blue-200 group relative overflow-hidden text-white">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-white rounded-full opacity-10 group-hover:scale-110 transition-transform duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-blue-50">Target Saldo Akhir</h3>
                        </div>
                        <div class="text-xl font-black tracking-tight">Rp {{ number_format($summary['expected_balance'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            </div>

            <!-- Bottom Content: Form & Calculator Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 items-start">
                
                <!-- Column 1: Denomination Calculator -->
                <div class="xl:col-span-2">
                    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden h-full">
                        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                            <h4 class="font-black text-lg tracking-tighter uppercase italic">Kalkulator Uang Fisik</h4>
                            <button @click="resetDenominations()" class="text-[10px] font-black text-gray-400 hover:text-red-500 uppercase tracking-widest px-3 py-1 bg-gray-50 rounded-lg transition-colors">Reset</button>
                        </div>
                    <div class="p-6 md:p-8 grid grid-cols-1 sm:grid-cols-2 gap-x-6 md:gap-x-10 xl:gap-x-16 gap-y-4 lg:gap-y-6">
                        <template x-for="(d, index) in denominations" :key="index">
                            <div class="flex items-center gap-4 group py-3 border-b border-gray-50/50">
                                <span class="text-xs font-black text-gray-400 w-20 shrink-0" x-text="d.label"></span>
                                <div class="flex items-center space-x-2 shrink-0">
                                    <span class="text-[10px] font-black text-gray-300 tracking-widest hidden sm:inline">X</span>
                                    <input type="number" x-model="d.qty" placeholder="0" class="w-16 px-2 py-2.5 bg-gray-50 border-gray-100 rounded-xl text-center font-black focus:ring-smash-blue/5 transition-all text-sm" />
                                </div>
                                <div class="flex-1 text-right">
                                    <span class="text-xs font-black text-gray-700 shadow-sm px-4 py-2 bg-gray-50/50 rounded-lg whitespace-nowrap" x-text="formatRupiah(d.value * (parseInt(d.qty) || 0))"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="p-8 bg-gray-50 border-t border-gray-100 space-y-6">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-black text-gray-500 uppercase tracking-widest italic leading-none">Total Hitung Fisik</span>
                            <span class="text-3xl font-black text-gray-900 tracking-tighter" x-text="formatRupiah(calculatedActualCash)"></span>
                        </div>

                        <!-- Difference Display -->
                        <div class="p-4 rounded-2xl" :class="difference === 0 ? 'bg-green-100/50' : (difference > 0 ? 'bg-blue-100/50' : 'bg-red-100/50')">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-widest" :class="difference === 0 ? 'text-green-600' : (difference > 0 ? 'text-blue-600' : 'text-red-600')">Selisih Saldo</span>
                                <span class="text-lg font-black" :class="difference === 0 ? 'text-green-700' : (difference > 0 ? 'text-blue-700' : 'text-red-700')" x-text="formatRupiah(difference)"></span>
                            </div>
                            <p x-show="difference < 0" class="text-[9px] font-bold text-red-500 uppercase tracking-tighter mt-1 leading-none">* Saldo fisik kurang dari target sistem</p>
                            <p x-show="difference > 0" class="text-[9px] font-bold text-blue-500 uppercase tracking-tighter mt-1 leading-none">* Saldo fisik lebih besar dari target sistem</p>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Catatan Penutupan</label>
                            <textarea x-model="notes" rows="2" class="w-full mt-1.5 px-4 py-3 bg-white border-gray-100 rounded-2xl font-black focus:ring-red-500/10 focus:border-smash-blue transition-all text-sm" placeholder="Wajib diisi jika ada selisih..."></textarea>
                        </div>

                        <button @click="closeShift()" :disabled="loading" class="w-full py-4 bg-smash-blue hover:bg-smash-blue text-white rounded-2xl font-black uppercase tracking-widest text-[10px] transition-all disabled:opacity-50 shadow-xl shadow-gray-200 mt-2">
                            Konfirmasi Tutup Toko & Akhiri Shift
                        </button>
                    </div>
                </div>
                </div>

                <!-- Column 2: Kas Management Forms -->
                <div class="space-y-8">
                    <!-- Petty Cash Form -->
                <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-8 space-y-6">
                    <h4 class="font-black text-lg tracking-tighter uppercase italic">Kas Keluar / Masuk</h4>
                    
                    <div class="flex p-1 bg-gray-50 rounded-2xl">
                        <button @click="movementType = 'out'" :class="movementType === 'out' ? 'bg-white shadow-sm text-red-600' : 'text-gray-400'" class="flex-1 py-2.5 rounded-xl font-black text-[10px] tracking-widest uppercase transition-all">Keluar</button>
                        <button @click="movementType = 'in'" :class="movementType === 'in' ? 'bg-white shadow-sm text-green-600' : 'text-gray-400'" class="flex-1 py-2.5 rounded-xl font-black text-[10px] tracking-widest uppercase transition-all">Masuk</button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nominal</label>
                            <input type="number" x-model="movementAmount" class="w-full mt-1.5 px-4 py-3 bg-gray-50 border-gray-100 rounded-2xl font-black focus:ring-smash-blue/10 focus:border-smash-blue transition-all" placeholder="0" />
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Keterangan</label>
                            <input type="text" x-model="movementReason" class="w-full mt-1.5 px-4 py-3 bg-gray-50 border-gray-100 rounded-2xl font-black focus:ring-smash-blue/10 focus:border-smash-blue transition-all text-sm" placeholder="Misal: Beli Gas, Bayar Parkir..." />
                        </div>
                        <button @click="submitMovement()" :disabled="loading" class="w-full py-4 bg-smash-blue hover:bg-smash-blue text-white rounded-2xl font-black uppercase tracking-widest text-[10px] transition-all disabled:opacity-50 shadow-xl shadow-gray-200 mt-2">
                             Simpan Mutasi Kas
                        </button>
                    </div>
                </div>

                <!-- Cash Movement History -->
                <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden animate-fadeIn">
                    <div class="p-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">
                        <h4 class="font-black text-xs tracking-widest uppercase italic text-gray-400">History Mutasi Kas</h4>
                        <span class="px-2 py-1 bg-white border border-gray-100 rounded-lg text-[9px] font-black text-gray-400 uppercase tracking-tighter">Shift #{{ $activeShift->id }}</span>
                    </div>
                    <div class="divide-y divide-gray-50 max-h-[400px] overflow-y-auto">
                        @forelse($activeShift->cashMovements->sortByDesc('created_at') as $move)
                            <div class="p-5 hover:bg-gray-50/50 transition-colors flex items-center justify-between group">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $move->type === 'in' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                                        @if($move->type === 'in')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-black text-gray-800 uppercase tracking-tight truncate">{{ $move->reason }}</p>
                                        <div class="flex items-center mt-0.5 space-x-2 text-[8px] font-bold text-gray-400/80 uppercase tracking-widest">
                                            <span>{{ $move->created_at->format('H:i') }}</span>
                                            <span>•</span>
                                            <span class="truncate">{{ $move->user->name ?? 'System' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 ml-4">
                                    <p class="text-[13px] font-black {{ $move->type === 'in' ? 'text-green-600' : 'text-red-500' }} tracking-tight">
                                        {{ $move->type === 'in' ? '+' : '-' }}{{ number_format($move->amount, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="p-10 text-center space-y-3">
                                <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center mx-auto border border-gray-100/50">
                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <p class="text-[9px] font-black text-gray-300 uppercase tracking-[0.2em]">Belum ada mutasi</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    @endif

</div>

<script>
    function shiftApp() {
        return {
            loading: false,
            openingBalance: '',
            notes: '',
            movementType: 'out',
            movementAmount: '',
            movementReason: '',
            expectedBalance: {{ $summary['expected_balance'] ?? 0 }},
            pettyCashNet: {{ ($summary['total_cash_in'] ?? 0) - ($summary['total_cash_out'] ?? 0) }},
            
            denominations: [
                { label: 'Rp 100.000', value: 100000, qty: '' },
                { label: 'Rp 50.000', value: 50000, qty: '' },
                { label: 'Rp 20.000', value: 20000, qty: '' },
                { label: 'Rp 10.000', value: 10000, qty: '' },
                { label: 'Rp 5.000', value: 5000, qty: '' },
                { label: 'Rp 2.000', value: 2000, qty: '' },
                { label: 'Rp 1.000', value: 1000, qty: '' },
                { label: 'Rp 500', value: 500, qty: '' },
            ],

            get calculatedActualCash() {
                return this.denominations.reduce((sum, d) => sum + (d.value * (parseInt(d.qty) || 0)), 0);
            },

            get difference() {
                return this.calculatedActualCash - this.expectedBalance;
            },

            formatRupiah(amount) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(amount);
            },

            resetDenominations() {
                this.denominations.forEach(d => d.qty = '');
            },

            async openShift() {
                if (!this.openingBalance || this.openingBalance < 0) {
                    Swal.fire('Error', 'Input saldo awal yang valid.', 'error');
                    return;
                }
                
                this.loading = true;
                try {
                    const response = await fetch('{{ route('pos.shift.open') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ opening_balance: this.openingBalance })
                    });
                    const data = await response.json();
                    if (data.success) {
                        window.location.href = '{{ route('pos.index') }}';
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Sistem error.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async submitMovement() {
                if (!this.movementAmount || !this.movementReason) {
                    Swal.fire('Error', 'Lengkapi data mutasi.', 'warning');
                    return;
                }

                this.loading = true;
                try {
                    const response = await fetch('{{ route('pos.shift.movement') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            type: this.movementType,
                            amount: this.movementAmount,
                            reason: this.movementReason
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                    }
                } catch (e) {
                    Swal.fire('Error', 'Gagal memproses.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async closeShift() {
                if (this.calculatedActualCash < 0) {
                    Swal.fire('Error', 'Total uang fisik tidak valid.', 'warning');
                    return;
                }

                if (this.difference !== 0 && !this.notes) {
                    Swal.fire('Catatan Wajib', 'Wajib mengisi catatan jika ada selisih uang.', 'warning');
                    return;
                }

                const res = await Swal.fire({
                    title: 'Konfirmasi Tutup Toko?',
                    text: 'Pastikan hitungan fisik di kalkulator sudah benar.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#E11D48',
                    confirmButtonText: 'Ya, Tutup Sekarang'
                });

                if (!res.isConfirmed) return;

                this.loading = true;
                try {
                    const response = await fetch('{{ route('pos.shift.close') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            closing_balance: this.calculatedActualCash,
                            notes: this.notes
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        Swal.fire('Shift Ditutup', 'Halaman akan diperbarui.', 'success')
                            .then(() => location.reload());
                    }
                } catch (e) {
                    Swal.fire('Error', 'Gagal menutup shift.', 'error');
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>

<style>
    [x-cloak] { display: none !important; }
    .animate-fadeIn {
        animation: fadeIn 0.5s ease-out forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
