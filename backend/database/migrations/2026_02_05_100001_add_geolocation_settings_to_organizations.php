<?php

declare(strict_types=1);

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
        Schema::table('organizations', function (Blueprint $table) {
            // Geolocation settings
            $table->boolean('location_required')->default(false)->after('name')
                ->comment('Whether location capture is required for time entries');
            
            // Geofencing settings
            $table->boolean('geofencing_enabled')->default(false)->after('location_required')
                ->comment('Whether geofencing validation is enabled');
            $table->integer('geofencing_radius')->nullable()->after('geofencing_enabled')
                ->comment('Geofencing radius in meters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'location_required',
                'geofencing_enabled',
                'geofencing_radius',
            ]);
        });
    }
};
