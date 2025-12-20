<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients');
            $table->foreignId('outlet_id')->constrained('outlets');

            $table->enum('movement_type', ['in', 'out', 'adjust']);
            $table->decimal('qty', 12, 3);
            $table->string('reference_type', 50)->nullable(); // Purchase, Order, Adjustment
            $table->string('reference_no', 100)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

