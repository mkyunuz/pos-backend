<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\AppHelpers;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
	private $response;
    public function __construct(){
    	$this->response = new AppHelpers();
    }

    public function index(Request $request){
    	$payload = null;
        try {
            $payload = Role::where("name", "!=", "")->orderBy("name", "asc")->get()->map(function($data){
                return [
                    "id" => sha1($data->id),
                    "name" => $data->name,
                ];
            });
        } catch (\Exception $e) {
            return AppHelpers::error($e, 500);
        }
        return $this->response->json($payload, 200);

    }
    

   
}
