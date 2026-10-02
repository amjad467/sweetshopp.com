<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'user_id',
        'sale_date',
        'subtotal',
        'discount',
        'total',
        'paid_amount',
        'debt_amount',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'sale_date'   => 'datetime',
        'subtotal'    => 'decimal:2',
        'discount'    => 'decimal:2',
        'total'       => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'debt_amount' => 'decimal:2',
    ];

    /* ---------- Relations ---------- */

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
