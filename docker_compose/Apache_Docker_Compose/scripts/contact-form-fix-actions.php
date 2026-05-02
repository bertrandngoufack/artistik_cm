<?php
/**
 * Reconstruit proprement les 2 actions email du formulaire Contact en
 * utilisant l'API officielle FrmFormAction (et non un patch direct du
 * post_content), pour que Formidable accepte tous les paramètres
 * (sujet, from, reply_to, message, destinataire).
 *
 * Ensuite envoie un test depuis le formulaire et affiche le mail-log.
 *
 * @package Artistik_CM
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$form_id = (int) get_option( 'ak_contact_form_id', 0 );
if ( ! $form_id ) {
    WP_CLI::error( 'ak_contact_form_id introuvable' );
}

/* Charger les classes Formidable */
if ( ! class_exists( 'FrmFormAction' ) ) {
    WP_CLI::error( 'FrmFormAction indisponible' );
}

/* ---------------------------------------------------------------
 * 1. Supprimer toutes les anciennes actions email du formulaire
 * --------------------------------------------------------------- */
$existing = FrmFormAction::get_action_for_form( $form_id, 'email' );
foreach ( $existing as $a ) {
    wp_delete_post( $a->ID, true );
    WP_CLI::log( "✗ Action email #{$a->ID} supprimée" );
}
/* Plus pour faire bonne mesure : tous les frm_form_actions orphelins */
$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'frm_form_actions'" );
WP_CLI::log( '✓ Table actions purgée.' );

/* ---------------------------------------------------------------
 * 2. Créer les 2 actions via l'API officielle
 * --------------------------------------------------------------- */
$action_email = FrmFormAction::get_action_class( 'email' );
$instance = new $action_email();

/* --- Action 1 : Notification admin (info@artistik.cm) --- */
$admin_settings = array(
    'event'          => array( 'create' ),
    'to'             => 'info@artistik.cm',
    'cc'             => '',
    'bcc'            => '',
    'reply_to'       => '[ak_email]',
    'reply_to_name'  => '[ak_nom]',
    'from'           => 'Artistik CM <tank@smpc.cm>',
    'email_subject'  => '[Artistik CM] Nouveau message de [ak_nom] — [ak_sujet]',
    'email_message'  => "Bonjour,\n\n"
        . "Un nouveau message a été reçu via le formulaire de contact du site artistik.cm.\n\n"
        . "Nom        : [ak_nom]\n"
        . "Email      : [ak_email]\n"
        . "Téléphone  : [ak_tel]\n"
        . "Solution   : [ak_sujet]\n\n"
        . "Message :\n[ak_message]\n\n"
        . "—\n"
        . "Envoyé depuis le formulaire de contact d'artistik.cm",
    'inc_user_info'  => 0,
    'plain_text'     => 1,
    'conditions'     => array( 'send_stop' => '', 'any_all' => '', 'show_hide' => '' ),
);

$admin_id = wp_insert_post( array(
    'post_type'    => 'frm_form_actions',
    'post_status'  => 'publish',
    'post_title'   => 'Notification admin',
    'post_excerpt' => 'email',
    'post_name'    => 'frm_form_actions_' . $form_id . '_admin',
    'post_content' => json_encode( $admin_settings ), /* Formidable ré-encode lui-même */
    'menu_order'   => $form_id,
) );
/* Attacher au form via menu_order + utiliser la méthode interne save */
update_post_meta( $admin_id, '_frm_form_id', $form_id );
$instance_admin = $instance;
$instance_admin->id_base = 'email';
$instance_admin->name    = 'Email Notifications';
$instance_admin->_set( $admin_id );
$instance_admin->save_settings( array_merge( $admin_settings, array(
    'post_title'    => 'Notification admin',
    'menu_order'    => $form_id,
    'ID'            => $admin_id,
    'post_status'   => 'publish',
    'post_excerpt'  => 'email',
    'event'         => array( 'create' ),
) ) );
WP_CLI::log( "✓ Action 'Notification admin' créée → ID $admin_id" );

