<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items');
            $table->foreignId('ingredient_id')->constrained('ingredients');
            $table->decimal('qty', 12, 3); // berapa gram/ml per 1 porsi
            $table->string('note', 150)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};

