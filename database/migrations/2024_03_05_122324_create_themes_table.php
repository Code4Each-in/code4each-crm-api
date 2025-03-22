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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('preview_image')->nullable();
            $table->text('category')->nullable();
            $table->bigInteger('primary_font');
            $table->bigInteger('secondary_font')->nullable();
            $table->bigInteger('tertiary_font')->nullable();
            $table->bigInteger('default_color')->nullable();
            $table->string('demo_url')->nullable();
            $table->enum('status',['active','deactive','draft','testing'])->default('active');
            $table->enum('accessibility',['paid','free'])->default('free');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('themes');
    }
};
