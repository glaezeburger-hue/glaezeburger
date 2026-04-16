<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseRestockItem;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Wastage;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /**
     * Display the financial dashboard.
     */
    public function dashboard(Request $request)
    {
        // Date Range Filtering
        $period = $request->get('period', 'this_month');
        $now = now();
        
        $startDate = match ($period) {
            'today' => $now->copy()->startOfDay(),
            '7_days' => $now->copy()->subDays(6)->startOfDay(),
            'this_month' => $now->copy()->startOfMonth(),
            'last_month' => $now->copy()->subMonth()->startOfMonth(),
            'custom' => $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : $now->copy()->startOfMonth(),
        };

        $endDate = match ($period) {
            'last_month' => $now->copy()->subMonth()->endOfMonth(),
            'custom' => $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : $now->copy()->endOfDay(),
            default => $now->copy()->endOfDay(),
        };

        // 1. Gross Revenue
        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('order_status', 'Sudah')
            ->get();
        $grossRevenue = $transactions->sum('total_amount');

        // 2. COGS (Cost of Goods Sold)
        $transactionIds = $transactions->pluck('id');
        $items = TransactionItem::with(['product', 'addons', 'variations'])->whereIn('transaction_id', $transactionIds)->get();
        $cogs = $items->sum(function ($item) {
            $baseCost = $item->product->cost_price ?? 0;
            $variationCostModifier = $item->variations ? $item->variations->sum('cost_modifier') : 0;
            $adjustedCost = max(0, $baseCost + $variationCostModifier);
            $productCost = $item->quantity * $adjustedCost;

            $addonCost = $item->addons->sum(function($addon) use ($item) {
                return $addon->quantity * $item->quantity * $addon->cost_price;
            });
            return $productCost + $addonCost;
        });

        // 3. Wastage Loss
        // To accurately calculate wastage loss, we fall back to average restock price or 0 if unknown
        $wastages = Wastage::whereBetween('wastage_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->get();
        $wastageLoss = 0;
        foreach ($wastages as $wastage) {
            // Find latest restock price for this raw material
            $latestRestock = ExpenseRestockItem::where('raw_material_id', $wastage->raw_material_id)
                ->latest()
                ->first();
            $unitCost = $latestRestock ? $latestRestock->unit_cost : 0;
            $wastageLoss += ($wastage->quantity * $unitCost);
        }

        // 4. Gross Profit
        $grossProfit = $grossRevenue - $cogs - $wastageLoss;

        // 5. Operating Expenses (EXCLUDE Restock because Restock is an asset conversion, not direct expense)
        // Only non-restock expenses affect Net Profit
        $restockCategoryIds = ExpenseCategory::where('is_restock', true)->pluck('id');
        $operatingExpensesQuery = Expense::whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNotIn('expense_category_id', $restockCategoryIds);
        
        $totalOperatingExpenses = $operatingExpensesQuery->sum('amount');

        // 6. Net Profit
        $netProfit = $grossProfit - $totalOperatingExpenses;

        // Revenue Breakdown (Cash vs QRIS)
        $revenueByMethod = $transactions->groupBy('payment_method')->map(function ($group) {
            return $group->sum('total_amount');
        });

        // Expense Breakdown by Category (Operating Expenses Only)
        $expensesByCategory = Expense::with('category')
            ->whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNotIn('expense_category_id', $restockCategoryIds)
            ->select('expense_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category_id')
            ->get()
            ->mapWithKeys(function ($exp) {
                return [$exp->category->name => $exp->total];
            });

        // Top 5 Expenses
        $topExpenses = Expense::with('category')
            ->whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNotIn('expense_category_id', $restockCategoryIds)
            ->orderByDesc('amount')
            ->limit(5)
            ->get();

        // Cash Flow Chart Data (Last 7 days or selected period if reasonably small)
        $days = [];
        $revenueData = [];
        $expenseData = []; // Cash Flow includes ALL expenses including Restock (cash out)
        
        $diffDays = $startDate->diffInDays($endDate);
        if ($diffDays <= 31) {
            for ($i = 0; $i <= $diffDays; $i++) {
                $date = $startDate->copy()->addDays($i)->format('Y-m-d');
                $days[] = Carbon::parse($date)->format('d M');
                
                $revenueData[] = Transaction::whereDate('created_at', $date)->where('order_status', 'Sudah')->sum('total_amount');
                $expenseData[] = Expense::whereDate('expense_date', $date)->sum('amount'); // ALL cash out
            }
        }

        return view('finance.dashboard', compact(
            'grossRevenue', 'cogs', 'wastageLoss', 'grossProfit', 
            'totalOperatingExpenses', 'netProfit',
            'revenueByMethod', 'expensesByCategory', 'topExpenses',
            'days', 'revenueData', 'expenseData', 'period'
        ));
    }

    /**
     * Display detailed Profit & Loss Statement.
     */
    public function profitLoss(Request $request)
    {
        // Similar logic, but structured specifically for P&L printing
        $period = $request->get('period', 'this_month');
        $now = now();
        
        $startDate = match ($period) {
            'this_month' => $now->copy()->startOfMonth(),
            'last_month' => $now->copy()->subMonth()->startOfMonth(),
            'this_year' => $now->copy()->startOfYear(),
            'custom' => $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : $now->copy()->startOfMonth(),
        };

        $endDate = match ($period) {
            'last_month' => $now->copy()->subMonth()->endOfMonth(),
            'this_year' => $now->copy()->endOfYear(),
            'custom' => $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : $now->copy()->endOfDay(),
            default => $now->copy()->endOfDay(),
        };

        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])->where('order_status', 'Sudah')->get();
        
        // P&L Components
        $grossSales = $transactions->sum('subtotal');
        $discounts = $transactions->sum('discount_amount') + $transactions->sum('voucher_discount_amount');
        $netSales = $grossSales - $discounts; // Note: transactions.net_sales exists but sum directly is safer due to partials

        $transactionIds = $transactions->pluck('id');
        $items = TransactionItem::with(['product', 'addons', 'variations'])->whereIn('transaction_id', $transactionIds)->get();
        $cogs = $items->sum(function ($item) {
            $baseCost = $item->product->cost_price ?? 0;
            $variationCostModifier = $item->variations ? $item->variations->sum('cost_modifier') : 0;
            $adjustedCost = max(0, $baseCost + $variationCostModifier);
            $productCost = $item->quantity * $adjustedCost;
            
            $addonCost = $item->addons->sum(function($addon) use ($item) {
                return $addon->quantity * $item->quantity * $addon->cost_price;
            });
            return $productCost + $addonCost;
        });

        // Wastage Loss
        $wastages = Wastage::whereBetween('wastage_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->get();
        $wastageLoss = 0;
        foreach ($wastages as $wastage) {
            $latestRestock = ExpenseRestockItem::where('raw_material_id', $wastage->raw_material_id)->latest()->first();
            $unitCost = $latestRestock ? $latestRestock->unit_cost : 0;
            $wastageLoss += ($wastage->quantity * $unitCost);
        }

        $grossProfit = $netSales - $cogs - $wastageLoss;

        $restockCategoryIds = ExpenseCategory::where('is_restock', true)->pluck('id');
        
        // Operational Expenses grouped by category
        $operatingExpenses = Expense::with('category')
            ->whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNotIn('expense_category_id', $restockCategoryIds)
            ->select('expense_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category_id')
            ->get();

        $totalOperatingExpenses = $operatingExpenses->sum('total');

        $taxCollected = $transactions->sum('tax_amount');

        $netProfit = $grossProfit - $totalOperatingExpenses;

        return view('finance.profit-loss', compact(
            'startDate', 'endDate', 'period',
            'grossSales', 'discounts', 'netSales',
            'cogs', 'wastageLoss', 'grossProfit',
            'operatingExpenses', 'totalOperatingExpenses',
            'taxCollected', 'netProfit'
        ));
    }
}
