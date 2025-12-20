<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// create_cafe_tables_table.php
return new class extends Migration {
    public function up(): void
    {
        Schema::create('cafe_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets');
            $table->string('name', 50); // contoh: Meja 1
            $table->string('code', 20)->nullable(); // T1, T2, dsb
            $table->integer('capacity')->default(1);
            $table->enum('status', ['available', 'occupied', 'reserved'])->default('available');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('cafe_tables');
    }
};

