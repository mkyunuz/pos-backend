<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Purchase;
use \App\Helpers\AppHelpers;
use App\Suppliers;
use App\Units;
use App\Warehouses;
use App\Models\PurchaseDetail;
use App\Repositories\PurchaseRepo;
use App\Products;
class PurchaseController extends Controller
{
	    	
	public function getPoNumber(Request $request){
		$response["error_code"] = "000";
		$response["error_message"] = "OK";
		$response["payload"] = null;
		try {
			$po_number = PurchaseRepo::generatePoNumber();
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
	    	$products = PurchaseRepo::filter($search, $query, $sort, $limit, $offset, $no_pagination);
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
		$validator = Validator::make($request->all(), Purchase::rules($request));
		if($validator->fails()){
			$response["error_code"] = "403";
			$response["error_message"] = $validator->errors() ;
			return response()->json($response, 403)->withHeaders(['Content-Type' => "application/json"]);
		} 

		$key = $request->key ?? null;
		$product_ids = $request->product_id ?? [];
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
		// return $product_ids;
		DB::beginTransaction();
		$due_date = "";
		$payment_date = NULL;
		if($request->due_date != "undefined") $due_date = new \DateTime( $request->due_date);
		if($request->payment_date != "undefined") $payment_date = new \DateTime( $request->payment_date);
		
		try {
			if(!$key){
				$paid_amount = $request->paid_amount ?? 0;
				$gt_after_ppn = $request->grand_total + $request->total_ppn;
				$balance = $gt_after_ppn - $paid_amount;
				$close = ($balance <= 0) ? 1 : 0;
				$update_stock = $request->update_stock;
				$model = Purchase::create([
					"purchase_number" => $request->purchase_number ?? NULL, 
					"po_number" => $request->po_number ?? NULL, 
					"supplier_id" => $supplier->id, 
					"payment_method" => $request->payment_method, 
					"due_date" => ($due_date) ? $due_date : $request->payment_date, 
					"shipping_costs" => 0, 
					"transaction_date" => $request->payment_date,
					"remark" => NULL,
					"company" => $supplier->company,
					"warehouse_id" => $warehouse_id,
					"supplier_address" => $supplier->address,
					"phone_number" => $request->contact_person ?? NULL,
					"shipping_to" => $request->warehouse_address ?? NULL,
					"user_id" => auth()->user()->id,
					"total_ppn" => $request->total_ppn,
					"grand_total" => $request->grand_total,
					"gt_after_ppn" => $gt_after_ppn,
					"close" => $close,
				]);
				$purchase = $model;
				$poId = $model->id;
				if($paid_amount){
					$hisory = $model->payments()->create(["amount" => $paid_amount, "user_id" => auth()->user()->id, "balance" => $balance, "payment_date" => $payment_date]);
				}else{
					$hisory = $model;
				}
				if($balance > 0){
					$purchase->_liabilities()->create([
						"description" => "Hutang pembelian (".$request->purchase_number.")",
						"amount" => $balance,
						"transaction_date" => $payment_date,
						"user_id" => auth()->user()->id,
					]);
				}
				$model->saveOrders($model->id, $detail_id, $product_ids, $qtys, $prices, $units, $discount, $subtotals, $ppn, auth()->user()->id);
				if($update_stock){
					$_product = new Products();
					$_product->updateStock($product_ids, $units, $qtys, $warehouse_id, $payment_date, date("H:i:s"));
				}
			}else{
				$model = Purchase::where(DB::raw("SHA1(id)"), $key);
				$poId = $model->get()->first()->id;
				$model->update([
					"purchase_number" => $request->purchase_number, 
					"po_number" => $request->po_number ?? NULL, 
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
				$po = Purchase::find($poId);
				$po->saveOrders($poId, $detail_id, $product_ids, $qtys, $prices, $units, $discount, $subtotals, $ppn, auth()->user()->id, explode(",", $remove_products));
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
	}

	public function view(Request $request){
		
		$response["error_code"] = "000";
    	$response["error_message"] = "ok";
		try{
			$model = PurchaseRepo::findByIdEncrypted($request->key);
    		$response["payload"] = $model;
		} catch(\Exception $e){
			return AppHelpers::error($e, 500);
		}
		return response()->json($response, 200)->withHeaders([
            'Content-Type' => "application/json",
        ]);
	}

}
