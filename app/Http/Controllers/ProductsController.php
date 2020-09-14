<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use \App\Products;
use \App\Prices;
use \App\Models\ProductHasSuppliers;
use \App\Models\ProductHasWarehouse;
use \App\Repositories\ProductRepo;
use \App\Helpers\AppHelpers;
use \App\Models\ProductHasUnit;
use Illuminate\Validation\Rule;

class ProductsController extends Controller
{
	public function __construct(){

	}
    public function index(Request $request){
    	$response["error_code"] = "000";
    	$response["error_message"] = "ok";
    	$limit = $request->limit ?? 100;
    	$page = (int) $request->page ?? 1;
    	$query = ($request->input("query")) ? (array) $request->input("query") : [];
    	$sort = $request->sort ?? null;
    	$search = $request->search ?? null;
    	$no_pagination = $request->no_pagination ?? true;
    	$offset = 0;
    	if($page > 0 ){
    		$offset = $page -1;
    	}
    	$offset = $limit * $offset;
    	try {
	    	$products = ProductRepo::filter($search, $query, $sort, $limit, $offset, $no_pagination);
            $totalPage = ceil($products["totalRecords"] / $limit);
	    	$response["payload"] = [];
            $totalPage = ceil($products["totalRecords"] / $limit);
            $response["payload"]["rows"] = [];
            if($no_pagination == true){
	            $response["payload"]["pagination"]["pageSize"] = $limit;
	            $response["payload"]["pagination"]["total"] = $totalPage;
	            $response["payload"]["pagination"]["current"] = $page;
	            $response["payload"]["pagination"]["totalRecords"] = $products["totalRecords"];
            }
            $response["payload"]["rows"] = $products["data"];

    	} catch (\Exception $e) {
            $response["payload"]["rows"] = [];
            $response["payload"]["pagination"] = [];
            $response["message"] = $e->getMessage()." ".$e->getLine(). "".$e->getFile();
    	}

		return response()->json($response, 200)->withHeaders([
            'Content-Type' => "application/json",
        ]);

    }
    

   
    public function save(Request $request){
    	// $a = $this->myfunction([["id" => 1], ["id" => 2]], "id" , 1);
    	// print_r($a);
    	// return $a;
    	// $w = ProductHasWarehouse::where(DB::raw("sha1(warehouse_id)"), "1b6453892473a467d07372d45eb05abc2031647a")
    							// ->where("product_id", 75)->get()->first();
// return ($w);
    	$response["error_code"] = "000";
    	$response["error_message"] = "ok";
    	$key = $request->key ?? null;
    	$units = $request->units ?? [];
    	$groups = $request->group ?? [];
    	$prices = $request->price ?? [];
    	$removed_price = $request->removed_price ?? [];
    	$price_group_id = $request->price_group_id ?? [];
    	$qtys = $request->qty ?? [];
    	$conversion_units = $request->conversion_units ?? [];
    	$conversion_qty = $request->conversion_qty ?? [];
    	$conversion_id = $request->conversion_id ?? [];
    	$suppliers = $request->suppliers ?? "";
    	$conversion_barcode = $request->conversion_barcode ?? [];
    	$suppliers = explode(",", $suppliers);
    	$removed_conversions = $request->removed_conversions ?? [];
		$warehouses = $request->warehouse ?? [];
    	$warehouses = explode(",", $warehouses);
		$validator = Validator::make($request->all(), Products::rules($request));
		if($validator->fails()){
			$response["error_code"] = "403";
			$response["error_message"] = $validator->errors() ;
			return response()->json($response, 403)->withHeaders(['Content-Type' => "application/json"]);
		} 
		// return $warehouses;
    	DB::beginTransaction();
    	if(!$key){
	    	try {
	    		$status = 200;
	    		$model = Products::create([
	    			"product_code" => $request->product_code,
	    			"product_name" => $request->product_name,
	    			"category" => $request->category,
	    			"unit" => $request->unit,
	    			"description" => $request->description,
	    			"purchase_price" => $request->purchase_price,
	    			"ppn" => $request->ppn,
	    			"status" => $request->status,
	    			"remark" => $request->remark,
	    			"barcode" => $request->barcode,
	    		]);
	    		$productId = $model->id;
	    		$model->savePrices($model->product_code, $price_group_id, $groups, $qtys, $units, $prices, $removed_price);
	    		$model->saveProductHasUnits($productId, $conversion_id, $conversion_units, $conversion_qty, $conversion_barcode, $removed_conversions);
	    		$model->saveSuppliers($productId, $suppliers);
	    		$model->saveProductHasWarehouses($productId, $warehouses);
	    		$response["payload"] = sha1($productId);
	    		$_key  = SHA1($productId);
	    		DB::commit();
	    	} catch (\Exception $e) {
	    		DB::rollBack();
	    		$status = 500;
	    		$response["error_code"] = $status;
	    		return $e;
	    		// $response["error_message"] = $e->getMessage()." - ".$e->getLine()." - ".$e->getFile();
	    	}
	    }else{
	    	try {
	    		$status = 200;
	    		$_key  = $key;
	    		$model = Products::where(DB::raw("SHA1(id)") , $key);
	    		$productId = $model->get()->first()->id;
	    		$model->update([
	    			"product_code" => $request->product_code,
	    			"product_name" => $request->product_name,
	    			"category" => $request->category,
	    			"unit" => $request->unit,
	    			"description" => $request->description,
	    			"purchase_price" => $request->purchase_price,
	    			"ppn" => $request->ppn ?? 0,
	    			"status" => $request->status,
	    			"remark" => $request->remark,
	    			"barcode" => $request->barcode,
	    		]);

	    		$product = Products::find($productId);
	    		$product->saveSuppliers($productId, $suppliers);
	    		$product->savePrices($request->product_code, $price_group_id, $groups, $qtys, $units, $prices, $removed_price);
	    		$product->saveProductHasUnits($productId, $conversion_id, $conversion_units, $conversion_qty, $conversion_barcode, $removed_conversions);
	    		$product->saveProductHasWarehouses($productId, $warehouses);
	    		$response["payload"] = $_key;
	    		DB::commit();
	    	} catch (\Exception $e) {
	    		DB::rollBack();
	    		$status = 500;
	    		$response["error_code"] = $status;
	    		$response["error_message"] = $e->getMessage(). "Line " .$e->getLine() ." ". $e->getFile();
	    	}
	    }

		return response()->json($response, $status)->withHeaders([
            'Content-Type' => "application/json",
        ]);
    }


