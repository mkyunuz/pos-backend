<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\ProductHasUnit;
use App\Models\ReturnDetail;
use App\Models\ProductHasWarehouse;
use App\Models\TransactionDetail;
use App\Products;
use App\Traits\PosTrait;

class Returns extends Model
{
	use PosTrait;
    protected $table="returns";
    protected $fillable=[
    	"transaction_id", 
    	"amount", 
    	"return_date", 
    	"user_id"
    ];


    public function detail(){
    	return $this->hasMany(ReturnDetail::class, "return_id", "id");
    }

 
    public function saveReturn(
        $user_id, 
        $transaction_id, 
        $return_id,
        $warehouse_id,
        $product_ids = [], 
        $qtys=[],
        $conversions = [],
        $return_amounts = [],
        $transaction_date,
        $transaction_time
    ){
        $returns = [];
        foreach ($product_ids as $idx => $product_id) {
            $qty = $qtys[$idx];
            $conversion = $conversions[$idx];
            if($qty > 0){     
                $stock_in_warehouse = ProductHasWarehouse::where("warehouse_id", $warehouse_id)
                        ->where(DB::raw("sha1(product_id)"), $product_id)
                        ->get()->first();
                if($stock_in_warehouse){
	                $hppData = TransactionDetail::where([
	                	"transaction_id" => $transaction_id,
	                	"product_id" => $stock_in_warehouse->product_id
	                ])->get()->first();

                	$hpp = $hppData->hpp;
                	$real_qty = $conversion * $qty;
	                $returns[] = [
	                    "return_id" => $return_id,
	                    "product_id" => $stock_in_warehouse->product_id,
	                    "qty" => $real_qty,
	                    "hpp" => $hpp,
	                    "amount" => $return_amounts[$idx],
	                    "user_id" => $user_id,
	                ];
	                $currentStock = ($stock_in_warehouse) ? $stock_in_warehouse->stock : 0;
	                $stock_in_warehouse->increment("stock", $real_qty);

	                $outStock = -1 * abs($qty);
	                StockCard::create([
	                    "warehouse_id" => $warehouse_id,
	                    "product_id" => $stock_in_warehouse->product_id,
	                    "type" => "in",
	                    "qty" => $real_qty,
	                    "stock" => $currentStock + $real_qty,
	                    "transaction_date" => $transaction_date,
	                    "transaction_time" => $transaction_time,
	                ]);
                }
            }
        }
        $this->detail()->createMany($returns);
    }
}
