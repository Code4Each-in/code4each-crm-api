<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
                $table->unsignedBigInteger('user_id')->nullable()->change();
                DB::statement("ALTER TABLE feedback MODIFY COLUMN type ENUM('review','complaint','feedback','suggestion','inquiry')");
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
            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            DB::statement("ALTER TABLE feedback MODIFY COLUMN type ENUM('review','complaint','feedback','suggestion')");
        });
    }
};
