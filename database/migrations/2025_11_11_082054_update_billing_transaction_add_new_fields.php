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
        Schema::table('billing_transaction', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('user_billing_id')->nullable();
            $table->unsignedBigInteger('agency_id')->after('plan_id')->nullable();
            $table->unsignedBigInteger('website_id')->after('agency_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('billing_transaction', function (Blueprint $table) {
             $table->dropColumn(['user_id', 'agency_id', 'website_id']);
        });
    }
};
