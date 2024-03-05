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
        Schema::table('agency_websites', function (Blueprint $table) {
            $table->string('others_category_name')->nullable()->after('website_category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agency_websites', function (Blueprint $table) {
            $table->dropColumn(['others_category_name']);
        });
    }
};
