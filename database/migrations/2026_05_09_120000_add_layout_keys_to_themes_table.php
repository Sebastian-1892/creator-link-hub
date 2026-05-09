<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->string('button_style', 32)->default('pill');
            $table->string('background_style', 32)->default('solid');
            $table->string('font_family', 32)->default('figtree');
            $table->string('card_style', 32)->default('flat');
            $table->string('template_group', 32)->default('general');
        });
    }

    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropColumn([
                'button_style',
                'background_style',
                'font_family',
                'card_style',
                'template_group',
            ]);
        });
    }
};
