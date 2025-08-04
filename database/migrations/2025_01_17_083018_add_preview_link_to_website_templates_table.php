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
        Schema::table('website_templates', function (Blueprint $table) {
            $table->string('preview_link')->nullable()->default(null)->after('category_id'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('website_templates', function (Blueprint $table) {
            $table->dropColumn('preview_link');
        });
    }
};
