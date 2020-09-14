<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Suppliers;
use App\Products;
class ProductHasSuppliers extends Model
{
    use SoftDeletes;
    protected $table = "product_has_suppliers";
    protected $fillable = ["product_id", "supplier_id"];

    public function supplier(){
    	$this->belongsTo(Suppliers::class, "supplier_id", "id");
    }
    public function product(){
    	$this->belongsTo(Products::class, "product_id", "id");
    }
}
