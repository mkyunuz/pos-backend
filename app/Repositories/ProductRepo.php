<?php

namespace App\Repositories;
use App\Products;
use App\Models\ProductHasWarehouse;
use App\Models\ProductHasUnit;
use App\Models\TransactionDetail;
use App\Models\ReturnDetail;
use App\Helpers\AppHelpers;
use Illuminate\Support\Facades\DB;
class ProductRepo{

	public static function filter($search = null, $query = [], $sort = [], $limit = 10, $offset = 0, $no_pagination = false){
		$model = Products::where("id", "!=", "");
    	if(is_array($query)){

    	}

    	if(is_array($query) && count($query) > 0){
    		$allow_filter = ["product_name"];
    		foreach ($query as $key => $value) {
    			if(in_array($key, $allow_filter)){
    				$model->where($key, "like", "%".$value."%");
    			}
    		}
    	}
    	if(is_array($sort) && count($sort) > 0){
            $allow_sort = array_merge($allow_filter, ["created_at", "updated_at"]);
    		$dir = isset($sort["dir"]) ? $sort["dir"]  : "desc";
    		$column = isset($sort["column"]) ? $sort["column"] : "created_at";
            if(in_array($column, $allow_sort) && in_array($dir, ["asc, desc"])){
    		     $model->orderBy($column, $dir);
            }
    	}

    	$totalRecords = $model->count();
    	if($no_pagination){
	    	$model->limit($limit);
	    	$model->offset($offset);
    	}
        $no = $offset+1;
    	$data = $model->when($search, function($query, $search){
    		return $query->where("product_name", "like", "%".$search."%")
    				->orWhere("product_name", "like", "%".$search."%")
    				->orWhere("product_code", "like", "%".$search."%")
    				->orWhereHas("categories", function($query) use ($search) {
    					$query->where("category_name", "like", "%".$search."%");
    				});
    	})->get()->map(function($data, $index ) use(&$no){
    		$unit = $data->_unit;
    		$units = [["unit_id" => sha1($unit->id), "unit_name" => $unit->unit_name]];
    		$conversions = $data->productConversions->map(function($data) use ($unit){
				return [
					"unit_id" => sha1($data->unit_id),
					"unit_name" => $data->unit->unit_name,
				];
			})->toArray();
			try {
				$units = array_merge($units, $conversions);
			} catch (\Exception $e) {
				
			}
    		return [
				"no" => $no++, 
		        "key" => sha1($data->id), 
		        "product_code" => $data->product_code, 
				"product_name" => $data->product_name, 
				"units" => $units, 
				"ppn" => $data->ppn, 
		        "category" => $data->categories->category_name, 
		        "purchase_price" => $data->purchase_price, 
		        "status" => $data->status, 
				"created" => $data->created_at->format('d M Y H:i:s'), 
				"last_modified" => $data->updated_at->format('d M Y H:i:s'), 
    		];
    	});
    	return ["totalRecords" => $totalRecords, "data" => $data];
	}

