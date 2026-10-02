<?php

namespace App\Http\Controllers;

use App\Models\{Sale, SaleReturn, ReturnItem, StockMovement, DebtTransaction, Product};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnController extends Controller
{
    public function store(Request $r, Sale $sale)
    {
        $d = $r->validate([
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity_grams' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($d, $sale) {
            $sale = Sale::whereKey($sale->id)->lockForUpdate()->firstOrFail();
            $ret = SaleReturn::create([
                'sale_id' => $sale->id,
                'user_id' => auth()->id(),
                'reason' => $d['reason'] ?? null,
                'returned_at' => now(),
                'total_amount' => 0,
            ]);

            $total = 0.0;
            foreach ($d['items'] as $x) {
                $item = $sale->items()->whereKey($x['sale_item_id'])->lockForUpdate()->firstOrFail();
                $returned = (int) ReturnItem::where('sale_item_id', $item->id)->sum('quantity_grams');
                $available = max(0, (int) $item->quantity_grams - $returned);
                $grams = (int) $x['quantity_grams'];
                if ($grams > $available) {
                    throw ValidationException::withMessages(['items' => 'بڕی گەڕاندنەوە لە بڕی ماوە زیاترە.']);
                }

                $amount = $item->quantity_grams > 0 ? round((float) $item->subtotal * $grams / $item->quantity_grams, 2) : 0;
                ReturnItem::create([
                    'return_id' => $ret->id,
                    'sale_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity_grams' => $grams,
                    'amount' => $amount,
                ]);

                $p = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
                $p->increment('current_stock_grams', $grams);
                StockMovement::create([
                    'product_id' => $p->id,
                    'type' => 'return',
                    'quantity_grams' => $grams,
                    'reference_type' => 'return',
                    'reference_id' => $ret->id,
                    'user_id' => auth()->id(),
                ]);
                $total += $amount;
            }

            $ret->update(['total_amount' => round($total, 2)]);

            if ($sale->customer_id && $total > 0) {
                $customer = $sale->customer()->lockForUpdate()->first();
                $outstanding = max(0, (float) $customer->balance);
                $debtReduction = min($outstanding, $total);
                if ($debtReduction > 0) {
                    DebtTransaction::create([
                        'customer_id' => $customer->id,
                        'sale_id' => $sale->id,
                        'type' => 'adjustment',
                        'amount' => -$debtReduction,
                        'description' => 'کەمکردنەوەی قەرز بەهۆی گەڕاندنەوە',
                        'user_id' => auth()->id(),
                        'transaction_date' => now(),
                    ]);
                }
            }
        });

        return back()->with('success', 'گەڕاندنەوە تۆمارکرا.');
    }
}
