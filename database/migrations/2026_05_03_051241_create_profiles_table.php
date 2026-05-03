<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('theme_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('display_name');
            $table->text('bio')->nullable();
            $table->string('avatar_path')->nullable();
            $table->json('theme_variables')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index(['workspace_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
