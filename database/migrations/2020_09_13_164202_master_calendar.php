<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MasterCalendar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("master_calendar", function(Blueprint $table){
            $table->increments("id");
            $table->date("calendar_date");
            $table->enum("holliday", ["Y", "N"]);
            $table->enum("public_holliday", ["Y", "N"]);
            $table->text("desctiption")->nullable();
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
        //
    }
}
