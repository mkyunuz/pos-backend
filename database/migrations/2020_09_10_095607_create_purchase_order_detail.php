<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrderDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("purchase_order_detail", function(Blueprint $table){
            $table->increments('id');
            $table->integer('purchase_order_id');
            $table->string("product_code", 20);
            $table->integer("qty")->unsigned();
            $table->integer("unit_id")->unsigned();
            $table->float("discount", 5,2);
            $table->float("subtotal", 18,2);
            $table->float("taxs", 5,2);
            $table->float("shipping_costs", 18,2);
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
