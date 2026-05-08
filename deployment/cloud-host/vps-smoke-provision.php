<?php

/**
 * Smoke-Test auf dem VPS: ruft den Provisioner wie der Marketing-Server auf (HMAC über rohen JSON-Body).
 *
 * Läuft typischerweise als root (Secret lesen):
 *   sudo php /opt/clh-provisioner/vps-smoke-provision.php
 *   sudo php vps-smoke-provision.php "http://127.0.0.1:9100/" "vpstest-abc123"
 *   sudo php vps-smoke-provision.php "https://provision.app.creatorlinkhub.eu/" "vpstest-abc123"
 *
 * Hinweis: Jeder erfolgreicher Lauf legt Tenant + DB an; Slug immer neu wählen oder vorher löschen.
 */
declare(strict_types=1);

if (PHP_VERSION_ID < 80300) {
    fwrite(STDERR, "PHP 8.3+ empfohlen.\n");
    exit(1);
}

$url = isset($argv[1]) ? rtrim((string) $argv[1], '/') . '/' : 'http://127.0.0.1:9100/';
$slug = isset($argv[2]) ? (string) $argv[2] : 'vps-test-' . bin2hex(random_bytes(4));

if (! preg_match('/^[a-z0-9](?:[a-z0-9-]{1,30}[a-z0-9])?$/', $slug)) {
    fwrite(STDERR, "Ungültiger Slug (Provisioner-Regex): {$slug}\n");
    exit(1);
}

$secretFile = getenv('CLH_SECRET_FILE') ?: '/etc/clh-provisioner/secret';
$secret = trim((string) (getenv('CLH_PROVISIONER_SECRET') ?: ''));
if ($secret === '' && is_readable($secretFile)) {
    $secret = trim((string) file_get_contents($secretFile));
}
if ($secret === '') {
    fwrite(STDERR, "Kein Secret: CLH_PROVISIONER_SECRET setzen oder lesbare Datei {$secretFile}\n");
    exit(1);
}

$baseDomain = getenv('CLH_CLOUD_BASE_DOMAIN') ?: 'app.creatorlinkhub.eu';
$domain = "{$slug}.{$baseDomain}";
$payload = [
    'license_id' => 0,
    'slug' => $slug,
    'domain' => $domain,
    'admin_email' => getenv('CLH_SMOKE_ADMIN_EMAIL') ?: 'smoke-test@invalid.local',
    'admin_name' => 'VPS Smoke',
    'action' => 'create',
    'ts' => time(),
    'nonce' => bin2hex(random_bytes(16)),
];

$raw = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$sig = hash_hmac('sha256', $raw, $secret);

$ctx = stream_context_create([
    'http' => [
        'method' => 'POST',
        'protocol_version' => 1.0,
        'header' => "Content-Type: application/json\r\nExpect:\r\nX-CLH-Signature: {$sig}\r\n",
        'content' => $raw,
        'timeout' => 600,
        'ignore_errors' => true,
    ],
    'ssl' => [
        'verify_peer' => true,
        'verify_peer_name' => true,
    ],
]);

echo "[vps-smoke-provision] POST {$url}\n";
echo "[vps-smoke-provision] slug={$slug} domain={$domain}\n";

$resp = @file_get_contents($url, false, $ctx);
$statusLine = $http_response_header[0] ?? '';

if ($resp === false) {
    fwrite(STDERR, "Request fehlgeschlagen: {$statusLine}\n");
    exit(1);
}

echo "[vps-smoke-provision] {$statusLine}\n";
$data = json_decode($resp, true);
if (! is_array($data)) {
    echo $resp, "\n";
    exit(1);
}

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";

if (($data['ok'] ?? null) === true || ! empty($data['instance_url'])) {
    exit(0);
}

exit(1);
