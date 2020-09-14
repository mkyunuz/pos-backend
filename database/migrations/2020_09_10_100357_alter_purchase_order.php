<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPurchaseOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("purchase_order", function(Blueprint $table){
            $table->date("transaction_date")->nullable()->after("shipping_costs");
        });
        Schema::table("purchase_order_detail", function(Blueprint $table){
            $table->dropColumn(["shipping_costs"]);
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
