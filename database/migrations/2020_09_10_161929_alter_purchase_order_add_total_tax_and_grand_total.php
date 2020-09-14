<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPurchaseOrderAddTotalTaxAndGrandTotal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("purchase_order", function(Blueprint $table){
            $table->float("total_ppn", 18,2)->after("supplier_id")->deafult(0);
            $table->float("grand_total", 18,2)->after("total_ppn")->deafult(0);
            $table->float("gt_after_ppn", 18,2)->after("grand_total")->deafult(0);
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
