@extends('layouts.app')

@section('header', 'Financial Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Period Selector & Actions --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <form action="{{ route('finance.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2">
            @foreach(['today' => 'Today', '7_days' => 'Last 7 Days', 'this_month' => 'This Month', 'last_month' => 'Last Month'] as $key => $label)
                <button type="submit" name="period" value="{{ $key }}" 
                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors border 
                    {{ $period === $key ? 'bg-smash-blue text-white border-smash-blue' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                    {{ $label }}
                </button>
            @endforeach
            
            <div x-data="{ open: {{ $period === 'custom' ? 'true' : 'false' }} }" class="relative flex items-center gap-2 ml-2">
                <button type="button" @click="open = !open" 
                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors border 
                    {{ $period === 'custom' ? 'bg-smash-blue text-white border-smash-blue' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                    Custom Range
                </button>
                
                <div x-show="open" class="flex items-center gap-2 mt-2 sm:mt-0" style="display: none;">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border-gray-200 text-sm">
                    <span class="text-gray-400">-</span>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border-gray-200 text-sm">
                    <button type="submit" name="period" value="custom" class="bg-gray-800 text-white px-3 py-2 rounded-lg text-sm hover:bg-gray-700">Apply</button>
                </div>
            </div>
        </form>

        <div>
            <a href="{{ route('finance.profit-loss', request()->all()) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 shadow-sm transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print P&L Statement
            </a>
        </div>
    </div>

    {{-- Main KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        
        {{-- Gross Revenue --}}
        <div class="bg-white rounded-2xl p-5 md:p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-blue-200 hover:shadow-[0_8px_25px_-4px_rgba(59,130,246,0.1)] transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-start justify-between relative z-10 w-full">
                <div class="flex-1 pr-4">
                    <h3 class="text-gray-500 text-[10px] md:text-[11px] font-bold uppercase tracking-widest leading-none mt-1 mb-2">Gross Revenue</h3>
                    <div class="text-2xl md:text-[28px] font-black text-gray-900 tracking-tight leading-none">Rp {{ number_format($grossRevenue, 0, ',', '.') }}</div>
                </div>
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0 border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        {{-- COGS --}}
        <div class="bg-white rounded-2xl p-5 md:p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-orange-200 hover:shadow-[0_8px_25px_-4px_rgba(249,115,22,0.1)] transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-gradient-to-br from-orange-50 to-orange-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-start justify-between relative z-10 w-full">
                <div class="flex-1 pr-4">
                    <h3 class="text-gray-500 text-[10px] md:text-[11px] font-bold uppercase tracking-widest leading-none mt-1 mb-2">COGS</h3>
                    <div class="text-2xl md:text-[28px] font-black text-gray-900 tracking-tight leading-none">Rp {{ number_format($cogs, 0, ',', '.') }}</div>
                </div>
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 shrink-0 border border-orange-100 group-hover:bg-orange-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
        </div>

        {{-- Gross Profit --}}
        <div class="bg-white rounded-2xl p-5 md:p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-teal-200 hover:shadow-[0_8px_25px_-4px_rgba(20,184,166,0.1)] transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-gradient-to-br from-teal-50 to-teal-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-start justify-between relative z-10 w-full">
                <div class="flex-1 pr-4">
                    <h3 class="text-gray-500 text-[10px] md:text-[11px] font-bold uppercase tracking-widest leading-none mt-1 mb-2">Gross Profit</h3>
                    <div class="text-2xl md:text-[28px] font-black text-teal-600 tracking-tight leading-none">Rp {{ number_format($grossProfit, 0, ',', '.') }}</div>
                </div>
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600 shrink-0 border border-teal-100 group-hover:bg-teal-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
        </div>

        {{-- Wastage & Expenses (Combined) --}}
        <div class="bg-white rounded-2xl p-5 md:p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-rose-200 hover:shadow-[0_8px_25px_-4px_rgba(244,63,94,0.1)] transition-all group relative overflow-hidden flex flex-col justify-center">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-gradient-to-br from-rose-50 to-rose-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="relative z-10 w-full">
                <!-- Wastage Row -->
                <div class="flex items-center justify-between mb-3 w-full">
                    <div class="flex-1 pr-2">
                        <div class="flex items-center gap-1.5 mb-1.5">
                            <h3 class="text-rose-500 text-[10px] md:text-[11px] font-bold uppercase tracking-widest leading-none">Wastage Loss</h3>
                        </div>
                        <div class="text-2xl md:text-[28px] font-black text-rose-600 tracking-tight leading-none">Rp {{ number_format($wastageLoss, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Wastage & Expenses (Combined) --}}
        <div class="bg-white rounded-2xl p-5 md:p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-rose-200 hover:shadow-[0_8px_25px_-4px_rgba(244,63,94,0.1)] transition-all group relative overflow-hidden flex flex-col justify-center">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-gradient-to-br from-rose-50 to-rose-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="relative z-10 w-full">
                <!-- Expenses Row -->
                <div class="flex items-center justify-between w-full">
                    <div class="flex-1 pr-2">
                        <div class="flex items-center gap-1.5 mb-1.5">
                            <h3 class="text-gray-500 text-[10px] md:text-[11px] font-bold uppercase tracking-widest leading-none">Operating Expenses</h3>
                        </div>
                        <div class="text-2xl md:text-[28px] font-black text-gray-900 tracking-tight leading-none">Rp {{ number_format($totalOperatingExpenses, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NET PROFIT -->
        <div class="md:col-span-1 lg:col-span-1 bg-gradient-to-br from-blue-600 to-indigo-800 rounded-2xl p-5 md:p-6 shadow-[0_8px_25px_-4px_rgba(37,99,235,0.4)] border border-blue-800 hover:shadow-[0_12px_30px_-4px_rgba(37,99,235,0.5)] transition-all group relative overflow-hidden text-white flex flex-col justify-center">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white rounded-full opacity-10 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-center justify-between relative z-10 w-full">
                <div class="flex-1 pr-4">
                    <h3 class="text-blue-100 text-[10px] md:text-[11px] font-bold uppercase tracking-widest leading-none mb-2">Net Profit (Laba Bersih)</h3>
                    <div class="text-4xl md:text-[35px] font-black tracking-tight leading-none">Rp {{ number_format($netProfit, 0, ',', '.') }}</div>
                </div>
                <div class="w-14 h-14 md:w-16 md:h-16 rounded-xl bg-white/10 flex items-center justify-center text-white shrink-0 border border-white/20 backdrop-blur-sm group-hover:bg-white group-hover:text-blue-600 transition-colors duration-300">
                    <svg class="w-7 h-7 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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
