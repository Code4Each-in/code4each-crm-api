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
            $table->unsignedBigInteger('parent_id')->nullable()->after('component_id');
            $table->unsignedBigInteger('group_id')->nullable()->after('parent_id');
            $table->foreign('parent_id')->references('id')->on('component_form_fields')->onDelete('set null');
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
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id','group_id']);
        });
    }
};
