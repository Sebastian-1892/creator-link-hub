<?php

/**
 * Builds lang/fr.json, lang/it.json and seeder data from German __() keys.
 *
 * Run: php scripts/build-hub-locale-seeds.php
 */

$base = dirname(__DIR__);

/** @var array<string, string> $deFr */
$deFr = require $base.'/database/seeders/data/hub_de_to_fr.php';

/** @var array<string, string> $deIt */
$deIt = require $base.'/database/seeders/data/hub_de_to_it.php';

/** @var array<string, string> $hubEn */
$hubEn = require $base.'/database/seeders/data/hub_en_translations.php';

$deJsonPath = $base.'/lang/de.json';
$deJson = is_file($deJsonPath)
    ? json_decode((string) file_get_contents($deJsonPath), true)
    : [];

$allKeys = array_unique(array_merge(
    array_keys($hubEn),
    array_keys($deFr),
    array_keys($deIt),
    is_array($deJson) ? array_keys($deJson) : [],
));

$buildLocale = static function (array $map) use ($allKeys, $hubEn, $deJson): array {
    $out = [];
    foreach ($allKeys as $key) {
        if (isset($map[$key]) && $map[$key] !== '') {
            $out[$key] = $map[$key];

            continue;
        }
        $enVal = $hubEn[$key] ?? null;
        if (is_string($enVal) && isset($map[$enVal]) && $map[$enVal] !== '') {
            $out[$key] = $map[$enVal];

            continue;
        }
        if (is_array($deJson) && isset($deJson[$key])) {
            $out[$key] = $deJson[$key];
        }
    }
    ksort($out);

    return $out;
};

$fr = $buildLocale($deFr);
$it = $buildLocale($deIt);

$writeJson = static function (array $data, string $path): void {
    file_put_contents(
        $path,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
    );
};

$writeJson($fr, $base.'/lang/fr.json');
$writeJson($it, $base.'/lang/it.json');

$export = static function (array $data, string $path): void {
    $content = "<?php\n\n/**\n * @return array<string, string>\n */\nreturn ".var_export($data, true).";\n";
    file_put_contents($path, $content);
};

$export($fr, $base.'/database/seeders/data/hub_fr_translations.php');
$export($it, $base.'/database/seeders/data/hub_it_translations.php');

echo 'Built '.count($fr).' FR and '.count($it)." IT hub JSON keys.\n";
