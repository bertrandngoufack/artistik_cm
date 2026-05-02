<?php
/**
 * Configure GoSMTP (SMTP mail.smpc.cm) + crée un formulaire Formidable de contact
 * + notifications (admin + accusé visiteur) + insertion shortcode dans la page d'accueil.
 *
 * Lancer :
 *   wp eval-file scripts/gosmtp-and-contact-form.php --path=/var/www/html/artistik_cm --allow-root
 *
 * @package Artistik_CM
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Utiliser : wp eval-file\n" );
	exit( 1 );
}

/* ---------------------------------------------------------------
 * 1. Configuration SMTP — GoSMTP (mail.smpc.cm:587 STARTTLS)
 * --------------------------------------------------------------- */
$smtp_host  = 'mail.smpc.cm';
$smtp_port  = 587;
$smtp_user  = 'tank@smpc.cm';
$smtp_pass  = 'Bateau@123';

/*
 * Délivrabilité : Gmail (et la plupart des fournisseurs) appliquent les politiques
 * SPF/DKIM/DMARC. Pour passer ces vérifications, le From: doit correspondre au
 * compte SMTP authentifié. On utilise donc tank@smpc.cm comme From: + Reply-To
 * vers info@artistik.cm (le filtre phpmailer_init du child theme s'en charge).
 */
$from_email   = $smtp_user;          // doit correspondre à smtp_username
$from_name    = 'Artistik CM';
$reply_to     = 'info@artistik.cm';
$contact_addr = 'info@artistik.cm';  // adresse affichée publiquement

$mailer_conn = array(
	'mail_type'                => 'smtp',
	'nickname'                 => 'SMPC SMTP',
	'from_email'               => $from_email,
	'force_from_email'         => 1,
	'from_name'                => $from_name,
	'force_from_name'          => 1,
	'return_path'              => 1,
	'smtp_host'                => $smtp_host,
	'smtp_port'                => $smtp_port,
	'encryption'               => 'tls',
	'smtp_auth'                => 'Yes',
	'smtp_username'            => $smtp_user,
	'smtp_password'            => $smtp_pass,
	'disable_ssl_verification' => '',
);

$existing_opts = get_option( 'gosmtp_options', array() );
if ( ! is_array( $existing_opts ) ) { $existing_opts = array(); }

$existing_mailers = isset( $existing_opts['mailer'] ) && is_array( $existing_opts['mailer'] )
	? $existing_opts['mailer']
	: array();
$existing_mailers[0] = $mailer_conn;

$gosmtp_options = array_merge(
	$existing_opts,
	array(
		'from_email'       => $from_email,
		'force_from_email' => 1,
		'from_name'        => $from_name,
		'force_from_name'  => 1,
		'return_path'      => 1,
		'mailer'           => $existing_mailers,
	)
);
update_option( 'gosmtp_options', $gosmtp_options );

/* Adresse publique de contact (utilisée comme Reply-To et destinataire admin). */
update_option( 'ak_smtp_reply_to', $reply_to );
if ( get_option( 'admin_email' ) !== $contact_addr ) {
	update_option( 'admin_email', $contact_addr );
}

echo "✅ GoSMTP configuré : SMTP $smtp_host:$smtp_port (STARTTLS)\n";
echo "   From:     $from_name <$from_email>  (aligné SMTP AUTH)\n";
echo "   Reply-To: $reply_to\n";

/* ---------------------------------------------------------------
 * 2. Formulaire de contact Formidable
 * --------------------------------------------------------------- */
if ( ! function_exists( 'FrmForm' ) && ! class_exists( 'FrmForm' ) ) {
	echo "Formidable non chargé — vérifier l'activation du plugin.\n";
	exit( 0 );
}

$form_key = 'artistik_contact';
$form_id  = FrmForm::get_id_by_key( $form_key );

if ( ! $form_id ) {
	$form_id = FrmForm::create(
		array(
			'name'        => 'Contact Artistik',
			'description' => 'Formulaire de contact pour visiteurs et clients potentiels.',
			'form_key'    => $form_key,
			'is_template' => 0,
			'status'      => 'published',
			'options'     => array(
				'submit_value'   => 'Envoyer',
				'success_action' => 'message',
				'success_msg'    => '<p>Merci pour votre message ! L’équipe Artistik vous répond sous 24 h ouvrées. Un accusé de réception vient de vous être envoyé par email.</p>',
				'akismet'        => '',
				'no_save'        => 0,
				'editable'       => 0,
				'antispam'       => 1,
				'js_validate'    => 1,
				'show_form'      => 1,
				/* On omet volontairement submit_html / before_html / after_html
				 * pour laisser Formidable injecter ses gabarits par défaut. */
			),
		)
	);
	echo "✅ Formulaire créé — id=$form_id key=$form_key\n";
} else {
	echo "ℹ️  Formulaire déjà présent — id=$form_id key=$form_key\n";
}

/* Helper : créer un champ s’il n'existe pas. */
function ak_create_field( int $form_id, string $field_key, string $type, string $name, array $extra = array() ): int {
	$field = FrmField::getOne( $field_key );
	if ( $field && (int) $field->form_id === $form_id ) {
		return (int) $field->id;
	}
	$values = array_merge(
		array(
			'form_id'  => $form_id,
			'field_key'=> $field_key,
			'type'     => $type,
			'name'     => $name,
			'required' => 0,
			'options'  => '',
		),
		$extra
	);
	$id = FrmField::create( $values );
	return (int) $id;
}

