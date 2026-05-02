<?php
/**
 * Pass 3 : nettoyage des doublons gettext + traductions correctes
 * pour le domaine effectif "aihub-child-artistik" / context "trp_context".
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

/* ---------- Supprimer les doublons créés au pass 2 (domain aihub-child) ---------- */
$ids_to_remove = $wpdb->get_col(
    "SELECT id FROM wp_trp_gettext_original_strings WHERE domain = 'aihub-child'"
);
if ( $ids_to_remove ) {
    $in = implode( ',', array_map( 'intval', $ids_to_remove ) );
    $wpdb->query( "DELETE FROM wp_trp_gettext_en_us WHERE original_id IN ($in)" );
    $wpdb->query( "DELETE FROM wp_trp_gettext_original_strings WHERE id IN ($in)" );
    WP_CLI::log( 'Supprimé ' . count( $ids_to_remove ) . ' doublons aihub-child.' );
}

/* ---------- Traductions cibles pour le bon domaine ---------- */
$domain  = 'aihub-child-artistik';
$context = 'trp_context';

$gettext = [
    'Artistik — Solutions (one-page)' => 'Artistik — Solutions (one-page)',
    'Artistik — accueil'              => 'Artistik — home',
    'Ouvrir le menu'                  => 'Open menu',
    'Menu principal'                  => 'Main menu',
    'Sélecteur de langue'             => 'Language selector',
    'Démo'                            => 'Demo',
    'Nous contacter'                  => 'Contact us',
    'Nos solutions logicielles'       => 'Our software solutions',
    'Des outils métier pensés pour votre activité' => 'Business tools tailored to your activity',
    'Chaque application regroupe des modules ciblés pour couvrir vos processus du quotidien — administratif, finance, utilisateurs et reporting.'
        => 'Each application bundles focused modules covering your day-to-day processes — administration, finance, users and reporting.',
    'Web'                             => 'Web',
    'Écrire à Artistik'               => 'Write to Artistik',
    'Retour en haut'                  => 'Back to top',
    'Contact'                         => 'Contact',
    'Email'                           => 'Email',
    'Site web'                        => 'Website',
    'Réponse sous 24 h'               => 'Reply within 24 h',
    'Jours ouvrés — accusé de réception immédiat.' => 'Business days — instant acknowledgment.',
    'Langue actuelle : %s'            => 'Current language: %s',
];

$updated = 0;
$inserted = 0;
foreach ( $gettext as $original => $translated ) {
    $row = $wpdb->get_row( $wpdb->prepare(
        "SELECT id FROM wp_trp_gettext_original_strings WHERE original = %s AND domain = %s AND context = %s LIMIT 1",
        $original, $domain, $context
    ) );

    if ( ! $row ) {
        $wpdb->insert( 'wp_trp_gettext_original_strings', [
            'original' => $original,
            'domain'   => $domain,
            'context'  => $context,
        ] );
        $original_id = (int) $wpdb->insert_id;
    } else {
        $original_id = (int) $row->id;
    }

    $existing = $wpdb->get_row( $wpdb->prepare(
        "SELECT id FROM wp_trp_gettext_en_us WHERE original_id = %d LIMIT 1", $original_id
    ) );
    if ( $existing ) {
        $wpdb->update( 'wp_trp_gettext_en_us',
            [ 'translated' => $translated, 'status' => 2, 'domain' => $domain ],
            [ 'id' => $existing->id ]
        );
        $updated++;
    } else {
        $wpdb->insert( 'wp_trp_gettext_en_us', [
            'original'    => $original,
            'translated'  => $translated,
            'domain'      => $domain,
            'status'      => 2,
            'original_id' => $original_id,
            'plural_form' => 0,
        ] );
        $inserted++;
    }
}

/* ---------- Vider transients TP pour forcer le refresh ---------- */
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_trp_%' OR option_name LIKE '_transient_timeout_trp_%'" );
wp_cache_flush();

WP_CLI::success( sprintf(
    'Pass 3 OK — %d updated, %d inserted (domain %s).',
    $updated, $inserted, $domain
) );
