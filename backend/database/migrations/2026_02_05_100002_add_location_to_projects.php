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
        Schema::table('projects', function (Blueprint $table) {
            // Project location for geofencing
            $table->decimal('latitude', 10, 8)->nullable()->after('status')
                ->comment('Project location latitude for geofencing');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude')
                ->comment('Project location longitude for geofencing');
            $table->integer('geofence_radius')->nullable()->after('longitude')
                ->comment('Project-specific geofence radius in meters (overrides organization default)');
            
            // QR code data
            $table->string('qr_code_token', 64)->nullable()->unique()->after('geofence_radius')
                ->comment('Unique token for QR code generation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'geofence_radius',
                'qr_code_token',
            ]);
        });
    }
};
