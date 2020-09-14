<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->increments('id');
            $table->string("product", 20);
            $table->string("price_group", 10);
            $table->integer("unit")->unsigned();
            $table->float("qty", 10,2);
            $table->float("price", 18,2)->default(0);
            $table->enum("status", ["active", "inactive"])->default("active");
            $table->timestamps();
            $table->foreign("unit")
                    ->references("id")
                    ->on("units")
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
        Schema::dropIfExists('prices');
    }
}
