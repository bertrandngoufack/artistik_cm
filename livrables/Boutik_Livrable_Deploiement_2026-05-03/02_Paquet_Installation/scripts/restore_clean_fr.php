<?php
/**
 * Étape 1 : restaure depuis EN les valeurs FR qui contiennent des chunks
 *           anglais résiduels (mots EN mélangés à du FR = signe d'une mauvaise
 *           traduction mot-à-mot précédente).
 * Étape 2 : applique uniquement notre dictionnaire de PHRASES COMPLÈTES
 *           (pas de mot-à-mot — déjà testé, donne du franglais).
 */
declare(strict_types=1);

$EN_DIR = '/var/www/html/Boutik/lang/en';
$FR_DIR = '/var/www/html/Boutik/lang/fr';

// Phrases complètes EN → FR (validées contextuellement, vocabulaire CM)
$phrases = [
    'welcome back'                      => 'Bon retour',
    'login to your'                     => 'Connectez-vous à',
    'sorry, your business is inactive!!' => "Désolé, votre entreprise est inactive !",
    'select base unit'                  => "Sélectionner l'unité de base",
    'times base unit'                   => "fois l'unité de base",
    'add as multiple of other unit'     => "Ajouter comme multiple d'une autre unité",
    'products deactivated successfully' => 'Produits désactivés avec succès',
    'reactivate'                        => 'Réactiver',
    'add discount'                      => 'Ajouter une remise',
    'profit margin %'                   => 'Marge bénéficiaire %',
    'profit margin'                     => 'Marge bénéficiaire',
    'forgot password?'                  => 'Mot de passe oublié ?',
    'forgot your password'              => 'Mot de passe oublié ?',
    'remember me'                       => 'Se souvenir de moi',
    'sign in'                           => 'Se connecter',
    'sign up'                           => "S'inscrire",
    'login'                             => 'Connexion',
    'logout'                            => 'Déconnexion',
    'register'                          => "S'inscrire",
    'submit'                            => 'Envoyer',
    'cancel'                            => 'Annuler',
    'save'                              => 'Enregistrer',
    'edit'                              => 'Modifier',
    'delete'                            => 'Supprimer',
    'view'                              => 'Voir',
    'add'                               => 'Ajouter',
    'update'                            => 'Mettre à jour',
    'username'                          => "Nom d'utilisateur",
    'password'                          => 'Mot de passe',
    'email'                             => 'E-mail',
    'name'                              => 'Nom',
    'first name'                        => 'Prénom',
    'last name'                         => 'Nom',
    'mobile'                            => 'Téléphone',
    'phone'                             => 'Téléphone',
    'address'                           => 'Adresse',
    'city'                              => 'Ville',
    'state'                             => 'Région',
    'country'                           => 'Pays',
    'zip code'                          => 'Boîte postale',
    'all rights reserved'               => 'Tous droits réservés',
    'manage modules'                    => 'Gérer les modules',
    'install'                           => 'Installer',
    'uninstall'                         => 'Désinstaller',
    'version'                           => 'Version',
    'source'                            => 'Source',
    'duplicate taxonomy type found'     => 'Type de taxonomie en doublon',
    'taxonomy type not found'           => 'Type de taxonomie introuvable',
    'profile updated successfully'      => 'Profil mis à jour avec succès',
    'password updated successfully'     => 'Mot de passe mis à jour avec succès',
    'you have entered wrong password'   => 'Mot de passe incorrect',
    "business doesn't have crm subscription" => "L'entreprise n'a pas d'abonnement CRM",
    'next'                              => 'Suivant',
    'previous'                          => 'Précédent',
    'finish'                            => 'Terminer',
    'home'                              => 'Accueil',
    'dashboard'                         => 'Tableau de bord',
    'settings'                          => 'Paramètres',
    'profile'                           => 'Profil',
];

