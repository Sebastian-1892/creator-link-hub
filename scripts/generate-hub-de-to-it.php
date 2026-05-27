<?php

$base = dirname(__DIR__);

/** @var array<string, string> $fr */
$fr = require $base.'/database/seeders/data/hub_de_to_fr.php';

/** @var array<string, string> $en */
$en = require $base.'/database/seeders/data/hub_en_translations.php';

/** @var array<string, string> $enIt */
$enIt = require $base.'/database/seeders/data/hub_en_to_it.php';

$it = [];
foreach ($fr as $deKey => $frVal) {
    $enVal = $en[$deKey] ?? $deKey;
    $it[$deKey] = $enIt[$enVal] ?? $enIt[$deKey] ?? $frVal;
}

$content = "<?php\n\n/**\n * German __() key => Italian translation (lang/it.json values).\n *\n * @return array<string, string>\n */\nreturn ".var_export($it, true).";\n";
file_put_contents($base.'/database/seeders/data/hub_de_to_it.php', $content);

$sameAsEn = 0;
foreach ($it as $k => $v) {
    if ($v === ($en[$k] ?? '')) {
        $sameAsEn++;
    }
}

echo 'Wrote '.count($it)." IT keys ($sameAsEn still match EN value).\n";
