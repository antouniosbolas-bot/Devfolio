<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('github_id');
            $table->string('name');
            $table->string('full_name');
            $table->text('description')->nullable();
            $table->string('html_url');
            $table->string('clone_url')->nullable();
            $table->string('default_branch')->nullable();
            $table->string('language')->nullable();
            $table->unsignedInteger('stars')->default(0);
            $table->unsignedInteger('forks')->default(0);
            $table->boolean('is_private')->default(false);
            $table->timestamp('pushed_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'github_id']);
            $table->index(['user_id', 'synced_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
