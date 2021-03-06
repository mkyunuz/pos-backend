<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableReturnDetailChangeHppFormatThrd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("return_details", function(Blueprint $table){
            $table->dropColumn("hpp");
        });
        Schema::table("return_details", function(Blueprint $table){
            $table->float("hpp", 18, 2)->default(0)->after("qty");
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
