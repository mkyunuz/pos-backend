<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\PurchaseOrder;
use \App\Helpers\AppHelpers;
use App\Suppliers;
use App\Units;
use App\Warehouses;
use App\Models\PurchaseOrderDetail;
use App\Repositories\PoRepo;
class PoController extends Controller
{
	    	
	public function getPoNumber(Request $request){
		$response["error_code"] = "000";
		$response["error_message"] = "OK";
		$response["payload"] = null;
		try {
			$po_number = PoRepo::generatePoNumber();
			$response["payload"] = $po_number;
		} catch (\Exception $e) {
			return AppHelpers::error($e, 500);
		}

		return $response;
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
	    	$products = PoRepo::filter($search, $query, $sort, $limit, $offset, $no_pagination);
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
    	$response["error_code"] = "000";
    	$response["error_message"] = "ok";
    	$status = 200;
		$validator = Validator::make($request->all(), PurchaseOrder::rules($request));
		if($validator->fails()){
			$response["error_code"] = "403";
			$response["error_message"] = $validator->errors() ;
			return response()->json($response, 403)->withHeaders(['Content-Type' => "application/json"]);
		} 

		$key = $request->key ?? null;
		$product_codes = $request->product_code ?? [];
		$units = $request->units ?? [];
		$qtys = $request->qtys ?? [];
		$prices = $request->prices ?? [];
		$detail_id = $request->detail_id ?? [];
		$discount = $request->discount ?? [];
		$subtotals = $request->totals ?? [];
		$ppn = $request->ppn ?? [];
		$key = $request->key ?? null;
		$remove_products = $request->remove_products ?? "";
		$supplier = Suppliers::where(DB::raw("SHA1(id)"), $request->supplier)->get()->first();
		$warehouse = Warehouses::where(DB::raw("SHA1(id)"), $request->warehouses)->get()->first();
		$warehouse_id = $warehouse->id;
		// return $warehouse_id;
		DB::beginTransaction();
		$due_date = "";
		if($request->due_date != "undefined") $due_date = new \DateTime( $request->due_date);
		
		try {
			if(!$key){
				$model = PurchaseOrder::create([
					"po_number" => $request->po_number, 
					"supplier_id" => $supplier->id, 
					"payment_method" => $request->payment_method, 
					"due_date" => ($due_date) ? $due_date : NULL, 
					"shipping_costs" => 0, 
					"transaction_date" => date("Y-m-d"),
					"remark" => NULL,
					"company" => $supplier->company,
					"warehouse_id" => $warehouse_id,
					"supplier_address" => $supplier->address,
					"phone_number" => $request->contact_person,
					"shipping_to" => $request->warehouse_address,
					"user_id" => auth()->user()->id,
					"total_ppn" => $request->total_ppn,
					"grand_total" => $request->grand_total,
					"gt_after_ppn" => $request->grand_total + $request->total_ppn,
					"close" => 0,
				]);
				$poId = $model->id;
				$model->saveOrders($model->id, $detail_id, $product_codes, $qtys, $prices, $units, $discount, $subtotals, $ppn, auth()->user()->id);
			}else{
				$model = PurchaseOrder::where(DB::raw("SHA1(id)"), $key);
				$poId = $model->get()->first()->id;
				$model->update([
					"po_number" => $request->po_number, 
					"supplier_id" => $supplier->id, 
					"payment_method" => $request->payment_method, 
					"due_date" => ($due_date) ? $due_date : NULL, 
					"shipping_costs" => 0, 
					"transaction_date" => date("Y-m-d"),
					"remark" => NULL,
					"company" => $supplier->company,
					"warehouse_id" => $warehouse_id,
					"supplier_address" => $supplier->address,
					"phone_number" => $request->contact_person,
					"shipping_to" => $request->warehouse_address,
					"user_id" => auth()->user()->id,
					"total_ppn" => $request->total_ppn,
					"grand_total" => $request->grand_total,
					"gt_after_ppn" => $request->grand_total + $request->total_ppn,
					"close" => 0,
				]);
				$po = PurchaseOrder::find($poId);
				$po->saveOrders($poId, $detail_id, $product_codes, $qtys, $prices, $units, $discount, $subtotals, $ppn, auth()->user()->id, explode(",", $remove_products));
			}
			$response["payload"] = sha1($poId);
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			return AppHelpers::error($e, 500);
		}
		return response()->json($response, $status)->withHeaders([
            'Content-Type' => "application/json",
        ]);
		// $Warehouses =
	}

	public function view(Request $request){
		$response["error_code"] = "000";
    	$response["error_message"] = "ok";
		try{
			$model = PoRepo::findByIdEncrypted($request->key);
    		$response["payload"] = $model;
		} catch(\Exception $e){
			return AppHelpers::error($e, 500);
		}
		return response()->json($response, 200)->withHeaders([
            'Content-Type' => "application/json",
        ]);
	}

}
