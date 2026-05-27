<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->string('link_type', 20)->default('link')->after('profile_id');
            $table->foreignId('parent_link_id')
                ->nullable()
                ->after('link_type')
                ->constrained('links')
                ->cascadeOnDelete();
            $table->string('image_url')->nullable()->after('url');
            $table->index(['profile_id', 'parent_link_id', 'position'], 'links_profile_parent_position_idx');
        });
    }

    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropIndex('links_profile_parent_position_idx');
            $table->dropConstrainedForeignId('parent_link_id');
            $table->dropColumn(['link_type', 'image_url']);
        });
    }
};
