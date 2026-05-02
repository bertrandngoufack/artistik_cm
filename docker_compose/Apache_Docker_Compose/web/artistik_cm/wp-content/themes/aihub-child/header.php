<?php
/**
 * Header simplifié pour Artistik (override AIHub via thème enfant).
 *
 * @package AIHub_Child_Artistik
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'artistik-body' ); ?>>
<?php wp_body_open(); ?>

<header id="ak-header" class="ak-header" role="banner">
	<div class="ak-header-inner">
		<a class="ak-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Artistik — accueil', 'aihub-child-artistik' ); ?>">
			<span class="ak-brand-mark" aria-hidden="true"></span>
			<span class="ak-brand-text"><?php bloginfo( 'name' ); ?></span>
		</a>

		<button class="ak-burger" aria-controls="ak-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'aihub-child-artistik' ); ?>">
			<span></span><span></span><span></span>
		</button>

		<nav id="ak-nav" class="ak-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'aihub-child-artistik' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_id'        => 'ak-primary-menu',
						'menu_class'     => 'ak-menu',
						'depth'          => 2,
						'fallback_cb'    => false,
					)
				);
			} else {
				echo '<ul class="ak-menu"><li><a href="#solumed">SoluMed</a></li><li><a href="#lycol">LyCol</a></li><li><a href="#simba">Simba</a></li><li><a href="#contact">Contact</a></li></ul>';
			}
			?>
			<?php
			if ( function_exists( 'artistik_lang_switcher_html' ) ) {
				echo artistik_lang_switcher_html();
			}
			?>
			<a class="ak-btn ak-btn--primary ak-cta-btn" href="#contact"><?php esc_html_e( 'Démo', 'aihub-child-artistik' ); ?></a>
		</nav>
	</div>
</header>

<script>
(function () {
	var burger = document.querySelector('.ak-burger');
	var nav = document.getElementById('ak-nav');
	if (burger && nav) {
		burger.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			burger.classList.toggle('is-open', open);
			burger.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		nav.addEventListener('click', function (e) {
			var t = e.target;
			if (t && t.tagName === 'A' && nav.classList.contains('is-open')) {
				nav.classList.remove('is-open');
				burger.classList.remove('is-open');
				burger.setAttribute('aria-expanded', 'false');
			}
		});
	}
	var hdr = document.getElementById('ak-header');
	if (hdr) {
		var onScroll = function () { hdr.classList.toggle('is-stuck', window.scrollY > 8); };
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}
})();
</script>
