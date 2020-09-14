<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\Products;
class Prices extends Model
{
    protected $table="prices";
    protected $fillable = ["product", "price_group", "unit", "qty", "price" ,"status"];

    public function product(){
    	$this->brlongsTo(Products::class, "product_code", "product");
    }
}
