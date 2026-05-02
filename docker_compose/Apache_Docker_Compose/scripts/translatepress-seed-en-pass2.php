<?php
/**
 * Pass 2 : ajoute les chaînes manquantes au dictionnaire TP (regular)
 * + alimente la table gettext pour les chaînes du theme/customizer.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$default_language = 'fr_FR';
$language_code    = 'en_US';

$trp   = TRP_Translate_Press::get_trp_instance();
$query = $trp->get_component( 'query' );

/* -----------------------------------------------------------------
 * 1) Regular dictionary : entités, fragments hero, textes SVG, etc.
 * ----------------------------------------------------------------- */
$dictionary = [
    // Entités HTML pour les items menu
    'Élèves &#038; étudiants'        => 'Pupils & students',
    'Patrimoine &#038; acteurs'      => 'Properties & people',
    'Contrats &#038; flux financiers' => 'Contracts & cash flows',
    'Éditions &#038; pilotage'       => 'Reports & monitoring',
    'Trésorerie générale &#038; individuelle' => 'Overall & individual cash flow',
    'Saisie &#038; mise à jour des notes' => 'Enter & update grades',

    // Fragment hero manquant (sans espace devant)
    'et l’'                          => 'and ',

    // Textes intégrés aux SVG illustrations
    'ECG · 78 bpm'                   => 'ECG · 78 bpm',
    'RDV / jour'                     => 'Appts / day',
    'Lits occupés'                   => 'Occupied beds',
    'Recettes'                       => 'Revenue',
    'Mathématiques'                  => 'Mathematics',
    'Physique'                       => 'Physics',
    'Français'                       => 'Français',
    'Histoire'                       => 'History',
    'Moyenne générale'               => 'Overall average',
    'Effectif total'                 => 'Total enrolment',
    '1 247 élèves'                   => '1,247 pupils',
    'Encaissements'                  => 'Payments',
    '12,4 M'                         => '12.4 M',
    'À LOUER'                        => 'FOR RENT',
    'Contrat de bail · #2026-014'    => 'Lease agreement · #2026-014',
    'Locataire'                      => 'Tenant',
    'M. Dupont'                      => 'Mr. Dupont',
    'Loyer mensuel'                  => 'Monthly rent',
    '350 000 FCFA'                   => 'XAF 350,000',
    'Suivi des loyers'               => 'Rent tracking',
    'Janv.'                          => 'Jan.',
    'Févr.'                          => 'Feb.',
    'Mars'                           => 'Mar.',

    // Divers
    'Artistik CM'                    => 'Artistik CM',
    'Loading...'                     => 'Loading...',
];

$query->insert_strings( array_keys( $dictionary ), $language_code, 0 );
$existing = $query->get_string_ids( array_keys( $dictionary ), $language_code );

$rows = [];
foreach ( $dictionary as $original => $translated ) {
    if ( ! isset( $existing[ $original ] ) ) continue;
    $rows[] = [
        'id'          => (int) $existing[ $original ]->id,
        'original'    => $original,
        'translated'  => $translated,
        'status'      => 2,
        'block_type'  => 0,
        'original_id' => 0,
    ];
}
if ( $rows ) {
    $ids   = wp_list_pluck( $rows, 'id' );
    $rows_existing = $wpdb->get_results(
        "SELECT id, original_id FROM wp_trp_dictionary_fr_fr_en_us WHERE id IN (" . implode( ',', $ids ) . ')',
        OBJECT_K
    );
    foreach ( $rows as &$r ) {
        if ( isset( $rows_existing[ $r['id'] ]->original_id ) ) {
            $r['original_id'] = (int) $rows_existing[ $r['id'] ]->original_id;
        }
    }
    unset( $r );
    $query->update_strings( $rows, $language_code );
}
WP_CLI::log( 'Regular dictionary : ' . count( $rows ) . ' lignes mises à jour.' );

/* -----------------------------------------------------------------
 * 2) Gettext : chaînes provenant de __() / Customizer defaults
 * ----------------------------------------------------------------- */
$gettext = [
    // [original, translated, domain]
    [ 'Nous contacter', 'Contact us', 'aihub-child' ],
    [ 'Nos solutions logicielles', 'Our software solutions', 'aihub-child' ],
    [ 'Des outils métier pensés pour votre activité', 'Business tools tailored to your activity', 'aihub-child' ],
    [
        'Chaque application regroupe des modules ciblés pour couvrir vos processus du quotidien — administratif, finance, utilisateurs et reporting.',
        'Each application bundles focused modules covering your day-to-day processes — administration, finance, users and reporting.',
        'aihub-child',
    ],
    // Customizer defaults possibles
    [ 'Artistik — logiciels métier', 'Artistik — business software', 'aihub-child' ],
    [ 'Des applications pensées pour votre secteur', 'Apps designed for your industry', 'aihub-child' ],
    [ 'Découvrir nos solutions', 'Discover our solutions', 'aihub-child' ],
    [ 'Démo', 'Demo', 'aihub-child' ],
    [ 'Un projet ? Parlons-en.', 'A project? Let’s talk.', 'aihub-child' ],
    [ 'Déploiement, démonstration ou accompagnement : l’équipe Artistik vous répond.', 'Deployment, demo or support: the Artistik team is here to help.', 'aihub-child' ],
    [ 'info@artistik.cm', 'info@artistik.cm', 'aihub-child' ],
    [ 'https://artistik.cm', 'https://artistik.cm', 'aihub-child' ],
    [ 'artistik.cm', 'artistik.cm', 'aihub-child' ],
    [ 'Année de création de SoluMed', 'Year SoluMed was created', 'aihub-child' ],
    [ 'familles de produits', 'product families', 'aihub-child' ],
    [ 'orienté gestion métier', 'focused on business management', 'aihub-child' ],
];

$inserted = 0;
foreach ( $gettext as $g ) {
    [ $original, $translated, $domain ] = $g;

    $row = $wpdb->get_row( $wpdb->prepare(
        "SELECT id FROM wp_trp_gettext_original_strings WHERE original = %s AND domain = %s LIMIT 1",
        $original, $domain
    ) );
    if ( ! $row ) {
        $wpdb->insert( 'wp_trp_gettext_original_strings', [
            'original' => $original,
            'domain'   => $domain,
            'context'  => '',
        ] );
        $original_id = (int) $wpdb->insert_id;
    } else {
        $original_id = (int) $row->id;
    }

    $existing_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM wp_trp_gettext_en_us WHERE original_id = %d LIMIT 1", $original_id
    ) );
    if ( $existing_id ) {
        $wpdb->update( 'wp_trp_gettext_en_us', [
            'translated' => $translated,
            'status'     => 2,
        ], [ 'id' => $existing_id ] );
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
WP_CLI::log( "Gettext : " . count( $gettext ) . " lignes traitées ($inserted insertions)." );

/* -----------------------------------------------------------------
 * 3) Purge transients TP / WP cache
 * ----------------------------------------------------------------- */
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_trp_%' OR option_name LIKE '_transient_timeout_trp_%'" );

WP_CLI::success( 'Pass 2 terminé.' );
