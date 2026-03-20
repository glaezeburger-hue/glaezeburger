@extends('layouts.app')

@section('header', 'Financial Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Period Selector & Actions --}}
    {{-- Period Selector & Actions --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0 bg-white/50 p-4 rounded-2xl border border-gray-100/50 shadow-sm backdrop-blur-sm">
        <form action="{{ route('finance.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-1.5 md:gap-2 w-full lg:w-auto">
            @foreach(['today' => 'Today', '7_days' => '7 Days', 'this_month' => 'This Month', 'last_month' => 'Last Month'] as $key => $label)
                <button type="submit" name="period" value="{{ $key }}" 
                    class="px-3 md:px-4 py-2 rounded-xl text-[10px] md:text-xs font-black uppercase tracking-widest transition-all border 
                    {{ $period === $key ? 'bg-smash-blue text-white border-smash-blue shadow-lg shadow-blue-500/20' : 'bg-white text-gray-500 border-gray-200 hover:border-blue-200 hover:text-smash-blue' }}">
                    {{ $label }}
                </button>
            @endforeach
            
            <div x-data="{ open: {{ $period === 'custom' ? 'true' : 'false' }} }" class="relative flex items-center gap-2">
                <button type="button" @click="open = !open" 
                    class="px-3 md:px-4 py-2 rounded-xl text-[10px] md:text-xs font-black uppercase tracking-widest transition-all border 
                    {{ $period === 'custom' ? 'bg-smash-blue text-white border-smash-blue shadow-lg shadow-blue-500/20' : 'bg-white text-gray-500 border-gray-200 hover:border-blue-200 hover:text-smash-blue' }}">
                    Custom
                </button>
                
                <div x-show="open" class="flex items-center gap-1.5 absolute top-full left-0 mt-2 p-3 bg-white rounded-xl shadow-xl border border-gray-100 z-50 animate-fadeIn" style="display: none;">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border-gray-100 text-[10px] font-bold focus:ring-smash-blue/20">
                    <span class="text-gray-300 font-bold">-</span>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border-gray-100 text-[10px] font-bold focus:ring-smash-blue/20">
                    <button type="submit" name="period" value="custom" class="bg-gray-900 text-white px-3 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-black transition-colors">Apply</button>
                </div>
            </div>
        </form>

        <div class="w-full lg:w-auto flex justify-end">
            <a href="{{ route('finance.profit-loss', request()->all()) }}" target="_blank" class="w-full lg:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-gray-900 hover:bg-black text-white rounded-xl text-[10px] font-black uppercase tracking-[0.15em] shadow-lg shadow-black/10 transition-all active:scale-95">
                <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print P&L Statement
            </a>
        </div>
    </div>

    {{-- Main KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-5">
        
        {{-- Gross Revenue --}}
        <div class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-blue-200 hover:shadow-[0_8px_25px_-4px_rgba(59,130,246,0.1)] transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-start justify-between relative z-10 w-full">
                <div class="flex-1 pr-2">
                    <h3 class="text-gray-400 text-[10px] md:text-[11px] font-black uppercase tracking-widest leading-none mt-1 mb-2">Gross Revenue</h3>
                    <div class="text-xl md:text-[24px] xl:text-[28px] font-black text-gray-900 tracking-tight leading-none">Rp {{ number_format($grossRevenue, 0, ',', '.') }}</div>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0 border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        {{-- COGS --}}
        <div class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-orange-200 hover:shadow-[0_8px_25px_-4px_rgba(249,115,22,0.1)] transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-gradient-to-br from-orange-50 to-orange-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-start justify-between relative z-10 w-full">
                <div class="flex-1 pr-2">
                    <h3 class="text-gray-400 text-[10px] md:text-[11px] font-black uppercase tracking-widest leading-none mt-1 mb-2">COGS</h3>
                    <div class="text-xl md:text-[24px] xl:text-[28px] font-black text-gray-900 tracking-tight leading-none">Rp {{ number_format($cogs, 0, ',', '.') }}</div>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 shrink-0 border border-orange-100 group-hover:bg-orange-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
        </div>

        {{-- Gross Profit --}}
        <div class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-teal-200 hover:shadow-[0_8px_25px_-4px_rgba(20,184,166,0.1)] transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-gradient-to-br from-teal-50 to-teal-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-start justify-between relative z-10 w-full">
                <div class="flex-1 pr-2">
                    <h3 class="text-gray-400 text-[10px] md:text-[11px] font-black uppercase tracking-widest leading-none mt-1 mb-2">Gross Profit</h3>
                    <div class="text-xl md:text-[24px] xl:text-[28px] font-black text-teal-600 tracking-tight leading-none">Rp {{ number_format($grossProfit, 0, ',', '.') }}</div>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600 shrink-0 border border-teal-100 group-hover:bg-teal-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
        </div>

        {{-- Wastage Loss --}}
        <div class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-rose-200 hover:shadow-[0_8px_25px_-4px_rgba(244,63,94,0.1)] transition-all group relative overflow-hidden flex flex-col justify-center">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-gradient-to-br from-rose-50 to-rose-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="relative z-10 w-full">
                <div class="flex items-center justify-between w-full">
                    <div class="flex-1 pr-2">
                        <h3 class="text-rose-500 text-[10px] md:text-[11px] font-black uppercase tracking-widest leading-none mb-2">Wastage Loss</h3>
                        <div class="text-xl md:text-[24px] xl:text-[28px] font-black text-rose-600 tracking-tight leading-none">Rp {{ number_format($wastageLoss, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Operating Expenses --}}
        <div class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-gray-200 hover:shadow-[0_8px_25px_-4px_rgba(0,0,0,0.05)] transition-all group relative overflow-hidden flex flex-col justify-center">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="relative z-10 w-full">
                <div class="flex items-center justify-between w-full">
                    <div class="flex-1 pr-2">
                        <h3 class="text-gray-400 text-[10px] md:text-[11px] font-black uppercase tracking-widest leading-none mb-2">Operating Expenses</h3>
                        <div class="text-xl md:text-[24px] xl:text-[28px] font-black text-gray-900 tracking-tight leading-none">Rp {{ number_format($totalOperatingExpenses, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Net Profit --}}
        <div class="bg-gradient-to-br from-blue-600 to-indigo-800 rounded-2xl p-4 md:p-5 shadow-[0_8px_25px_-4px_rgba(37,99,235,0.4)] border border-blue-800 hover:shadow-[0_12px_30px_-4px_rgba(37,99,235,0.5)] transition-all group relative overflow-hidden text-white flex flex-col justify-center">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white rounded-full opacity-10 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-center justify-between relative z-10 w-full">
                <div class="flex-1 pr-2">
                    <h3 class="text-blue-100 text-[10px] md:text-[11px] font-black uppercase tracking-widest leading-none mb-2">Net Profit (Laba Bersih)</h3>
                    <div class="text-xl md:text-[24px] xl:text-[28px] font-black tracking-tight leading-none">Rp {{ number_format($netProfit, 0, ',', '.') }}</div>
                </div>
                <div class="w-11 h-11 md:w-13 md:h-13 rounded-xl bg-white/10 flex items-center justify-center text-white shrink-0 border border-white/20 backdrop-blur-sm group-hover:bg-white group-hover:text-blue-600 transition-colors duration-300">
                    <svg class="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Cash Flow Chart --}}
        <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-800">Cash Flow (Revenue vs Expenses)</h3>
                <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-200">Includes Restock cash out</span>
            </div>
            <div class="relative h-72">
                @if(count($days) > 0)
                    <canvas id="cashflowChart"></canvas>
                @else
                    <div class="absolute inset-0 flex items-center justify-center text-gray-400 italic">Select a period of 31 days or less to view chart.</div>
                @endif
            </div>
        </div>

        {{-- Top Expenses Breakdown --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6">Top Expenses (Operating)</h3>
            
            @if($topExpenses->count() > 0)
                <div class="space-y-4">
                    @php 
                        $maxExp = $topExpenses->max('amount') ?: 1; 
                    @endphp
                    @foreach($topExpenses as $exp)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium text-gray-700 flex items-center gap-2">
                                <span>{{ $exp->category->icon ?? '💸' }}</span>
                                {{ $exp->category->name }}
                            </span>
                            <span class="font-bold text-gray-900">Rp {{ number_format($exp->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-rose-500 h-2 rounded-full" style="width: {{ ($exp->amount / $maxExp) * 100 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-8 relative h-40">
                    <canvas id="expenseDonutChart"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                    <svg class="w-12 h-12 mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                    <p>No operational expenses recorded.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Cash Flow Chart
    @if(count($days) > 0)
    const ctxCashflow = document.getElementById('cashflowChart').getContext('2d');
    
    // Gradients
    const gradientRev = ctxCashflow.createLinearGradient(0, 0, 0, 400);
    gradientRev.addColorStop(0, 'rgba(56, 189, 248, 0.4)'); // text-sky-400
    gradientRev.addColorStop(1, 'rgba(56, 189, 248, 0.0)');
    
    const gradientExp = ctxCashflow.createLinearGradient(0, 0, 0, 400);
    gradientExp.addColorStop(0, 'rgba(244, 63, 94, 0.4)'); // text-rose-500
    gradientExp.addColorStop(1, 'rgba(244, 63, 94, 0.0)');

    new Chart(ctxCashflow, {
        type: 'line',
        data: {
            labels: {!! json_encode($days) !!},
            datasets: [
                {
                    label: 'Gross Revenue',
                    data: {!! json_encode($revenueData) !!},
                    borderColor: '#38bdf8', // sky-400
                    backgroundColor: gradientRev,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#38bdf8',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Cash Outflow (Expenses + Restock)',
                    data: {!! json_encode($expenseData) !!},
                    borderColor: '#f43f5e', // rose-500
                    backgroundColor: gradientExp,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#f43f5e',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { family: "'Inter', sans-serif" } } },
                tooltip: { 
                    backgroundColor: 'rgba(17, 24, 39, 0.9)', 
                    padding: 12, 
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) { label += ': '; }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#f3f4f6', drawBorder: false },
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                            return 'Rp ' + value;
                        },
                        font: { family: "'Inter', sans-serif" }
                    }
                },
                x: { 
                    grid: { display: false, drawBorder: false },
                    ticks: { font: { family: "'Inter', sans-serif" } }
                }
            }
        }
    });
    @endif

    // Expense Donut Chart
    @if($expensesByCategory->count() > 0)
    const ctxDonut = document.getElementById('expenseDonutChart').getContext('2d');
    const expensesByCategoryData = {!! json_encode($expensesByCategory) !!};
    const donutLabels = Object.keys(expensesByCategoryData);
    const donutData = Object.values(expensesByCategoryData);
    
    // Generated colors for categories
    const colors = ['#f43f5e', '#ec4899', '#d946ef', '#a855f7', '#8b5cf6', '#6366f1', '#3b82f6', '#0ea5e9'];

    new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
            labels: donutLabels,
            datasets: [{
                data: donutData,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) { label += ': '; }
                            if (context.parsed !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
    @endif
});
</script>
@endsection
