<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Suppliers;
use App\Users;
use App\Units;
use App\Warehouses;
use App\Models\PurchaseDetail;
use App\Models\PaymentHistory;

class Purchase extends Model
{
    protected $table="purchase";
    protected $fillable = [
    	"purchase_number", 
    	"po_number", 
    	"supplier_id", 
    	"payment_method", 
    	"shipping_costs", 
    	"transaction_date",
    	"remark",
    	"company",
    	"warehouse_id",
    	"supplier_address",
    	"phone_number",
    	"shipping_to",
    	"user_id",
    	"total_ppn",
    	"grand_total",
    	"gt_after_ppn",
    	"due_date",
    	"close",
    ];
    public static function rules($request){
	    $rules = [
	    	"purchase_number" => "required",
	    	"warehouses" => "required",
	    	// "warehouse_address" => "required",
	    	// "contact_person" => "required",
	    	"supplier" => "required",
	    	"grand_total" => "required",
	    	"total_ppn" => "required",
	    	"payment_method" => "required",
	    ];
	    return $rules;
    }
    public function supplier(){
    	return $this->hasOne(Suppliers::class, "supplier_id", "id");
    }
    public function warehouse(){
    	return $this->hasOne(Warehouses::class, "id", "warehouse_id");
    }
    public function orders(){
    	return $this->hasMany(PurchaseDetail::class, "purchase_id", "id");

    }

    public function payments(){
    	return $this->morphMany(PaymentHistory::class, "payment");
    }
    
    public function saveOrders(
    	$purchase_id,
    	$detail_id = [], 
    	$product_codes =[], 
    	$qtys = [],
    	$prices = [],
    	$units = [],
    	$discount = [],
    	$subtotals = [],
    	$ppn = [],
    	$user_id,
    	$removed_products = []
    ){
    	$saveOrders = [];
    	foreach ($removed_products as $rmIndex => $rmValue) PurchaseDetail::where(DB::raw("SHA1(id)"), $rmValue)->delete();
    	foreach ($detail_id as $pgIdx => $pgValue) {
    		PurchaseDetail::where(DB::raw("SHA1(id)"), $detail_id[$pgIdx])
    			->update([
	    			"qty" => $qtys[$pgIdx],
	    			"price" => $prices[$pgIdx],
	    			"unit_id" => Units::where(DB::raw("SHA1(id)"), $units[$pgIdx])->get()->first()->id,
	    			"discount" => $discount[$pgIdx],
	    			"subtotal" => $subtotals[$pgIdx],
	    			"taxs" => $ppn[$pgIdx],
	    			"user_id" => $user_id,
    			]);
    			Log::info($detail_id[$pgIdx]);
    	}

    	foreach ($product_codes as $key => $value) {
    		if($detail_id[$key] == "undefined" || !$detail_id[$key]){
	    		$saveOrders[] = [
	    			"purchase_id" => $purchase_id,
	    			"product_code" => $value,
	    			"qty" => $qtys[$key],
	    			"price" => $prices[$key],
	    			"unit_id" => Units::where(DB::raw("SHA1(id)"), $units[$key])->get()->first()->id,
	    			"discount" => $discount[$key],
	    			"subtotal" => $subtotals[$key],
	    			"taxs" => $ppn[$key],
	    			"user_id" => $user_id,
	    		];
    		}
    	}
    	$this->orders()->createMany($saveOrders);
    }
    public function user(){
        return $this->hasOne(Users::class, "user_id", "id");
    }    
}
