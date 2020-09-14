<?php

namespace App\Repositories;
use App\Products;
use Illuminate\Support\Facades\DB;
class ProductRepo{

	public static function filter($search = null, $query = [], $sort = [], $limit = 10, $offset = 0, $no_pagination = false){
		$model = Products::where("id", "!=", "");
    	$model = Products::where("id", "!=", "");
    	if(is_array($query)){

    	}

    	if(is_array($query) && count($query) > 0){
    		$allow_filter = ["product_name"];
    		foreach ($query as $key => $value) {
    			if(in_array($key, $allow_filter)){
    				$model->where($key, "like", "%".$value."%");
    			}
    		}
    	}
    	if(is_array($sort) && count($sort) > 0){
            $allow_sort = array_merge($allow_filter, ["created_at", "updated_at"]);
    		$dir = isset($sort["dir"]) ? $sort["dir"]  : "desc";
    		$column = isset($sort["column"]) ? $sort["column"] : "created_at";
            if(in_array($column, $allow_sort) && in_array($dir, ["asc, desc"])){
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
    		return $query->where("product_name", "like", "%".$search."%")
    				->orWhere("product_name", "like", "%".$search."%")
    				->orWhere("product_code", "like", "%".$search."%")
    				->orWhereHas("categories", function($query) use ($search) {
    					$query->where("category_name", "like", "%".$search."%");
    				});
    	})->get()->map(function($data, $index ) use(&$no){
    		$unit = $data->_unit;
    		$units = [["unit_id" => sha1($unit->id), "unit_name" => $unit->unit_name]];
    		$conversions = $data->productConversions->map(function($data) use ($unit){
				return [
					"unit_id" => sha1($data->unit_id),
					"unit_name" => $data->unit->unit_name,
				];
			})->toArray();
			try {
				$units = array_merge($units, $conversions);
			} catch (\Exception $e) {
				
			}
    		return [
				"no" => $no++, 
		        "key" => sha1($data->id), 
		        "product_code" => $data->product_code, 
				"product_name" => $data->product_name, 
				"units" => $units, 
				"ppn" => $data->ppn, 
		        "category" => $data->categories->category_name, 
		        "purchase_price" => $data->purchase_price, 
		        "status" => $data->status, 
				"created" => $data->created_at->format('d M Y H:i:s'), 
				"last_modified" => $data->updated_at->format('d M Y H:i:s'), 
    		];
    	});
    	return ["totalRecords" => $totalRecords, "data" => $data];
	}

	public static function findByKey($key){
		$model = Products::where(DB::raw("SHA1(id)") , $key);
		$data = $model->get()->first();
		if($data){
    		$data = [
    			"key" => sha1($data->id),
    			"product_name" => $data->product_name,
    			"product_code" => $data->product_code,
    			"category" => $data->category,
    			"unit" => $data->unit,
    			"ppn" => $data->ppn,
    			"suppliers" => $data->suppliers->map(function($data){
    				return sha1($data->supplier_id);
    			}),
    			"purchase_price" => $data->purchase_price,
    			"status" => $data->status,
    			"barcode" => $data->barcode,
    			"description" => $data->description,
    			"prices" => $data->prices,
    			"conversions" => $data->productConversions,
                "remark" => $data->remark,
    			"created" => $data->created_at->format('d M Y H:i:s'),
    			"last_modified" => $data->updated_at->format('d M Y H:i:s'),
    		];
		}

		return $data ?? null;
	}
}