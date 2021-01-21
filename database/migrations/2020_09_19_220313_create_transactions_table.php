<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string("transaction_number", 15);
            $table->float("total_ppn", 18,2)->unsigned();
            $table->float("grand_total", 18,2)->unsigned();
            $table->float("gt_after_ppn", 18,2)->unsigned();
            $table->string("payment_method", 50);
            $table->date("due_date")->nullable();
            $table->float("shipping_costs", 18,2)->nullable();
            $table->float("services", 18,2)->nullable();
            $table->date("transaction_date")->nullable();
            $table->text("remark")->nullable();
            $table->integer("customer_id")->unsigned()->nullable();
            $table->string("phone_number", 15)->nullable();
            $table->text("shipping_to")->nullable();
            $table->integer("user_id")->unsigned();
            $table->integer("close")->unsigned();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign("customer_id")->references("id")->on("customers")->onDelete("cascade");
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
        Schema::dropIfExists('transactions');
    }
}
