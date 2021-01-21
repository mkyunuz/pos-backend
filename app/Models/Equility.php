<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Equility extends Model
{
    protected $table = "equility";

    public static $rules = [
    	"amount" => "required",
    	"entry_type" => "required",
    ];
    protected $fillable = [
    	"amount",
    	"balance",
    	"entry_type",
    	"entry_date",
    	"user_id",
    ];
}
