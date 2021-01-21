<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use \App\Helpers\AppHelpers;
use App\Models\JournalEntries;
use App\Models\Accounts;
class JournalController extends Controller
{
	    	
	public function index(Request $request){
		$response = AppHelpers::getInstance();
		$payload = null;
		try {
			
			$model = JournalEntries::all();
			$payload = $model->map(function($data) {
				return [
					"date" => $data->transaction_date,
					"details" => $data->details->map(function($data){
						return [
							"account" => $data->account->name,
							"amount" => $data->amount,
							"entry_type" => $data->entry_type
						];
					})
				];
			});
		} catch (\Exception $e) {
			AppHelpers::error($e, 500);
		}
		return $response->json($payload);
	}

	public function bukubesar(Request $request){
		$response = AppHelpers::getInstance();
		$payload = null;
		try {
			
			$model = JournalEntries::all();
			$payload = Accounts::has("joutnalDetail")->get()->map(function($data){
				return [
					"id" => $data->id,
					"account_name" => $data->name,
					"details" => $data->joutnalDetail->map(function($data) {
						return [
							"entry_type" => $data->entry_type,
							"amount" => $data->amount,
						];
					})
				];
			});
		} catch (\Exception $e) {
			return AppHelpers::error($e, 500);
		}
		return $response->json($payload);
	}

}
