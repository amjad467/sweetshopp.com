<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtTransaction extends Model
{
    protected $fillable = [
        'customer_id',
        'sale_id',
        'payment_id',
        'type',
        'amount',
        'description',
        'user_id',
        'transaction_date',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    /* ---------- Relations ---------- */

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
