<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

final class ClhUpdateManifestService
{
    private const HTTP_TIMEOUT_SECONDS = 20;

    /**
     * @return array{
     *     ok: bool,
     *     error?: string,
     *     details?: string,
     *     installed_version?: string,
     *     latest_version?: string,
     *     release_update_available?: bool,
     *     min_php_version?: string|null,
     *     changelog_url?: string|null,
     *     download_url?: string|null,
     *     manifest_url?: string
     * }
     */
    public function refresh(): array
    {
        $url = trim((string) (config('creator.update_manifest_url') ?? ''));
        if ($url === '') {
            return ['ok' => false, 'error' => 'no_manifest_url', 'manifest_url' => ''];
        }

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT_SECONDS)
                ->acceptJson()
                ->get($url);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'error' => 'http_failed',
                'details' => $e->getMessage(),
                'manifest_url' => $url,
            ];
        }

        if (! $response->successful()) {
            return [
                'ok' => false,
                'error' => 'http_status',
                'details' => (string) $response->status(),
                'manifest_url' => $url,
            ];
        }

        $data = $response->json();
        if (! is_array($data)) {
            return ['ok' => false, 'error' => 'invalid_json', 'manifest_url' => $url];
        }

        $latest = isset($data['latest_version']) ? trim((string) $data['latest_version']) : '';
        if ($latest === '') {
            return ['ok' => false, 'error' => 'missing_latest_version', 'manifest_url' => $url];
        }

        $installed = trim((string) config('creator.installed_version', '0.0.0'));
        $releaseUpdateAvailable = version_compare($installed, $latest, '<');

        $entry = $this->findVersionEntry($data, $latest);
        $changelogUrl = is_array($entry) ? (isset($entry['changelog_url']) ? trim((string) $entry['changelog_url']) : null) : null;
        $downloadUrl = is_array($entry) ? (isset($entry['download_url']) ? trim((string) $entry['download_url']) : null) : null;
        if ($changelogUrl === '') {
            $changelogUrl = null;
        }
        if ($downloadUrl === '') {
            $downloadUrl = null;
        }

        $minPhp = Arr::get($data, 'min_php_version');
        $minPhp = is_string($minPhp) ? trim($minPhp) : null;
        if ($minPhp === '') {
            $minPhp = null;
        }

        return [
            'ok' => true,
            'manifest_url' => $url,
            'installed_version' => $installed,
            'latest_version' => $latest,
            'release_update_available' => $releaseUpdateAvailable,
            'min_php_version' => $minPhp,
            'changelog_url' => $changelogUrl,
            'download_url' => $downloadUrl,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    private function findVersionEntry(array $data, string $latest): ?array
    {
        $versions = $data['versions'] ?? null;
        if (! is_array($versions)) {
            return null;
        }

        foreach ($versions as $row) {
            if (is_array($row) && isset($row['version']) && trim((string) $row['version']) === $latest) {
                return $row;
            }
        }

        return null;
    }
}
