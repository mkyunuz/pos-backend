<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\PurchaseOrder;
use App\Units;
use App\Users;
use App\Products;
class PurchaseDetail extends Model
{
    protected $table="purchase_detail";
    protected $fillable = [
    	"purchase_id", 
    	"product_code", 
        "qty", 
        "price",
        "unit_id", 
        "discount", 
        "subtotal", 
        "taxs", 
        "user_id", 
    	
    ];
    
    public function po(){
    	return $this->hasOne(PurchaseOrder::class, "purchase_id", "id");
    }
    public function unit(){
        return $this->hasOne(PurchaseOrder::class, "unit_id", "id");
    }
    public function product(){
        return $this->belongsTo(Products::class, "product_code", "product_code");
    }
    public function user(){
        return $this->hasOne(Users::class, "user_id", "id");
    }

}
