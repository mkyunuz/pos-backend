<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Equility;
use \App\Helpers\AppHelpers;
use App\Suppliers;
use App\Units;
use App\Warehouses;
use App\Models\PurchaseDetail;
use App\Repositories\PurchaseRepo;
use App\Models\JournalEntries;
class EquilityController extends Controller
{
	    	
	
	public function index(Request $request){
		
	}
	public function save(Request $request){
    	$response = AppHelpers::getInstance();
		$payload = null;
    	$status = 200;
		$validator = Validator::make($request->all(), Equility::$rules);
		if($validator->fails()){
			return $response->setMessage($validator->errors())
					->setErrorCode("403")
					->json($payload, 403);
		} 

		
		DB::beginTransaction();
		$due_date = "";
		$user_id = auth()->user()->id;
		try {
			$amount = $request->amount;
			$key = $request->key ?? null;;
			$entry_type = $request->entry_type;
			$entry_date = NULL;
			if($request->entry_date != "undefined") $entry_date = new \DateTime( $request->entry_date);
			if(!$key){
				$model = Equility::create([
					"amount" => $amount,
					"balance" => 0,
					"entry_type" => $entry_type,
					"entry_date" => $entry_date,
					"user_id" => $user_id,
				]);
				$journalId = $model->id;
				$journal = $model->journals()->create([
					"transaction_id" => date("Ymdhis"),
					"transaction_date" => $entry_date,
					"user_id" => $user_id,
				]);
				if($entry_type == "C"){
					$journal->details()->create([
						"journal_id" => $journalId,
						"account_id" => 7,
						"entry_type" => "C",
						"amount" => $amount,
					]);
					$journal->details()->create([
						"journal_id" => $journalId,
						"account_id" => 1,
						"entry_type" => "D",
						"amount" => $amount,
					]);

				}else{
					$journal->details()->create([
						"journal_id" => $journalId,
						"account_id" => 1,
						"entry_type" => "D",
						"amount" => $amount,
					]);
					$journal->details()->create([
						"journal_id" => $journalId,
						"account_id" => 7,
						"entry_type" => "C",
						"amount" => $amount,
					]);
				}

			}else{
				
			}
			$payload = sha1($journalId);
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			return AppHelpers::error($e, 500);
		}
		return $response->json($payload, 200);
	}

	public function view(Request $request){
		$response["error_code"] = "000";
    	$response["error_message"] = "ok";
		try{
			$model = PurchaseRepo::findByIdEncrypted($request->key);
    		$response["payload"] = $model;
		} catch(\Exception $e){
			return AppHelpers::error($e, 500);
		}
		return $response->json($payload, 200);
	}

}
