<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("responsable_gestionnaires", function (Blueprint $table) {
            $table->id();
            $table->foreignId("responsable_id")->constrained("users")->onDelete("cascade");
            $table->foreignId("gestionnaire_id")->constrained("users")->onDelete("cascade");
            $table->timestamps();
            $table->unique(["responsable_id", "gestionnaire_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("responsable_gestionnaires");
    }
};
