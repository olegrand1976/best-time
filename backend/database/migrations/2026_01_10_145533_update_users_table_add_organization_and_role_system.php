<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we need to drop the existing enum and create a new one
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("DROP TYPE IF EXISTS users_role_enum");

        // Add organization_id and other fields first
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->string('phone')->nullable()->after('email');
            $table->string('employee_number')->nullable()->after('phone');
            $table->date('hire_date')->nullable()->after('employee_number');
            $table->boolean('is_active')->default(true)->after('hire_date');
        });

        // Change role column to new enum values
        DB::statement("ALTER TABLE users ALTER COLUMN role TYPE varchar(255)");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'responsable', 'ouvrier', 'team_leader'))");
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'ouvrier'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn(['organization_id', 'phone', 'employee_number', 'hire_date', 'is_active']);
        });

        // Revert role to old enum
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ALTER COLUMN role TYPE varchar(255)");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'employee'))");
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'employee'");
    }
};
