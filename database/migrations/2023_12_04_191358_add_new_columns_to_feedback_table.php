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
        Schema::table('feedback', function (Blueprint $table) {
            $table->string('title')->nullable()->after('type');
            $table->unsignedBigInteger('website_id')->nullable()->after('user_id');
            $table->unsignedBigInteger('agency_id')->nullable()->after('website_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropColumn(['title','website_id','agency_id']);
        });
    }
};
