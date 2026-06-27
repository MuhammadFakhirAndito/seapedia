<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->constrained()->onDelete('cascade');
            $table->enum('delivery_method', ['instant', 'next_day', 'regular']);

            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->foreignId('voucher_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('promo_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('delivery_fee', 12, 2);
            $table->decimal('ppn_amount', 12, 2);
            $table->decimal('total_amount', 12, 2);

            $table->enum('status', [
                'sedang_dikemas', 'menunggu_pengirim', 'sedang_dikirim',
                'pesanan_selesai', 'dikembalikan',
            ])->default('sedang_dikemas');

            $table->boolean('is_refunded')->default(false);
            $table->boolean('is_income_reversed')->default(false);
            $table->boolean('is_stock_restored')->default(false);

            $table->timestamp('sla_due_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->string('product_name_snapshot');
            $table->decimal('price_snapshot', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
