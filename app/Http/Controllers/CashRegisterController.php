<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashMovement;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashRegisterController extends Controller
{
    /**
     * Display the shift management page.
     */
    public function index()
    {
        $activeShift = CashRegister::where('status', 'open')
            ->first();
            
        $summary = null;
        if ($activeShift) {
            $summary = $this->calculateShiftTotals($activeShift);
            $activeShift->load(['cashMovements.user']);
        }

        return view('pos.shift', compact('activeShift', 'summary'));
    }

    /**
     * Get active shift for the current user.
     */
    public function getActiveShift()
    {
        $shift = CashRegister::where('status', 'open')
            ->first();

        return response()->json([
            'success' => true,
            'shift' => $shift
        ]);
    }

    /**
     * Open a new shift.
     */
    public function openShift(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0'
        ]);

        // Check if there's already an open shift
        $existing = CashRegister::where('status', 'open')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active shift.'
            ], 422);
        }

        $shift = CashRegister::create([
            'user_id' => auth()->id(),
            'opening_balance' => $request->opening_balance,
            'status' => 'open',
            'opened_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shift opened successfully.',
            'shift' => $shift
        ]);
    }

    /**
     * Get real-time summary for the active shift.
     */
    public function getSummary()
    {
        $shift = CashRegister::where('status', 'open')
            ->first();

        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'No active shift found.'], 404);
        }

        $summary = $this->calculateShiftTotals($shift);

        return response()->json([
            'success' => true,
            'summary' => $summary
        ]);
    }

    /**
     * Add a cash movement (In/Out) to the active shift.
     */
    public function addCashMovement(Request $request)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255'
        ]);

        $shift = CashRegister::where('status', 'open')
            ->first();

        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'No active shift found.'], 403);
        }

        $movement = CashMovement::create([
            'cash_register_id' => $shift->id,
            'user_id' => auth()->id(),
            'type' => $request->type,
            'amount' => $request->amount,
            'reason' => $request->reason
        ]);

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->type) . ' movement recorded.',
            'movement' => $movement
        ]);
    }

    /**
     * Display the shift history list.
     */
    public function history()
    {
        $shifts = CashRegister::with('user')
            ->where('status', 'closed')
            ->latest('closed_at')
            ->paginate(10);

        // Calculate aggregates for 4 stats cards
        $stats = CashRegister::where('status', 'closed')
            ->select([
                DB::raw('COUNT(*) as total_shifts'),
                DB::raw('SUM(total_cash_sales) as total_cash'),
                DB::raw('SUM(total_qris_sales) as total_qris'),
                DB::raw('SUM(difference) as total_diff'),
            ])
            ->first();

        return view('pos.shift-history', compact('shifts', 'stats'));
    }

    /**
     * Display a specific shift details.
     */
    public function show(CashRegister $shift)
    {
        $summary = $this->calculateShiftTotals($shift);
        
        // Load related detail items for the shift view
        $transactions = Transaction::with('user')
            ->where('cash_register_id', $shift->id)
            ->latest()
            ->get();
            
        $movements = CashMovement::with('user')
            ->where('cash_register_id', $shift->id)
            ->latest()
            ->get();

        return view('pos.shift-details', compact('shift', 'summary', 'transactions', 'movements'));
    }

    /**
     * Close the active shift.
     */
    public function closeShift(Request $request)
    {
        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $shift = CashRegister::where('status', 'open')
            ->first();

        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'No active shift found.'], 404);
        }

        $summary = $this->calculateShiftTotals($shift);
        
        $difference = $request->closing_balance - $summary['expected_balance'];

        // If there's a difference, notes are recommended/required by business logic (handled in UI usually, but let's be safe)
        if ($difference != 0 && empty($request->notes)) {
            return response()->json([
                'success' => false,
                'message' => 'Notes are required when there is a cash difference.'
            ], 422);
        }

        $shift->update([
            'closing_balance' => $request->closing_balance,
            'expected_balance' => $summary['expected_balance'],
            'total_cash_sales' => $summary['total_cash_sales'],
            'total_qris_sales' => $summary['total_qris_sales'],
            'difference' => $difference,
            'notes' => $request->notes,
            'status' => 'closed',
            'closed_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shift closed successfully.',
            'shift' => $shift
        ]);
    }

    /**
     * Internal helper to calculate dynamic totals for a shift.
     */
    private function calculateShiftTotals($shift)
    {
        // 1. Calculate Sales
        $sales = Transaction::where('cash_register_id', $shift->id)
            ->where('payment_status', 'Paid') // Only paid ones are in the drawer
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->pluck('total', 'payment_method');

        $cashSales = (float) ($sales['Cash'] ?? 0);
        $qrisSales = (float) ($sales['QRIS'] ?? 0);

        // 2. Calculate Cash Movements
        $movements = CashMovement::where('cash_register_id', $shift->id)
            ->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->get()
            ->pluck('total', 'type');

        $cashIn = (float) ($movements['in'] ?? 0);
        $cashOut = (float) ($movements['out'] ?? 0);

        // 3. Expected Balance (Physics Cash in Drawer)
        // formula: Opening + Cash Sales + Cash In - Cash Out
        $expected = $shift->opening_balance + $cashSales + $cashIn - $cashOut;

        return [
            'opening_balance' => (float) $shift->opening_balance,
            'total_cash_sales' => $cashSales,
            'total_qris_sales' => $qrisSales,
            'total_cash_in' => $cashIn,
            'total_cash_out' => $cashOut,
            'expected_balance' => $expected
        ];
    }
}
