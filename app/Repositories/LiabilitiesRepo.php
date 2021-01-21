<?php
namespace App\Repositories;

use App\Models\Liabilities;

class LiabilitiesRepo
{
	
	public static function filter($search = null, $query = [], $sort = [], $limit = 10, $offset = 0, $no_pagination = false){
		$model = Liabilities::where("id", "!=", "");
    	if(is_array($query)){

    	}

    	if(is_array($query) && count($query) > 0){
    		$allow_filter = [];
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
    	/*$data = $model->when($search, function($query, $search){
    		return $query->where("product_name", "like", "%".$search."%")
    				->orWhere("product_name", "like", "%".$search."%")
    				->orWhere("product_code", "like", "%".$search."%")
    				->orWhereHas("categories", function($query) use ($search) {
    					$query->where("category_name", "like", "%".$search."%");
    				});
    	})*/
    	$data = $model->get()->map(function($data, $index ) use(&$no){
    		/*$unit = $data->_unit;
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
				
			}*/
			$liabilities_type = $data->liabilities_type;
			$liabilities_reff=explode("\\", $liabilities_type);
			$liabilities_reff = end($liabilities_reff);
			$transaction_id = null;
			$transaction_amount = 0;
			$paid = $data->liabilities->payments->sum("amount");
			if($liabilities_reff == "Purchase"){
				$transaction_id = $data->liabilities->purchase_number;
				$transaction_amount = $data->liabilities->gt_after_ppn;
			}
			$due= $transaction_amount - $paid;
    		return [
				"no" => $no++, 
		        "key" => sha1($data->id), 
		        "transaction_date" => $data->transaction_date, 
				"amount" => $data->amount, 
				"transaction_id" => $data->liabilities->purchase_number, 
				"description" => $data->description, 
				"due" => $due, 
				"created" => $data->created_at->format('d M Y H:i:s'), 
				"last_modified" => $data->updated_at->format('d M Y H:i:s'), 
    		];
    	});
    	return ["totalRecords" => $totalRecords, "data" => $data];
	}
}