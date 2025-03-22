<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('current_plan', function (Blueprint $table) {
            $table->id();
            $table->integer('agency_id'); 
            $table->integer('website_id');
            $table->integer('plan_id'); 
            $table->date('website_start_date'); 
            $table->boolean('status')->default(1);
            $table->integer('planexpired')->default(15);
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
        Schema::dropIfExists('current_plan');
    }
};
