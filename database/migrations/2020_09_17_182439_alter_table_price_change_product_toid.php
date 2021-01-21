<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTablePriceChangeProductToid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('prices', function (Blueprint $table) {
            $table->dropColumn(["product"]);
        });
        Schema::table('prices', function (Blueprint $table) {
            $table->integer("product")->unsigned()->after("id");
            $table->foreign("product")
                    ->references("id")
                    ->on("products")
                    ->onDelete("cascade")
                    ->onUpdate("cascade");
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
