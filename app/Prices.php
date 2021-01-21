<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Products;
class Prices extends Model
{
    protected $table="prices";
    protected $fillable = ["product", "price_group", "unit", "qty", "price" ,"status"];

    public function product(){
    	$this->brlongsTo(Products::class, "id", "product");
    }
    public function basePrice(){
    	return $this->belongsTo(Prices::class, "product", "product")->orderBy("qty", "desc");
    }
}
