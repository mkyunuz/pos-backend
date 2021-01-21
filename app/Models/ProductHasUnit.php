<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Products;
use App\Prices;
use App\Units;
class ProductHasUnit extends Model
{
	use SoftDeletes;
    protected $table="product_has_unit";
    protected $fillable = ['product_id', "unit_id", "qty", "barcode", "default_unit"];
    
    public function product(){
    	return $this->belongsTo(Products::class, "product_id", "id");
    }

    public function unit(){
    	return $this->belongsTo(Units::class, "unit_id", "id");
    }

    public function getPrice(){
    	return $this->hasMany(Prices::class, "product", "product_id")->where("unit", $this->unit_id);
    }
}
