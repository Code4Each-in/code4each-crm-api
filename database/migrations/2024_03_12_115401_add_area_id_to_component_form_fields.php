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
        Schema::table('component_form_fields', function (Blueprint $table) {
            $table->integer('area_id')->default('0')->constrained()->after('component_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('component_form_fields', function (Blueprint $table) {
            $table->dropColumn('area_id');
        });
    }
};
