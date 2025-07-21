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
        Schema::table('players', function (Blueprint $table) {
            $table->float('rating')->default(1500);
            $table->float('rd')->default(350);
            $table->float('volatility')->default(0.06);
            $table->float('last_displayed_rating')->default(1500);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn(['rating', 'rd', 'volatility', 'last_displayed_rating']);
        });
    }
};
