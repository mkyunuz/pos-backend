<?php
namespace App\Repositories;

use App\Models\Customers;
use Illuminate\Support\Facades\DB;
class CustomersRepo {

	public static function generateCustomerNumber(){
		$model = Customers::select(DB::raw("max(customer_id) as last"))->get()->first();
		$next = 1;
		if($model->last){
			$next += $model->last;
		}
		$customer_number = $next = str_pad($next, 6, '0', STR_PAD_LEFT);
		return$customer_number;
	}
	public static function filter($search = null, $query = [], $sort = [], $limit = 10, $offset = 0, $no_pagination = false){

		$model = Customers::where("id", "!=", "");

    	$allow_filter = [];
    	if(is_array($query) && count($query) > 0){
    		foreach ($query as $key => $value) {
    			if(in_array($key, $allow_filter)){
    				$model->where($key, "like", "%".$value."%");
    			}
    		}
    	}
            
    	if(is_array($sort) && count($sort) > 0){
            $allow_sort = array_merge($allow_filter, ["created_at", "updated_at", "name", "registered_at", "phone_number", "address"]);
    		$dir = isset($sort["dir"]) ? $sort["dir"]  : "desc";
    		$column = isset($sort["column"]) ? $sort["column"] : "created_at";
            if(in_array($column, $allow_sort) && in_array($dir, ["asc", "desc"])){
            	if($column == "registered_at"){
            		$column = "created_at";
            	}
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
    		return $query->where("name", "like", "%".$search."%")
    				->orWhere("address", "like", "%".$search."%");
    	})->get()->map(function($data, $index ) use(&$no){
			
    		return [
				"no" => $no++, 
		        "key" => sha1($data->id), 
		        "name" => $data->name, 
		        "phone_number" => $data->phone_number, 
		        "address" => $data->address, 
		        "status" => $data->status, 
				"registered_at" => $data->created_at->format('d M Y'), 
				"created" => $data->created_at->format('d M Y H:i:s'), 
				"updated_at" => $data->updated_at->format('d M Y H:i:s'), 
    		];
    	});
    	return ["totalRecords" => $totalRecords, "data" => $data];
	}

	public static function findByIdEncrypted($key){
		
		try {
			$model = Customers::where(DB::raw("sha1(id)"), $key)->get()->first();
			return $model;
		} catch (\Exception $e) {
			return null;
		}
	}
}