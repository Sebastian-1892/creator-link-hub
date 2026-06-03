<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class SpotifyAccount extends Model
{
    protected $fillable = [
        'workspace_id',
        'spotify_user_id',
        'encrypted_access_token',
        'encrypted_refresh_token',
        'expires_at',
        'last_sync_at',
        'connection_status',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_sync_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Workspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function accessToken(): string
    {
        return Crypt::decryptString($this->encrypted_access_token);
    }

    public function refreshToken(): string
    {
        return Crypt::decryptString($this->encrypted_refresh_token);
    }

    public function setAccessToken(string $token): void
    {
        $this->encrypted_access_token = Crypt::encryptString($token);
    }

    public function setRefreshToken(string $token): void
    {
        $this->encrypted_refresh_token = Crypt::encryptString($token);
    }

    public function isConnected(): bool
    {
        return $this->connection_status === 'connected';
    }

    public function isTokenExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
