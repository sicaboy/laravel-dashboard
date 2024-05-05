<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_two_dimension', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('graph_key')->index();
            $table->string('column_x');
            $table->string('column_y');
            $table->string('value');

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
        Schema::dropIfExists('data_two_dimension');
    }
}
