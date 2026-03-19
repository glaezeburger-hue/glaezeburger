<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class KdsController extends Controller
{
    /**
     * Display the Kitchen Display System (KDS) view.
     */
    public function index()
    {
        return view('kds.index');
    }

    /**
     * Get all pending orders ('order_status' = 'Belum').
     */
    public function getPendingOrders()
    {
        $orders = Transaction::with('items.product')
            ->where('order_status', '!=', 'Sudah')
            ->orderBy('created_at', 'asc') // FIFO
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                    'payment_status' => $transaction->payment_status,
                    'created_at' => $transaction->created_at->toIso8601String(),
                    'items' => $transaction->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->product->name ?? 'Unknown',
                            'quantity' => $item->quantity,
                        ];
                    }),
                ];
            });

        return response()->json($orders);
    }

    /**
     * Mark a transaction as completed ('order_status' = 'Sudah').
     */
    public function markAsComplete(Transaction $transaction)
    {
        $transaction->update(['order_status' => 'Sudah']);

        return response()->json([
            'success' => true,
            'message' => 'Order marked as complete.'
        ]);
    }
}
