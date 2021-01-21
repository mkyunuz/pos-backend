<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;
use App\Models\ProductHasWarehouse;
use App\Models\ProductHasUnit;
use App\Models\Returns;
use App\Models\ReturnDetail;
use App\Products;
use App\Traits\PosTrait;
class TransactionDetail extends Model
{
    use PosTrait;

    protected $table="transaction_detail";
    protected $fillable=[
    	"transaction_id", 
    	"product_id", 
    	"qty", 
    	"unit_id", 
        "hpp",
    	"price", 
    	"discount", 
    	"subtotal", 
    	"ppn", 
    	"user_id"
    ];

    public function returns(){
        return $this->hasManyThrough(
            ReturnDetail::class, 
            Returns::class, 
            "transaction_id", 
            "return_id", 
            "transaction_id", 
            "id"
        );
    }
}
