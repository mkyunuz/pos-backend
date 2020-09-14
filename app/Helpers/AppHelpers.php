<?php

namespace App\Helpers;

use Illuminate\Http\Response;
class AppHelpers{
	private static $http_code = 200;
	private static $error_message = "ok";
	private static $payloads = [];
	public static function error(\Exception $e, $http_code = 500){
		$error_message = $e->getMessage();
		$error_line = $e->getLine();
		$error_file = $e->getFile();
		$response["error_code"] = $http_code;
		$response["error_message"] = $error_message . " - " .$error_file . " at " .$error_line;
		$response["payload"] = null;
		return response()->json($response, $http_code)->withHeaders(['Content-Type' => "application/json"]);
	}

	
}