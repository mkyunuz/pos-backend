<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStockCardsRenameColumnSock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("stock_cards", function(Blueprint $table){
            $table->dropColumn("sock");

        });
        Schema::table("stock_cards", function(Blueprint $table){
            $table->float("stock", 7,2)->default(0)->after("qty");

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
