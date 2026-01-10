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
     * Table pivot pour la relation many-to-many entre ouvriers et responsables.
     * Un ouvrier peut avoir plusieurs responsables.
     */
    public function up(): void
    {
        Schema::create('user_responsables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ouvrier_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('responsable_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Ensure unique combination
            $table->unique(['ouvrier_id', 'responsable_id']);
            
            // Index for performance
            $table->index('ouvrier_id');
            $table->index('responsable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_responsables');
    }
};
