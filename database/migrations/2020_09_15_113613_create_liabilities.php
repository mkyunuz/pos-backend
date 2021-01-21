<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLiabilities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("liabilities", function(Blueprint $table){
            $table->increments('id');
            $table->text("description")->nullable();
            $table->float("amount", 18,2);
            $table->integer("liabilities_id");
            $table->string("liabilities_type");
            $table->integer("user_id")->unsigned();
            $table->softDeletes();
            $table->timestamps();
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
