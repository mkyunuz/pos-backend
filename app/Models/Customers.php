<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;

class Customers extends Model
{
    use SoftDeletes;
    protected $table = "customers";
    protected $fillable = ["name", "customer_id", "address", "phone_number", "user_id", "status"];
    public static function rules(){
    	return [
	    	"customer_name" => "required",
	    	"customer_address" => "required",
	    	"phone_number" => "required",
	    	"status" => "required",
	    ];
	}

	public function user(){
		return $this->hasOne(User::class);
	}


}
