<?php

namespace App\Support;

use InvalidArgumentException;

class SpotifyUrlParser
{
    /** @var list<string> */
    private const RESOURCE_TYPES = ['track', 'episode', 'show', 'playlist', 'album', 'artist'];

    /**
     * @return array{provider: string, resource_type: string, provider_id: string, canonical_url: string, embed_url: string}|null
     */
    public static function tryParse(string $input): ?array
    {
        try {
            return self::parse($input);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * @return array{provider: string, resource_type: string, provider_id: string, canonical_url: string, embed_url: string}
     */
    public static function parse(string $input): array
    {
        $input = trim($input);

        if ($input === '') {
            throw new InvalidArgumentException('empty');
        }

        if (preg_match('/^spotify:(track|episode|show|playlist|album|artist):([a-zA-Z0-9]+)$/i', $input, $matches) === 1) {
            return self::build(strtolower($matches[1]), $matches[2]);
        }

        if (preg_match('#https?://(?:open\.)?spotify\.com/(?:intl-[a-z]{2}(?:-[a-z]{2})?/)?(track|episode|show|playlist|album|artist)/([a-zA-Z0-9]+)#i', $input, $matches) === 1) {
            return self::build(strtolower($matches[1]), $matches[2]);
        }

        throw new InvalidArgumentException('invalid');
    }

    /**
     * @return array{provider: string, resource_type: string, provider_id: string, canonical_url: string, embed_url: string}
     */
    private static function build(string $type, string $id): array
    {
        if (! in_array($type, self::RESOURCE_TYPES, true)) {
            throw new InvalidArgumentException('invalid type');
        }

        $id = preg_replace('/\?.*$/', '', $id) ?? $id;

        return [
            'provider' => 'spotify',
            'resource_type' => $type,
            'provider_id' => $id,
            'canonical_url' => "https://open.spotify.com/{$type}/{$id}",
            'embed_url' => "https://open.spotify.com/embed/{$type}/{$id}",
        ];
    }
}
