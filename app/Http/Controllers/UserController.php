<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\AppHelpers;
use App\User;
use App\Repositories\UserRepo;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\UserHasWarehouse;

class UserController extends Controller
{
	private $response;
    public function __construct(){
    	$this->response = new AppHelpers();
    }

    public function index(Request $request){
    	$payload = null;
    	$limit = $request->limit ?? 100;
    	$page = (int) $request->page ?? 1;
    	$query = ($request->input("query")) ? (array) $request->input("query") : [];
    	$sort = $request->sort ?? null;
    	$search = $request->search ?? null;
    	$no_pagination = $request->no_pagination ?? true;
    	$offset = 0;
    	if($page > 0 ){
    		$offset = $page -1;
    	}
    	$offset = $limit * $offset;
    	try {
	    	$model = User::where("id", "!=", null);
	    	$users = $model->get();
            $totalRecords = $users->count();
            $totalPage = ceil($totalRecords / $limit);
	    	$payload = [];
            $payload["rows"] = [];
            if($no_pagination == true){
	            $payload["pagination"]["pageSize"] = $limit;
	            $payload["pagination"]["total"] = $totalPage;
	            $payload["pagination"]["current"] = $page;
	            $payload["pagination"]["totalRecords"] = $totalRecords;
            }
            $payload["rows"] = $users->map(function($data){
            	return [
            		"id" => sha1($data->id),
            		"name" => $data->name,
            		"email" => $data->email,
            	];
            });
    	} catch (\Exception $e) {
           return AppHelpers::error($e, 500);
    	}

		return $this->response->json($payload, 200);

    }
    

    public function view(Request $request){

    	$payload = null;
    	try {
    		$id = $request->id ?? null;
    		$model = UserRepo::findByIdEncrypted($id);
    		if($model){
    			$payload = [
    				"id" => sha1($model->id),
    				"name" => $model->name,
    				"email" => $model->email,
    				"role" => $model->roles->first()->name,
    				"warehouses" => $model->warehouses->map(function($data){
    					return sha1($data->warehouse_id);
    				}),
    				"created_at" => $model->created_at->format("d-m-Y H:i:s"),
    			];
    		}

    	} catch (\Exception $e) {
    		return AppHelpers::error($e, 500);
    	}
    	return $this->response->json($payload, 200);
    }
    public function getAllRole(Request $request){
    	$payload = null;
    	try {
    		$payload = Role::all();
    	} catch (\Exception $e) {
    		return AppHelpers::error($e, 500);
    	}
    	return $this->response->json($payload, 200);
    }
    public function addRole(Request $request){
    	try {
    		$id = $request->id ?? null;
    		$user = UserRepo::findByIdEncrypted($id);
    	} catch (\Exception $e) {
    		
    	}
    }
    public function save(Request $request){
    	$payload = null;
    	$userHasWarehouse = new UserHasWarehouse();
    	$warehouses = $request->warehouses ?? null;
    	if($warehouses) $warehouses =explode(",", $warehouses);
    	$id = $request->id ?? null;
    	
    	DB::beginTransaction();
    	try {
    		$role = $request->role ?? null;
    		$model = User::where(DB::raw("sha1(id)"), $request->id);
    		$user = $model->get()->first();
    		
    		$model->update([
    			"name" => $request->name,
    			"email" => $request->email,
    		]);
    		if( $user ){
    			if($role) $user->syncRoles($role);
    			$payload = $request->id;
    			$userHasWarehouse->sync($user->id, $warehouses);
    		}
    		DB::commit();
    		$payload = sha1($user->id);
    	} catch (\Exception $e) {
    		DB::rollBack();
    		return AppHelpers::error($e, 500);
    	}
    	return $this->response->json($payload, 200);
    }

    public function checkEmail(Request $request){
    	$id = $request->id ?? null;
    	return AppHelpers::uniqueCheck(User::class, $request, "email", "id");
    }
    public function info(){
    	$payload = null;
    	try {
    		$id = $request->id ?? null;
    		$model = auth()->user();
    		// return $model;
    		if($model){
    			$payload = [
    				"name" => $model->name,
    				"email" => $model->email,
    				"role" => $model->roles->first()->name,
    				"warehouses" => $model->warehouses->map(function($data){
    					return [ 
    						"warehouse_id" => sha1($data->warehouse_id),
    						"warehouse_name" => $data->warehouse->warehouse_name,
    					];
    				}),
    				"created_at" => $model->created_at->format("d-m-Y H:i:s"),
    			];
    		}


    	} catch (\Exception $e) {
    		return AppHelpers::error($e, 500);
    	}
    	return $this->response->json($payload, 200);
    }
    public function getRoles(Request $request){
    	$payload=null;
    	try {
    		$payload = auth()->user()->permissions->map(function($data){
    			return [$data->name];
    		});
    	} catch (\Exception $e) {
    		return AppHelpers::error($e, 500);
    	}
    	return $this->response->payload($payload, 200);
    }

    public function syncRole(Request $request){

    }
}
