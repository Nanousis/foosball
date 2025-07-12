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
        // Add elo change columns to the games table
        Schema::table('games', function (Blueprint $table) {
            $table->integer('winner1_elo_change')->nullable();
            $table->integer('winner2_elo_change')->nullable();
            $table->integer('loser1_elo_change')->nullable();
            $table->integer('loser2_elo_change')->nullable();
        });

        // Add stats columns to the players table
        Schema::table('players', function (Blueprint $table) {
            $table->unsignedInteger('games_played')->default(0);
            $table->unsignedInteger('total_score')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'winner1_elo_change',
                'winner2_elo_change',
                'loser1_elo_change',
                'loser2_elo_change',
            ]);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn(['games_played', 'total_score']);
        });
    }
};
