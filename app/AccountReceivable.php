<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountReceivable extends Model
{
    	protected $fillable = ["description", "amount", "transaction_date", "user_id"];
        protected $guraded=[];
        protected $table="account_receivables";

        public function account_receivables(){
        	return $this->morphTo();
        }
}
