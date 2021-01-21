<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePendingTransaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("pending_transactions", function(Blueprint $table) {
            $table->increments("id");
            $table->integer("warehouse_id")->nullable()->unsigned();
            $table->integer("user_id")->nullable()->unsigned();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign("warehouse_id")->references("id")->on("warehouses")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
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
