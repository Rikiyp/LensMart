<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perketat unique constraint reviews: sertakan customer_id.
     * Constraint lama (product_id, order_id) sudah cukup karena satu order
     * dimiliki satu customer, tapi menambah customer_id membuat intent lebih
     * eksplisit dan melindungi dari edge case jika data order berubah.
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Hapus constraint lama sebelum membuat yang baru.
            $table->dropUnique(['product_id', 'order_id']);

            // Constraint baru: satu ulasan per (produk x order x pelanggan).
            $table->unique(
                ['product_id', 'order_id', 'customer_id'],
                'reviews_product_order_customer_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique('reviews_product_order_customer_unique');
            $table->unique(['product_id', 'order_id']);
        });
    }
};
