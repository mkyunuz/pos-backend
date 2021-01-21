<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\User;

class UserRepo
{
	
	public static function findByIdEncrypted($id){
		try {
			$model = User::where(DB::raw("sha1(id)"), $id)->get()->first();
			return $model;
		} catch (\Exception $e) {
			return null;
		}
	}
}