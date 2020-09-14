<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAccountsAddCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("accounts", function(Blueprint $table){
           
            $table->enum("category", ["Harta", "Hutang", "Modal", "Pendapatan", "Beban", "Pendapatan Lain-lain", "Beban Lain-lain"])->after("name");
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
