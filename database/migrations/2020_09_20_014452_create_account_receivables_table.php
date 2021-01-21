<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountReceivablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_receivables', function (Blueprint $table) {
            $table->increments('id');
            $table->text("description")->nullable();
            $table->date("transaction_date");
            $table->float("amount", 18,2);
            $table->integer("account_receivable_id");
            $table->string("account_receivable_type");
            $table->integer("user_id")->unsigned();
            $table->softDeletes();
            $table->timestamps();
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
        Schema::dropIfExists('account_receivables');
    }
}
