<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use \App\Categories;

class CategoriesController extends Controller
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
    					// return var_dump($query);
    	// return ($request->input("limit"));
    	try {
	    	$model = Categories::where("category_id", "!=", "");
	    	if(is_array($query)){

	    	}
	    	if(is_array($query)){
	    		$allow_filter = ["category_name", "category_id"];
	    		foreach ($query as $key => $value) {
	    			if(in_array($key, $allow_filter)){
	    				$model->where($key, "like", "%".$value."%");
	    			}
	    		}
	    	}

	    	if(is_array($sort)){
	    		$dir = isset($sort["dir"]) ? $sort["dir"]  : "desc";
	    		$column = is_array($sort["column"]) ? $sort["column"] : "created_at";
	    		$model->orderBy($column, $dir);
	    	}
	    	$totalRecords = $model->count();
	    	$model->limit($limit);
	    	$model->offset($offset);
	    	$data = $model->get();
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
	    			"key" => sha1($key->category_id), 
	    			"category_id" => $key->category_id, 
	    			"category_name" => $key->category_name, 
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
    	$category_id = ($request->input("category_id")) ? trim($request->input("category_id")) :null;
    	$category_name = ($request->input("category_name")) ? trim($request->input("category_name")) :null;
    	$current_id = ($request->input("current_id")) ? trim($request->input("current_id")) :null;
    	
    	if(!$category_name){
    		$response["error_code"] = "422";
    		$response["error_message"] = "Invalid category name";
    		return response()->json($response, 400)->withHeaders([
                'Content-Type' => "application/json",
            ]);
    	}
    	if (!$category_id) {
    		$response["error_code"] = "422";
    		$response["error_message"] = "Invalid category name";
    		return response()->json($response, 400)->withHeaders([
                'Content-Type' => "application/json",
            ]);
    	}

    	$checkCategoryId = $this->checkCategoryId($request);
    	if($checkCategoryId > 0){
			$response["error_code"] = "422";
			$response["error_message"] = "category code exist";
			return response()->json($response, 400)->withHeaders([
	            'Content-Type' => "application/json",
	        ]);
    	}
    	if(!$current_id){

	    	try {
	    		$status = 200;
	    		$model = new Categories();
	    		$model->category_id = $category_id;	
	    		$model->category_name = $category_name;
	    		$model->save();
	    		$response["payload"] = sha1($model->category_id);
	    	} catch (\Exception $e) {
	    		$status = 500;
	    		$response["error_code"] = $status;
	    		$response["error_message"] = $e->getMessage();
	    	}
	    }else{
	    	try {
	    		$status = 200;
	    		$model = Categories::where(DB::raw("SHA1(category_id)") , $current_id)->update([
	    			"category_id" => $category_id,
	    			"category_name" => $category_name,
	    		]);
	    		$response["payload"] = sha1($category_id);
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
    
    public function checkCategoryId(Request $request){
    	$category_id = ($request->input("category_id")) ? trim($request->input("category_id")) :null;
    	$current = ($request->input("current_id")) ? trim($request->input("current_id")) :null;
    	$model = Categories::where("category_id", $category_id)
    			->when($current, function($q, $current){
    				$q->where(DB::raw("SHA1(category_id)"), "!=", $current);
    			});
    	return $model->count();
    }

    public function update(Request $request){
    	$response["error_code"] = "000";
    	$response["error_message"] = "ok";
    	$current_id = ($request->input("current_id")) ? trim($request->input("current_id")) :null;
    	$category_id = ($request->input("category_id")) ? trim($request->input("category_id")) :null;
    	$category_name = ($request->input("category_name")) ? trim($request->input("category_name")) :null;
    	
    	if(!$category_name){
    		$response["error_code"] = "422";
    		$response["error_message"] = "Invalid category name";
    		return response()->json($response, 400)->withHeaders([
                'Content-Type' => "application/json",
            ]);
    	}
    	if (!$category_id) {
    		$response["error_code"] = "422";
    		$response["error_message"] = "Invalid category name";
    		return response()->json($response, 400)->withHeaders([
                'Content-Type' => "application/json",
            ]);
    	}
    	if (!$current_id) {
    		$response["error_code"] = "422";
    		$response["error_message"] = "Invalid category id";
    		return response()->json($response, 400)->withHeaders([
                'Content-Type' => "application/json",
            ]);
    	}

    	$checkCategoryId = $this->checkCategoryId($request);
    	if($checkCategoryId > 0){
			$response["error_code"] = "422";
			$response["error_message"] = "category code exist";
			return response()->json($response, 400)->withHeaders([
	            'Content-Type' => "application/json",
	        ]);
    	}

    	try {
    		$status = 200;
    		$model = Categories::where(DB::raw("SHA1(category_id)") , $current_id)->update([
    			"category_id" => $category_id,
    			"category_name" => $category_name,
    		]);
    		$response["payload"] = sha1($category_id);
    	} catch (\Exception $e) {
    		$status = 500;
    		$response["error_code"] = $status;
    		$response["error_message"] = $e->getMessage();
    	}

		return response()->json($response, $status)->withHeaders([
            'Content-Type' => "application/json",
        ]);
    }

    public function delete(Request $request){
    	$status = 200;
    	$response["error_code"] = "000";
    	$response["error_message"] = "ok";
    	$category_id = ($request->input("category_id")) ? trim($request->input("category_id")) :null;
    	try {
    		$model = Categories::where(DB::raw("SHA1(category_id)") , $category_id)->delete();
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
    	$category_id = ($request->input("key")) ? trim($request->input("key")) :null;
    	try {
    		$model = Categories::where(DB::raw("SHA1(category_id)") , $category_id);
    		$data = $model->get()->first();
    		if($data){
	    		$response["payload"] = [
	    			"key" => sha1($data->category_id),
	    			"category_id" => $data->category_id,
	    			"category_name" => $data->category_name,
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
