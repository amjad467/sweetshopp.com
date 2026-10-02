<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; class ReturnItem extends Model { protected $fillable=['return_id','sale_item_id','product_id','quantity_grams','amount']; protected $casts=['quantity_grams'=>'integer','amount'=>'decimal:2']; public function saleReturn(){return $this->belongsTo(SaleReturn::class,'return_id');} public function product(){return $this->belongsTo(Product::class);} }
