<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Equility;
use App\Models\AccountReceivable;
use \App\Helpers\AppHelpers;
use App\Repositories\ARRepo;
class AccountReceivableController extends Controller
{
	private $response;	
	private $userId;	
	public function __construct(){
		$this->response = new AppHelpers();
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
	    	$products = ARRepo::filter($search, $query, $sort, $limit, $offset, $no_pagination);
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
            return AppHelpers::error($e, 500);
    	}

		return response()->json($response, 200)->withHeaders([
            'Content-Type' => "application/json",
        ]);
	}
	public function save(Request $request){
    	$payload = null;
    	DB::beginTransaction();
    	try{
    		$model = AccountReceivable::where(DB::raw("SHA1(id)"), $request->key)->get()->first();
    		$payload = $model;
    		if($model){
    			$debit = ($model->account_receivables->gt_after_ppn - $model->account_receivables->payments->sum("amount") ) - $request->amount;

    			$payment_date = date("Y-m-d");
    			$userId = auth()->user()->id;
    			$account_receivables = $model->account_receivables;
    			$payment = $model->account_receivables->payments()->create([
    				"amount" => $request->amount, 
    				"user_id" => $userId,
    				"balance" => $debit,
                    "paid_amount" => $request->amount,
                    "debit" => 0,
    				"payment_date" => $payment_date,
    			]);
				if($debit <=0){
					$account_receivables->update(["close" => 1]);
				}

    		}
    		$payload = $request->key;
    		DB::commit();
    	}catch(\Exception $e){
    		DB::rollBack();
    		return AppHelpers::error($e, 500);
    	}
    	return $this->response->json($payload, 200);
	}

	public function view(Request $request){
		
	}

}
