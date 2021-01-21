<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class PaymentHistory extends Model
{
	protected $table = "payment_history";
	protected $guarded = [];
    public function payment(){
    	return $this->morphTo();
    }
}
