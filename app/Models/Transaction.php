<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransactionDetail;
use App\Models\PaymentHistory;
use App\AccountReceivable;
use App\Repositories\ProductRepo;
use App\Models\ProductHasWarehouse;
use App\Models\Returns;
use App\Models\ReturnDetail;
use App\Models\StockCard;
use App\Warehouses;
use Illuminate\Support\Facades\DB;
class Transaction extends Model
{
    protected $table="transactions";
    protected $fillable=["transaction_number", "total_ppn", "grand_total", "gt_after_ppn", "payment_method", "due_date", "shipping_costs", "services", "transaction_date", "remark", "customer_id", "phone_number", "shipping_to", "user_id", "close", "total_discount", "warehouse_id", "transaction_time"];

    public static $rules = [
    	"warehouse_id" => "required",
    	"change" => "required",
    	"grandTotal" => "required",
    	"payment_amount" => "required",
    	"payment_method" => "required",
    	"total_ppn" => "required",
    	// "products" => "required",
    	"service" => "required",
    	"totalPurchase" => "required",
    	"transaction_date" => "required",
    	"transaction_number" => "required",
    ];

    public $payment_id;
    public function detail(){
    	return $this->hasMany(TransactionDetail::class, "transaction_id", "id");
    }

    public function returns(){
        return $this->hasMany(Returns::class, "transaction_id", "id");
    }
    public function warehouse(){
        return $this->hasOne(Warehouses::class, "id", "warehouse_id");
    }
    public function saveItems($userId, $transaction_id, $warehouse_id, $transaction_date, $transaction_time, $data = [] ){
    	$items = [];
    	foreach ($data as $key) {
    		$qty = $key["qty"] * $key["conversion"];
            $hpp = ProductRepo::getHpp($key["product_id"]);
    		$warehouseStock = ProductHasWarehouse::where([
    			"product_id" => $key['product_id'], 
    			"warehouse_id" => $warehouse_id])->get()->first();

    		$items[] = [
    			"transaction_id" => $transaction_id,
    			"product_id" => $key["product_id"],
    			"qty" => $qty,
                "hpp" => $hpp,
    			"unit_id" => $key["unit_id"],
    			"price" => $key["price"],
    			"discount" => $key["discount"],
    			"subtotal" => $key["total"],
    			"ppn" => $key["ppn"],
    			"user_id" => $userId,
    		];

           

            $currentStock = ($warehouseStock) ? $warehouseStock->stock : 0;
            $warehouseStock->decrement("stock", $qty);
            $outStock = -1 * abs($qty);
            StockCard::create([
                "warehouse_id" => $warehouse_id,
                "product_id" => $key['product_id'],
                "type" => "out",
                "qty" => $outStock,
                "stock" => $currentStock - $qty,
                "transaction_date" => $transaction_date,
                "transaction_time" => $transaction_time,
            ]);
    	}
    	$this->detail()->createMany($items);
    }

    public function monthlySales(){
        // return $this->has
    }
    public function payments(){
    	return $this->morphMany(PaymentHistory::class, "payment");
    }
    public function paymentOne(){
    	return $this->morphOne(PaymentHistory::class, "payment")->where(DB::raw("SHA1(id)"), $this->payment_id);
    }

    public function account_receivables(){
    	return $this->morphMany(AccountReceivable::class, "account_receivables");
    }

    
}
