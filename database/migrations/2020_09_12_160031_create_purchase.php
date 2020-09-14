<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("purchase", function(Blueprint $table){
            $table->increments('id');
            $table->string("purchase_number", 15);
            $table->string("po_number", 15)->nullable();
            $table->integer("supplier_id")->unsigned();
            $table->float("total_ppn", 18,2)->unsigned();
            $table->float("grand_total", 18,2)->unsigned();
            $table->float("gt_after_ppn", 18,2)->unsigned();
            $table->string("payment_method", 50);
            $table->date("due_date")->nullable();
            $table->float("shipping_costs", 18,2)->nullable();
            $table->date("transaction_date")->nullable();
            $table->text("remark")->nullable();
            $table->string("company", 100)->nullable();
            $table->text("supplier_address");
            $table->string("phone_number", 100)->nullable();
            $table->text("shipping_to");
            $table->integer("user_id")->unsigned();
            $table->integer("close")->unsigned();
            $table->softDeletes();
            $table->timestamps();
            $table->integer("warehouse_id")->nullable()->unsigned();
            $table->foreign("supplier_id")->references("id")->on("suppliers");
            $table->foreign("user_id")->references("id")->on("users");
            $table->foreign("warehouse_id")->references("id")->on("warehouses");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
