<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id')->unsigned();
            $table->string("product_code", 20);
            $table->integer("qty")->unsigned();
            $table->integer("unit_id")->unsigned();
            $table->float("price", 18,2);
            $table->float("discount", 5,2);
            $table->float("subtotal", 18,2);
            $table->float("ppn", 5,2);
            $table->integer("user_id")->unsigned();
            $table->foreign("user_id")->references("id")->on("users");
            $table->foreign("transaction_id")->references("id")->on("transactions");
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->float("discount", 18,2)->after("grand_total")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_details');
    }
}
