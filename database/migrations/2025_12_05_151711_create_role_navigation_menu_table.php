<?php
// database/migrations/2025_01_01_000300_create_role_navigation_menu_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_navigation_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles');
            $table->foreignId('navigation_menu_id')->constrained('navigation_menus');

            $table->boolean('can_view')->default(true);
            $table->timestamps();

            $table->unique(['role_id', 'navigation_menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_navigation_menu');
    }
};

