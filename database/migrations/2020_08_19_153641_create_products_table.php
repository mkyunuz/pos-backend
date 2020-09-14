<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string("product_code", 20);
            $table->string("product_name", 150);
            $table->string("category", 10);
            $table->integer("unit")->unsigned();
            $table->text("description");
            $table->float("purchase_price", 18,2);
            $table->float("ppn", 5,2);
            $table->enum("status", ["active", "inactive"])->default("active");
            $table->text("remark");
            $table->text("barcode");
            $table->foreign("unit")
                    ->references("id")
                    ->on("units")
                    ->onDelete("cascade")
                    ->onUpdate("cascade");

            $table->foreign("category")
                    ->references("category_id")
                    ->on("categories")
                    ->onDelete("cascade")
                    ->onUpdate("cascade");
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
        Schema::dropIfExists('products');
    }
}
