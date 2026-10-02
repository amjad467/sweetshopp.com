<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; class SaleReturn extends Model { protected $table='returns'; protected $fillable=['sale_id','user_id','total_amount','reason','returned_at']; protected $casts=['total_amount'=>'decimal:2','returned_at'=>'datetime']; public function sale(){return $this->belongsTo(Sale::class);} public function items(){return $this->hasMany(ReturnItem::class);} }