/* --- Action 2 : Accusé visiteur ([ak_email]) --- */
$visitor_settings = array(
    'event'          => array( 'create' ),
    'to'             => '[ak_email]',
    'cc'             => '',
    'bcc'            => '',
    'reply_to'       => 'info@artistik.cm',
    'reply_to_name'  => 'Artistik CM',
    'from'           => 'Artistik CM <tank@smpc.cm>',
    'email_subject'  => 'Artistik CM — Bien reçu votre message',
    'email_message'  => "Bonjour [ak_nom],\n\n"
        . "Nous avons bien reçu votre demande concernant : [ak_sujet].\n"
        . "Notre équipe revient vers vous très rapidement.\n\n"
        . "Récapitulatif de votre message :\n"
        . "----------------------------------\n"
        . "[ak_message]\n"
        . "----------------------------------\n\n"
        . "Bonne journée,\n"
        . "L'équipe Artistik CM\n"
        . "info@artistik.cm",
    'inc_user_info'  => 0,
    'plain_text'     => 1,
    'conditions'     => array( 'send_stop' => '', 'any_all' => '', 'show_hide' => '' ),
);

$visitor_id = wp_insert_post( array(
    'post_type'    => 'frm_form_actions',
    'post_status'  => 'publish',
    'post_title'   => 'Accusé de réception visiteur',
    'post_excerpt' => 'email',
    'post_name'    => 'frm_form_actions_' . $form_id . '_visitor',
    'post_content' => json_encode( $visitor_settings ),
    'menu_order'   => $form_id,
) );
update_post_meta( $visitor_id, '_frm_form_id', $form_id );
$instance_visitor = $instance;
$instance_visitor->id_base = 'email';
$instance_visitor->name    = 'Email Notifications';
$instance_visitor->_set( $visitor_id );
$instance_visitor->save_settings( array_merge( $visitor_settings, array(
    'post_title'    => 'Accusé de réception visiteur',
    'menu_order'    => $form_id,
    'ID'            => $visitor_id,
    'post_status'   => 'publish',
    'post_excerpt'  => 'email',
    'event'         => array( 'create' ),
) ) );
WP_CLI::log( "✓ Action 'Accusé visiteur' créée → ID $visitor_id" );

/* ---------------------------------------------------------------
 * 3. Re-vérifier le format final lu par Formidable
 * --------------------------------------------------------------- */
$reload = FrmFormAction::get_action_for_form( $form_id, 'email' );
WP_CLI::log( "\n==== Re-lecture des actions ====" );
foreach ( $reload as $a ) {
    $cfg = is_array( $a->post_content ) ? $a->post_content : maybe_unserialize( $a->post_content );
    WP_CLI::log( "Action #{$a->ID} '{$a->post_title}' :" );
    WP_CLI::log( "   to            = " . ( $cfg['to'] ?? '?' ) );
    WP_CLI::log( "   from          = " . ( $cfg['from'] ?? '?' ) );
    WP_CLI::log( "   reply_to      = " . ( $cfg['reply_to'] ?? '?' ) );
    WP_CLI::log( "   email_subject = " . ( $cfg['email_subject'] ?? '?' ) );
    WP_CLI::log( "   plain_text    = " . ( $cfg['plain_text'] ?? '?' ) );
}

/* ---------------------------------------------------------------
 * 4. Test : simuler un submit
 * --------------------------------------------------------------- */
WP_CLI::log( "\n==== Test submit ====" );
$fields = FrmField::get_all_for_form( $form_id );
$values = array();
foreach ( $fields as $f ) {
    switch ( $f->field_key ) {
        case 'ak_nom':     $values[ $f->id ] = 'Bertrand TestFix'; break;
        case 'ak_email':   $values[ $f->id ] = 'bertrandngoufack@gmail.com'; break;
        case 'ak_tel':     $values[ $f->id ] = '+237 600 000 000'; break;
        case 'ak_sujet':   $values[ $f->id ] = 'Information générale'; break;
        case 'ak_message': $values[ $f->id ] = "Test API officielle Formidable à " . date( 'H:i:s' ); break;
        case 'ak_consent': $values[ $f->id ] = '1'; break;
    }
}
delete_option( 'ak_mail_log' ); /* reset log */
$entry_id = FrmEntry::create( array( 'form_id' => $form_id, 'item_meta' => $values ) );
WP_CLI::log( "Entry créée : $entry_id" );

WP_CLI::log( "\n==== Mail-log post-submit ====" );
$log = get_option( 'ak_mail_log', array() );
foreach ( $log as $i => $e ) {
    WP_CLI::log( "[$i] " . json_encode( $e, JSON_UNESCAPED_UNICODE ) );
}

WP_CLI::success( 'Actions reconfigurées via API officielle. Vérifie le mail-log.' );
