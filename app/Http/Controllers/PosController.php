<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use App\Helpers\AppHelpers;
use App\Repositories\ProductRepo;
use App\Repositories\PosRepo;
use App\Models\Transaction;
use App\Models\ProductHasWarehouse;
use App\Models\Returns;
use App\Models\TransactionDetail;
use App\Warehouses;
use App\Models\PurchaseDetail;
use App\Models\PendingTransactions;
use App\Repositories\PendingTransactionsRepo;

class PosController extends Controller
{
	private $response;
	public function __construct(){
		$this->response = new AppHelpers();
	}    

	public function index(Request $request){
    	$payload = [];
    	$limit = $request->limit ?? 10;
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
	    	$sales = PosRepo::filter($search, $query, $sort, $limit, $offset, $no_pagination);
            $totalPage = ceil($sales["totalRecords"] / $limit);
	    	$payload = [];
            $totalPage = ceil($sales["totalRecords"] / $limit);
            $payload["rows"] = [];
            if($no_pagination == true){
	            $payload["pagination"]["pageSize"] = $limit;
	            $payload["pagination"]["total"] = $totalPage;
	            $payload["pagination"]["current"] = $page;
	            $payload["pagination"]["totalRecords"] = $sales["totalRecords"];
            }
            $payload["rows"] = $sales["data"];

    	} catch (\Exception $e) {
            return AppHelpers::error($e, 500);
    	}

