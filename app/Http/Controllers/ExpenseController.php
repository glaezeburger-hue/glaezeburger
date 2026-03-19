<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseRestockItem;
use App\Models\RawMaterial;
use App\Models\Wastage;
use App\Models\CashRegister;
use App\Models\CashMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses.
     */
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'user', 'restockItems.rawMaterial']);

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $expenses = $query->latest('expense_date')->latest()->paginate(15)->withQueryString();
        $categories = ExpenseCategory::orderBy('name')->get();
        $rawMaterials = RawMaterial::orderBy('name')->get();

        // Summary calculations
        $totalPeriod = $query->sum('amount');
        
        $currentMonth = now()->format('Y-m');
        $totalMonth = Expense::where('expense_date', 'like', $currentMonth . '%')->sum('amount');
        
        $restockCategoryIds = ExpenseCategory::where('is_restock', true)->pluck('id');
        $restockCurrentMonth = Expense::whereIn('expense_category_id', $restockCategoryIds)
                                      ->where('expense_date', 'like', $currentMonth . '%')
                                      ->sum('amount');

        return view('expenses.index', compact(
            'expenses', 'categories', 'rawMaterials', 
            'totalPeriod', 'totalMonth', 'restockCurrentMonth'
        ));
    }

    /**
     * Store a newly created expense.
     */
    public function store(Request $request)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:Cash,Transfer,QRIS',
            'receipt' => 'nullable|image|max:2048',
            'notes' => 'nullable|string',
            
            // Restock validation (made nullable to prevent conflicts with non-restock expenses)
            'restock_items' => 'nullable|array',
            'restock_items.*.raw_material_id' => 'nullable|exists:raw_materials,id',
            'restock_items.*.quantity' => 'nullable|numeric|min:0.01',
            'restock_items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->except(['receipt', 'restock_items']);
            $data['user_id'] = auth()->id();

            if ($request->hasFile('receipt')) {
                $data['receipt_image'] = $request->file('receipt')->store('receipts', 'public');
            }

            $expense = Expense::create($data);

            // Automation: If payment is Cash, record it in Cash Movement (Shift)
            if ($expense->payment_method === 'Cash') {
                $activeShift = CashRegister::where('status', 'open')->first();
                if ($activeShift) {
                    CashMovement::create([
                        'cash_register_id' => $activeShift->id,
                        'user_id' => auth()->id(),
                        'type' => 'out',
                        'amount' => $expense->amount,
                        'reason' => 'Pengeluaran: ' . $expense->description
                    ]);
                }
            }

            $category = ExpenseCategory::find($request->expense_category_id);
            if ($category && $category->is_restock && $request->has('restock_items')) {
                foreach ($request->restock_items as $item) {
                    ExpenseRestockItem::create([
                        'expense_id' => $expense->id,
                        'raw_material_id' => $item['raw_material_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                    ]);

                    // Increment stock
                    RawMaterial::where('id', $item['raw_material_id'])->increment('stock', $item['quantity']);
                }
            }

            DB::commit();
            return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error recording expense: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(Expense $expense)
    {
        try {
            DB::beginTransaction();

            // Rollback stock if it was a restock
            if ($expense->isRestock()) {
                foreach ($expense->restockItems as $item) {
                    RawMaterial::where('id', $item->raw_material_id)->decrement('stock', $item->quantity);
                }
            }

            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }

            $expense->delete();

            DB::commit();
            return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting expense.');
        }
    }

    /**
     * Display a listing of wastages.
     */
    public function wastageIndex(Request $request)
    {
        $query = Wastage::with(['rawMaterial', 'user']);

        if ($request->filled('date_from')) {
            $query->whereDate('wastage_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('wastage_date', '<=', $request->date_to);
        }

        $wastages = $query->latest('wastage_date')->latest()->paginate(15)->withQueryString();
        $rawMaterials = RawMaterial::orderBy('name')->get();

        $currentMonth = now()->format('Y-m');
        $wastageCurrentMonth = Wastage::where('wastage_date', 'like', $currentMonth . '%')->count();

        return view('expenses.wastages', compact('wastages', 'rawMaterials', 'wastageCurrentMonth'));
    }

    /**
     * Store a newly created wastage.
     */
    public function storeWastage(Request $request)
    {
        $request->validate([
            'raw_material_id' => 'required|exists:raw_materials,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|in:expired,damaged,spillage,other',
            'description' => 'nullable|string|max:255',
            'wastage_date' => 'required|date',
        ]);

        try {
            $data = $request->all();
            $data['user_id'] = auth()->id();

            // Boot event handles stock decrement
            Wastage::create($data);

            return redirect()->route('wastages.index')->with('success', 'Wastage recorded successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error recording wastage: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified wastage.
     */
    public function destroyWastage(Wastage $wastage)
    {
        // Boot event handles stock increment
        $wastage->delete();
        return redirect()->route('wastages.index')->with('success', 'Wastage deleted successfully.');
    }

    /**
     * Return JSON for expense categories
     */
    public function categories()
    {
        return response()->json(ExpenseCategory::orderBy('name')->get());
    }

    /**
     * Store Category via AJAX
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:expense_categories,name',
            'icon' => 'nullable|string',
            'is_restock' => 'boolean'
        ]);

        $category = ExpenseCategory::create($request->all());
        return response()->json([
            'success' => true,
            'category' => $category,
            'message' => 'Category added successfully.'
        ]);
    }
}
