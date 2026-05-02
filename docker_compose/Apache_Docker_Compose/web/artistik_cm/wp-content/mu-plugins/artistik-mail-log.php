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