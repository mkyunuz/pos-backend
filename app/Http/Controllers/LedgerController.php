<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use \App\Helpers\AppHelpers;
use App\Models\JournalEntries;
use App\Models\JournalEntriesDetail;
use App\Models\Accounts;
class LedgerController extends Controller
{
	
	public function index(Request $request){
		$response = AppHelpers::getInstance();
		$payload = null;
		/*$response["payload"] = Accounts::has("joutnalDetail")->get()->map(function($data){
			return [
				"id" => $data->id,
				"account_name" => $data->name,
				"details" => $data->joutnalDetail->map(function($data) {
					return [
						"transaction_date" => $data->journal->transaction_date,
						"entry_type" => $data->entry_type,
						"amount" => $data->amount,
					];
				})
			];
		});*/
		try {
			// $model = JournalEntries::all();
			$payload = Accounts::has("joutnalDetail")->get()->map(function($data){
				return [
					"id" => $data->id,
					"account_name" => $data->name,
					"details" => JournalEntriesDetail::select("transaction_date", "account_id", "entry_type", DB::raw("sum(amount) as amount"))->where("account_id", $data->id)->groupBy("transaction_date", "account_id", "entry_type")->get()->map(function($data){
						return [
							"transaction_date" => $data->transaction_date,
							"entry_type" => $data->entry_type,
							"amount" => $data->amount,
						];
					})
				];
			});
		} catch (\Exception $e) {
			return AppHelpers::error($e, 500);
		}
		return $response->json($payload, 200);
	}

}
