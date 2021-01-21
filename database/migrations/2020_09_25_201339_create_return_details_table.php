<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReturnDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("return_id")->unsigned()->nullable();
            $table->integer("product_id")->unsigned()->nullable();
            $table->float("qty", 5, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign("return_id")->references("id")->on("returns");
            $table->foreign("product_id")->references("id")->on("products");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('return_details');
    }
}
