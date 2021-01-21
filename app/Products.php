<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Prices;
use App\Categories;
use App\Models\ProductHasUnit;
use App\Models\ProductHasSuppliers;
use App\Models\ProductHasWarehouse;
use App\Models\StockCard;
use App\Suppliers;
use App\Warehouses;
use App\Units;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;
class Products extends Model
{
	use SoftDeletes;
    protected $table="products";
    protected $attributes = [
        'ppn' => 0,
    ];

    public static function boot() {
        parent::boot();
       self::deleting(function($product) {
            $product->prices()->each(function($prices) {
               $prices->delete();
            });
            $product->productConversions()->each(function($conversion) {
               $conversion->delete(); 
            });
       });
    }
    public static function rules($request){
    	$updateRules =[
	    	"product_code" =>  
	    		Rule::unique('products')->where($rq = function ($query) use ($request) {
	    			$product_code = ($request->product_code) ? trim($request->product_code) :null;
					$key = ($request->key) ? trim($request->key) :null;
			    	return$query->where("product_code",$product_code)->when($key, function($query, $key){
						return $query->where(DB::raw("sha1(id)"), "!=",  $key);
			    });
			}),
	    	"product_name" => "required",
	    	"category" => "required",
	    	"unit" => "required",
	    	"purchase_price" => "required",
	    	"status" => "required",
	    	"barcode" => "required",
	    	"group" => "required",
	    	"price" => "required",
	    	"qty" => "required",
	    ];
    	    return $updateRules;
    }
    protected $fillable = ["product_name", "product_code", "category", "unit", "description", "purchase_price", "ppn", "status", "remark", "barcode"];

    public function prices($select = []){
    	$data = $this->hasMany(Prices::class, "product", "id");
    	return $data;
    }
    public function defaultPrice(){
        return $this->hasOne(Prices::class, "product", "id")->where("prices.unit", "=", $this->unit)->orderBy(DB::raw("FIELD(price_group, 'umum', 'member')"))->orderBy("qty", "DESC");
    }
    public function purchases(){
        return $this->hasMany(PurchaseDetail::class, "product_id", "id");
    }
    public function pricesUnitFilter($select = []){
    	$data = $this->hasManyThrough(Prices::class, ProductHasUnit::class, "unit_id", "unit", "id", "product_id");
    	return $data;
    }
    public function getBarcode(){
        return $this->hasOne(ProductHasUnit::class, "product_id", "id")->where("unit_id", $this->unit_id);
    }
	public function categories($select = []){
	    	return $this->belongsTo(Categories::class, "category", "category_id");
	}

    public function productConversions($select = []){
    	$data = $this->hasMany(ProductHasUnit::class, "product_id", "id")->where("default_unit", "!=", 1);
    	return $data;
    }
    public function suppliers(){
    	return $this->hasMany(ProductHasSuppliers::class, "product_id", "id");
    }

    public function saveSuppliers($product_id, $suppliers = []){
    	$this->suppliers()->forceDelete();
    	$data = [];
    	foreach ($suppliers as $key => $value) {
    		$supplier = Suppliers::where(DB::raw("sha1(id)"), $value)->get()->first();
    		if($supplier){
    			$data[] = ["product_id" => $product_id, "supplier_id" => $supplier->id];
    		}

    	}
    	$this->suppliers()->createMany($data);
    }
    public function savePrices(
    	$product_code, 
    	$price_group_id, 
    	$groups = [], 
    	$qtys = [], 
    	$units = [], 
    	$prices =[], 
    	$removed_price = []
    ){
    	$newPrice = [];
    	foreach ($removed_price as $rmIndex => $rmValue) Prices::where("id", $rmValue)->delete();
		foreach ($price_group_id as $pgIdx => $pgValue) {
			Prices::where("id", $price_group_id[$pgIdx])
				->update([
					"price_group" => $groups[$pgIdx],
					"unit" => $units[$pgIdx],
					"qty" => str_replace(",", "", $qtys[$pgIdx]),
					"price" => str_replace(",", "", $prices[$pgIdx]),
					"status" => "active",
				]);
		}

		foreach ($groups as $key => $value) {
			if($price_group_id[$key] == "undefined" || !$price_group_id[$key]){
    			$newPrice[] = [
    				"product" => $product_code,
    				"price_group" => $value,
    				"unit" => $units[$key],
    				"qty" => str_replace(",", "", $qtys[$key]),
    				"price" => str_replace(",", "", $prices[$key]),
    				"status" => "active",
    			];

			}
		}
		if($newPrice) $this->prices()->createMany($newPrice);
    }

    public function produtHasWarehouses(){
    	return $this->hasMany(ProductHasWarehouse::class, "product_id", "id");
    }
    private function findArray($products, $field, $value){
    	foreach($products as $key => $product){
            if($product[$field] == $value ) return $product;
       }
       return [];
    }

