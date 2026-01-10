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
     * Ajoute le champ encoded_by_user_id pour tracer qui a encodé le timesheet.
     * Si null, c'est l'utilisateur lui-même qui a encodé.
     * Si rempli, c'est un responsable d'équipe ou responsable qui a encodé à sa place.
     */
    public function up(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->foreignId('encoded_by_user_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User who encoded this entry (null if encoded by the user themselves)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropForeign(['encoded_by_user_id']);
            $table->dropColumn('encoded_by_user_id');
        });
    }
};
