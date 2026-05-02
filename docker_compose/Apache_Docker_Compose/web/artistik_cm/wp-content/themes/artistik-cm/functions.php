<?php
/**
 * Artistik CM — thème minimal + Bulma local (pas de CDN).
 *
 * @package Artistik_CM
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ARTISTIK_CM_VERSION', '1.0.0' );

/**
 * Chargement traductions.
 */
function artistik_cm_setup(): void {
	load_theme_textdomain( 'artistik-cm', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	register_nav_menus(
		array(
			'primary' => __( 'Menu principal', 'artistik-cm' ),
		)
	);
}
add_action( 'after_setup_theme', 'artistik_cm_setup' );

/**
 * Walker : liens du menu au format Bulma (.navbar-item).
 */
final class Artistik_CM_Nav_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ): void {}

	public function end_lvl( &$output, $depth = 0, $args = null ): void {}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ): void {
		if ( ! ( $data_object instanceof WP_Post ) ) {
			return;
		}
		$classes = in_array( 'current-menu-item', $data_object->classes ?? array(), true )
			? 'navbar-item is-active'
			: 'navbar-item';
		$output .= sprintf(
			'<a class="%1$s" href="%2$s">%3$s</a>',
			esc_attr( $classes ),
			esc_url( $data_object->url ),
			esc_html( $data_object->title )
		);
	}

	public function end_el( &$output, $data_object, $depth = 0, $args = null ): void {}
}

/**
 * Bulma depuis assets locaux (exigence : pas de dépendance CDN pour le CSS du framework).
 */
function artistik_cm_assets(): void {
	$theme_uri = get_template_directory_uri();
	$bulma_ver = '1.0.3';

	wp_enqueue_style(
		'artistik-bulma',
		$theme_uri . '/assets/vendor/bulma/bulma.min.css',
		array(),
		$bulma_ver
	);

	wp_enqueue_style(
		'artistik-cm-style',
		get_stylesheet_uri(),
		array( 'artistik-bulma' ),
		ARTISTIK_CM_VERSION
	);

	wp_add_inline_style(
		'artistik-cm-style',
		'body { scroll-behavior: smooth; }'
	);
}
add_action( 'wp_enqueue_scripts', 'artistik_cm_assets' );

/**
 * Classe body pour cibler les styles.
 */
function artistik_cm_body_class( array $classes ): array {
	$classes[] = 'theme-artistik-cm';
	return $classes;
}
add_filter( 'body_class', 'artistik_cm_body_class' );
