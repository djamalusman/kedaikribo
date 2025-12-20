<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->nullable()->constrained('outlets');
            $table->string('name', 150);
            $table->enum('type', ['percent', 'nominal']); // diskon %
            $table->decimal('value', 12, 2);              // 10 => 10%
            $table->decimal('min_amount', 12, 2)->default(0); // min transaksi
            $table->boolean('is_active')->default(true);
            $table->boolean('is_loyalty')->default(false); // promo khusus loyalty?
            $table->integer('min_orders')->default(0);     // min jumlah transaksi utk loyalty
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
