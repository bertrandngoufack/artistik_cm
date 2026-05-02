<?php
/**
 * Test wp_mail() en capturant TOUTE la conversation SMTP (debug niveau 4).
 *
 * @package Artistik_CM
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* 0. S'assurer que reply-to est configuré */
update_option( 'ak_smtp_reply_to', 'info@artistik.cm' );

$to      = 'bertrandngoufack@gmail.com';
$subject = '[Artistik CM] Test SMTP automatique ' . date( 'Y-m-d H:i:s' );
$body    = "<h2>Bonjour Bertrand,</h2>"
         . "<p>Ceci est un test automatique envoyé par Artistik CM.</p>"
         . "<ul>"
         . "<li>Date : " . date( 'd/m/Y H:i:s' ) . "</li>"
         . "<li>Serveur SMTP : mail.smpc.cm:587 (STARTTLS)</li>"
         . "<li>Compte : tank@smpc.cm</li>"
         . "<li>From: : Artistik CM &lt;tank@smpc.cm&gt; (aligné SPF/DMARC)</li>"
         . "<li>Reply-To: : info@artistik.cm</li>"
         . "</ul>"
         . "<p>Si tu reçois ce mail, vérifie SVP les en-têtes (Authentication-Results) "
         . "pour voir le statut SPF/DKIM/DMARC.</p>"
         . "<p>— Artistik CM</p>";

$headers = array(
    'Content-Type: text/html; charset=UTF-8',
    'Reply-To: info@artistik.cm',
);

/* === Capturer tout le debug PHPMailer === */
$debug = '';
add_action( 'phpmailer_init', function ( $mailer ) use ( &$debug ) {
    /** @var PHPMailer\PHPMailer\PHPMailer $mailer */
    $mailer->SMTPDebug   = 4;          // 0=off, 1=client, 2=client+server, 3=connection, 4=low-level
    $mailer->Debugoutput = function ( $str, $level ) use ( &$debug ) {
        $debug .= "[L$level] $str\n";
    };
    $mailer->Hostname = 'artistik.cm';
    $mailer->Helo     = 'artistik.cm';
    $mailer->XMailer  = 'Artistik CM mailer';
}, 999999 );

/* Capturer aussi les erreurs wp_mail */
$mail_failed_msg = '';
add_action( 'wp_mail_failed', function ( $err ) use ( &$mail_failed_msg ) {
    $mail_failed_msg = is_wp_error( $err ) ? $err->get_error_message() : print_r( $err, true );
} );

echo "==== Envoi à $to ====\n\n";
$ok = wp_mail( $to, $subject, $body, $headers );
echo "Résultat wp_mail() : " . ( $ok ? '✅ TRUE' : '❌ FALSE' ) . "\n\n";

if ( $mail_failed_msg ) {
    echo "==== wp_mail_failed ====\n$mail_failed_msg\n\n";
}

echo "==== Conversation SMTP (debug PHPMailer niveau 4) ====\n";
echo $debug;
