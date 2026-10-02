<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; class StockMovement extends Model { protected $fillable=['product_id','type','quantity_grams','reference_type','reference_id','notes','user_id']; protected $casts=['quantity_grams'=>'integer']; public function product(){return $this->belongsTo(Product::class);} public function user(){return $this->belongsTo(User::class);} }
