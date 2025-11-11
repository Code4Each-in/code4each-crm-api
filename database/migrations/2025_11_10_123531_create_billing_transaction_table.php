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
        Schema::create('billing_transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_billing_id');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('order_id')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->longText('payment_details')->nullable();
            $table->longText('card_details')->nullable();
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
        Schema::dropIfExists('billing_transaction');
    }
};
