<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ProductsTrait;

class StockCard extends Model
{
	use ProductsTrait;
    protected $table = "stock_cards";
    protected $fillable = [
    	"warehouse_id", 
    	"product_id", 
    	"type" , 
    	"type", 
    	"qty", 
    	"stock", 
    	"transaction_date", 
    	"transaction_time",
    	"stockable_id", 
    	"stockable_type", 
    	"user_id",
	];

	public function stockable(){
		return $this->morphTo();
	}	
}