function isProbablyMixedFranglais(string $val): bool {
    // Contient des indicateurs typiques de "franglais" : mots anglais entiers
    // mélangés à des mots français.
    $englishPatterns = '/\b(the|and|for|with|without|account|password|select|update|delete|add|edit|view|business|product|item|sale|invoice|customer|supplier|payment|stock|user|role|please|click|here|status|amount|total|date|time|hello|welcome|sorry|name|email|address|city|country|state|number|here|there|while|after|before|when|until|next|previous|all|none|new|old|enable|disable|paid|unpaid|due|create|created|active|inactive|enabled|disabled|today|yesterday)\b/i';
    if (! preg_match($englishPatterns, $val)) return false;

    // Et contient aussi du français
    return (bool) preg_match('/\b(le|la|les|de|des|à|une?|du|et|ou|mais|avec|sans|votre|vos|notre|nos|pour|par|sur|cette|ce|ces|tous|toute|chaque|peut|doit|sera|être|avoir|dans|sont|est|été|aux?)\b/i', $val);
}

function flatten(array $array, string $prefix = ''): array {
    $out = [];
    foreach ($array as $k => $v) {
        $key = $prefix === '' ? (string) $k : "$prefix.$k";
        if (is_array($v)) $out += flatten($v, $key);
        else $out[$key] = $v;
    }
    return $out;
}

function setNested(array &$arr, string $key, $value): void {
    $parts = explode('.', $key);
    $ref = &$arr;
    foreach ($parts as $p) {
        if (! isset($ref[$p]) || ! is_array($ref[$p])) $ref[$p] = [];
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

$totalRestored = 0;
$totalPhraseTr = 0;
$totalKept = 0;

foreach (glob("$EN_DIR/*.php") as $enPath) {
    $name = basename($enPath);
    $frPath = "$FR_DIR/$name";
    if (! file_exists($frPath)) continue;

    $en = require $enPath;
    $fr = require $frPath;
    if (! is_array($en) || ! is_array($fr)) continue;

    $enFlat = flatten($en);
    $frFlat = flatten($fr);

    $merged = $fr;
    foreach ($enFlat as $k => $enVal) {
        $frVal = $frFlat[$k] ?? null;

        // Cas 1 : valeur FR contient déjà [EN] (placeholder) → on tente de traduire
        if (is_string($frVal) && strpos($frVal, '[EN] ') === 0) {
            $rawEn = substr($frVal, 5);
            $key = mb_strtolower(rtrim(trim($rawEn), '.!?:;,'));
            if (isset($phrases[$key])) {
                setNested($merged, $k, $phrases[$key]);
                $totalPhraseTr++;
            } else {
                $totalKept++; // reste [EN]
            }
            continue;
        }

        // Cas 2 : valeur FR existe mais ressemble à du franglais → restauration EN + marquage [EN]
        if (is_string($frVal) && is_string($enVal) && isProbablyMixedFranglais($frVal)) {
            $key = mb_strtolower(rtrim(trim($enVal), '.!?:;,'));
            if (isset($phrases[$key])) {
                setNested($merged, $k, $phrases[$key]);
                $totalPhraseTr++;
            } else {
                setNested($merged, $k, '[EN] ' . $enVal);
                $totalRestored++;
            }
            continue;
        }

        // Cas 3 : FR absent → ajouter [EN]
        if ($frVal === null && is_string($enVal)) {
            setNested($merged, $k, '[EN] ' . $enVal);
            $totalRestored++;
        }
    }

    file_put_contents($frPath, "<?php\n\nreturn " . arrayExport($merged) . ";\n");
}

echo "===========================================================\n";
echo " Restauration FR + traduction par phrases\n";
echo "===========================================================\n";
echo " Franglais détecté → restauré [EN] : $totalRestored\n";
echo " Phrases traduites depuis dico    : $totalPhraseTr\n";
echo " Restant [EN] (à traduire main)  : $totalKept\n";
echo "===========================================================\n";
