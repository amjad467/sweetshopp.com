<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'opening_balance',
        'credit_limit',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'credit_limit'    => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    /* ---------- Relations ---------- */

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function debtTransactions()
    {
        return $this->hasMany(DebtTransaction::class);
    }

    /* ---------- Accessors ---------- */

    /**
     * ئەم accessor‌ە باڵانسی ئێستای کڕیار ئەژمێرێت.
     *
     * گرنگ: $this->debtTransactions (بێ براکێت) بەکاردەهێنرێت
     * بۆ ئەوەی eager-loaded data بەکاربهێنرێت و N+1 query نەبێت.
     *
     * - debt     → زیادکردن (قەرزی نوێ)
     * - payment  → کەمکردنەوە (پارەدان)
     * - adjustment → زیاد/کەم (گەڕاندنەوە یان ڕاستکردنەوە)
     */
    public function getBalanceAttribute(): float
    {
        $transactions = $this->debtTransactions;

        $debts       = $transactions->where('type', 'debt')->sum('amount');
        $payments    = $transactions->where('type', 'payment')->sum('amount');
        $adjustments = $transactions->where('type', 'adjustment')->sum('amount');

        return max(0, (float) $this->opening_balance + (float) $debts - (float) $payments + (float) $adjustments);
    }
}
