<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("transaction_id")->unsigned();
            $table->integer("product_id")->unsigned();
            $table->float("qty", 5,2)->detault(0);
            $table->float("hpp", 18,2)->detault(0);
            $table->float("price", 18,2)->detault(0);
            $table->float("discount", 5,2)->detault(0);
            $table->float("subtotal", 18,2)->detault(0);
            $table->integer("user_id")->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign("transaction_id")->references("id")->on("transactions")->onDelete("cascade");
            $table->foreign("product_id")->references("id")->on("products")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('returns');
    }
}