	public static function findByKey($key){
		$model = Products::where(DB::raw("SHA1(id)") , $key);
		$data = $model->get()->first();
		if($data){
    		$data = [
    			"key" => sha1($data->id),
    			"product_name" => $data->product_name,
    			"product_code" => $data->product_code,
    			"category" => $data->category,
    			"unit" => $data->unit,
    			"ppn" => $data->ppn,
    			"suppliers" => $data->suppliers->map(function($data){
    				return sha1($data->supplier_id);
    			}),
    			"warehouses" => $data->produtHasWarehouses->map(function($data){
    				return sha1($data->warehouse_id);
    			}),
    			"purchase_price" => $data->purchase_price,
    			"status" => $data->status,
    			"barcode" => $data->barcode,
    			"description" => $data->description,
    			"prices" => $data->prices,
    			"conversions" => $data->productConversions,
                "remark" => $data->remark,
    			"created" => $data->created_at->format('d M Y H:i:s'),
    			"last_modified" => $data->updated_at->format('d M Y H:i:s'),
    		];
		}

		return $data ?? null;
	}
	public static function productPos($key, $warehouse = null){
		$data = [];
		try {

			/*$model = ProductHasWarehouse::where("warehouse_id", "!=", null)->when($key, function($query, $key){
				return $query->whereHas("product", function($query) use ($key) {
					return $query->where("product_name", "like", "%".$key."%")->orWhere("barcode", $key);
				});
			})->when($warehouse, function($query, $warehouse){
				return $query->where(DB::Raw("sha1(warehouse_id)"), $warehouse);
			});
			$no = 1;
			$data = $model->get()->map(function($data) use (&$no){
				return [
					"no" => $no++,
					"key" => sha1($data->warehouse_id),
					"product_id" => sha1($data->product->id),
					"product_name" => $data->product->product_name,
					"unit" => $data->product->unit,
					"unit2" => $data->hasUnit,
				];
			}); 


			*/
			// $key=$ke;

			/*$model = ProductHasUnit::where("id", "!=", "null")
						->whereHas("product", function($query) use ($warehouse, $key){
							$query->join("product_has_warehouse", "products.id", "product_has_warehouse.product_id")
							->where(DB::raw("SHA1(product_has_warehouse.warehouse_id)"), $warehouse);
							// ->where(DB::raw("product_has_warehouse.warehouse_id"), 1);
						})->when($key, function($query, $key){
							$query->where("barcode", "like", "%".$key."%")
									->orWhereHas("product", function($qu) use($key){
										$qu->where("product_name", "like", "%".$key."%");
							});
						});*/
			$model = ProductHasUnit::where("id", "!=", "null")
				->whereHas("product", function($query) use ($warehouse, $key){
					$query->join("product_has_warehouse", "products.id", "product_has_warehouse.product_id")
						->where(function($query) use ($warehouse, $key){
							$query->where(DB::raw("SHA1(product_has_warehouse.warehouse_id)"), $warehouse)
								->where("products.product_name", "like", "%".$key."%");

						})->orWhere(function($query) use ($warehouse, $key){
							$query->where(DB::raw("SHA1(product_has_warehouse.warehouse_id)"), $warehouse)
								->where("product_has_unit.barcode", "like", "%".$key."%");
						})->with(["produtHasWarehouses" => function($query){
							$query->select("warehouse_id");
						}]);
				// ->where(DB::raw("product_has_warehouse.warehouse_id"), 1);
			});
			$data = $model->get()->map(function($data){
				return [
					"product_id" => $data->product_id, 
					"barcode" => $data->barcode, 
					"unit_id" => $data->unit_id, 
					"unit" => $data->unit->unit_name, 
					"discount" => 0, 
					"ppn" => $data->product->ppn, 
					"stock" => $data->stock, 
					"conversion" => $data->qty, 
					"prices" => $data->getPrice->map(function($data) {
						return [
							"price_group" => $data->price_group,
							"qty" => $data->qty,
							"price" => $data->price,
						];
					}), 
					"product_name" => $data->product->product_name, 
				];
			});
						// $data = $model->get();
			/*$product_has_unit = ProductHasUnit::join("products", "product_has_unit.product_id", "products.id")
				->select("products.id", "products.product_code", "products.product_name", "products.category", DB::raw("product_has_unit.unit_id as unit"), "products.description", "products.purchase_price", "products.ppn", "products.status", "products.remark", DB::raw("product_has_unit.barcode as barcode"), "products.created_at", "products.updated_at", "products.deleted_at")
				->where("product_has_unit.id", "!=", null)
				->where("products.barcode", "!=", null);

			$model = Products::where("id", "!=", null)->select("*")->union($product_has_unit);
			$data= $model->get();*/
				
		} catch (\Exception $e) {
			return AppHelpers::error($e, 500);
		}

		return $data;


	}

	public static function getHpp($product_id){
		$model = Products::find($product_id);

		# Ambil purchase
		$purchases = $model->purchases;

		#ambil total pembelian
		$total_purchase = $purchases->sum(function($data) {
			return $data->subtotal * ((100 - $data->discount) / 100);
		});
		# ambil qty pembelian
		$qty_total = $purchases->sum("qty");
		#hitung hpp sementar dari pembelian
		$tmp_hpp = 0;
		try{
			$tmp_hpp = round($total_purchase / $qty_total, 2);
		}catch(\Exception $e){

		}
		// return $tmp_hpp;

		#ambil data transaksi
		$transaction = TransactionDetail::where("product_id", $product_id)->get();
		# ambil total hpp
		// $total_transaction = $transaction->sum(DB::raw("(hpp * qty) * ((100 - discount) / 100)"));
		$total_transaction = $transaction->sum(function($data){
			$return = $data->returns->sum("qty");
			// return $data->hpp;
			return ($data->hpp * ($data->qty - $return)) * ((100 - $data->discount) / 100);
		});
		// return $total_transaction;
		$total_transaction = round($total_transaction, 2);
		# ambil jumlah kuantiti penjualan
		$total_transaction_qty = $transaction->sum("qty");

		# tuliskan nilai retur disini
		$return = ReturnDetail::where("product_id", $product_id);
		$return_qty = $return->sum("qty");
		$return_amount = $return_qty * $tmp_hpp;


		# nilai produk
		// $remainig_transaction = $total_purchase - ($total_transaction - $return_amount);
		$remainig_transaction = $total_purchase - $total_transaction;
		#ambil stoock
		$stock = round($model->produtHasWarehouses->sum("stock") , 2);
		# HPP
		// return $remainig_transaction;
		// $stock = 2;
		$stock = $stock ? $stock : 1;
		$hpp = round($remainig_transaction / $stock, 2);
		
		return $hpp ?? 0;
		/*return [
			"product_id" => $model->id,
			"total_purchase" => $total_purchase,
			"qty_total" => $qty_total,
			"tmp_hpp" => $tmp_hpp,
			"total_transaction" => round($total_transaction, 2),
			"total_transaction_qty" => $total_transaction_qty,
			"remainig_transaction" => $remainig_transaction,
			"stock" => $stock,
			"hpp" => $hpp,
		];*/
	}
}