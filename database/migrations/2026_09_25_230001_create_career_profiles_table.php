<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('headline')->nullable();
            $table->text('summary')->nullable();
            $table->json('experience')->nullable();
            $table->json('education')->nullable();
            $table->json('certifications')->nullable();
            $table->json('cv_project_ids')->nullable();
            $table->string('analysis_status')->default('not_started');
            $table->timestamp('analyzed_at')->nullable();
            $table->string('target_role')->nullable();
            $table->string('cv_language', 12)->default('en');
            $table->string('cv_template', 32)->default('classic');
            $table->string('shared_token', 64)->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_profiles');
    }
};
