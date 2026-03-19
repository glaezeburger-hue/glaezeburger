@extends('layouts.app')

@section('header', 'Overview & Analytics')

@section('content')
<div class="space-y-6">
    <!-- Row 1: Primary KPIs (Today) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        <!-- Today's Revenue -->
        <div class="bg-white rounded-2xl p-5 md:p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-blue-200 hover:shadow-[0_8px_25px_-4px_rgba(59,130,246,0.1)] transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-start justify-between relative z-10 w-full">
                <div class="flex-1 pr-4">
                    <div class="flex items-center gap-2 mb-2">
                        <h3 class="text-gray-500 text-[10px] md:text-[11px] font-bold uppercase tracking-widest leading-none mt-1">Gross Revenue</h3>
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[9px] font-black rounded uppercase tracking-widest leading-none">Today</span>
                    </div>
                    <div class="text-2xl md:text-[28px] font-black text-gray-900 tracking-tight">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</div>
                </div>
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0 border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Today's Orders -->
        <div class="bg-white rounded-2xl p-5 md:p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-orange-200 hover:shadow-[0_8px_25px_-4px_rgba(249,115,22,0.1)] transition-all group relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-gradient-to-br from-orange-50 to-orange-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-start justify-between relative z-10 w-full">
                <div class="flex-1 pr-4">
                    <div class="flex items-center gap-2 mb-2">
                        <h3 class="text-gray-500 text-[10px] md:text-[11px] font-bold uppercase tracking-widest leading-none mt-1">Total Orders</h3>
                        <span class="px-2 py-0.5 bg-orange-50 text-orange-600 text-[9px] font-black rounded uppercase tracking-widest leading-none">Today</span>
                    </div>
                    <div class="text-2xl md:text-[28px] font-black text-gray-900 tracking-tight">{{ number_format($totalOrdersToday) }} <span class="text-sm font-semibold text-gray-400">Trans.</span></div>
                </div>
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 shrink-0 border border-orange-100 group-hover:bg-orange-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Today's Net Profit -->
        <div class="md:col-span-2 lg:col-span-1 bg-gradient-to-br from-blue-600 to-indigo-800 rounded-2xl p-5 md:p-6 shadow-[0_8px_25px_-4px_rgba(37,99,235,0.4)] border border-blue-800 hover:shadow-[0_12px_30px_-4px_rgba(37,99,235,0.5)] transition-all group relative overflow-hidden text-white">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-white rounded-full opacity-10 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-start justify-between relative z-10 w-full">
                <div class="flex-1 pr-4">
                    <div class="flex items-center gap-2 mb-2">
                        <h3 class="text-blue-100 text-[10px] md:text-[11px] font-bold uppercase tracking-widest leading-none mt-1">Net Profit Est.</h3>
                        <span class="px-2 py-0.5 bg-white/20 text-white text-[9px] font-black rounded uppercase tracking-widest backdrop-blur-sm leading-none">Today</span>
                    </div>
                    <div class="text-2xl md:text-[28px] font-black tracking-tight">Rp {{ number_format($todayNetProfit, 0, ',', '.') }}</div>
                </div>
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-white/10 flex items-center justify-center text-white shrink-0 border border-white/20 backdrop-blur-sm group-hover:bg-white group-hover:text-blue-600 transition-colors duration-300">
                    <svg class="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Secondary Monthly KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mt-4 md:mt-6">
        <!-- Monthly Revenue -->
        <div class="bg-white rounded-2xl p-5 md:p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-indigo-200 hover:shadow-[0_8px_25px_-4px_rgba(99,102,241,0.1)] transition-all flex flex-col justify-between group relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-gradient-to-br from-indigo-50 to-indigo-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            
            <div class="flex items-start justify-between relative z-10 mb-6 w-full">
                <div class="flex-1 pr-4">
                    <h3 class="text-gray-500 text-[10px] md:text-[11px] font-bold uppercase tracking-widest mb-2 leading-none">Monthly Revenue</h3>
                    <div class="text-xl md:text-[26px] mt-1 font-black text-gray-900 tracking-tight leading-none">Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</div>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0 border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
            
            <div class="relative z-10 pt-4 border-t border-gray-100 flex items-center justify-between w-full">
                <span class="text-gray-500 text-[10px] md:text-[11px] font-bold uppercase tracking-widest">Avg. Order Value</span>
                <span class="text-gray-900 text-[11px] md:text-[12px] font-black tracking-tight">Rp {{ number_format($averageOrderValue, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Monthly Orders -->
        <div class="bg-white rounded-2xl p-5 md:p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-purple-200 hover:shadow-[0_8px_25px_-4px_rgba(168,85,247,0.1)] transition-all flex flex-col justify-between group relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            
            <div class="flex items-start justify-between relative z-10 mb-6 w-full">
                <div class="flex-1 pr-4">
                    <h3 class="text-gray-500 text-[10px] md:text-[11px] font-bold uppercase tracking-widest mb-2 leading-none">Monthly Volume</h3>
                    <div class="text-xl md:text-[26px] mt-1 font-black text-gray-900 tracking-tight leading-none">{{ number_format($monthlyOrdersCount) }} <span class="text-sm font-semibold text-gray-400">Orders</span></div>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 shrink-0 border border-purple-100 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
            
            <div class="relative z-10 pt-4 border-t border-gray-100 flex items-center justify-between w-full">
                <span class="text-gray-500 text-[10px] md:text-[11px] font-bold uppercase tracking-widest">Pending Orders</span>
                <span class="text-[11px] md:text-[12px] font-black tracking-tight {{ $pendingOrdersToday > 0 ? 'text-orange-500' : 'text-gray-500' }}">{{ $pendingOrdersToday }} Today</span>
            </div>
        </div>

        <!-- Monthly Net Profit -->
        <div class="md:col-span-2 lg:col-span-1 bg-white rounded-2xl p-5 md:p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-emerald-200 hover:shadow-[0_8px_25px_-4px_rgba(16,185,129,0.1)] transition-all flex flex-col justify-between group relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            
            <div class="flex items-start justify-between relative z-10 mb-6 w-full">
                <div class="flex-1 pr-4">
                    <h3 class="text-gray-500 text-[10px] md:text-[11px] font-bold uppercase tracking-widest mb-2 leading-none">Monthly Net Profit</h3>
                    <div class="text-xl md:text-[26px] mt-1 font-black text-emerald-600 tracking-tight leading-none">Rp {{ number_format($monthlyNetProfit, 0, ',', '.') }}</div>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0 border border-emerald-100 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            
            <div class="relative z-10 pt-4 border-t border-gray-100 flex items-center justify-between w-full">
                <span class="text-gray-500 text-[10px] md:text-[11px] font-bold uppercase tracking-widest">Monthly Expenses</span>
                <span class="text-[11px] md:text-[12px] font-black text-red-500 tracking-tight">Rp {{ number_format($monthlyExpenses, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Row 3: Charts (Revenue vs Expenses & Category Mix) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Chart -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-8 shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 space-y-4 md:space-y-0 relative z-10">
                <div>
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Business Cashflow</h3>
                    <p class="text-[11px] font-bold text-gray-400 uppercase mt-1 tracking-tight">Revenue vs. Operating Expenses (Last 7 Days)</p>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-smash-blue"></div>
                        <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Revenue</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Expenses</span>
                    </div>
                </div>
            </div>
            <div class="relative h-80 w-full z-10">
                <canvas id="cashflowChart"></canvas>
            </div>
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 bg-blue-50/20 rounded-full blur-3xl"></div>
        </div>

        <!-- Donut Chart -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col">
            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-2">Category Mix</h3>
            <p class="text-[11px] font-bold text-gray-400 uppercase mb-8 tracking-tight">Revenue contribution by category</p>
            <div class="relative flex-1 flex flex-col justify-center min-h-[250px]">
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="mt-6 space-y-2">
                @foreach($salesByCategory->take(3) as $cat)
                <div class="flex items-center justify-between text-[11px]">
                    <span class="font-bold text-gray-500 uppercase tracking-tight">{{ $cat->name }}</span>
                    <span class="font-black text-gray-900">Rp{{ number_format($cat->total, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Row 4: Hourly Heatmap Bar Chart (Full Width) -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mt-6">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Traffic Density</h3>
                <p class="text-[11px] font-bold text-gray-400 uppercase mt-1 tracking-tight">Order distribution by hour today</p>
            </div>
            <div class="text-right">
                <span class="block text-xl font-black text-orange-500 tracking-tighter">{{ array_sum($hourlyPattern) }}</span>
                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Total Today</span>
            </div>
        </div>
        <div class="relative h-48 w-full">
            <canvas id="hourlyChart"></canvas>
        </div>
    </div>

    <!-- Row 5: Tables Area (Best Sellers & Recent Transactions) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- Best Sellers -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col overflow-hidden">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Top Performers</h3>
                <a href="{{ route('products.index') }}" class="text-[10px] font-black text-smash-blue uppercase tracking-widest hover:underline">Full Inventory →</a>
            </div>
            <div class="space-y-6 flex-1">
                @forelse($bestSellers as $item)
                <div class="flex items-center group">
                    <div class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center flex-shrink-0 overflow-hidden group-hover:border-blue-100 transition-colors">
                        @if($item->product->image_path)
                            <img src="{{ asset('storage/' . $item->product->image_path) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                        @else
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        @endif
                    </div>
                    <div class="ml-4 flex-1 min-w-0">
                        <p class="text-[13px] font-black text-gray-900 truncate group-hover:text-smash-blue transition-colors">{{ $item->product->name }}</p>
                        <div class="flex items-center space-x-2 mt-0.5">
                            <span class="px-2 py-0.5 bg-gray-100 text-[9px] font-black text-gray-500 rounded uppercase tracking-widest">{{ $item->total_sold }} Sold</span>
                            <span class="text-[10px] font-bold text-gray-300">/</span>
                            <span class="text-[10px] font-bold text-gray-400">Rp{{ number_format($item->product->selling_price, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="text-[13px] font-black text-gray-900 text-right">
                        Rp{{ number_format($item->total_revenue, 0, ',', '.') }}
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center h-full text-center py-8">
                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest">No Sales Data</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Recent Activity</h3>
                <a href="{{ route('transactions.index') }}" class="text-[10px] font-black text-smash-blue uppercase tracking-widest hover:underline">History →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="hidden md:table-header-group">
                        <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">
                            <th class="pb-4">Invoice</th>
                            <th class="pb-4">Time</th>
                            <th class="pb-4 text-right">Method</th>
                            <th class="pb-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentTransactions as $tx)
                        <tr class="group text-[13px]">
                            <td class="py-4 font-black text-gray-900 uppercase tracking-tighter">
                                <span class="group-hover:text-smash-blue transition-colors">{{ substr($tx->invoice_number, -4) }}</span>
                            </td>
                            <td class="py-4 font-bold text-gray-500 text-[11px]">
                                {{ $tx->created_at->format('H:i') }}
                            </td>
                            <td class="py-4 text-right">
                                <span class="px-2 py-0.5 {{ $tx->payment_method == 'QRIS' ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700' }} text-[9px] font-black rounded uppercase tracking-widest">
                                    {{ $tx->payment_method }}
                                </span>
                            </td>
                            <td class="py-4 text-right font-black text-gray-900">
                                Rp{{ number_format($tx->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-[11px] font-black text-gray-300 uppercase tracking-widest">No activity</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Row 6: Alerts & Support -->
    <div class="bg-red-50/50 rounded-3xl p-8 border border-red-100 flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0 mt-6">
        <div class="flex items-center space-x-6">
            <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-red-500 shadow-sm">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <h4 class="text-sm font-black text-red-900 uppercase tracking-widest">Inventory Attention Required</h4>
                <p class="text-xs font-bold text-red-600/70 mt-1">{{ $lowStockMaterials->count() }} ingredients are running below safe thresholds.</p>
            </div>
        </div>
        <a href="{{ route('raw-materials.index') }}" class="px-8 py-3 bg-red-500 text-white text-xs font-black rounded-2xl shadow-xl shadow-red-500/20 hover:bg-red-600 transition-all uppercase tracking-widest active:scale-95">
            Resolve Stock Issues
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Shared Chart.js Config
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#9CA3AF';

    // 1. Cashflow Chart
    const flowCtx = document.getElementById('cashflowChart').getContext('2d');
    const flowRevGradient = flowCtx.createLinearGradient(0, 0, 0, 300);
    flowRevGradient.addColorStop(0, 'rgba(10, 86, 200, 0.15)');
    flowRevGradient.addColorStop(1, 'rgba(10, 86, 200, 0)');

    new Chart(flowCtx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: 'Revenue',
                    data: @json($revenueChartData),
                    borderColor: '#0A56C8',
                    backgroundColor: flowRevGradient,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2
                },
                {
                    label: 'Expenses',
                    data: @json($expenseChartData),
                    borderColor: '#F87171',
                    borderDash: [5, 5],
                    tension: 0.4,
                    fill: false,
                    borderWidth: 2,
                    pointRadius: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { cornerRadius: 12, padding: 12 } },
            scales: {
                x: { grid: { display: false } },
                y: { 
                    border: { display: false },
                    grid: { color: '#F3F4F6', borderDash: [5, 5] },
                    ticks: {
                        callback: function(value) { return 'Rp' + (value/1000).toLocaleString() + 'k'; }
                    }
                }
            }
        }
    });

    // 2. Category Mix Chart
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: @json($salesByCategory->pluck('name')),
            datasets: [{
                data: @json($salesByCategory->pluck('total')),
                backgroundColor: ['#0A56C8', '#6366F1', '#8B5CF6', '#EC4899', '#F43F5E', '#F97316'],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '80%',
            plugins: {
                legend: { display: false }
            }
        }
    });

    // 3. Hourly Bar Chart
    const hrCtx = document.getElementById('hourlyChart').getContext('2d');
    const hrGradient = hrCtx.createLinearGradient(0, 0, 0, 150);
    hrGradient.addColorStop(0, '#FB923C');
    hrGradient.addColorStop(1, 'rgba(251, 146, 60, 0.2)');

    new Chart(hrCtx, {
        type: 'bar',
        data: {
            labels: Array.from({length: 24}, (_, i) => `${i}:00`),
            datasets: [{
                data: @json($hourlyPattern),
                backgroundColor: hrGradient,
                borderRadius: 8,
                maxBarThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                y: { display: false }
            }
        }
    });
});
</script>

<style>
    .rounded-3xl { border-radius: 1.75rem; }
</style>
@endsection
