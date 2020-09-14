<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("accounts", function(Blueprint $table){
            $table->integer("id")->unsigned()->unique();
            $table->string("name", 50);
            $table->text("description")->nullable();
            $table->integer("user_id")->unsigned();
            $table->softDeletes();
            $table->timestamps();
            $table->primary("id");
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
