<?php

namespace App\Helpers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AppHelpers{
	private static $http_code = 200;
	private static $error_message = "ok";
	private static $payloads = [];
	private $error_code = "000";
	// private static $error_message = "OK";
	// private $payload = null;
	private static $instance=null;
	public function __construct(){

	}
	public static function error(\Exception $e, $http_code = 500){
		$error_message = $e->getMessage();
		$error_line = $e->getLine();
		$error_file = $e->getFile();
		$response["error_code"] = $http_code;
		$response["error_message"] = $error_message . " - " .$error_file . " at " .$error_line;
		$response["payload"] = null;
		return response()->json($response, $http_code)->withHeaders(['Content-Type' => "application/json"]);
	}
	public static function getInstance(){
		if(!self::$instance){
			self::$instance = new AppHelpers();
		}
		return self::$instance; 
	}
	public function setMessage($message){
		self::$error_message=$message;
		return $this;
	}
	public function setErrorCode($error_code){
		$this->error_code = $error_code;
		return $this;
	}
	public function json($payload = null, $status = 200){
		$response = [
			"error_code" => $this->error_code, 
			"error_message" => self::$error_message, 
		]; 
		if($payload){
			$response["payload"] = $payload;
		}
		return response()->json($response, $status)->withHeaders([
            'Content-Type' => "application/json",
        ]);;
	}

	public function __toString()
    {
        return "ok";
    }

	public static function uniqueCheck($class, Request $request, $column, $column_id = null){
		$id = $request->{$column_id} ?? null;
		$input = $request->{$column} ?? null;
		$model = $class::where($column, $input);
		if($id){
			$model->where(DB::raw("sha1(".$column_id.")"), "!=", $id);
		}
		return $model->count();
	}
}