    public function updateStock($products = [], $units= [], $qtys = [], $warehouse, $transaction_date, $transaction_time){
    	foreach ($products as $key => $value) {
    		$product = Products::where(DB::raw("sha1(id)"), $value)->get()->first();
    		$qty=$qtys[$key];
    		$productId=$product->id;
    		if($product){
    			$hash_default_unit = sha1($product->unit);
    			if($hash_default_unit != $qtys[$key]){
    				$unit = ProductHasUnit::where("product_id", $product->id)->where(DB::raw("SHA1(unit_id)"), $units[$key])->get()->first();
    				$qty = ($unit) ? ($unit->qty * $qty) : $qty;
    			}
    		}
    		// $product->update(["stock" => $]);
    		if($warehouse){
    			ProductHasWarehouse::where([
    				"warehouse_id" => $warehouse,
    				"product_id" => $productId
    			])->increment("stock", $qty);
                $currentStockModel = StockCard::where(["product_id"=>$productId, "warehouse_id" => $warehouse])->orderBy("transaction_date", "desc")->get()->first();

                $currentStock = ($currentStockModel) ? $currentStockModel->qty : 0;
                StockCard::create([
                    "warehouse_id" => $warehouse,
                    "product_id" => $productId,
                    "type" => "in",
                    "qty" => $qty,
                    "stock" => $currentStock + $qty,
                    "transaction_date" => $transaction_date,
                    "transaction_time" => $transaction_time,
                ]);
    		}
    	}
    	// $this->where(DBLL)
    }
    
    

    public function saveProductHasWarehouses($productId, $warehouses = []){
		
		$data = [];
		$prevData = [];
		foreach ($warehouses as $key => $value) {
			$current_warehouse = ProductHasWarehouse::where(DB::raw("sha1(warehouse_id)"), $value)
						->where("product_id", $productId)->get()->first();
			if($current_warehouse){
				$prevData[] = [
					"warehouse_id" => $current_warehouse->warehouse_id, 
					"stock" => $current_warehouse->stock
				];

			}
		}
		$this->produtHasWarehouses()->forceDelete();
		foreach ($warehouses as $key => $value) {
			$warehouse = Warehouses::where(DB::raw("sha1(id)"), $value)->get()->first();
			LOG::info(["total" => $value."--".$productId, "prevData" => $prevData]);
			if($warehouses){
				$stock = 0;
				$search = $this->findArray($prevData, "warehouse_id", $warehouse->id);
				LOG::info(["search" => $search]);
				if($search){
					$stock = isset($search["stock"]) ? $search["stock"] : 0;
				}
				LOG::info(["stock" => $search]);
				$data[] = ["product_id" => $productId, "warehouse_id" => $warehouse->id, "stock" => $stock];
			}

		}

		$this->produtHasWarehouses()->createMany($data);
    	
    }

    public function _unit(){
    	return $this->hasOne(Units::class, "id", "unit");
    }

    public function saveDefaultUnit($unit, $id = null){
    	if($id){
    		ProductHasUnit::where([
    			"unit_id" => $id, 
    			"product_id" => $unit["product_id"] , 
    			"default_unit" => 1
    		])->update(["unit_id" => $unit["unit_id"], "barcode" => $unit["barcode"]]);
    	}else{
    		ProductHasUnit::create($unit);
    	}
    }
    public function saveProductHasUnits(
    	$productId, 
    	$conversion_id, 
    	$conversion_units = [], 
    	$conversion_qty = [], 
    	$conversion_barcode = [], 
    	$removed_conversions = []
    ){

    	$modelHasUnit = [];
		foreach ($removed_conversions as $rmC => $rmV) ProductHasUnit::where("id", $rmV)->delete();
		foreach ($conversion_id as $cI => $cV) {
			ProductHasUnit::where("id", $conversion_id[$cI])->where("default_unit", "!=", 1)
				->update([
					"unit_id" => $conversion_units[$cI],
					"qty" => str_replace(",", "", $conversion_qty[$cI]),
					"barcode" => isset($conversion_barcode) ? $conversion_barcode[$cI] : NULL,
				]);
		}
		foreach ($conversion_units as $index => $unit_id) {
			if($conversion_id[$index] == "undefined" || !$conversion_id[$index]){
    			$modelHasUnit[] = [
    				"product_id" => $productId,
    				"unit_id" => $unit_id,
    				"qty" => isset($conversion_qty) ? $conversion_qty[$index] : 0,
    				"barcode" => isset($conversion_barcode) ? $conversion_barcode[$index] : NULL,
    			];
			}
		}
		if($modelHasUnit) $this->productConversions()->createMany($modelHasUnit);
    }


}
