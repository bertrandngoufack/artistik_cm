<?php
/**
 * v4 : utilise les bonnes clés Formidable (`email_to` au lieu de `to`)
 * + shortcodes ID. C'est cette version qui doit fonctionner pleinement.
 *
 * @package Artistik_CM
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$form_id = (int) get_option( 'ak_contact_form_id', 0 );
if ( ! $form_id || ! class_exists( 'FrmEmailAction' ) ) {
    WP_CLI::error( 'Pré-requis manquant.' );
}

$fields = FrmField::get_all_for_form( $form_id );
$id_by_key = array();
foreach ( $fields as $f ) {
    $id_by_key[ $f->field_key ] = (int) $f->id;
}

$EMAIL    = '[' . $id_by_key['ak_email']       . ']';
$NOM      = '[' . $id_by_key['ak_nom']         . ']';
$TEL      = '[' . $id_by_key['ak_tel']         . ']';
$SUJET    = '[' . $id_by_key['ak_sujet']       . ']';
$MESSAGE  = '[' . $id_by_key['ak_message']     . ']';
$ORG      = '[' . $id_by_key['ak_entreprise']  . ']';

WP_CLI::log( "EMAIL=$EMAIL NOM=$NOM SUJET=$SUJET" );

$existing = FrmFormAction::get_action_for_form( $form_id, 'email' );
foreach ( $existing as $a ) {
    wp_delete_post( $a->ID, true );
}
$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'frm_form_actions'" );
FrmFormAction::clear_cache();
WP_CLI::log( '✓ Anciennes actions purgées.' );

function ak_create_email_action( int $form_id, string $title, array $opts ): int {
    $email = new FrmEmailAction();
    $action = $email->prepare_new( $form_id );
    $defaults = (array) $action->post_content;
    $merged = wp_parse_args( $opts, $defaults );

    $settings = (array) $action;
    $settings['post_title']   = $title;
    $settings['post_content'] = $merged;
    $settings['post_status']  = 'publish';
    $settings['post_excerpt'] = 'email';
    $settings['menu_order']   = $form_id;
    return (int) $email->save_settings( $settings );
}

/* --- Notification admin (utilise `email_to`) --- */
$admin_id = ak_create_email_action( $form_id, 'Notification admin', array(
    'event'         => array( 'create' ),
    'email_to'      => 'info@artistik.cm',
    'cc'            => '',
    'bcc'           => '',
    'reply_to'      => $EMAIL,
    'from'          => 'Artistik CM <info@artistik.cm>',
    'email_subject' => '[Artistik CM] Nouveau message de ' . $NOM . ' — ' . $SUJET,
    'email_message' => "Bonjour,\n\n"
        . "Un nouveau message a été reçu via le formulaire de contact du site artistik.cm.\n\n"
        . "Nom         : $NOM\n"
        . "Organisation: $ORG\n"
        . "Email       : $EMAIL\n"
        . "Téléphone   : $TEL\n"
        . "Solution    : $SUJET\n\n"
        . "Message :\n$MESSAGE\n\n"
        . "—\n"
        . "Envoyé depuis le formulaire de contact d'artistik.cm",
    'inc_user_info' => 0,
    'plain_text'    => 1,
) );
WP_CLI::log( "✓ Notification admin → ID $admin_id (email_to=info@artistik.cm)" );

/* --- Accusé visiteur (email_to = $EMAIL = [8]) --- */
$visitor_id = ak_create_email_action( $form_id, 'Accusé de réception visiteur', array(
    'event'         => array( 'create' ),
    'email_to'      => $EMAIL,
    'cc'            => '',
    'bcc'           => '',
    'reply_to'      => 'info@artistik.cm',
    'from'          => 'Artistik CM <info@artistik.cm>',
    'email_subject' => 'Artistik CM — Bien reçu votre message',
    'email_message' => "Bonjour $NOM,\n\n"
        . "Nous avons bien reçu votre demande concernant : $SUJET.\n"
        . "Notre équipe revient vers vous très rapidement.\n\n"
        . "Récapitulatif de votre message :\n"
        . "----------------------------------\n"
        . "$MESSAGE\n"
        . "----------------------------------\n\n"
        . "Bonne journée,\n"
        . "L'équipe Artistik CM\n"
        . "info@artistik.cm",
    'inc_user_info' => 0,
    'plain_text'    => 1,
) );
WP_CLI::log( "✓ Accusé visiteur → ID $visitor_id (email_to=$EMAIL)" );

FrmFormAction::clear_cache();
wp_cache_flush();

/* --- Test --- */
WP_CLI::log( "\n==== Test submit ====" );
$values = array();
foreach ( $fields as $f ) {
    switch ( $f->field_key ) {
        case 'ak_nom':         $values[ $f->id ] = 'Bertrand TestV4'; break;
        case 'ak_entreprise':  $values[ $f->id ] = 'Artistik QA'; break;
        case 'ak_email':       $values[ $f->id ] = 'bertrandngoufack@gmail.com'; break;
        case 'ak_tel':         $values[ $f->id ] = '+237 691 50 60 70'; break;
        case 'ak_sujet':       $values[ $f->id ] = 'Démonstration'; break;
        case 'ak_message':     $values[ $f->id ] = 'Test V4 - email_to + ID shortcodes - ' . date( 'H:i:s' ); break;
        case 'ak_consent':     $values[ $f->id ] = '1'; break;
    }
}
delete_option( 'ak_mail_log' );
$entry_id = FrmEntry::create( array( 'form_id' => $form_id, 'item_meta' => $values ) );
WP_CLI::log( "Entry créée : $entry_id" );

WP_CLI::log( "\n==== Mail-log ====" );
$log = get_option( 'ak_mail_log', array() );
foreach ( $log as $i => $e ) {
    WP_CLI::log( "[$i] " . json_encode( $e, JSON_UNESCAPED_UNICODE ) );
}

WP_CLI::success( 'v4 OK.' );
