<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 50)->unique(); // INV2025-0001
            $table->foreignId('outlet_id')->constrained('outlets');
            $table->foreignId('table_id')->nullable()->constrained('cafe_tables');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->foreignId('cashier_id')->constrained('users'); // user kasir

            $table->dateTime('order_date');
            $table->enum('order_type', ['dine_in', 'take_away', 'delivery'])->default('dine_in');
            $table->enum('status', ['draft', 'paid', 'void'])->default('draft');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);

            $table->string('payment_status', 20)->default('unpaid'); // unpaid/partial/paid
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};


