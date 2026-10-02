<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'barcode',
        'description',
        'image',
        'purchase_price_per_kg',
        'selling_price_per_kg',
        'minimum_stock_grams',
        'current_stock_grams',
        'unit_type',
        'is_active',
    ];

    protected $casts = [
        'purchase_price_per_kg' => 'decimal:2',
        'selling_price_per_kg'  => 'decimal:2',
        'minimum_stock_grams'   => 'integer',
        'current_stock_grams'   => 'integer',
        'is_active'             => 'boolean',
    ];

    /* ---------- Relations ---------- */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /* ---------- Helpers ---------- */

    public function pricePerGram(): float
    {
        return (float) $this->selling_price_per_kg / 1000;
    }

    public function getStockKgAttribute(): float
    {
        return $this->current_stock_grams / 1000;
    }
}
