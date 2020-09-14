<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use \App\Suppliers;

class SuppliersController extends Controller
{
    public function index(Request $request){
    	$response["error_code"] = "000";
    	$response["error_message"] = "ok";
    	$limit = ($request->input("limit")) ? (int) $request->input("limit") : 10;
    	$page = ($request->input("page")) ? (int) $request->input("page") : 1;
    	$query = ($request->input("query")) ? (array) $request->input("query") : null;
    	$sort = ($request->input("sort")) ? (array) $request->input("sort") : null;
    	$offset = 0;
    	if($page > 0 ){
    		$offset = $page -1;
    	}
    	$offset = $limit * $offset;
    	try {
	    	$model = Suppliers::where("id", "!=", "");
	    	if(is_array($query)){

	    	}
	    	if(is_array($query)){
	    		$allow_filter = ["name", "company", "email", "phone", "address"];
	    		foreach ($query as $key => $value) {
	    			if(in_array($key, $allow_filter)){
	    				$model->where($key, "like", "%".$value."%");
	    			}
	    		}
	    	}

	    	if(is_array($sort)){
                $allow_sort = array_merge($allow_filter, ["created_date", "last_modified"]);
	    		$dir = isset($sort["dir"]) ? $sort["dir"]  : "desc";
	    		$column = isset($sort["column"]) ? $sort["column"] : "created_at";
                if(in_array($column, $allow_sort) && in_array($dir, ["asc", "desc"])){
                    if($column == "last_modified"){
                        $column = "updated_at";
                    }else if($column == "created_date"){
                        $column = "created_at";
                    }
	    		     $model->orderBy($column, $dir);
                }
	    	}
	    	$totalRecords = $model->count();
	    	$model->limit($limit);
	    	$model->offset($offset);
	    	$data = $model->get();
	    	$response["payload"] = [];
            $totalPage = ceil($totalRecords / $limit);
            $response["payload"]["rows"] = [];
            $response["payload"]["pagination"]["pageSize"] = $limit;
            $response["payload"]["pagination"]["total"] = $totalPage;
            $response["payload"]["pagination"]["current"] = $page;
            $response["payload"]["pagination"]["totalRecords"] = $totalRecords;
            $no = $offset+1;
	    	foreach ($data as $key) {
	    		array_push($response["payload"]["rows"],[ 
                    "no" => $no++,
	    			"id" => sha1($key->id), 
	    			"name" => $key->name, 
                    "company" => $key->company, 
                    "address" => $key->address, 
                    "phone" => $key->phone, 
                    "email" => $key->email, 
                    "remark" => $key->remark, 
	    			"created" => $key->created_at->format('d M Y H:i:s'), 
	    			"last_modified" => $key->updated_at->format('d M Y H:i:s'), 
	    		]);
	    	}

    	} catch (\Exception $e) {
    		$response["payload"]["rows"] = [];
            $response["payload"]["pagination"] = [];
    	}
		return response()->json($response, 200)->withHeaders([
            'Content-Type' => "application/json",
        ]);

    }

    public function save(Request $request){
    	$response["error_code"] = "000";
    	$response["error_message"] = "ok";
    	$key = ($request->input("key")) ? trim($request->input("key")) :null;
    	$name = ($request->input("name")) ? trim($request->input("name")) :null;
    	$company = ($request->input("company")) ? trim($request->input("company")) :null;
        $address = ($request->input("address")) ? trim($request->input("address")) :null;
        $phone = ($request->input("phone")) ? trim($request->input("phone")) :null;
        $email = ($request->input("email")) ? trim($request->input("email")) :null;
        $remark = ($request->input("remark")) ? trim($request->input("remark")) :null;
    	
    	if(!$name){
    		$response["error_code"] = "422";
    		$response["error_message"] = "Invalid name";
    		return response()->json($response, 400)->withHeaders([
                'Content-Type' => "application/json",
            ]);
    	}
    	if (!$company) {
    		$response["error_code"] = "422";
    		$response["error_message"] = "Invalid company";
    		return response()->json($response, 400)->withHeaders([
                'Content-Type' => "application/json",
            ]);
    	}
        if (!$phone) {
            $response["error_code"] = "422";
            $response["error_message"] = " Invalid phone";
            return response()->json($response, 400)->withHeaders([
                'Content-Type' => "application/json",
            ]);
        }

    	if(!$key){
	    	try {
	    		$status = 200;
	    		$model = new Suppliers();
	    		$model->company = $company;	
	    		$model->name = $name;
                $model->address = $address;
                $model->phone = $phone;
                $model->email = $email;
                $model->remark = $remark;
	    		$model->save();
	    		$response["payload"] = sha1($model->id);
	    	} catch (\Exception $e) {
	    		$status = 500;
	    		$response["error_code"] = $status;
	    		$response["error_message"] = $e->getMessage();
	    	}
	    }else{
	    	try {
	    		$status = 200;
	    		$model = Suppliers::where(DB::raw("SHA1(id)") , $key)->update([
	    			"company" => $company,
	    			"name" => $name,
                    "address" => $address,
                    "phone" => $phone,
                    "email" => $email,
                    "remark" => $remark,
	    		]);
	    		$response["payload"] = $key;
	    	} catch (\Exception $e) {
	    		$status = 500;
	    		$response["error_code"] = $status;
	    		$response["error_message"] = $e->getMessage();
	    	}
	    }

		return response()->json($response, $status)->withHeaders([
            'Content-Type' => "application/json",
        ]);
    }
    
    public function checkSupplierId(Request $request){
    	$company = ($request->input("company")) ? trim($request->input("company")) :null;
    	$current = ($request->input("current_id")) ? trim($request->input("current_id")) :null;
    	$model = Suppliers::where("company", $company)
    			->when($current, function($q, $current){
    				$q->where("company", "!=", $current);
    			});
    	return $model->count();
    }

    public function delete(Request $request){
    	$status = 200;
    	$response["error_code"] = "000";
    	$response["error_message"] = "ok";
    	$key = ($request->input("key")) ? trim($request->input("key")) :null;
    	try {
    		$model = Suppliers::where(DB::raw("SHA1(id)") , $key)->delete();
    	} catch (\Exception $e) {
    		$status = 500;
    		$response["error_code"] = $status;
    		$response["error_message"] = $e->getMessage();
    	}

		return response()->json($response, $status)->withHeaders([
            'Content-Type' => "application/json",
        ]);
    }

    public function view(Request $request){
    	$status = 200;
    	$response["error_code"] = "000";
    	$response["error_message"] = "ok";
    	$key = ($request->input("key")) ? trim($request->input("key")) :null;
    	try {
    		$model = Suppliers::where(DB::raw("SHA1(id)") , $key);
    		$data = $model->get()->first();
    		if($data){
	    		$response["payload"] = [
	    			"key" => sha1($data->id),
	    			"company" => $data->company,
	    			"name" => $data->name,
                    "address" => $data->address,
                    "phone" => $data->phone,
                    "email" => $data->email,
                    "remark" => $data->remark,
	    			"created" => $data->created_at->format('d M Y H:i:s'),
	    			"last_modified" => $data->updated_at->format('d M Y H:i:s'),
	    		];
    		}else{
    			$status = 201;
	    		$response["error_code"] = $status;
	    		$response["error_message"] = "No record";
    		}
    	} catch (\Exception $e) {
    		$status = 500;
    		$response["error_code"] = $status;
    		$response["error_message"] = $e->getMessage();
    	}

		return response()->json($response, $status)->withHeaders([
            'Content-Type' => "application/json",
        ]);
    }

}
