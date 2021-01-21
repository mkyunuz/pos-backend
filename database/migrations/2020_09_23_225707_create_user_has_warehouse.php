<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserHasWarehouse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("user_has_warehouse", function($table){
            $table->increments("id");
            $table->integer("user_id")->unsigned()->nullable();
            $table->integer("warehouse_id")->unsigned()->nullable();
            $table->integer("modified_by")->unsigned()->nullable();
            $table->timestamps();
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("modified_by")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("warehouse_id")->references("id")->on("warehouses")->onDelete("cascade");
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
