<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use \App\Units;

class UnitsController extends Controller
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
	    	$model = Units::where("id", "!=", "");
	    	if(is_array($query)){

	    	}
	    	if(is_array($query)){
	    		$allow_filter = ["unit_name", "unit_code"];
	    		foreach ($query as $key => $value) {
	    			if(in_array($key, $allow_filter)){
	    				$model->where($key, "like", "%".$value."%");
	    			}
	    		}
	    	}

	    	if(is_array($sort)){
                $allow_sort = array_merge($allow_filter, ["created_at", "updated_at"]);
	    		$dir = isset($sort["dir"]) ? $sort["dir"]  : "desc";
	    		$column = isset($sort["column"]) ? $sort["column"] : "created_at";
                if(in_array($column, $allow_sort) && in_array($dir, ["asc", "desc"])){
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
	    			"unit_id" => $key->id, 
	    			"unit_name" => $key->unit_name, 
                    "unit_code" => $key->unit_code, 
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
    	$unit_name = ($request->input("unit_name")) ? trim($request->input("unit_name")) :null;
    	$unit_code = ($request->input("unit_code")) ? trim($request->input("unit_code")) :null;
    	
    	if(!$unit_name){
    		$response["error_code"] = "422";
    		$response["error_message"] = "Invalid unit_name";
    		return response()->json($response, 400)->withHeaders([
                'Content-Type' => "application/json",
            ]);
    	}
    	if (!$unit_code) {
    		$response["error_code"] = "422";
    		$response["error_message"] = "Invalid unit_code";
    		return response()->json($response, 400)->withHeaders([
                'Content-Type' => "application/json",
            ]);
    	}
    	$checkUnitId = $this->checkUnitId($request);
    	if($checkUnitId>0){
			$response["error_code"] = "422";
			$response["error_message"] = "Unit code exist";
			return response()->json($response, 400)->withHeaders([
	            'Content-Type' => "application/json",
	        ]);
    	}

    	if(!$key){
	    	try {
	    		$status = 200;
	    		$model = new Units();
	    		$model->unit_code = $unit_code;	
	    		$model->unit_name = $unit_name;
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
	    		$model = Units::where(DB::raw("SHA1(id)") , $key)->update([
	    			"unit_code" => $unit_code,
	    			"unit_name" => $unit_name,
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
    
    public function checkUnitId(Request $request){
    	$unit_code = ($request->input("unit_code")) ? trim($request->input("unit_code")) :null;
    	$key = ($request->input("key")) ? trim($request->input("key")) :null;
    	$model = Units::where("unit_code", $unit_code)
    			->when($key, function($q, $key){
    				$q->where(DB::raw("SHA1(id)"), "!=", $key);
    			});
    	return $model->count();
    }

    public function delete(Request $request){
    	$status = 200;
    	$response["error_code"] = "000";
    	$response["error_message"] = "ok";
    	$key = ($request->input("key")) ? trim($request->input("key")) :null;
    	try {
    		$model = Units::where(DB::raw("SHA1(id)") , $key)->delete();
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
    		$model = Units::where(DB::raw("SHA1(id)") , $key);
    		$data = $model->get()->first();
    		if($data){
	    		$response["payload"] = [
	    			"key" => sha1($data->id),
	    			"unit_code" => $data->unit_code,
	    			"unit_name" => $data->unit_name,
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
