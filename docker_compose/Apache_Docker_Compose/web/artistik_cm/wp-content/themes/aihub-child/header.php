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
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'artistik-body' ); ?>>
<?php wp_body_open(); ?>

<header id="ak-header" class="ak-header" role="banner">
	<div id="ak-nav-backdrop" class="ak-nav-backdrop" aria-hidden="true"></div>
	<div class="ak-header-inner">
		<a class="ak-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Artistik — accueil', 'aihub-child-artistik' ); ?>">
			<span class="ak-brand-mark" aria-hidden="true"></span>
			<span class="ak-brand-text"><?php bloginfo( 'name' ); ?></span>
		</a>

		<button type="button" class="ak-burger" aria-controls="ak-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'aihub-child-artistik' ); ?>">
			<span></span><span></span><span></span>
		</button>
	</div>

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
</header>

<script>
(function () {
	var burger = document.querySelector('.ak-burger');
	var nav = document.getElementById('ak-nav');
	var backdrop = document.getElementById('ak-nav-backdrop');
	var hdr = document.getElementById('ak-header');
	var mqMobile = typeof window.matchMedia === 'function' ? window.matchMedia('(max-width: 960px)') : null;

	function syncNavTop() {
		if (!hdr) {
			return;
		}
		/* Bas réel dans le viewport + marge pour ombre/header flou (anti « menu coupé »). */
		var r = hdr.getBoundingClientRect();
		var slack = mqMobile && mqMobile.matches ? 12 : 4;
		var px = Math.ceil(r.bottom) + slack;
		document.documentElement.style.setProperty('--ak-nav-top', px + 'px');
	}

	function closeSiblingSubmenus(li) {
		var p = li && li.parentElement;
		if (!p || !nav) {
			return;
		}
		Array.prototype.forEach.call(p.children, function (sib) {
			if (!sib.classList.contains('menu-item-has-children') || sib === li) {
				return;
			}
			sib.classList.remove('is-submenu-open');
			var b = sib.querySelector(':scope > .ak-submenu-toggle');
			if (b) {
				b.setAttribute('aria-expanded', 'false');
			}
		});
	}

	function closeAllSubmenus() {
		var menu = nav ? nav.querySelector('.ak-menu') : null;
		if (!menu) {
			return;
		}
		Array.prototype.forEach.call(menu.children, function (li) {
			if (!li.classList.contains('menu-item-has-children')) {
				return;
			}
			li.classList.remove('is-submenu-open');
			var b = li.querySelector(':scope > .ak-submenu-toggle');
			if (b) {
				b.setAttribute('aria-expanded', 'false');
			}
		});
	}

	function setMenu(open) {
		if (!burger || !nav || !backdrop) {
			return;
		}
		closeAllSubmenus();
		nav.classList.toggle('is-open', open);
		burger.classList.toggle('is-open', open);
		burger.setAttribute('aria-expanded', open ? 'true' : 'false');
		burger.setAttribute(
			'aria-label',
			open
				? '<?php echo esc_js( __( 'Fermer le menu', 'aihub-child-artistik' ) ); ?>'
				: '<?php echo esc_js( __( 'Ouvrir le menu', 'aihub-child-artistik' ) ); ?>'
		);
		backdrop.classList.toggle('is-visible', open);
		backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
		document.documentElement.classList.toggle('ak-nav-open', open);
		if (hdr) {
			hdr.classList.toggle('ak-nav-drawer-open', open && mqMobile && mqMobile.matches);
		}
		document.body.style.overflow = open ? 'hidden' : '';
		syncNavTop();
		requestAnimationFrame(syncNavTop);
		if (open) {
			setTimeout(syncNavTop, 50);
			setTimeout(syncNavTop, 260);
		}
	}

	function injectSubmenuToggles() {
		var menu = nav ? nav.querySelector('.ak-menu') : null;
		if (!menu) {
			return;
		}
		Array.prototype.forEach.call(menu.children, function (li) {
			if (!li.classList.contains('menu-item-has-children') || li.querySelector(':scope > .ak-submenu-toggle')) {
				return;
			}
			var link = li.querySelector(':scope > a');
			var submenu = li.querySelector(':scope > ul.sub-menu');
			if (!link || !submenu) {
				return;
			}
			var toggle = document.createElement('button');
			toggle.type = 'button';
			toggle.className = 'ak-submenu-toggle';
			toggle.setAttribute('aria-expanded', 'false');
			toggle.setAttribute('aria-haspopup', 'true');
			toggle.setAttribute(
				'aria-label',
				'<?php echo esc_js( __( 'Basculer le sous-menu', 'aihub-child-artistik' ) ); ?>'
			);
			link.insertAdjacentElement('afterend', toggle);
			toggle.addEventListener('click', function () {
				if (!mqMobile || !mqMobile.matches || !nav) {
					return;
				}
				var willOpen = !li.classList.contains('is-submenu-open');
				if (willOpen) {
					closeSiblingSubmenus(li);
					li.classList.add('is-submenu-open');
					toggle.setAttribute('aria-expanded', 'true');
				} else {
					li.classList.remove('is-submenu-open');
					toggle.setAttribute('aria-expanded', 'false');
				}
			});
		});
	}

	if (!burger || !nav || !backdrop || !hdr) {
		return;
	}

	syncNavTop();
	injectSubmenuToggles();

	burger.addEventListener('click', function () {
		setMenu(!nav.classList.contains('is-open'));
	});

	backdrop.addEventListener('click', function () {
		setMenu(false);
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && nav.classList.contains('is-open')) {
			setMenu(false);
		}
	});

	if (mqMobile && mqMobile.addEventListener) {
		mqMobile.addEventListener('change', function () {
			if (!mqMobile.matches) {
				setMenu(false);
			}
			if (hdr) {
				hdr.classList.remove('ak-nav-drawer-open');
			}
			syncNavTop();
		});
	}

	nav.addEventListener('click', function (e) {
		if (!nav.classList.contains('is-open')) {
			return;
		}
		var link = e.target && e.target.closest instanceof Function ? e.target.closest('a') : null;
		if (!(link instanceof HTMLAnchorElement) || link.closest('.ak-lang')) {
			return;
		}
		if (!nav.contains(link)) {
			return;
		}
		closeAllSubmenus();
		setMenu(false);
	}, false);

	window.addEventListener('resize', syncNavTop, { passive: true });

	window.addEventListener('orientationchange', function () {
		setTimeout(syncNavTop, 150);
	}, { passive: true });

	window.addEventListener('scroll', function () {
		hdr.classList.toggle('is-stuck', window.scrollY > 8);
	}, { passive: true });

	hdr.classList.toggle('is-stuck', window.scrollY > 8);

	if (typeof ResizeObserver !== 'undefined') {
		new ResizeObserver(syncNavTop).observe(hdr);
	}
})();
</script>
