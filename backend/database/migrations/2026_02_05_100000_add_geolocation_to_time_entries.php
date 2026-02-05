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
        Schema::table('time_entries', function (Blueprint $table) {
            // Geolocation fields
            $table->decimal('latitude', 10, 8)->nullable()->after('duration');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->float('location_accuracy')->nullable()->after('longitude')->comment('GPS accuracy in meters');
            $table->timestamp('location_captured_at')->nullable()->after('location_accuracy');
            
            // QR code tracking
            $table->boolean('qr_code_scanned')->default(false)->after('location_captured_at');
            
            // Index for location-based queries
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropColumn([
                'latitude',
                'longitude',
                'location_accuracy',
                'location_captured_at',
                'qr_code_scanned',
            ]);
        });
    }
};
