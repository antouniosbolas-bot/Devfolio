<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('selected_for_profile')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_fork')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->string('category')->default('personal');
            $table->longText('readme_excerpt')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'selected_for_profile',
                'is_archived',
                'is_fork',
                'is_featured',
                'category',
                'readme_excerpt',
            ]);
        });
    }
};
