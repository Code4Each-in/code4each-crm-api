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
        Schema::create('component_area', function (Blueprint $table) {
            $table->id();
            $table->integer('component_id')->constrained();
            $table->string('area_name');
            $table->double('x_axis', 8, 2);
            $table->double('y_axis', 8, 2);
            $table->double('area_width', 8, 2);
            $table->double('area_height', 8, 2);
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
        Schema::dropIfExists('component_area');
    }
};
