<?php

namespace App\Services;

use App\Models\{Sale, SaleItem, Product, Payment, DebtTransaction, StockMovement, Customer};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function create(array $data, int $userId): Sale
    {
        return DB::transaction(function () use ($data, $userId) {
            $subtotal = 0.0;
            $items = [];

            foreach ($data['items'] as $line) {
                $p = Product::whereKey($line['product_id'])->lockForUpdate()->firstOrFail();
                abort_if(! $p->is_active, 422, 'بەرهەم چالاک نییە.');

                $grams = (int) $line['quantity_grams'];
                $quantity = (float) $line['quantity'];

                // بۆ piece، quantity_grams وەک ژمارەی دانە بەکاردێت بۆ ئەوەی stock ledger یەک بنەما هەبێت.
                $stockRequired = $p->unit_type === 'piece' ? (int) ceil($quantity) : $grams;
                if ($p->current_stock_grams < $stockRequired) {
                    throw ValidationException::withMessages(['items' => 'کۆگای ' . $p->name . ' بەشی پێویست نییە.']);
                }

                $unitPrice = $p->unit_type === 'piece'
                    ? (float) $p->selling_price_per_kg
                    : (float) $p->selling_price_per_kg / 1000;
                $lineTotal = round($quantity * $unitPrice, 2);
                $subtotal += $lineTotal;
                $items[] = [$p, $line, $stockRequired, $unitPrice, $lineTotal];
            }

            $discount = round((float) ($data['discount'] ?? 0), 2);
            if ($discount > $subtotal) {
                throw ValidationException::withMessages(['discount' => 'داشکاندن ناتوانێت لە کۆی فرۆشتن زیاتر بێت.']);
            }

            $total = round($subtotal - $discount, 2);
            $paid = round((float) ($data['paid_amount'] ?? 0), 2);
            if ($paid > $total) {
                throw ValidationException::withMessages(['paid_amount' => 'بڕی پارەی دراو ناتوانێت لە کۆی فرۆشتن زیاتر بێت.']);
            }

            $debt = round($total - $paid, 2);
            $customer = null;
            if ($debt > 0) {
                if (empty($data['customer_id'])) {
                    throw ValidationException::withMessages(['customer_id' => 'بۆ فرۆشتنی قەرزدار دەبێت کڕیار هەڵبژێردرێت.']);
                }
                $customer = Customer::whereKey($data['customer_id'])->lockForUpdate()->firstOrFail();
                if (! $customer->is_active) {
                    throw ValidationException::withMessages(['customer_id' => 'ئەم کڕیارە چالاک نییە.']);
                }
                $newBalance = $customer->balance + $debt;
                if ((float) $customer->credit_limit > 0 && $newBalance > (float) $customer->credit_limit) {
                    throw ValidationException::withMessages(['customer_id' => 'سنووری قەرزی کڕیار پڕبووەتەوە.']);
                }
            } elseif (! empty($data['customer_id'])) {
                $customer = Customer::whereKey($data['customer_id'])->lockForUpdate()->first();
                if ($customer && ! $customer->is_active) {
                    throw ValidationException::withMessages(['customer_id' => 'ئەم کڕیارە چالاک نییە.']);
                }
            }

            $sale = Sale::create([
                'invoice_number' => 'TMP-' . bin2hex(random_bytes(8)),
                'customer_id' => $customer?->id,
                'user_id' => $userId,
                'sale_date' => now(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid_amount' => $paid,
                'debt_amount' => $debt,
                'payment_status' => $debt <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'debt'),
                'notes' => $data['notes'] ?? null,
            ]);
            $sale->update(['invoice_number' => 'INV-' . str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT)]);

            foreach ($items as [$p, $line, $stockRequired, $unitPrice, $lineTotal]) {
                $cost = $p->unit_type === 'piece'
                    ? (float) $p->purchase_price_per_kg * (float) $line['quantity']
                    : ((float) $p->purchase_price_per_kg / 1000) * (int) $line['quantity_grams'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $p->id,
                    'quantity' => $line['quantity'],
                    'quantity_grams' => $stockRequired,
                    'unit_price' => $unitPrice,
                    'cost_price' => round($cost, 2),
                    'discount' => 0,
                    'subtotal' => $lineTotal,
                    'profit' => round($lineTotal - $cost, 2),
                ]);

                $p->decrement('current_stock_grams', $stockRequired);
                StockMovement::create([
                    'product_id' => $p->id,
                    'type' => 'sale',
                    'quantity_grams' => -$stockRequired,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'user_id' => $userId,
                ]);
            }

            if ($paid > 0) {
                $payment = Payment::create([
                    'customer_id' => $sale->customer_id,
                    'sale_id' => $sale->id,
                    'user_id' => $userId,
                    'amount' => $paid,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'payment_date' => now(),
                ]);
                if ($sale->customer_id && $debt > 0) {
                    DebtTransaction::create([
                        'customer_id' => $sale->customer_id,
                        'sale_id' => $sale->id,
                        'payment_id' => $payment->id,
                        'type' => 'payment',
                        'amount' => $paid,
                        'description' => 'پارەدانی بەشی فرۆشتن',
                        'user_id' => $userId,
                        'transaction_date' => now(),
                    ]);
                }
            }

            if ($debt > 0) {
                DebtTransaction::create([
                    'customer_id' => $sale->customer_id,
                    'sale_id' => $sale->id,
                    'type' => 'debt',
                    'amount' => $debt,
                    'description' => 'قەرزی فرۆشتن',
                    'user_id' => $userId,
                    'transaction_date' => now(),
                ]);
            }

            return $sale->load('items.product', 'customer', 'payments');
        });
    }
}
