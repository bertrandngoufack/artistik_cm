<?php
/**
 * En-tête minimal — menus et barre à personnaliser dans Elementor / Apparence.
 *
 * @package Artistik_CM
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<nav class="navbar is-artistik is-light" role="navigation" aria-label="<?php esc_attr_e( 'Menu principal', 'artistik-cm' ); ?>">
	<div class="navbar-brand">
		<a class="navbar-item has-text-weight-bold" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			Artistik
		</a>
		<a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="artistik-nav">
			<span aria-hidden="true"></span>
			<span aria-hidden="true"></span>
			<span aria-hidden="true"></span>
		</a>
	</div>
	<div id="artistik-nav" class="navbar-menu">
		<div class="navbar-start">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'fallback_cb'    => false,
					'walker'         => new Artistik_CM_Nav_Walker(),
				)
			);
			?>
		</div>
	</div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const burger = document.querySelector('.navbar-burger');
	const nav = document.getElementById('artistik-nav');
	if (burger && nav) {
		burger.addEventListener('click', function() {
			burger.classList.toggle('is-active');
			nav.classList.toggle('is-active');
		});
	}
});
</script>
