<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePendingTransactionsDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pending_transactions_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("pending_transaction_id")->unsigned();
            $table->integer("qty")->unsigned();
            $table->integer("unit_id")->unsigned();
            $table->float("price", 18,2)->default(0);
            $table->float("discount", 18,2)->default(0);
            $table->float("subtotal", 18,2)->default(0);
            $table->integer("product_id")->unsigned();
            $table->foreign("pending_transaction_id")->references("id")->on("pending_transactions")->onDelete("cascade");
            $table->foreign("unit_id")->references("id")->on("units")->onDelete("cascade");
            $table->foreign("product_id")->references("id")->on("products")->onDelete("cascade");
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
        Schema::dropIfExists('pending_transactions_details');
    }
}
