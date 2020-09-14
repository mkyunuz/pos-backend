<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\JournalEntries;
use App\Models\Accounts;
class JournalEntriesDetail extends Model
{
    protected $table = "journal_entries_detail";
    protected $fillable = ["journal_id", "account_id", "description", "entry_type", "amount"];
    protected $guarded = [];

    public function journal(){
    	return $this->belongsTo(JournalEntries::class, "journal_id", "id");
    }
    public function account(){
    	return $this->belongsTo(Accounts::class, "account_id", "id");
    }
}
