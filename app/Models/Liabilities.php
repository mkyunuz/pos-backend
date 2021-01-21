<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Liabilities extends Model
{
	protected $fillable = ["description", "amount", "transaction_date", "user_id"];
    protected $guraded=[];
    protected $table="liabilities";

    public function liabilities(){
    	return $this->morphTo();
    }
}
