<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            $table->decimal('total_amount', 10, 2)->change();

            $table->string('status')->default('pending')->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::table('order_items', function (Blueprint $table) {

            $table->unsignedBigInteger('order_id')->nullable(false)->change();
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->integer('quantity')->default(1)->change();
            $table->decimal('price', 10, 2)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
