<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\JournalEntries;
class PaymentHistory extends Model
{
	protected $table = "payment_history";
	protected $guarded = [];
    public function payment(){
    	return $this->morphTo();
    }

    public function journals(){
    	return $this->morphMany(JournalEntries::class, "journalable");
    }
}
