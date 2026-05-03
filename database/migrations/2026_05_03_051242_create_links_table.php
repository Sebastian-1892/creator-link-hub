<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('url');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('opens_in_new_tab')->default(true);
            $table->boolean('tracking_enabled')->default(true);
            $table->timestamps();

            $table->index(['profile_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
