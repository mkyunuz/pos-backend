<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ProductsTrait;
use App\Warehouese;
class InitialInventory extends Model
{
	use ProductsTrait;
	
    protected $table = "initial_inventories";
    protected $fillable = ["warehouse_id", "product_id", "qty", "amount"];

    public function warhouse(){
    	return $this->belongsTo(Warehouese::class);
    }
}
