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
        Schema::create('rating_progress', function (Blueprint $table) {
            $table->id();
            $table->date('current_rating_day')->nullable(false);
            $table->timestamps();
        });

        DB::table('rating_progress')->insert([
            'current_rating_day' => '2025-07-07',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_progress');
    }
};
