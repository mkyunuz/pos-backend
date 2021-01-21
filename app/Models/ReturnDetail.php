<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnDetail extends Model
{
    protected $table = "return_details";
    protected $fillable=[
    	"return_id", 
    	"product_id", 
    	"qty",
    	"hpp",
    	"amount"
    ];


    public function _return(){
    	return $this->belongsTo(Returns::class, "return_id", "id");
    }
}
