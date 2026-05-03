<?php

namespace App\Jobs;

use App\Models\ClickEvent;
use App\Models\Link;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordClickEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $linkId,
        public ?string $sessionId,
        public ?string $ipHash,
        public ?string $userAgent,
        public ?string $country
    ) {}

    public function handle(): void
    {
        $link = Link::query()->find($this->linkId);

        if (! $link || ! $link->tracking_enabled) {
            return;
        }

        ClickEvent::query()->create([
            'link_id' => $link->id,
            'profile_id' => $link->profile_id,
            'session_id' => $this->sessionId,
            'ip_hash' => $this->ipHash,
            'user_agent' => $this->userAgent ? substr($this->userAgent, 0, 512) : null,
            'country' => $this->country,
            'created_at' => now(),
        ]);
    }
}
