<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table pour gérer les responsables d'équipe (team_leader).
     * Un team_leader (ouvrier) peut encoder les timesheets pour d'autres ouvriers.
     */
    public function up(): void
    {
        Schema::create('team_leaders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_leader_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ouvrier_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Ensure unique combination
            $table->unique(['team_leader_id', 'ouvrier_id']);
            
            // Index for performance
            $table->index('team_leader_id');
            $table->index('ouvrier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_leaders');
    }
};
