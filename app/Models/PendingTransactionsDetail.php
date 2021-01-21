<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PendingTransactions;
use App\Models\ProductHasUnit;
use App\Products;
use App\Units;
class PendingTransactionsDetail extends Model
{
    protected $table="pending_transactions_details";
    protected $fillable = [ 
    	"pending_transaction_id", 
    	"qty", 
    	"unit_id",
    	"ppn",
    	"price", 
    	"discount", 
    	"subtotal", 
    	"product_id"
    ];

    public function details(){
    	return $this->belongsTo(PendingTransactions::class, "pending_transaction_id", "id");
    }
    public function product(){
    	return $this->belongsTo(Products::class, "product_id", "id");
    }

    public function hasUnit(){
    	return $this->hasOne(ProductHasUnit::class, "product_id", "product_id")->where("unit_id", $this->unit_id);
    }

    public function unit(){
    	return $this->belongsTo(Units::class, "unit_id", "id");
    }
}
