<?php
/**
 * v2 : recrée les 2 actions email du formulaire Contact en utilisant
 * `FrmEmailAction::save_settings()` qui appelle `FrmDb::save_settings()`,
 * laquelle encode `post_content` au format JSON attendu par Formidable.
 *
 * @package Artistik_CM
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$form_id = (int) get_option( 'ak_contact_form_id', 0 );
if ( ! $form_id ) {
    WP_CLI::error( 'ak_contact_form_id introuvable' );
}

if ( ! class_exists( 'FrmEmailAction' ) ) {
    WP_CLI::error( 'FrmEmailAction indisponible' );
}

/* ---------------------------------------------------------------
 * 1. Purge complète des actions du form
 * --------------------------------------------------------------- */
$existing = FrmFormAction::get_action_for_form( $form_id, 'email' );
foreach ( $existing as $a ) {
    wp_delete_post( $a->ID, true );
    WP_CLI::log( "✗ Action email #{$a->ID} supprimée" );
}
$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'frm_form_actions'" );
WP_CLI::log( '✓ Table frm_form_actions purgée.' );

FrmFormAction::clear_cache();

/* ---------------------------------------------------------------
 * 2. Créer les 2 actions via la méthode officielle
 * --------------------------------------------------------------- */

function ak_create_email_action( int $form_id, string $title, array $opts ): int {
    $email = new FrmEmailAction();

    /* prepare_new() retourne un objet (stdClass) avec post_content = array de défauts */
    $action          = $email->prepare_new( $form_id );
    $defaults        = (array) $action->post_content;
    $action_settings = wp_parse_args( $opts, $defaults );

    /* Construire le tableau attendu par save_settings() */
    $settings = (array) $action;
    $settings['post_title']   = $title;
    $settings['post_content'] = $action_settings; // sera encodé par FrmDb
    $settings['post_status']  = 'publish';
    $settings['post_excerpt'] = 'email';
    $settings['menu_order']   = $form_id;

    return (int) $email->save_settings( $settings );
}

/* --- Action 1 : Notification admin (info@artistik.cm) --- */
$admin_id = ak_create_email_action( $form_id, 'Notification admin', array(
    'event'         => array( 'create' ),
    'to'            => 'info@artistik.cm',
    'cc'            => '',
    'bcc'           => '',
    'reply_to'      => '[ak_email]',
    'reply_to_name' => '[ak_nom]',
    'from'          => 'Artistik CM <tank@smpc.cm>',
    'email_subject' => '[Artistik CM] Nouveau message de [ak_nom] — [ak_sujet]',
    'email_message' => "Bonjour,\n\n"
        . "Un nouveau message a été reçu via le formulaire de contact du site artistik.cm.\n\n"
        . "Nom        : [ak_nom]\n"
        . "Email      : [ak_email]\n"
        . "Téléphone  : [ak_tel]\n"
        . "Solution   : [ak_sujet]\n\n"
        . "Message :\n[ak_message]\n\n"
        . "—\n"
        . "Envoyé depuis le formulaire de contact d'artistik.cm",
    'inc_user_info' => 0,
    'plain_text'    => 1,
) );
WP_CLI::log( "✓ Action 'Notification admin' → ID $admin_id" );

/* --- Action 2 : Accusé visiteur ([ak_email]) --- */
$visitor_id = ak_create_email_action( $form_id, 'Accusé de réception visiteur', array(
    'event'         => array( 'create' ),
    'to'            => '[ak_email]',
    'cc'            => '',
    'bcc'           => '',
    'reply_to'      => 'info@artistik.cm',
    'reply_to_name' => 'Artistik CM',
    'from'          => 'Artistik CM <tank@smpc.cm>',
    'email_subject' => 'Artistik CM — Bien reçu votre message',
    'email_message' => "Bonjour [ak_nom],\n\n"
        . "Nous avons bien reçu votre demande concernant : [ak_sujet].\n"
        . "Notre équipe revient vers vous très rapidement.\n\n"
        . "Récapitulatif de votre message :\n"
        . "----------------------------------\n"
        . "[ak_message]\n"
        . "----------------------------------\n\n"
        . "Bonne journée,\n"
        . "L'équipe Artistik CM\n"
        . "info@artistik.cm",
    'inc_user_info' => 0,
    'plain_text'    => 1,
) );
WP_CLI::log( "✓ Action 'Accusé visiteur' → ID $visitor_id" );

/* ---------------------------------------------------------------
 * 3. Vérifier que le post_content est bien lu par Formidable
 * --------------------------------------------------------------- */
FrmFormAction::clear_cache();
$reload = FrmFormAction::get_action_for_form( $form_id, 'email' );
WP_CLI::log( "\n==== Re-lecture (" . count( $reload ) . " actions) ====" );
foreach ( $reload as $a ) {
    $cfg = (array) $a->post_content;
    WP_CLI::log( "Action #{$a->ID} '{$a->post_title}' :" );
    WP_CLI::log( '   to            = ' . ( $cfg['to'] ?? '?' ) );
    WP_CLI::log( '   from          = ' . ( $cfg['from'] ?? '?' ) );
    WP_CLI::log( '   reply_to      = ' . ( $cfg['reply_to'] ?? '?' ) );
    WP_CLI::log( '   email_subject = ' . ( $cfg['email_subject'] ?? '?' ) );
    WP_CLI::log( '   plain_text    = ' . ( $cfg['plain_text'] ?? '?' ) );
}

/* ---------------------------------------------------------------
 * 4. Test : un submit
 * --------------------------------------------------------------- */
WP_CLI::log( "\n==== Test submit ====" );
$fields = FrmField::get_all_for_form( $form_id );
$values = array();
foreach ( $fields as $f ) {
    switch ( $f->field_key ) {
        case 'ak_nom':     $values[ $f->id ] = 'Bertrand TestV2'; break;
        case 'ak_email':   $values[ $f->id ] = 'bertrandngoufack@gmail.com'; break;
        case 'ak_tel':     $values[ $f->id ] = '+237 600 000 000'; break;
        case 'ak_sujet':   $values[ $f->id ] = 'Information générale'; break;
        case 'ak_message': $values[ $f->id ] = "Test V2 - actions reconfigurées via API officielle - " . date( 'H:i:s' ); break;
        case 'ak_consent': $values[ $f->id ] = '1'; break;
    }
}
delete_option( 'ak_mail_log' );
$entry_id = FrmEntry::create( array( 'form_id' => $form_id, 'item_meta' => $values ) );
WP_CLI::log( "Entry créée : $entry_id" );

WP_CLI::log( "\n==== Mail-log post-submit ====" );
$log = get_option( 'ak_mail_log', array() );
foreach ( $log as $i => $e ) {
    WP_CLI::log( "[$i] " . json_encode( $e, JSON_UNESCAPED_UNICODE ) );
}

WP_CLI::success( 'Formulaire reconfiguré via API officielle Formidable.' );
