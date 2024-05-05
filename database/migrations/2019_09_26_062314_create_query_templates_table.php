<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueryTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_query_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('template_key')->unique();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->text('sql')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('app_query_select', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_id')->unsigned()->nullable();
            $table->string('title')->nullable();
            $table->string('field')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->foreign('template_id')->references('id')->on('app_query_templates');
        });

        Schema::create('app_query_variables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_id')->unsigned()->nullable();
            $table->string('label')->nullable();
            $table->string('variable')->nullable();
            $table->integer('required')->nullable();
            $table->string('field')->nullable();
            $table->string('operator')->nullable();
            $table->string('type')->nullable();
            $table->string('default')->nullable();
            $table->string('related_table')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('template_id')->references('id')->on('app_query_templates');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_query_variables');
        Schema::dropIfExists('app_query_select');
        Schema::dropIfExists('app_query_templates');
    }
}
