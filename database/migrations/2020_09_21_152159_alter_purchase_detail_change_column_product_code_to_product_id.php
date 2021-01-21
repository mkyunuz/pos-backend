<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPurchaseDetailChangeColumnProductCodeToProductId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("purchase_detail", function(Blueprint $table){
            $table->dropColumn(["product_code"]);
        });
        Schema::table("purchase_detail", function(Blueprint $table){
            $table->integer("product_id")->unsigned()->nullable()->after("purchase_id");
            $table->foreign("product_id")->references("id")->on("products")->onDelete("cascade");
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
