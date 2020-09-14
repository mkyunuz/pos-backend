<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProductHasSuppliers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create("product_has_suppliers", function(Blueprint $table){
            $table->integer("product_id")->unsigned();
            $table->integer("supplier_id")->unsigned();
            $table->primary(["product_id","supplier_id"]);
            $table->foreign("product_id")->references("id")->on("products");
            $table->foreign("supplier_id")->references("id")->on("suppliers");
            $table->softDeletes();
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
