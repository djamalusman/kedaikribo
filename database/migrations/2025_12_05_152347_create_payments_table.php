<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->enum('payment_method', ['cash', 'qris', 'transfer']);
            $table->decimal('amount', 12, 2);
            $table->string('ref_no', 100)->nullable(); // no referensi transaksi QRIS/transfer
            $table->dateTime('paid_at');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

