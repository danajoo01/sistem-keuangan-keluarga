<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_menu_access', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->foreignId('menu_list_id')->constrained('menu_list')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role', 'menu_list_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_menu_access');
    }
};
