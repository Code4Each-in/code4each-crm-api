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
            $table->text('meta_key1')->nullable()->after('default_value');
            $table->text('meta_key2')->nullable()->after('meta_key1');
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
            $table->dropColumn(['meta_key1','meta_key2']);
        });
    }
};
