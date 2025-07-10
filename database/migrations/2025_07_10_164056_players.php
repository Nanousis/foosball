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
        //
        Schema::create("players", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string("name", 20);
            $table->string("avatar")->nullable(); // Allow null for avatar
            $table->unsignedTinyInteger("wins")->nullable();
            $table->unsignedTinyInteger("losses")->nullable();
            $table->unsignedTinyInteger("elo")->nullable();
            $table->timestamps(); // Created at and updated at timestamps
            $table->softDeletes(); // Soft delete column
            $table->unique("name"); // Ensure player names are unique
            $table->index("name"); // Index for faster lookups by name
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("players");
    }
};
