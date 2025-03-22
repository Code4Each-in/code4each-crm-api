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
        Schema::create('theme_dependencies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('theme_id')->constrained();
            $table->string('name');
            $table->string('type');
            $table->string('path');
            $table->string('version');
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
        Schema::dropIfExists('theme_dependencies');
    }
};
