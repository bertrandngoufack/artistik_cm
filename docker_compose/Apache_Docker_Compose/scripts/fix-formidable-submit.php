<?php
/**
 * Restaure le bouton « Envoyer » du formulaire de contact si son submit_html
 * a été enregistré vide à la création.
 *
 * Lancer :
 *   wp eval-file scripts/fix-formidable-submit.php --path=/var/www/html/artistik_cm --allow-root
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Utiliser : wp eval-file\n" );
	exit( 1 );
}

if ( ! class_exists( 'FrmForm' ) ) {
	echo "Formidable non chargé.\n";
	exit( 1 );
}

$form_id = (int) get_option( 'ak_contact_form_id', 0 );
if ( $form_id <= 0 ) {
	$form_id = (int) FrmForm::get_id_by_key( 'artistik_contact' );
}
if ( $form_id <= 0 ) {
	echo "Formulaire introuvable.\n";
	exit( 1 );
}

$form = FrmForm::getOne( $form_id );
if ( ! $form ) {
	echo "Form #$form_id introuvable.\n";
	exit( 1 );
}

$options = is_array( $form->options ) ? $form->options : maybe_unserialize( $form->options );
if ( ! is_array( $options ) ) { $options = array(); }

/* Récupère le HTML du bouton par défaut si absent ou vide */
if ( empty( $options['submit_html'] ) || trim( wp_strip_all_tags( $options['submit_html'] ) ) === '' ) {
	$default = '';
	if ( class_exists( 'FrmFormsHelper' ) && method_exists( 'FrmFormsHelper', 'get_default_html' ) ) {
		$default = (string) FrmFormsHelper::get_default_html( 'submit' );
	}
	if ( $default === '' ) {
		$default = '<div class="frm_submit"><button class="frm_button_submit frm_final_submit" type="submit" formnovalidate="formnovalidate" data-frm-final-submit="true" data-frm-final-button="true">[button_label]</button></div>';
	}
	$options['submit_html'] = $default;
	echo "✅ submit_html restauré au défaut Formidable.\n";
}

/* Garantit aussi le label du bouton et l'action de succès */
if ( empty( $options['submit_value'] ) ) {
	$options['submit_value'] = 'Envoyer';
}
if ( empty( $options['success_action'] ) ) {
	$options['success_action'] = 'message';
}
if ( empty( $options['success_msg'] ) ) {
	$options['success_msg'] = '<p>Merci pour votre message ! L’équipe Artistik vous répond sous 24 h ouvrées. Un accusé de réception vient de vous être envoyé par email.</p>';
}

/* Garantit before_html / after_html par défaut */
foreach ( array( 'before_html' => 'before', 'after_html' => 'after' ) as $opt_key => $type ) {
	if ( empty( $options[ $opt_key ] ) && class_exists( 'FrmFormsHelper' ) && method_exists( 'FrmFormsHelper', 'get_default_html' ) ) {
		$options[ $opt_key ] = (string) FrmFormsHelper::get_default_html( $type );
	}
}

global $wpdb;
$wpdb->update(
	$wpdb->prefix . 'frm_forms',
	array( 'options' => maybe_serialize( $options ) ),
	array( 'id' => $form_id )
);

if ( method_exists( 'FrmForm', 'clear_form_cache' ) ) {
	FrmForm::clear_form_cache();
}

echo "✅ Formulaire #$form_id mis à jour. Label bouton : " . $options['submit_value'] . "\n";
