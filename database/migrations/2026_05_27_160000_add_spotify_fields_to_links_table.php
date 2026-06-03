<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->string('provider', 32)->nullable()->after('preset_key');
            $table->string('provider_id', 64)->nullable()->after('provider');
            $table->string('provider_resource_type', 32)->nullable()->after('provider_id');
            $table->boolean('is_dynamic')->default(false)->after('provider_resource_type');
            $table->string('cached_title')->nullable()->after('is_dynamic');
            $table->string('cached_artist')->nullable()->after('cached_title');
            $table->string('cached_image', 2048)->nullable()->after('cached_artist');

            $table->index(['provider', 'is_dynamic']);
        });
    }

    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropIndex(['provider', 'is_dynamic']);
            $table->dropColumn([
                'provider',
                'provider_id',
                'provider_resource_type',
                'is_dynamic',
                'cached_title',
                'cached_artist',
                'cached_image',
            ]);
        });
    }
};
