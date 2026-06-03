<?php

use App\Support\SpotifyUrlParser;

test('parses open spotify track url', function () {
    $parsed = SpotifyUrlParser::parse('https://open.spotify.com/track/6rqhFgbbKwnb9MLmUQDhG6?si=abc');

    expect($parsed['provider'])->toBe('spotify')
        ->and($parsed['resource_type'])->toBe('track')
        ->and($parsed['provider_id'])->toBe('6rqhFgbbKwnb9MLmUQDhG6')
        ->and($parsed['canonical_url'])->toBe('https://open.spotify.com/track/6rqhFgbbKwnb9MLmUQDhG6')
        ->and($parsed['embed_url'])->toBe('https://open.spotify.com/embed/track/6rqhFgbbKwnb9MLmUQDhG6');
});

test('parses spotify uri', function () {
    $parsed = SpotifyUrlParser::parse('spotify:episode:0EW99qKq4Jm5J0DZp0GNa5');

    expect($parsed['resource_type'])->toBe('episode')
        ->and($parsed['provider_id'])->toBe('0EW99qKq4Jm5J0DZp0GNa5');
});

test('parses intl spotify url', function () {
    $parsed = SpotifyUrlParser::parse('https://open.spotify.com/intl-de/track/6rqhFgbbKwnb9MLmUQDhG6');

    expect($parsed['resource_type'])->toBe('track')
        ->and($parsed['provider_id'])->toBe('6rqhFgbbKwnb9MLmUQDhG6');
});

test('try parse returns null for invalid input', function () {
    expect(SpotifyUrlParser::tryParse('not-spotify'))->toBeNull();
});

test('parse rejects invalid input', function () {
    SpotifyUrlParser::parse('https://example.com/track/1');
})->throws(InvalidArgumentException::class);
