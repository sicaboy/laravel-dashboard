<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDashboardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('app_dashboard', function(Blueprint $table) {
            $table->increments('id');
            $table->string('from_date')->default('-3 months');
            $table->string('to_date')->default('today');
            $table->string('title', 255);
            $table->string('type', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        /*Schema::create('app_dashboard_role', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('dashboard')->unsigned();
            $table->integer('role')->unsigned();

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('dashboard')->references('id')->on('app_dashboard');
            $table->foreign('role')->references('id')->on('app_role');
        });
        */
        Schema::create('app_dashboard_section', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('dashboard_id')->unsigned()->nullable();
            $table->string('title', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->integer('order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('dashboard_id')->references('id')->on('app_dashboard');
        });


        Schema::create('app_dashboard_chart_group', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('section_id')->unsigned();
            $table->string('title');
            $table->string('description', 255)->nullable();
            $table->text('options')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('section_id')->references('id')->on('app_dashboard_section');
        });

        Schema::create('app_dashboard_chart', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('chart_group_id')->unsigned();
            $table->string('title');
            $table->string('description', 255)->nullable();
            $table->string('type');
            $table->string('showif')->default(1);
            $table->string('tab')->nullable();
            $table->text('options')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('chart_group_id')->references('id')->on('app_dashboard_chart_group');
        });

        Schema::create('app_dashboard_chart_filter', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('chart_id')->unsigned()->nullable();
            $table->string('title', 255);
            $table->integer('order');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('chart_id')->references('id')->on('app_dashboard_chart');
        });

        Schema::create('app_dashboard_chart_series', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('chart_id')->unsigned();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('query_template_key')->nullable();
            $table->string('variable')->nullable();
            $table->text('sql')->nullable();
            $table->text('extra')->nullable();
            $table->string('showif')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('chart_id')->references('id')->on('app_dashboard_chart');
        });

        Schema::create('app_dashboard_chart_field', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('chart_series_id')->unsigned();
            $table->string('field');
            $table->string('title');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('chart_series_id')->references('id')->on('app_dashboard_chart_series');
        });


        Schema::create('app_dashboard_search_field', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('dashboard_id')->unsigned()->nullable();
            $table->string('field', 255);
            $table->string('label', 255);
            $table->string('hint')->nullable();
            $table->string('hint_extra')->nullable();
            $table->boolean('required');
            $table->string('rules', 2000);
            $table->string('type', 255);
            $table->integer('length');
            $table->string('params', 2000);
            $table->integer('order');
            $table->string('related_table')->nullable();
            $table->boolean('readonly')->default(0);
            $table->string('trigger_on_change', 2000)->nullable();
            $table->string('trigger_on_change_query', 2000)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('dashboard_id')->references('id')->on('app_dashboard');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_dashboard_search_field');
        Schema::dropIfExists('app_dashboard_chart_field');
        Schema::dropIfExists('app_dashboard_chart_series');
        Schema::dropIfExists('app_dashboard_chart_filter');
        Schema::dropIfExists('app_dashboard_chart');
        Schema::dropIfExists('app_dashboard_chart_group');
        Schema::dropIfExists('app_dashboard_section');
        Schema::dropIfExists('app_dashboard');
    }
}
