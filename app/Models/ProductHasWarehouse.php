<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Products;
use App\Warehouses;
use App\Models\ProductHasUnit;
class ProductHasWarehouse extends Model
{
	
	protected $table="product_has_warehouse";
    protected $fillable = ["product_id", "warehouse_id", "stock"];
    public static $rules = [
    	"product_id" => "required|number",
    	"warehouse_id" => "required|numeric",
    	"stock" => "required|numeric",
    ];

    public function product(){
    	return $this->belongsTo(Products::class, "product_id", "id");
    } 
    public function hasUnit(){
    	return $this->belongsToMany(ProductHasUnit::class, "products" ,"id", "id", "product_id", "product_id");
    } 

    public function warehouse(){
    	return $this->belongsTo(Warehouses::class, "product_id", "id");
    }
}