		return $this->response->json($payload, 200);
	}

	public function transactionNumber(Request $request){
		try {
			$data = PosRepo::generateTransactionNumber();
			if($data){
				return $this->response->json($data, 200);
			}
		} catch (\Exception $e) {
			return AppHelpers::error($e, 500);
		}
	}
	public function products(Request $request){
		$keyword = $request->keyword ?? null;
		$warehouse = $request->warehouse ?? null;
		$payload = [];
		try {
			$model = ProductRepo::productPos($keyword, $warehouse);
			$payload["rows"] = $model;
		} catch (\Exception $e) {
			return AppHelpers::error($e, 500);
		}

		return $this->response->json($payload, 200);
	}

	public function save(Request $request){
		$payload = null;
		$validator = Validator::make($request->all(), Transaction::$rules);
		if($validator->fails()){
			return $this->response->setErrorCode(403)->json($validator->errors(), 403);
		}

		DB::beginTransaction();
		try {
			$userId = auth()->user()->id;
			$products = $request->products ?? [];
			$pending_id = $request->pending_id ?? null;
			$transaction_number = $request->transaction_number;
			$transaction_date = null;
			$due_date = null;
			try{
				if($request->due_date != "undefined") $due_date = new \DateTime( $request->due_date);
				if($request->transaction_date) $transaction_date = new \DateTime( $request->transaction_date);
			}catch(\Exception $e){

			}
			$payment_amount = $request->payment_amount ?? 0;
			$totalPurchase = $request->totalPurchase ?? 0;
			$service = $request->service ?? 0;
			$grandTotal = $request->grandTotal ?? 0;
			$grandTotalAndService = $service + $grandTotal;
			$total_discount = $request->total_discount ?? 0;
			$service = $request->service ?? 0;
			$status = 0;
			$close = (($grandTotalAndService - $payment_amount) <=0) ? 1 : 0;
			$warehouse = Warehouses::where(DB::raw("sha1(id)"), $request->warehouse_id)->get()->first();
			$warehouse_id = ($warehouse->id) ?? null;
			if(!$warehouse) return $this->response->setErrorCode(403)->setErrorMessage("request error")->json(null,500); 
			$model = Transaction::create([
				"warehouse_id" => $warehouse_id,
				"transaction_number" => $transaction_number,
				"total_ppn" => $request->total_ppn ,
				"grand_total" => $totalPurchase ,
				"gt_after_ppn" => $grandTotalAndService ,
				"total_discount" =>  $total_discount,
				"payment_method" => $request->payment_method ,
				"due_date" => $due_date,
				// "shipping_costs" => $request->shipping_costs ?? 0 ,
				"services" => $service,
				"transaction_date" => $transaction_date ,
				"transaction_time" => date("H:i:s") ,
				"remark" => $request->remark ?? null ,
				"customer_id" => $request->customer ?? NULL ,
				"phone_number" => $request->phone_number ?? NULL ,
				"shipping_to" => $request->shipping_to ?? NULL ,
				"user_id" =>  $userId,
				"close" => $close
			]);
		
			$transactionId=$model->id;
			if($products) $model->saveItems($userId, $transactionId, $warehouse_id, $transaction_date, $model->transaction_time, $products);

			$grand_total = ($grandTotal + $service - $total_discount);
			$debit = $grandTotalAndService - $payment_amount;
			if($payment_amount > 0 ){
				$amount = $payment_amount;
				if($payment_amount > $grand_total){
					$amount = $grand_total;
				}
				# simpan histori pembayaran
				$payment = $model->payments()->create([
					"amount" => $amount, 
					"paid_amount" => $payment_amount, 
					"debit" => abs($debit), 
					"user_id" => $userId,
					"balance" => ($debit >=0) ? $debit : 0 ,
					"payment_date" => $transaction_date,
				]);
			}
			$model->account_receivables()->create([
				"description" => "Piutang dagang (".$transaction_number.")",
				"amount" => $debit,
				"transaction_date" => $transaction_date,
				"user_id" => $userId,
			]);
		
				
			if($debit <=0) $model->update(["close" => 1]);
			
			if($pending_id){
				PendingTransactions::where(DB::raw("SHA1(id)"), $pending_id)->delete();
			}
			$payload = sha1($transactionId);
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			return AppHelpers::error($e, 500);
		}
		return $this->response->json($payload, 200);
	}

	public function saveReturn(Request $request){
		$payload = null;
		
		DB::beginTransaction();
		try{
			$id = $request->id ?? null;
			$product_id = $request->product_id ?? [];
			$qty = $request->qty ?? [];
			$hpp = $request->hpp ?? [];
			$price = $request->price ?? [];
			$conversion = $request->conversion ?? [];
			$discount = $request->discount ?? [];
			$qty = $request->qty ?? [];
			$return_amount = $request->return_amount ?? [];
			$return_date =  NULL;
			if($request->return_date != "undefined") $return_date = new \DateTime( $request->return_date);
			$total_return_amount = $request->total_return_amount ?? 0;
			// return $return_amount;
			$model = Transaction::where(DB::raw("SHA1(id)"), $id)->get()->first();
			if($model){
				$userId = auth()->user()->id;
				$return = Returns::create([
					"transaction_id" => $model->id,
					"amount" => $total_return_amount,
					"return_date" => $return_date
				]);
				$returnId = $return->id;
				$return->saveReturn($userId, 
					$model->id, 
					$returnId, 
					$model->warehouse_id, 
					$product_id, 
					$qty, 
					$conversion,
					$return_amount,
					$return_date,
					date("H:i:s")
				);
			}
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			return AppHelpers::error($e, 500);
		}

		return $this->response->json($payload, 200);

	}

	public function hold(Request $request){
		$payload = null;
		$validator = Validator::make($request->all(), PendingTransactions::$rules);
		if($validator->fails()){
			return $this->response->setErrorCode(403)->json($validator->errors(), 403);
		}

		DB::beginTransaction();
		try {
			$userId = auth()->user()->id;
			$products = $request->products;
			$transaction_number = $request->transaction_number;
			$transaction_date = null;
			$due_date = null;
			try{
				if($request->due_date != "undefined") $due_date = new \DateTime( $request->due_date);
				if($request->transaction_date) $transaction_date = new \DateTime( $request->transaction_date);
			}catch(\Exception $e){

			}
			$warehouse = Warehouses::where(DB::raw("sha1(id)"), $request->warehouse_id)->get()->first();
			$warehouse_id = ($warehouse->id) ?? null;
			if(!$warehouse) return $this->response->setErrorCode(403)->setErrorMessage("request error")->json(null,500); 
			$model = PendingTransactions::create([
				"warehouse_id" => $warehouse_id,
				"transaction_date" => $transaction_date ,
				"user_id" =>  $userId,
			]);
		
			$pendingTransactionId=$model->id;
			$model->saveItems($userId, $pendingTransactionId, $warehouse_id, $products);

			$payload = sha1($pendingTransactionId);
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			return AppHelpers::error($e, 500);
		}
		return $this->response->json($payload, 200);
	}

	public function pendingTransaction(Request $request){

		
    	$payload = [];
    	$limit = $request->limit ?? 10;
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
    		$model = PendingTransactionsRepo::filter($search, $query, $sort, $limit, $offset, $no_pagination);
            $totalPage = ceil($model["totalRecords"] / $limit);
	    	$payload = [];
            $totalPage = ceil($model["totalRecords"] / $limit);
            $payload["rows"] = [];
            if($no_pagination == true){
	            $payload["pagination"]["pageSize"] = $limit;
	            $payload["pagination"]["total"] = $totalPage;
	            $payload["pagination"]["current"] = $page;
	            $payload["pagination"]["totalRecords"] = $model["totalRecords"];
            }
            $payload["rows"] = $model["data"];

    	} catch (\Exception $e) {
            return AppHelpers::error($e, 500);
    	}

		return $this->response->json($payload, 200);
	}

	public function receiptOfPayment(Request $request){
		$key = $request->key ?? null;
		$payment_id = $request->payment_id ?? null;
		$payload = null;
		try{
			$payload = PosRepo::receiptOfPayment($key, $payment_id);
		}catch(\Exception $e){
			return AppHelpers::error($e, 500);
		}
		return $this->response->json($payload, 200);
	}

	public function summary(Request $request){
		$payload = null;
		try{

			$key = ($request->key) ?? null;
			if($key) $payload = PosRepo::findByIdEncrypted($key);

		}catch(\Exception $e){
			return AppHelpers::error($e, 500);
		}
		return $this->response->json($payload, 200);
	}

	
}
