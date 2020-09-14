<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("purchase_order", function(Blueprint $table){
            $table->increments('id');
            $table->string("po_number", 15)->nullable();
            $table->integer("supplier_id")->unsigned();
            $table->string("payment_method", 50);
            $table->text("remark")->nullable();
            $table->integer("user_id")->unsigned();
            $table->integer("close")->unsigned();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign("supplier_id")->references("id")->on("suppliers");
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
