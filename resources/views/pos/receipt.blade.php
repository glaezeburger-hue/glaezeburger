<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $transaction->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            width: 58mm;
            padding: 5mm;
            margin: 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .large { font-size: 14px; }
        .dashed-line {
            border-bottom: 1px dashed black;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        th, td {
            padding: 2px 0;
        }
        .w-full { width: 100%; }
        .mb-2 { margin-bottom: 5px; }
        .mt-2 { margin-top: 5px; }
        
        /* Auto-print helpers */
        @media print {
            body { 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
            }
        }
    </style>
</head>
<body>

    <div class="text-center mb-2">
        <div class="large bold">GLÆZE Burger</div>
        <div>Burger Specialist</div>
        <div>Bekasi, Indonesia</div>
    </div>

    <div class="dashed-line"></div>

    <table class="mb-2">
        <tr>
            <td class="text-left">No</td>
            <td class="text-left">: {{ $transaction->invoice_number }}</td>
        </tr>
        <tr>
            <td class="text-left">Date</td>
            <td class="text-left">: {{ $transaction->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="text-left">Cashier</td>
            <td class="text-left">: {{ $transaction->user ? $transaction->user->name : 'N/A' }}</td>
        </tr>
    </table>

    <div class="dashed-line"></div>

    <table class="mb-2">
        <tr>
            <th class="text-left">Item</th>
            <th class="text-center">@</th>
            <th class="text-right">Subtotal</th>
        </tr>
        @foreach($transaction->transactionItems as $item)
        <tr>
            <td colspan="3" class="bold pb-0">{{ $item->product->name }}</td>
        </tr>
        @if($item->notes)
        <tr>
            <td colspan="3" class="pb-0" style="font-size: 10px; font-style: italic; color: #555;">* {{ $item->notes }}</td>
        </tr>
        @endif
        <tr>
            <td class="text-left">{{ $item->quantity }}x</td>
            <td class="text-center">{{ number_format($item->price, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="dashed-line"></div>

    <table class="mb-2 mt-2">
        <tr>
            <td class="text-left">Subtotal</td>
            <td class="text-right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($transaction->discount_amount > 0)
        <tr>
            <td class="text-left">Discount</td>
            <td class="text-right">- Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($transaction->voucher_discount_amount > 0)
        <tr>
            <td class="text-left">Voucher ({{ $transaction->voucher->code ?? 'VOUCHER' }})</td>
            <td class="text-right">- Rp {{ number_format($transaction->voucher_discount_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($transaction->tax_amount > 0)
        <tr>
            <td class="text-left">PB1 Tax (10%)</td>
            <td class="text-right">+ Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td class="text-left bold large pt-2 pb-2">Grand Total</td>
            <td class="text-right bold large pt-2 pb-2">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Payment</td>
            <td class="text-right">{{ strtoupper($transaction->payment_method) }}</td>
        </tr>
    </table>

    <div class="dashed-line"></div>

    <div class="text-center mt-2">
        <div class="bold">THANK YOU FOR VISITING!</div>
        <div>Follow us @glaezeburger</div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>
