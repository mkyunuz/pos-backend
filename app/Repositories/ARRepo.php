<?php
namespace App\Repositories;

use App\AccountReceivable;

class ARRepo
{
	
	public static function filter($search = null, $query = [], $sort = [], $limit = 10, $offset = 0, $no_pagination = false){
		$model = AccountReceivable::where("id", "!=", "");
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
    	$data = $model->get()->map(function($data, $index ) use(&$no){
    		
			$liabilities_type = $data->liabilities_type;
			$liabilities_reff=explode("\\", $liabilities_type);
			$liabilities_reff = end($liabilities_reff);
			$transaction_id = null;
			$transaction_amount = 0;
			$paid = $data->account_receivables->payments->sum("amount");
			$transaction_amount = $data->account_receivables->gt_after_ppn + $data->account_receivables->total_ppn;
			
			$remaining= $transaction_amount - $paid;
    		return [
				"no" => $no++, 
		        "key" => sha1($data->id), 
		        "transaction_date" => $data->transaction_date, 
				"amount" => $transaction_amount, 
                "debit" => $data->amount, 
				"transaction_number" => $data->account_receivables->transaction_number, 
				"description" => $data->description, 
				"remaining" => $remaining, 
				"created" => $data->created_at->format('d M Y H:i:s'), 
				"last_modified" => $data->updated_at->format('d M Y H:i:s'), 
    		];
    	});
    	return ["totalRecords" => $totalRecords, "data" => $data];
	}
}