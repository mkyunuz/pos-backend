<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\JournalEntriesDetail;
class JournalEntries extends Model
{
    protected $table = "journal_entries";
    // protected $fillable = ["transaction_id", "transaction_date", "account_id", "description", "entry_type", "amount", "user_id"];
    protected $guarded = [];
    public function journalable(){
    	return $this->morphTo();
    }

    public function details(){
    	return $this->hasMany(JournalEntriesDetail::class, "journal_id", "id");
    }
}
