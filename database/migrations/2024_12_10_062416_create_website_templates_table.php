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
        Schema::create('website_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');
            $table->string('category_id');
            $table->string('featured_image')->nullable();
            $table->enum('status', ['active', 'deactive', 'draft', 'testing'])->default('active');
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
        Schema::dropIfExists('website_templates');
    }
};
