<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPurchaseOrder2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("purchase_order", function(Blueprint $table){
            $table->string("company", 100)->nullable()->after("remark");
            $table->text("supplier_address")->after("company");
            $table->string("phone_number", 100)->after("supplier_address");
            $table->text("shipping_to")->after("phone_number");
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