    public function delete(Request $request){
    	$response["error_code"] = "000";
    	$response["error_message"] = "ok";
    	$status = 200;
    	$key = $request->key ?? null;
    	DB::beginTransaction();
    	try {
    		$model = Products::where(DB::raw("SHA1(id)") , $key)->get()->first();
    		$model->delete();
    		DB::commit();
    	} catch (\Exception $e) {
    		$status = 500;
    		$response["error_code"] = "000";
    		$response["error_message"] = $e->getMessage()." ## ".$e->getLine(). "##" .$e->getFile();
    		DB::rollBack();
    	}
		return response()->json($response, $status)->withHeaders(['Content-Type' => "application/json"]);
    }

    public function view(Request $request){
    	$status = 200;
    	$response["error_code"] = "000";
    	$response["error_message"] = "ok";
    	$key = ($request->input("key")) ? trim($request->input("key")) :null;
    	try {
    		$products = ProductRepo::findByKey($key);
    		if($products){
	    		$response["payload"] = $products;
    		}else{
    			$status = 201;
	    		$response["error_code"] = $status;
	    		$response["error_message"] = "No record";
    		}
    	} catch (\Exception $e) {
    		/*$status = 500;
    		$response["error_code"] = $status;
    		$response["error_message"] = $e->getMessage();*/
    		return AppHelpers::error($e, 500);
    	}

		return response()->json($response, $status)->withHeaders(['Content-Type' => "application/json"]);
    }

    public function checkProductId(Request $request){
    	$product_code = ($request->input("product_code")) ? trim($request->input("product_code")) :null;
    	$key = ($request->input("key")) ? trim($request->input("key")) :null;
    	$model = Products::where("product_code", $product_code)
    			->when($key, function($q, $key){
    				$q->where(DB::raw("SHA1(id)"), "!=", $key);
    			});
    	return $model->count();
    }

    public function checkBarcode(Request $request){
    	/*$barcode = ($request->input("barcode")) ? trim($request->input("barcode")) :null;
    	$key = ($request->input("key")) ? trim($request->input("key")) :null;
    	$key = ($request->input("barcode_key")) ? trim($request->input("barcode_key")) :null;
    	$model = Products::where("barcode", $barcode)
    			->when($key, function($q, $key){
    				$q->where(DB::raw("SHA1(id)"), "!=", $key);
    			});
    	return $model->count();*/
    	return 0;
    }

}