$nom_id    = ak_create_field( (int) $form_id, 'ak_nom',     'text',     'Nom complet',   array( 'required' => 1, 'field_options' => array( 'placeholder' => 'Votre nom' ) ) );
$entreprise_id = ak_create_field( (int) $form_id, 'ak_entreprise', 'text', 'Organisation', array( 'field_options' => array( 'placeholder' => 'Votre structure (optionnel)' ) ) );
$email_id  = ak_create_field( (int) $form_id, 'ak_email',   'email',    'Email',         array( 'required' => 1 ) );
$tel_id    = ak_create_field( (int) $form_id, 'ak_tel',     'phone',    'Téléphone',     array() );

$sujet_id  = ak_create_field( (int) $form_id, 'ak_sujet', 'select', 'Solution concernée',
	array(
		'required'      => 1,
		'options'       => array( 'Information générale', 'SoluMed (santé)', 'LyCol (éducation)', 'Simba (immobilier)', 'Démonstration', 'Autre' ),
		'field_options' => array( 'blank' => '— Choisir —' ),
	)
);
$msg_id    = ak_create_field( (int) $form_id, 'ak_message', 'textarea', 'Votre message',
	array( 'required' => 1, 'field_options' => array( 'placeholder' => 'Décrivez votre projet ou votre besoin…' ) )
);
$consent_id= ak_create_field( (int) $form_id, 'ak_consent', 'checkbox', 'Consentement',
	array(
		'required' => 1,
		'options'  => array( 'J’accepte d’être recontacté par Artistik au sujet de ma demande.' ),
	)
);

echo "✅ Champs synchronisés (nom, organisation, email, téléphone, sujet, message, consentement).\n";

/* ---------------------------------------------------------------
 * 3. Notifications (admin + accusé visiteur)
 * --------------------------------------------------------------- */
$existing_actions = function_exists( 'FrmFormAction' ) ? FrmFormAction::get_action_for_form( (int) $form_id, 'email' ) : array();
$has_admin_notif  = false;
$has_user_notif   = false;
foreach ( (array) $existing_actions as $a ) {
	if ( ! is_object( $a ) ) { continue; }
	if ( strpos( (string) $a->post_title, 'admin' ) !== false )    { $has_admin_notif = true; }
	if ( strpos( (string) $a->post_title, 'visiteur' ) !== false ) { $has_user_notif  = true; }
}

/* Helper : crée une notification email Formidable de manière propre. */
function ak_create_email_action( int $form_id, string $title, array $settings ): int {
	$post_id = wp_insert_post(
		array(
			'post_type'    => FrmFormActionsController::$action_post_type,
			'post_excerpt' => 'email',
			'post_title'   => $title,
			'menu_order'   => $form_id,
			'post_status'  => 'publish',
			'post_content' => '',
		)
	);
	if ( $post_id && ! is_wp_error( $post_id ) ) {
		/* Stocke les paramètres correctement. Formidable lit post_content via maybe_unserialize. */
		global $wpdb;
		$wpdb->update(
			$wpdb->posts,
			array( 'post_content' => maybe_serialize( $settings ) ),
			array( 'ID' => $post_id )
		);
		clean_post_cache( $post_id );
		return (int) $post_id;
	}
	return 0;
}

if ( ! $has_admin_notif ) {
	ak_create_email_action(
		(int) $form_id,
		'Notification admin',
		array(
			'event'         => array( 'create' ),
			'email_subject' => 'Nouveau message Artistik — [ak_sujet] · [ak_nom]',
			'email_message' => "Vous avez reçu un nouveau message via le formulaire de contact Artistik.\n\n— Nom : [ak_nom]\n— Organisation : [ak_entreprise]\n— Email : [ak_email]\n— Téléphone : [ak_tel]\n— Solution : [ak_sujet]\n\nMessage :\n[ak_message]\n\n--\nDate : [created-at]\nIP : [ip]\n",
			'email_to'      => array( 'info@artistik.cm' ),
			'cc'            => array(),
			'bcc'           => array(),
			'reply_to'      => '[ak_email]',
			'reply_to_name' => '[ak_nom]',
			'from'          => 'Artistik CM <info@artistik.cm>',
			'plain_text'    => 0,
			'inc_user_info' => 1,
		)
	);
	echo "✅ Notification admin (vers info@artistik.cm) créée.\n";
} else {
	echo "ℹ️  Notification admin déjà présente.\n";
}

if ( ! $has_user_notif ) {
	ak_create_email_action(
		(int) $form_id,
		'Accusé de réception visiteur',
		array(
			'event'         => array( 'create' ),
			'email_subject' => 'Artistik CM — Nous avons bien reçu votre message',
			'email_message' => "Bonjour [ak_nom],\n\nMerci d’avoir contacté Artistik. Nous avons bien reçu votre demande concernant : [ak_sujet].\n\nNotre équipe vous répondra sous 24 heures ouvrées à l’adresse [ak_email].\n\nRappel de votre message :\n———————————————\n[ak_message]\n———————————————\n\nÀ très vite,\nL’équipe Artistik\nhttps://artistik.cm — info@artistik.cm",
			'email_to'      => array( '[ak_email]' ),
			'cc'            => array(),
			'bcc'           => array(),
			'reply_to'      => 'info@artistik.cm',
			'reply_to_name' => 'Artistik CM',
			'from'          => 'Artistik CM <info@artistik.cm>',
			'plain_text'    => 1,
			'inc_user_info' => 0,
		)
	);
	echo "✅ Notification accusé visiteur créée (envoi vers l’email saisi).\n";
} else {
	echo "ℹ️  Notification visiteur déjà présente.\n";
}

/* ---------------------------------------------------------------
 * 4. Stocke l’ID du formulaire dans une option pour le thème.
 * --------------------------------------------------------------- */
update_option( 'ak_contact_form_id', (int) $form_id );

echo "\n✅ Tout est en place. Formulaire id=$form_id, shortcode : [formidable id=\"$form_id\"]\n";
