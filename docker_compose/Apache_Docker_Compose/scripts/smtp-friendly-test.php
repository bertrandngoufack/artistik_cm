<?php
/**
 * 2 envois test : un court "humain" + un avec en-têtes complètes.
 * Affiche le résultat et le Message-ID pour que l'utilisateur puisse vérifier.
 *
 * @package Artistik_CM
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* S'assurer que le reply-to est bien fixé (utilisé par le filtre du child theme) */
update_option( 'ak_smtp_reply_to', 'info@artistik.cm' );

$to = 'bertrandngoufack@gmail.com';

/* ---- MAIL 1 — court & humain (faible spam-score) ---- */
$subject1 = 'Bonjour Bertrand';
$body1    = "Bonjour Bertrand,\n\n"
          . "Petit message de la part d'Artistik CM.\n"
          . "Si tu lis ces lignes, la chaîne SMTP est opérationnelle.\n\n"
          . "Bonne journée,\n"
          . "L'équipe Artistik";

/* ---- MAIL 2 — version HTML avec contenu commercial typique ---- */
$subject2 = 'Confirmation de votre demande sur le site Artistik';
$body2    = '<p>Bonjour Bertrand,</p>'
          . '<p>Nous avons bien reçu votre message via notre site '
          . '<a href="https://artistik.cm">artistik.cm</a> et reviendrons '
          . 'vers vous très rapidement.</p>'
          . '<p>Cordialement,<br/>L\'équipe Artistik CM</p>';
$headers2 = array(
    'Content-Type: text/html; charset=UTF-8',
    'Reply-To: info@artistik.cm',
);

/* Capturer Message-ID de chaque envoi */
$message_ids = array();
add_action( 'phpmailer_init', function ( $mailer ) use ( &$message_ids ) {
    /** @var PHPMailer\PHPMailer\PHPMailer $mailer */
    $mailer->Hostname = 'artistik.cm';
    $mailer->Helo     = 'artistik.cm';
    $mailer->XMailer  = 'Artistik CM mailer';
    if ( ! $mailer->MessageID ) {
        $mailer->MessageID = sprintf( '<%s@artistik.cm>',
            bin2hex( random_bytes( 12 ) ) . '.' . time() );
    }
    $message_ids[] = $mailer->MessageID;
}, 999999 );

$failed = '';
add_action( 'wp_mail_failed', function ( $err ) use ( &$failed ) {
    $failed .= ( is_wp_error( $err ) ? $err->get_error_message() : print_r( $err, true ) ) . "\n";
} );

echo "==== Envoi 1 (texte court) à $to ====\n";
$ok1 = wp_mail( $to, $subject1, $body1 );
echo 'Résultat : ' . ( $ok1 ? '✅ TRUE' : '❌ FALSE' ) . "\n\n";

echo "==== Envoi 2 (HTML, sujet commercial) à $to ====\n";
$ok2 = wp_mail( $to, $subject2, $body2, $headers2 );
echo 'Résultat : ' . ( $ok2 ? '✅ TRUE' : '❌ FALSE' ) . "\n\n";

if ( $failed ) {
    echo "==== Erreurs wp_mail_failed ====\n$failed\n";
}

echo "==== Message-IDs envoyés ====\n";
foreach ( $message_ids as $mid ) {
    echo "  $mid\n";
}

echo "\n==== Que faire côté Gmail ? ====\n";
echo "1. Ouvrir https://mail.google.com\n";
echo "2. Vérifier le dossier 'Spam' / 'Indésirables' (les nouveaux expéditeurs y vont souvent)\n";
echo "3. Vérifier 'Promotions' et 'Notifications'\n";
echo "4. Rechercher : from:tank@smpc.cm (ou) from:smpc.cm\n";
echo "5. Si trouvé → cliquer 'Pas spam' + ajouter aux contacts\n";
echo "6. Sans rien trouver → vérifier auprès du registrar smpc.cm si DKIM peut être ajouté\n";
