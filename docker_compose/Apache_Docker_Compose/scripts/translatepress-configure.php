<?php
/**
 * Configure TranslatePress : FR par défaut, EN comme langue secondaire,
 * sélecteur de langue activé. Téléverse les .mo si nécessaire.
 *
 * Lancer :
 *   wp eval-file scripts/translatepress-configure.php --path=/var/www/html/artistik_cm --allow-root
 *
 * @package Artistik_CM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'get_option' ) ) {
	fwrite( STDERR, "Utiliser : wp eval-file\n" );
	exit( 1 );
}

/* Force la langue d’admin sur FR si possible. */
if ( function_exists( 'switch_to_locale' ) ) {
	update_option( 'WPLANG', 'fr_FR' );
	update_option( 'timezone_string', 'Africa/Douala' );
}

$existing = get_option( 'trp_settings', array() );
if ( ! is_array( $existing ) ) { $existing = array(); }

$publish_languages = array( 'fr_FR', 'en_US' );

$settings = array_merge(
	array(
		'default-language'      => 'fr_FR',
		'translation-languages' => array( 'en_US' ),
		'publish-languages'     => $publish_languages,
		'add-subdirectory-to-default-language' => 'no',
		'force-language-to-custom-links'       => 'yes',
		'native_or_english_name'                => 'native_name',
		'g-translate'                          => 'no',
		'translation-memory'                    => 'enabled',
		'enable-language-publishing'            => 'yes',
		'show-language-switcher'                => 'yes',
		'shortcode-options'      => array(
			'flags-position'        => 'left',
			'shortcode-name'        => 'language-switcher',
			'full-language-names'   => 'yes',
			'short-language-names'  => 'no',
			'flags'                 => 'yes',
		),
		'menu-options' => array(
			'flags-position'        => 'left',
			'full-language-names'   => 'yes',
			'short-language-names'  => 'no',
			'flags'                 => 'yes',
		),
		'floater-options' => array(
			'enabled'               => 'no',
			'flags-position'        => 'left',
			'full-language-names'   => 'yes',
			'short-language-names'  => 'no',
			'flags'                 => 'yes',
		),
		'url-slugs' => array(
			'fr_FR' => 'fr',
			'en_US' => 'en',
		),
	),
	$existing
);

/* Force-cible : on impose en/fr et FR par défaut, même si une ancienne config existait. */
$settings['default-language']      = 'fr_FR';
$settings['translation-languages'] = array( 'en_US' );
$settings['publish-languages']     = $publish_languages;
$settings['url-slugs']             = array( 'fr_FR' => 'fr', 'en_US' => 'en' );
$settings['native_or_english_name'] = 'native_name';
$settings['add-subdirectory-to-default-language'] = 'no';
$settings['force-language-to-custom-links']       = 'yes';
$settings['translation-memory']                   = 'enabled';
$settings['enable-language-publishing']           = 'yes';
$settings['show-language-switcher']               = 'yes';

update_option( 'trp_settings', $settings );

/* Code de langue d'affichage du sélecteur — flag visuel. */
update_option( 'trp_advanced_settings', array_merge(
	(array) get_option( 'trp_advanced_settings', array() ),
	array(
		'enable_hreflang_xdefault'         => 'yes',
		'force_slash_at_end_of_links'      => 'yes',
		'translate_emails'                 => 'yes',
	)
) );

printf(
	"OK — TranslatePress configuré : défaut = %s, langues publiées = [%s].\n",
	$settings['default-language'],
	implode( ', ', $settings['publish-languages'] )
);
