<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Products;
use App\Units;
class ProductHasUnit extends Model
{
	use SoftDeletes;
    protected $table="product_has_unit";
    protected $fillable = ['product_id', "unit_id", "qty", "barcode"];
    public function product(){
    	return $this->belongsTo(Products::class, "product_code", "product_id");
    }

    public function unit(){
    	return $this->belongsTo(Units::class, "unit_id", "id");
    }
}
