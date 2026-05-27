<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('header_layout', 32)->default('classic')->after('theme_variables');
            $table->string('banner_image_path')->nullable()->after('header_layout');
            $table->string('wallpaper_image_path')->nullable()->after('banner_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['header_layout', 'banner_image_path', 'wallpaper_image_path']);
        });
    }
};
