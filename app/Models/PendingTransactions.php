<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PendingTransactionsDetail;
class PendingTransactions extends Model
{
    protected $table="pending_transactions"; 
    protected $fillable = ["warehouse_id", "user_id", "transaction_date"];
   	public static $rules = [
   		"warehouse_id" => "required",
   		"products" => "required",
   		"transaction_date" => "required",
   	];

   	public static function boot() {
   	   parent::boot();
   	   parent::boot();
   	   self::deleting(function($product) {
   	        $product->detail()->each(function($detail) {
   	           $detail->delete();
   	        });
   	   });
   	}
    public function detail(){
    	return $this->hasMany(PendingTransactionsDetail::class, "pending_transaction_id", "id");
    }

    public function saveItems($userId, $pending_transaction_id, $warehouse_id, $data = [] ){
    	$items = [];
    	foreach ($data as $key) {
    		$qty = $key["qty"] * $key["conversion"];
    		$items[] = [
    			"pending_transaction_id" => $pending_transaction_id,
    			"product_id" => $key["product_id"],
    			"qty" => $qty,
    			"unit_id" => $key["unit_id"],
    			"price" => $key["price"],
    			"discount" => $key["discount"],
    			"subtotal" => $key["total"],
    			"ppn" => $key["ppn"]
    		];
    	}
    	$this->detail()->createMany($items);
    }
}
