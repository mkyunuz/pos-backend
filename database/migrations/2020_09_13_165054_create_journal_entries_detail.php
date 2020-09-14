<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournalEntriesDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists("journal_entries_detail");
        Schema::create("journal_entries_detail", function(Blueprint $table){
            $table->increments("id");
            $table->integer("journal_id")->unsigned();
            $table->integer("account_id")->unsigned();
            $table->string("description")->nullable();
            $table->enum("entry_type", ["D", "C"]);
            $table->float("amount", 18,2);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign("journal_id")->references("id")->on("journal_entries");
            $table->foreign("account_id")->references("id")->on("accounts");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
