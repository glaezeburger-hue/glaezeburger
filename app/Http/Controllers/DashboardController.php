<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseRestockItem;
use App\Models\Wastage;
use App\Models\RawMaterial;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with financial reports.
     */
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // ── Today's Primary Metrics ──────────────────────────────
        $todayRevenue = Transaction::whereDate('created_at', $today)
            ->where('order_status', 'Sudah')
            ->sum('total_amount');

        $totalOrdersToday = Transaction::whereDate('created_at', $today)
            ->where('order_status', 'Sudah')
            ->count();

        $pendingOrdersToday = Transaction::whereDate('created_at', $today)
            ->where('order_status', 'Belum')
            ->count();

        // ── Monthly Primary Metrics ──────────────────────────────
        $monthlyRevenue = Transaction::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where('order_status', 'Sudah')
            ->sum('total_amount');

        $monthlyOrdersCount = Transaction::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where('order_status', 'Sudah')
            ->count();

        $averageOrderValue = $monthlyOrdersCount > 0 ? $monthlyRevenue / $monthlyOrdersCount : 0;

        // ── Net Profit Calculation (Simplified for Dashboard) ───
        
        // Today's Profit Components
        $todayTransactions = Transaction::whereDate('created_at', $today)->where('order_status', 'Sudah')->get();
        $todayTransactionIds = $todayTransactions->pluck('id');
        
        $todayCogs = TransactionItem::whereIn('transaction_id', $todayTransactionIds)
            ->with('product')
            ->get()
            ->sum(function($item) {
                return $item->quantity * ($item->product->cost_price ?? 0);
            });

        $restockCategoryIds = ExpenseCategory::where('is_restock', true)->pluck('id');
        $todayExpenses = Expense::whereDate('expense_date', $today)
            ->whereNotIn('expense_category_id', $restockCategoryIds)
            ->sum('amount');

        $todayNetProfit = $todayRevenue - $todayCogs - $todayExpenses;

        // Monthly Profit Components
        $monthlyTransactions = Transaction::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where('order_status', 'Sudah')
            ->get();
        $monthlyTransactionIds = $monthlyTransactions->pluck('id');

        $monthlyCogs = TransactionItem::whereIn('transaction_id', $monthlyTransactionIds)
            ->with('product')
            ->get()
            ->sum(function($item) {
                return $item->quantity * ($item->product->cost_price ?? 0);
            });

        $monthlyExpenses = Expense::whereMonth('expense_date', $currentMonth)
            ->whereYear('expense_date', $currentYear)
            ->whereNotIn('expense_category_id', $restockCategoryIds)
            ->sum('amount');

        $monthlyNetProfit = $monthlyRevenue - $monthlyCogs - $monthlyExpenses;

        // ── Payment Split (Today) ───────────────────────────────
        $paymentSplit = Transaction::whereDate('created_at', $today)
            ->where('order_status', 'Sudah')
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->pluck('total', 'payment_method')
            ->toArray();

        // ── Sales by Category (Monthly) ─────────────────────────
        $salesByCategory = TransactionItem::select('categories.name', DB::raw('SUM(transaction_items.subtotal) as total'))
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereMonth('transactions.created_at', $currentMonth)
            ->whereYear('transactions.created_at', $currentYear)
            ->where('transactions.order_status', 'Sudah')
            ->groupBy('categories.name')
            ->get();

        // ── Hourly Sales Pattern (Today) ────────────────────────
        $hourlySales = Transaction::whereDate('created_at', $today)
            ->where('order_status', 'Sudah')
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();
        
        $hourlyPattern = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyPattern[] = $hourlySales[$i] ?? 0;
        }

        // ── Recent Transactions ─────────────────────────────────
        $recentTransactions = Transaction::with('user')
            ->latest()
            ->limit(5)
            ->get();

        // ── Best Selling Products (Current Month, Top 5) ─────────
        $bestSellers = TransactionItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_sold'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->whereHas('transaction', function ($q) use ($currentMonth, $currentYear) {
                $q->whereMonth('created_at', $currentMonth)
                  ->whereYear('created_at', $currentYear)
                  ->where('order_status', 'Sudah');
            })
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->with('product:id,name,selling_price,image_path')
            ->get();

        // ── Low Stock Raw Materials ──────────────────────────────
        $lowStockMaterials = RawMaterial::whereColumn('stock', '<=', 'low_stock_threshold')
            ->orderBy('stock', 'asc')
            ->get(['id', 'name', 'stock', 'sku', 'unit', 'low_stock_threshold']);

        // ── Revenue vs Expenses Chart Data (Last 7 Days) ─────────
        $chartLabels = [];
        $revenueChartData = [];
        $expenseChartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('D, d');
            
            $revenueChartData[] = (float) Transaction::whereDate('created_at', $date)
                ->where('order_status', 'Sudah')
                ->sum('total_amount');
            
            $expenseChartData[] = (float) Expense::whereDate('expense_date', $date)
                ->sum('amount');
        }

        return view('dashboard', compact(
            'todayRevenue', 'totalOrdersToday', 'todayNetProfit', 'todayExpenses', 'pendingOrdersToday',
            'monthlyRevenue', 'monthlyOrdersCount', 'monthlyNetProfit', 'monthlyExpenses', 'averageOrderValue',
            'paymentSplit', 'salesByCategory', 'hourlyPattern', 'recentTransactions',
            'bestSellers', 'lowStockMaterials', 'chartLabels', 'revenueChartData', 'expenseChartData'
        ));
    }
}
