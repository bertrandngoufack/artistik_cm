<?php
/**
 * 1) Supprime les actions email orphelines et les doublons sur le form contact.
 * 2) Met à jour les actions actives avec les bons destinataires + reply-to.
 * 3) Active un mail-log persistant (option ak_mail_log : 50 derniers envois).
 * 4) Envoie un test depuis le formulaire pour vérifier la chaîne complète.
 *
 * @package Artistik_CM
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$form_id = (int) get_option( 'ak_contact_form_id', 0 );
if ( ! $form_id ) {
    WP_CLI::error( 'ak_contact_form_id introuvable' );
}

/* ---------------------------------------------------------------
 * 1. Charger toutes les actions du form via Formidable
 * --------------------------------------------------------------- */
$actions = FrmFormAction::get_action_for_form( $form_id );
$by_title = array();
foreach ( $actions as $a ) {
    $by_title[ $a->post_title ][] = (int) $a->ID;
}

/* Pour chaque titre, garder le premier ID et supprimer les autres (doublons) */
$keep = array();
foreach ( $by_title as $title => $ids ) {
    sort( $ids );
    $keep[ $title ] = $ids[0];
    for ( $i = 1; $i < count( $ids ); $i++ ) {
        wp_delete_post( $ids[ $i ], true );
        WP_CLI::log( "✗ Supprimé doublon action #{$ids[$i]} ($title)" );
    }
}

/* Supprimer aussi les vieilles actions orphelines */
$orphans = $wpdb->get_col( $wpdb->prepare(
    "SELECT ID FROM {$wpdb->posts}
     WHERE post_type = 'frm_form_actions'
       AND ID NOT IN (" . implode( ',', array_map( 'intval', $keep ) ) . ")"
) );
foreach ( $orphans as $oid ) {
    wp_delete_post( (int) $oid, true );
    WP_CLI::log( "✗ Orpheline supprimée action #$oid" );
}

/* ---------------------------------------------------------------
 * 2. Mettre à jour les actions conservées avec settings propres
 * --------------------------------------------------------------- */
function ak_update_email_action( int $action_id, array $settings ): void {
    global $wpdb;
    $wpdb->update(
        $wpdb->posts,
        array( 'post_content' => maybe_serialize( $settings ) ),
        array( 'ID' => $action_id )
    );
    clean_post_cache( $action_id );
}

$contact_email = 'info@artistik.cm';

if ( isset( $keep['Notification admin'] ) ) {
    $aid = (int) $keep['Notification admin'];
    ak_update_email_action( $aid, array(
        'event'         => array( 'create' ),
        'to'            => $contact_email,
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
    WP_CLI::log( "✓ Action #$aid (Notification admin → $contact_email) reconfigurée." );
}

if ( isset( $keep['Accusé de réception visiteur'] ) ) {
    $aid = (int) $keep['Accusé de réception visiteur'];
    ak_update_email_action( $aid, array(
        'event'         => array( 'create' ),
        'to'            => '[ak_email]',
        'cc'            => '',
        'bcc'           => '',
        'reply_to'      => $contact_email,
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
            . "$contact_email",
        'inc_user_info' => 0,
        'plain_text'    => 1,
    ) );
    WP_CLI::log( "✓ Action #$aid (Accusé visiteur → [ak_email]) reconfigurée." );
}

/* ---------------------------------------------------------------
 * 3. Mail log persistant (50 derniers envois, en option)
 *    Le hook est ajouté ici de façon idempotente via mu-plugin.
 * --------------------------------------------------------------- */
$mu_dir  = WPMU_PLUGIN_DIR;
if ( ! is_dir( $mu_dir ) ) {
    @mkdir( $mu_dir, 0755, true );
}
$mu_file = $mu_dir . '/artistik-mail-log.php';
$plugin_code = <<<'PHP'
<?php
/**
 * Plugin Name: Artistik Mail Log
 * Description: Trace les 50 derniers envois wp_mail() dans l'option ak_mail_log.
 * Version: 1.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_mail_failed', function ( $err ) {
    artistik_log_mail( array( 'ok' => false, 'error' => is_wp_error( $err ) ? $err->get_error_message() : print_r( $err, true ) ) );
} );

add_filter( 'wp_mail', function ( $args ) {
    artistik_log_mail( array(
        'ok'      => true,
        'to'      => is_array( $args['to'] ?? '' ) ? implode( ',', $args['to'] ) : (string) ( $args['to'] ?? '' ),
        'subject' => (string) ( $args['subject'] ?? '' ),
        'len'     => strlen( is_array( $args['message'] ?? '' ) ? print_r( $args['message'], true ) : (string) ( $args['message'] ?? '' ) ),
    ) );
    return $args;
} );

function artistik_log_mail( array $entry ): void {
    $log = get_option( 'ak_mail_log', array() );
    if ( ! is_array( $log ) ) { $log = array(); }
    $entry['date'] = current_time( 'mysql' );
    array_unshift( $log, $entry );
    $log = array_slice( $log, 0, 50 );
    update_option( 'ak_mail_log', $log, false );
}
PHP;
file_put_contents( $mu_file, $plugin_code );
WP_CLI::log( "✓ MU-plugin mail-log installé : $mu_file" );

/* ---------------------------------------------------------------
 * 4. Test : simuler un submit du formulaire avec données factices
 * --------------------------------------------------------------- */
WP_CLI::log( "\n==== Test : simulation d'un submit du formulaire ====" );
if ( class_exists( 'FrmEntry' ) ) {
    /* Charger les IDs des champs */
    $fields = FrmField::get_all_for_form( $form_id );
    $values = array();
    foreach ( $fields as $f ) {
        switch ( $f->field_key ) {
            case 'ak_nom':     $values[ $f->id ] = 'Test Auto'; break;
            case 'ak_email':   $values[ $f->id ] = 'bertrandngoufack@gmail.com'; break;
            case 'ak_tel':     $values[ $f->id ] = '+237 600 000 000'; break;
            case 'ak_sujet':   $values[ $f->id ] = 'Information générale'; break;
            case 'ak_message': $values[ $f->id ] = "Test automatique de la chaîne formulaire → wp_mail → SMTP smpc.cm.\nDate : " . date( 'd/m/Y H:i:s' ); break;
            case 'ak_consent': $values[ $f->id ] = 1; break;
        }
    }
    $entry_id = FrmEntry::create( array(
        'form_id' => $form_id,
        'item_meta' => $values,
    ) );
    WP_CLI::log( "✓ Entry de test créée : ID = $entry_id" );
    WP_CLI::log( "  → cela déclenche normalement les 2 actions email (admin + accusé visiteur)" );
} else {
    WP_CLI::warning( 'FrmEntry indisponible — submit non simulé.' );
}

WP_CLI::log( "\n==== Mail log après test ====" );
$log = get_option( 'ak_mail_log', array() );
foreach ( array_slice( $log, 0, 10 ) as $i => $e ) {
    WP_CLI::log( "  [$i] " . json_encode( $e, JSON_UNESCAPED_UNICODE ) );
}

WP_CLI::success( 'Formulaire nettoyé + actions reconfigurées + mail-log installé + test envoyé.' );
