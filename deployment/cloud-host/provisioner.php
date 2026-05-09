<?php

/**
 * Cloud-Provisioner (läuft auf dem VPS): HMAC-Auth, Anti-Replay, Aufruf der Tenant-Skripte per sudo.
 *
 * Umgebung:
 *   CLH_PROVISIONER_SECRET — gemeinsames Geheimnis mit Marketing-Server (config provisioner.hmac_secret)
 *   oder Datei /etc/clh-provisioner/secret (eine Zeile, ohne Newline)
 *   CLH_CONFIG — JSON-Konfiguration (Standard: /etc/clh-provisioner/config.json)
 *   CLH_NONCE_DIR — Nonce-Cache (Standard: /var/lib/clh-provisioner/nonces)
 *
 * Request: POST application/json (roher Body wird für HMAC signiert — Reihenfolge der Keys wie vom Client)
 * Header: X-CLH-Signature: hex_hmac_sha256(raw_body, secret)
 */

declare(strict_types=1);

/**
 * @param array<string, mixed> $data
 */
function clhProvisionerJson(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

function clhProvisionerSecret(): string
{
    $s = getenv('CLH_PROVISIONER_SECRET') ?: '';
    if ($s !== '') {
        return $s;
    }
    $f = '/etc/clh-provisioner/secret';
    if (is_readable($f)) {
        $s = trim((string) file_get_contents($f));

        return $s;
    }

    return '';
}

/** @return array<string, mixed> */
function clhProvisionerLoadConfig(): array
{
    $path = getenv('CLH_CONFIG') ?: '/etc/clh-provisioner/config.json';
    if (! is_readable($path)) {
        return [];
    }
    $j = json_decode((string) file_get_contents($path), true);

    return is_array($j) ? $j : [];
}

function clhProvisionerLog(string $line): void
{
    $f = getenv('CLH_PROVISIONER_LOG') ?: '/var/log/clh-provisioner.log';
    @error_log('[clh-provisioner] ' . $line . "\n", 3, $f);
}

/** Vollständige Skript-Ausgabe für Diagnose (clh-provisioner-User muss schreiben dürfen). */
function clhProvisionerLastScriptErrorPath(): string
{
    return getenv('CLH_LAST_SCRIPT_ERROR_LOG') ?: '/var/lib/clh-provisioner/last-script-error.log';
}

/**
 * stderr + stdout zusammenführen; bei langem Output das Ende behalten (Composer: viel Log am Anfang, Fehler oft am Schluss).
 */
function clhProvisionerScriptErrorDetail(string $stderr, string $stdout, int $maxLen = 24000): string
{
    $stderr = trim($stderr);
    $stdout = trim($stdout);
    $parts = [];
    if ($stderr !== '') {
        $parts[] = $stderr;
    }
    if ($stdout !== '') {
        $parts[] = "--- stdout ---\n".$stdout;
    }
    $merged = implode("\n\n", $parts);

    if ($merged === '') {
        return '(keine Ausgabe)';
    }
    if (strlen($merged) <= $maxLen) {
        return $merged;
    }

    return '… (nur letzte '.$maxLen.' von '.strlen($merged).' Zeichen) …'."\n".substr($merged, -$maxLen);
}

/**
 * Tenant-Skripte sollen bei Erfolg genau eine JSON-Zeile auf stdout ausgeben. Composer/Laravel Artisan
 * können jedoch noch Text/Zeilen davor oder (selten) dazwischen ausgeben — dann schlägt json_decode(trim)
 * auf dem Gesamtbuffer fehl. Wir nehmen zuerst den ganzen Puffer; sonst die letzte Zeile, die mit `{`
 * beginnt und gültiges JSON ist.
 *
 * @return array<string, mixed>|null
 */
function clhProvisionerDecodeScriptStdout(string $stdout): ?array
{
    $trim = trim($stdout);
    if ($trim === '') {
        return null;
    }
    $first = json_decode($trim, true);
    if (is_array($first)) {
        return $first;
    }
    $lines = preg_split('/\r\n|\n|\r/', $trim);
    if (! is_array($lines)) {
        return null;
    }
    for ($i = count($lines) - 1; $i >= 0; $i--) {
        $line = trim((string) $lines[$i]);
        if ($line === '' || $line[0] !== '{') {
            continue;
        }
        $decoded = json_decode($line, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }

    return null;
}

function clhProvisionerClientSignature(): string
{
    $s = strtolower(trim((string) ($_SERVER['HTTP_X_CLH_SIGNATURE'] ?? '')));
    if ($s !== '') {
        return $s;
    }
    if (function_exists('getallheaders')) {
        $hdrs = getallheaders();
        if (is_array($hdrs)) {
            foreach ($hdrs as $name => $value) {
                if (strcasecmp((string) $name, 'X-CLH-Signature') === 0) {
                    return strtolower(trim((string) $value));
                }
            }
        }
    }

    return '';
}

/** Roher JSON-Body (vom Router gepuffert oder direkt aus php://input). */
function clhProvisionerRawBody(): string
{
    if (array_key_exists('CLH_RAW_HTTP_BODY', $GLOBALS)) {
        $raw = (string) $GLOBALS['CLH_RAW_HTTP_BODY'];
        unset($GLOBALS['CLH_RAW_HTTP_BODY']);

        return $raw;
    }

    return file_get_contents('php://input') ?: '';
}

header('X-Content-Type-Options: nosniff');

$secret = clhProvisionerSecret();
if ($secret === '') {
    clhProvisionerJson(['error' => 'CLH_PROVISIONER_SECRET not set'], 500);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
    $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
    if ($path === '/' || $path === '/health' || str_ends_with($path, '/health')) {
        $cfg = clhProvisionerLoadConfig();
        $rz = (string) ($cfg['release_zip'] ?? '/opt/clh-releases/current.zip');
        clhProvisionerJson([
            'ok' => true,
            'service' => 'clh-provisioner',
            'checks' => [
                'release_zip' => [
                    'path' => $rz,
                    'exists' => file_exists($rz),
                    /** Für clh-provisioner; sudo-Skript läuft als root und kann auch root-only-ZIPs lesen */
                    'readable_by_provisioner_user' => is_readable($rz),
                ],
                'provision_script' => [
                    'path' => '/usr/local/bin/clh-provision-tenant.sh',
                    'executable' => is_executable('/usr/local/bin/clh-provision-tenant.sh'),
                ],
                'resume_script' => [
                    'path' => '/usr/local/bin/clh-resume-tenant.sh',
                    'executable' => is_executable('/usr/local/bin/clh-resume-tenant.sh'),
                ],
            ],
        ], 200);
    }
    clhProvisionerJson(['error' => 'not found'], 404);
}

if ($method !== 'POST') {
    clhProvisionerJson(['error' => 'method not allowed'], 405);
}

$raw = clhProvisionerRawBody();
$sig = clhProvisionerClientSignature();
$expected = hash_hmac('sha256', $raw, $secret);
if (! hash_equals($expected, $sig)) {
    clhProvisionerJson(['error' => 'invalid signature'], 401);
}

try {
    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
} catch (\Throwable) {
    clhProvisionerJson(['error' => 'invalid json'], 400);
}
if (! is_array($data)) {
    clhProvisionerJson(['error' => 'invalid json'], 400);
}

$ts = (int) ($data['ts'] ?? 0);
if ($ts <= 0 || abs(time() - $ts) > 300) {
    clhProvisionerJson(['error' => 'stale or invalid ts'], 400);
}
$nonce = (string) ($data['nonce'] ?? '');
if (! preg_match('/^[a-f0-9]{32}$/', $nonce)) {
    clhProvisionerJson(['error' => 'invalid nonce'], 400);
}

$nonceDir = getenv('CLH_NONCE_DIR') ?: '/var/lib/clh-provisioner/nonces';
if (! is_dir($nonceDir)) {
    @mkdir($nonceDir, 0700, true);
}
$nonceFile = $nonceDir . '/' . $nonce;
if (is_file($nonceFile)) {
    clhProvisionerJson(['error' => 'replay'], 400);
}
@file_put_contents($nonceFile, (string) (time() + 600));
foreach (glob($nonceDir . '/*') ?: [] as $f) {
    if (is_file($f) && @filemtime($f) < time() - 7200) {
        @unlink($f);
    }
}

$action = (string) ($data['action'] ?? '');
$slug = (string) ($data['slug'] ?? '');
if (! preg_match('/^[a-z0-9](?:[a-z0-9-]{1,30}[a-z0-9])?$/', $slug)) {
    clhProvisionerJson(['error' => 'invalid slug'], 400);
}
$domain = (string) ($data['domain'] ?? '');
if ($domain === '' || str_contains($domain, '..') || strlen($domain) > 253) {
    clhProvisionerJson(['error' => 'invalid domain'], 400);
}

$script = match ($action) {
    'create' => '/usr/local/bin/clh-provision-tenant.sh',
    'delete' => '/usr/local/bin/clh-delete-tenant.sh',
    'suspend' => '/usr/local/bin/clh-suspend-tenant.sh',
    'resume' => '/usr/local/bin/clh-resume-tenant.sh',
    default => '',
};
if ($script === '' || ! is_executable($script)) {
    clhProvisionerLog("missing script action={$action} path={$script}");
    clhProvisionerJson(['error' => 'unknown action or script not installed'], 500);
}

$config = clhProvisionerLoadConfig();
$tenantRoot = (string) ($config['tenants_root'] ?? '/var/www/clh-tenants');
$releaseZip = (string) ($config['release_zip'] ?? '');
$dbDriver = (string) ($config['db_driver'] ?? 'mysql');

$cmd = [
    'sudo', '-n', $script,
    '--slug', $slug,
    '--domain', $domain,
    '--tenant-root', $tenantRoot,
    '--db-driver', $dbDriver,
];
if ($action === 'create') {
    if ($releaseZip === '') {
        clhProvisionerJson(['error' => 'release_zip not configured (empty path in config)'], 500);
    }
    $email = strtolower(trim((string) ($data['admin_email'] ?? '')));
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        clhProvisionerJson(['error' => 'invalid admin_email'], 400);
    }
    $name = trim((string) ($data['admin_name'] ?? 'Admin'));
    $name = $name !== '' ? mb_substr($name, 0, 120) : 'Admin';
    $cmd[] = '--release-zip';
    $cmd[] = $releaseZip;
    $cmd[] = '--admin-email';
    $cmd[] = $email;
    $cmd[] = '--admin-name';
    $cmd[] = $name;
}

$des = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];
$proc = proc_open($cmd, $des, $pipes, null, null, ['bypass_shell' => true]);
if (! is_resource($proc)) {
    clhProvisionerJson(['error' => 'proc_open failed'], 500);
}
if (is_resource($pipes[0])) {
    fclose($pipes[0]);
}
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$exit = proc_close($proc);

if ($exit !== 0) {
    $stderrT = trim($stderr);
    $stdoutT = trim($stdout);
    $mergedFull = ($stderrT !== '' ? $stderrT : '').($stderrT !== '' && $stdoutT !== '' ? "\n\n--- stdout ---\n" : '').$stdoutT;
    $detail = clhProvisionerScriptErrorDetail($stderr, $stdout);
    $errFile = clhProvisionerLastScriptErrorPath();
    @file_put_contents(
        $errFile,
        gmdate('c')." exit={$exit}\n\n".$mergedFull."\n",
        LOCK_EX
    );
    clhProvisionerLog('script fail exit='.$exit.' detail_len='.strlen($detail).' full_log='.$errFile);
    clhProvisionerJson([
        'error' => 'provision script failed',
        'detail' => $detail,
        'full_log_path' => $errFile,
    ], 500);
}

$trim = trim((string) $stdout);
$out = clhProvisionerDecodeScriptStdout($trim);
if (! is_array($out)) {
    clhProvisionerJson(['error' => 'invalid script JSON', 'raw' => substr($trim, 0, 200)], 500);
}

clhProvisionerLog("ok action={$action} slug={$slug}");
clhProvisionerJson($out);
