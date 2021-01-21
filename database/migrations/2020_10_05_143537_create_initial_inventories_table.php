<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInitialInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('initial_inventories', function (Blueprint $table) {
            $table->integer("warehouse_id")->unsigned();
            $table->integer("product_id")->unsigned();
            $table->float("qty", 7,2)->default(0);
            $table->primary(["warehouse_id", "product_id"]);
            $table->foreign("product_id")->references("id")->on("products");
            $table->foreign("warehouse_id")->references("id")->on("warehouses");
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
        Schema::dropIfExists('initial_inventories');
    }
}
