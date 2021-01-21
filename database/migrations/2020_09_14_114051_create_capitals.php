<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCapitals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("capitals", function(Blueprint $table){
            $table->increments("id");
            $table->float("amount", 18,2);
            $table->float("balance", 18,2);
            $table->enum("entry_type", ["D", "C"]);
            $table->date("entry_date")->nullable();
            $table->integer("user_id")->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign("user_id")->references("id")->on("users");
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
