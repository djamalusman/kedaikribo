<?php

// database/migrations/2025_01_01_000200_create_navigation_menus_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('navigation_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()
                ->constrained('navigation_menus'); // submenu

            $table->string('name', 100);           // nama tampil
            $table->string('route_name', 150)->nullable(); // nama route Laravel
            $table->string('url', 255)->nullable();        // kalau pakai url custom
            $table->string('icon', 100)->nullable();       // icon class
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_menus');
    }
};
