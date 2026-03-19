<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit & Loss Statement - GLÆZE Burger</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body { background-color: white; }
            .no-print { display: none; }
            .print-border { border-color: black !important; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900 antialiased p-8">

<div class="max-w-4xl mx-auto bg-white p-12 shadow-md rounded-xl">
    
    {{-- Header Content --}}
    <div class="flex justify-between items-start border-b-2 border-gray-900 pb-6 mb-8 print-border">
        <div>
            <h1 class="text-3xl font-black tracking-tighter uppercase mb-1">Profit & Loss Statement</h1>
            <h2 class="text-xl font-bold text-gray-600">GLÆZE Burger</h2>
            <p class="text-sm text-gray-500 mt-2">
                Period: {{ $startDate->format('d M Y') }} – {{ $endDate->format('d M Y') }}
                <br>
                Generated: {{ now()->format('d M Y H:i:s') }}
            </p>
        </div>
        <div class="text-right no-print">
            <button onclick="window.print()" class="px-6 py-2 bg-gray-900 text-white font-bold rounded-lg hover:bg-black transition-colors">
                Print Report
            </button>
        </div>
    </div>

    {{-- Financial Data Table --}}
    <div class="w-full">
        
        {{-- 1. REVENUE --}}
        <div class="mb-6">
            <h3 class="text-lg font-bold border-b border-gray-300 pb-2 mb-3 text-smash-blue">1. REVENUE (PENDAPATAN)</h3>
            
            <div class="flex justify-between py-2 text-sm">
                <span>Gross Sales (Penjualan Kotor)</span>
                <span>Rp {{ number_format($grossSales, 0, ',', '.') }}</span>
            </div>
            
            @if($discounts > 0)
            <div class="flex justify-between py-2 text-sm text-rose-600">
                <span>Less: Discounts & Vouchers</span>
                <span>(Rp {{ number_format($discounts, 0, ',', '.') }})</span>
            </div>
            @endif
            
            <div class="flex justify-between py-3 text-base font-bold bg-gray-50 px-4 mt-2 rounded">
                <span>Net Sales (Penjualan Bersih)</span>
                <span>Rp {{ number_format($netSales, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- 2. COGS & WASTAGE --}}
        <div class="mb-6">
            <h3 class="text-lg font-bold border-b border-gray-300 pb-2 mb-3 text-orange-600">2. COST OF GOODS SOLD (HPP)</h3>
            
            <div class="flex justify-between py-2 text-sm">
                <span>Product Costs (Bahan Baku Terjual)</span>
                <span>Rp {{ number_format($cogs, 0, ',', '.') }}</span>
            </div>
            
            @if($wastageLoss > 0)
            <div class="flex justify-between py-2 text-sm text-red-600">
                <span>Wastage Loss (Bahan Terbuang/Rusak)</span>
                <span>Rp {{ number_format($wastageLoss, 0, ',', '.') }}</span>
            </div>
            @endif
            
            <div class="flex justify-between py-3 text-base font-bold bg-gray-50 px-4 mt-2 rounded">
                <span>Total COGS</span>
                <span>Rp {{ number_format($cogs + $wastageLoss, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- 3. GROSS PROFIT --}}
        <div class="flex justify-between py-4 text-lg font-black bg-blue-50 px-4 mb-8 border-l-4 border-smash-blue">
            <span>GROSS PROFIT (LABA KOTOR)</span>
            <span class="text-smash-blue">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span>
        </div>

        {{-- 4. OPERATING EXPENSES --}}
        <div class="mb-6">
            <h3 class="text-lg font-bold border-b border-gray-300 pb-2 mb-3 text-rose-600">3. OPERATING EXPENSES (BEBAN OPERASIONAL)</h3>
            
            @forelse($operatingExpenses as $expense)
            <div class="flex justify-between py-2 text-sm">
                <span class="flex items-center gap-2">
                    <span>{{ $expense->category->icon ?? '' }}</span>
                    {{ $expense->category->name }}
                </span>
                <span>Rp {{ number_format($expense->total, 0, ',', '.') }}</span>
            </div>
            @empty
            <div class="py-2 text-sm text-gray-500 italic">No operating expenses recorded in this period.</div>
            @endforelse
            
            <div class="flex justify-between py-3 text-base font-bold bg-gray-50 px-4 mt-2 rounded border-t border-gray-200">
                <span>Total Operating Expenses</span>
                <span>Rp {{ number_format($totalOperatingExpenses, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- 5. NET PROFIT --}}
        <div class="flex justify-between py-6 text-2xl font-black bg-gray-900 text-white px-6 mt-8 rounded-xl shadow-lg print-border">
            <span>NET PROFIT (LABA BERSIH)</span>
            <span>Rp {{ number_format($netProfit, 0, ',', '.') }}</span>
        </div>

        {{-- Notes Footer --}}
        <div class="mt-12 pt-6 border-t border-gray-200 text-xs text-gray-500">
            <p><strong>Note:</strong></p>
            <ul class="list-disc pl-4 mt-2 space-y-1">
                <li>Restock Bahan Baku transactions are <span class="font-bold underline">not</span> included in Operating Expenses as they are treated as an asset exchange (Cash to Inventory). They are accounted for within COGS and Wastage Loss.</li>
                <li>Tax PB1 Collected (Rp {{ number_format($taxCollected, 0, ',', '.') }}) is excluded from both Revenue and Expenses as it is a pure liability to be remitted to the government.</li>
            </ul>
        </div>
        
    </div>
</div>

</body>
</html>
