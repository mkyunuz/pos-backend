<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTablePaymentHistoryAddColumnPaidAmountAndDue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("payment_history",function(Blueprint $table){
            $table->float("paid_amount", 18,2)->unsigned()->default(0)->after("balance");
            $table->float("due", 18,2)->unsigned()->default(0)->after("paid_amount");
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
