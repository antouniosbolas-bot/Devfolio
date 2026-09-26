<?php

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
        Schema::table('users', function (Blueprint $table) {
            $table->string('github_id')->nullable()->unique()->after('email');
            $table->string('github_username')->nullable()->index()->after('github_id');
            $table->string('avatar_url')->nullable()->after('github_username');
            $table->text('bio')->nullable()->after('avatar_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['github_id']);
            $table->dropIndex(['github_username']);
            $table->dropColumn(['github_id', 'github_username', 'avatar_url', 'bio']);
        });
    }
};
