<?php
/**
 * Applique les traductions françaises finales (supprime les préfixes [EN]).
 * Usage : php finalize_fr_lang.php  (depuis la racine du repo ou avec chemin absolu vers web/Boutik)
 */
declare(strict_types=1);

$boutik = $argv[1] ?? (dirname(__DIR__, 3) . '/web/Boutik');
if (! is_dir($boutik . '/lang/fr')) {
    fwrite(STDERR, "Répertoire Boutik introuvable : $boutik\n");
    exit(1);
}

$patchJson = getenv('BOUTIK_FR_PATCH_JSON') ?: (__DIR__ . '/data/lang_v1_fr_patch.json');

$patchFiles = [
    'lang_v1.php' => json_decode(
        file_get_contents($patchJson),
        true,
        512,
        JSON_THROW_ON_ERROR
    ),
    'home.php' => [
        'total_sell' => 'Ventes totales',
        'total_purchase' => 'Achats totaux',
        'total_sells' => 'Ventes totales (:currency)',
        'product_stock_alert' => 'Alerte stock produits',
        'stock_expiry_alert' => 'Alerte péremption des stocks',
    ],
    'messages.php' => [
        'filter_by_date' => 'Filtrer par date',
    ],
    'cash_register.php' => [
        'total_sales' => 'Ventes totales',
        'total_cash' => 'Total espèces',
        'total_card_slips' => 'Total glissements carte',
        'total_cheques' => 'Total chèques',
        'total_refund' => 'Total remboursements',
    ],
    'report.php' => [
        'total_sell' => 'Total des ventes',
        'stock_report' => 'État des stocks',
        'total_expense' => 'Total des dépenses',
        'stock_expiry_report' => 'Rapport de péremption des stocks',
        'closing_stock' => 'Stock de clôture',
    ],
];

foreach ($patchFiles as $file => $patch) {
    $path = "$boutik/lang/fr/$file";
    if (! file_exists($path)) {
        continue;
    }
    /** @var array<string, mixed> $lang */
    $lang = require $path;
    foreach ($patch as $key => $val) {
        if (array_key_exists($key, $lang)) {
            $lang[$key] = $val;
        }
    }
    if ($file === 'lang_v1.php') {
        $lang['total_sell_return'] = 'Total retours de vente';
    }
    $out = "<?php\n\nreturn " . var_export($lang, true) . ";\n";
    file_put_contents($path, $out);
    echo "Patched $file (" . count($patch) . " clés)\n";
}

echo "Terminé.\n";

/*
 * Dernière passe : toute chaîne restante « [EN] … » est remplacée par la
 * valeur anglaise du même fichier EN (supprime le préfixe [EN] à l'écran).
 */
foreach (glob($boutik . '/lang/fr/*.php') ?: [] as $path) {
    $base = basename($path);
    $enPath = $boutik . '/lang/en/' . $base;
    if (! is_file($enPath)) {
        continue;
    }
    /** @var array<string, mixed> $fr */
    $fr = require $path;
    /** @var array<string, mixed> $en */
    $en = require $enPath;
    $dirty = false;
    foreach ($fr as $key => $val) {
        if (! is_string($val) || ! str_starts_with($val, '[EN] ')) {
            continue;
        }
        if (! array_key_exists($key, $en) || ! is_string($en[$key])) {
            continue;
        }
        $fr[$key] = $en[$key];
        $dirty = true;
    }
    if ($dirty) {
        file_put_contents($path, "<?php\n\nreturn " . var_export($fr, true) . ";\n");
        echo "Fallback EN (sans préfixe [EN]) : $base\n";
    }
}
