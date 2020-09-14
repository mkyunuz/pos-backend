<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiModuleGenerator extends Controller
{
    public function __construuct(){

    }

    public function index(){
    	return view("api-module-generator.amg-index");
    }
    
    public function generate(Request $request){
    	$module_name = ($request->input("module_name")) ? : null;
    	$colum_name = ($request->input("colum_name")) ? : null;
    	$colum_type = ($request->input("colum_type")) ? : null;
    	$length = ($request->input("length")) ? : null;
    	$primary = ($request->input("primary")) ? : null;
    	$controller_name = ($request->input("controller_name")) ? : null;
    	$controller_path = ($request->input("controller_path")) ? : null;
    	$route_group = ($request->input("route_group")) ? : null;
    	$visible = ($request->input("visible")) ? : null;
    	$auto_increment = ($request->input("auto_increment")) ? : null;
    	$relation = ($request->input("relation")) ? : null;
    	$relation_table = ($request->input("relation_table")) ? : null;
    	$relation_key = ($request->input("relation_key")) ? : null;
    	$unique = ($request->input("unique")) ? : null;
    	$nullable = ($request->input("nullable")) ? : null;
    	$searchable = ($request->input("searchable")) ? : null;
    	$module["table_name"] = $module_name;
    	$module["columns"] = [];
    		// print($relation);
    	foreach ($colum_name as $key => $value) {
    		$tmpRelation = isset($relation[$key]) ? true : false;
    		$relationData = null;
    		if($tmpRelation){
    			$relationData = [
    				"table" => isset($relation_table[$key]) ? $relation_table[$key] : null,
    				"key" => isset($relation_key[$key]) ? $relation_key[$key] : null,
    			];
    		}
    		$tmp = [
    			"name" => $value,
    			"type" => $colum_type[$key],
    			"primary" => isset($primary[$key]) ? true : null,
    			"length" => $length[$key],
    			"auto_increment" => isset($auto_increment[$key]) ? true : null,
    			"relation" => $relationData,
    			"nullable" => isset($nullable[$key]) ? true : null,
    			"unique" => isset($unique[$key]) ? true : null,
    			"visible" => isset($visible[$key]) ? true : null,
    			"searchable" => isset($searchable[$key]) ? true : null,
    		];
    		array_push($module["columns"], $tmp);
    	}
    	return $module;
    }
}
