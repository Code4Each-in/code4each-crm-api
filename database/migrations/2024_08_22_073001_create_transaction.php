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
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id');
            $table->string('order_id');
            $table->integer('plan_id');
            $table->integer('user_id');
            $table->integer('website_id');
            $table->integer('agency_id');
            $table->decimal('amount', 10, 2);
            $table->string('signature');
            $table->boolean('is_refunded')->default(0);
            $table->decimal('refunded_amount', 10, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction');
    }
};
