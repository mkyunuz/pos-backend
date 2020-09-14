<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableJournalEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("journal_entries", function(Blueprint $table){
            $table->increments("id");
            $table->string("transaction_id", 15);
            $table->date("transaction_date");
            $table->integer("account_id")->unsigned();
            $table->string("description")->nullable();
            $table->enum("entry_type", ["D", "C"]);
            $table->float("amount", 18,2);
            $table->integer("user_id")->unsigned();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign("user_id")->references("id")->on("users");
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
