<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'quantity_grams',
        'unit_price',
        'cost_price',
        'discount',
        'subtotal',
        'profit',
    ];

    protected $casts = [
        'quantity'       => 'decimal:3',
        'quantity_grams' => 'integer',
        'unit_price'     => 'decimal:2',
        'cost_price'     => 'decimal:2',
        'discount'       => 'decimal:2',
        'subtotal'       => 'decimal:2',
        'profit'         => 'decimal:2',
    ];

    /* ---------- Relations ---------- */

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
