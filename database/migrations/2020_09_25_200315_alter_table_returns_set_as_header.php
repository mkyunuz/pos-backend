<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableReturnsSetAsHeader extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("returns", function(Blueprint $table){
            $table->dropForeign("returns_product_id_foreign");
            $table->dropColumn(["product_id", "qty"]);
            $table->float("amount", 18,2)->default(0)->after("transaction_id");
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
