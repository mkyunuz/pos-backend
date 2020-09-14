<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Products;
class Categories extends Model
{
    protected $table="categories";

    public function products(){
    	$this->hasMany(Products::class, "category", "category_id");
    }
}
