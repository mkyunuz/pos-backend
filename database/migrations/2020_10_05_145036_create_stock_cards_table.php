<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('warehouse_id')->unsigned()->nullable();
            $table->integer('product_id')->unsigned()->nullable();
            $table->enum("type", ["in", "out"])->nullable();
            $table->float("qty", 7,2)->default(0);
            $table->float("sock", 7,2)->default(0);
            $table->date('transaction_date')->nullable();
            $table->time('transaction_time', 0)->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign("product_id")->references("id")->on("products");
            $table->foreign("warehouse_id")->references("id")->on("warehouses");
            $table->foreign("user_id")->references("id")->on("users");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_cards');
    }
}
