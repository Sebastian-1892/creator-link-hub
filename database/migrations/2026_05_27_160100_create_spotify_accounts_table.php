<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spotify_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('spotify_user_id', 64);
            $table->text('encrypted_access_token');
            $table->text('encrypted_refresh_token');
            $table->timestamp('expires_at');
            $table->timestamp('last_sync_at')->nullable();
            $table->string('connection_status', 32)->default('connected');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spotify_accounts');
    }
};
