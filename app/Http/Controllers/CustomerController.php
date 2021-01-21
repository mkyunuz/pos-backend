<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\AppHelpers;
use App\Models\Customers;
use App\Repositories\CustomersRepo;
class CustomerController extends Controller
{
    public $response;
    public function __construct(){
    	$this->response = new AppHelpers();
    }

    public function index(Request $request){
    	
    	$payload = [];
    	$limit = $request->limit ?? 100;
    	$page = ((int) $request->page == 0) ? 1 : (int) $request->page;
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
	    	$products = CustomersRepo::filter($search, $query, $sort, $limit, $offset, $no_pagination);
            $totalPage = ceil($products["totalRecords"] / $limit);
	    	$payload = [];
            $totalPage = ceil($products["totalRecords"] / $limit);
            $response["payload"]["rows"] = [];
            if($no_pagination == true){
	            $payload["pagination"]["pageSize"] = $limit;
	            $payload["pagination"]["total"] = $totalPage;
	            $payload["pagination"]["current"] = $page;
	            $payload["pagination"]["totalRecords"] = $products["totalRecords"];
            }
            $payload["rows"] = $products["data"];

    	} catch (\Exception $e) {
            return AppHelpers::error($e, 500);
    	}

    	return $this->response->json($payload, 200);
    }
    public function save(Request $request){
    	$validator = Validator::make($request->all(), Customers::rules());
    	if($validator->fails()){
    		return $this->response->setMessage($validator->errors())
    				->setErrorCode(403)->json();
    	}
    	$key = $request->key ?? null;
    	$payload = null;
    	try{
    		$userId = auth()->user()->id;
    		if(!$key){
	    		$model = Customers::create([
	    			"customer_id" => CustomersRepo::generateCustomerNumber(),
	    			"name" => $request->customer_name,
	    			"status" => $request->status,
	    			"phone_number" => $request->phone_number,
	    			"address" => $request->customer_address,
	    			"user_id" => $userId,
	    		]);
    		}else{
    			$model = CustomersRepo::findByIdEncrypted($request->key);
    			if($model){
    				$model->update([
    					"name" => $request->customer_name,
		    			"status" => $request->status,
		    			"phone_number" => $request->phone_number,
		    			"address" => $request->customer_address,
		    			"user_id" => $userId,
    				]);
    			}
    		}
    		$payload = sha1($model->id);
    	}catch(\Exception $e){
    		return AppHelpers::error($e, 500);
    	}
    	return $this->response->json($payload, 200);
    }

    public function view(Request $request){}
    public function delete(Request $request){
    	try{
    		CustomersRepo::findByIdEncrypted($request->key)->delete();
    	}catch(\Exception $e){
    		return AppHelpers::error($e, 500);
    	}

    	return $this->response->json(null, 200);
    }


}
