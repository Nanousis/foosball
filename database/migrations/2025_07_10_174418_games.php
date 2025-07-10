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
        Schema::create("games", function (Blueprint $table) {
            $table->bigIncrements("id");
            // Foreign keys to players table
            $table->unsignedBigInteger("winner1_id");
            $table->unsignedBigInteger("winner2_id");
            $table->unsignedBigInteger("loser1_id");
            $table->unsignedBigInteger("loser2_id");
            $table->unsignedTinyInteger("winner_score")->nullable();
            $table->unsignedTinyInteger("loser_score")->nullable();
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at
            // Define foreign key constraints
            $table->foreign("winner1_id")->references("id")->on("players")->onDelete("cascade");
            $table->foreign("winner2_id")->references("id")->on("players")->onDelete("cascade");
            $table->foreign("loser1_id")->references("id")->on("players")->onDelete("cascade");
            $table->foreign("loser2_id")->references("id")->on("players")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
