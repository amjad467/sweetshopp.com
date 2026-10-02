<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'address', 'opening_balance', 'credit_limit', 'notes', 'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

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

    /**
     * Current balance is calculated from each sale's remaining debt_amount,
     * opening balance, later debt payments (payments not tied to a sale),
     * and return/adjustment transactions. This also handles old partial sales
     * whose ledger accidentally recorded only the residual debt as a debt entry
     * and the initial payment as another entry.
     */
    public function getBalanceAttribute(): float
    {
        $salesDebt = (float) $this->sales()->sum('debt_amount');
        $laterPayments = (float) $this->payments()->whereNull('sale_id')->sum('amount');
        $adjustments = (float) $this->debtTransactions()->where('type', 'adjustment')->sum('amount');

        return max(0, round((float) $this->opening_balance + $salesDebt - $laterPayments + $adjustments, 2));
    }
}
