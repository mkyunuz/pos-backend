<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\JournalEntries;
class Calendar extends Model
{
    protected $table = "master_calendar";

    public function getJournalEntries(){
    	$this->hasMany(JournalEntries::class, "transaction_date", "str_date");
    }
}
