<?php
/**
 * Synchronise les fichiers de traduction FR à partir de EN.
 *
 * - Crée les fichiers FR manquants (avec valeurs EN comme placeholder préfixé [EN])
 * - Ajoute les clés EN absentes en FR
 * - Préserve toutes les valeurs FR existantes
 * - Applique un dictionnaire de remplacement de vocabulaire camerounais
 *   (Wilaya → Région, TPS → TVA, etc.) si le terme nord-africain est détecté.
 *
 * Usage : docker exec -u www-data artistik-php_web php /tmp/sync_fr_from_en.php
 */
declare(strict_types=1);

$EN = '/var/www/html/Boutik/lang/en';
$FR = '/var/www/html/Boutik/lang/fr';

// Dictionnaire de vocabulaire à corriger (sensible à la casse)
$cmrFix = [
    'Wilaya'      => 'Région',
    'wilaya'      => 'région',
    'TPS / TVA / Autre' => 'TVA / NIU / Autre',
    'TPS'         => 'TVA',
    'GST'         => 'TVA',
    'GSTIN'       => 'NIU',
    'VAT'         => 'TVA',
    'PAN'         => 'NIU',
];

function flatten(array $array, string $prefix = ''): array {
    $out = [];
    foreach ($array as $k => $v) {
        $key = $prefix === '' ? (string) $k : "$prefix.$k";
        if (is_array($v)) {
            $out += flatten($v, $key);
        } else {
            $out[$key] = $v;
        }
    }
    return $out;
}

function setNested(array &$arr, string $key, $value): void {
    $parts = explode('.', $key);
    $ref = &$arr;
    foreach ($parts as $p) {
        if (! isset($ref[$p]) || ! is_array($ref[$p])) {
            $ref[$p] = [];
        }
        $ref = &$ref[$p];
    }
    $ref = $value;
}

function arrayExport($var, int $indent = 0): string {
    $pad = str_repeat('    ', $indent);
    if (is_array($var)) {
        $isList = array_keys($var) === range(0, count($var) - 1);
        $lines = [];
        foreach ($var as $k => $v) {
            $key = $isList ? '' : (is_int($k) ? "$k => " : "'" . addcslashes((string) $k, "'\\") . "' => ");
            $lines[] = $pad . '    ' . $key . arrayExport($v, $indent + 1) . ',';
        }
        return "[\n" . implode("\n", $lines) . "\n" . $pad . ']';
    }
    if (is_bool($var)) return $var ? 'true' : 'false';
    if (is_null($var)) return 'null';
    if (is_int($var) || is_float($var)) return (string) $var;
    return '"' . addcslashes((string) $var, "\"\\\$") . '"';
}

$enFiles = glob("$EN/*.php");
$processed = $created = $patched = $added_keys = 0;
$report = [];

foreach ($enFiles as $enPath) {
    $name = basename($enPath);

    // Cas spécial : EN a "pagination.php", FR a "paginate.php" → on lit les deux
    $frCandidates = ["$FR/$name"];
    if ($name === 'pagination.php') {
        $frCandidates[] = "$FR/paginate.php";
    }

    $frPath = null;
    foreach ($frCandidates as $c) {
        if (file_exists($c)) { $frPath = $c; break; }
    }
    $frExisted = $frPath !== null;
    if (! $frExisted) {
        $frPath = "$FR/$name";
    }

    /** @var array $en */
    $en = require $enPath;
    /** @var array $fr */
    $fr = $frExisted ? (require $frPath) : [];

    if (! is_array($en) || ! is_array($fr)) {
        $report[] = "  ⚠ $name : ignoré (pas un tableau)";
        continue;
    }

    $enFlat = flatten($en);
    $frFlat = flatten($fr);

    $merged = $fr;
    $localAdded = 0;
    foreach ($enFlat as $k => $v) {
        if (! array_key_exists($k, $frFlat)) {
            // Clé manquante en FR : on insère la valeur EN avec préfixe [EN] pour signaler
            $placeholder = is_string($v) ? '[EN] ' . $v : $v;
            setNested($merged, $k, $placeholder);
            $localAdded++;
        }
    }

    // Patch vocabulaire CM (récursif)
    $localPatched = 0;
    array_walk_recursive($merged, function (&$val) use ($cmrFix, &$localPatched) {
        if (! is_string($val)) return;
        $orig = $val;
        foreach ($cmrFix as $bad => $good) {
            $val = str_replace($bad, $good, $val);
        }
        if ($orig !== $val) $localPatched++;
    });

    $php = "<?php\n\nreturn " . arrayExport($merged) . ";\n";
    file_put_contents($frPath, $php);
    chown($frPath, 'www-data');
    chgrp($frPath, 'www-data');

    if (! $frExisted) {
        $created++;
        $report[] = sprintf('  + créé:    %-25s (%d clés EN copiées avec préfixe [EN])', $name, $localAdded);
    } elseif ($localAdded > 0 || $localPatched > 0) {
        $processed++;
        $added_keys += $localAdded;
        $patched += $localPatched;
        $report[] = sprintf('  ✓ patché:  %-25s (+%d clés ajoutées, %d termes corrigés)', $name, $localAdded, $localPatched);
    } else {
        $report[] = sprintf('  · ok:      %-25s (aucun changement)', $name);
    }
}

echo "===========================================================\n";
echo "Synchronisation des traductions FR depuis EN\n";
echo "===========================================================\n";
foreach ($report as $line) echo $line . "\n";
echo "===========================================================\n";
echo sprintf("Bilan : %d fichiers créés, %d patchés, %d clés ajoutées, %d termes camerounisés\n",
    $created, $processed, $added_keys, $patched);
echo "===========================================================\n";
