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
        Schema::create('social_post_platforms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('social_post_id')->nullable();
            $table->string('platform')->nullable();
            $table->enum('status', ['pending', 'posted', 'failed'])->default('pending');
            $table->text('response')->nullable();
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
        Schema::dropIfExists('social_post_platforms');
    }
};
