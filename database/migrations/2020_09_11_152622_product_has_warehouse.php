<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProductHasWarehouse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create("product_has_warehouse", function(Blueprint $table){
            $table->increments("id");
            $table->integer("product_id")->unsigned();
            $table->integer("warehouse_id")->unsigned();
            $table->float("stock", 18,2)->default(0);
            $table->softDeletes();
            $table->foreign("product_id")->references("id")->on("products");
            $table->foreign("warehouse_id")->references("id")->on("warehouses");
            $table->timestamps();
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
