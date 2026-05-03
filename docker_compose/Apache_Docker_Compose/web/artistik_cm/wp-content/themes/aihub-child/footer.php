<?php
/**
 * Footer simplifié — Artistik (thème enfant AIHub).
 *
 * @package AIHub_Child_Artistik
 */
?>
<footer class="ak-footer" role="contentinfo">
	<div class="ak-footer-inner">
		<div class="ak-footer-brand">
			<strong><?php bloginfo( 'name' ); ?></strong>
			<p class="ak-muted">Solutions logicielles métier — Santé · Éducation · Immobilier.</p>
			<div class="ak-footer-social">
				<ul class="ak-social-strip" role="list" aria-label="<?php esc_attr_e( 'Réseaux sociaux (liens à venir)', 'aihub-child-artistik' ); ?>">
					<li class="ak-social-item">
						<span class="ak-social-slot ak-social-slot--linkedin" role="img" aria-label="<?php esc_attr_e( 'LinkedIn — non disponible pour le moment', 'aihub-child-artistik' ); ?>">
							<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452z"/></svg>
						</span>
					</li>
					<li class="ak-social-item">
						<span class="ak-social-slot ak-social-slot--x" role="img" aria-label="<?php esc_attr_e( 'X (Twitter) — non disponible pour le moment', 'aihub-child-artistik' ); ?>">
							<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.451-6.231Zm-1.161 17.52h1.833L7.084 4.126H5.117l12.067 17.624Z"/></svg>
						</span>
					</li>
					<li class="ak-social-item">
						<span class="ak-social-slot ak-social-slot--facebook" role="img" aria-label="<?php esc_attr_e( 'Facebook — non disponible pour le moment', 'aihub-child-artistik' ); ?>">
							<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12c0 4.94 3.49 9.06 8.08 10v-7h-2.4V12h2.41V9.77c0-2.45 1.49-3.81 3.71-3.81 1.07 0 2.41.21 2.41.21v2.71h-1.35c-1.35 0-1.76.84-1.76 1.7V12h2.93l-.47 3h-2.46v7c4.59-.94 8.07-5.06 8.07-10C22 6.48 17.52 2 12 2Z"/></svg>
						</span>
					</li>
					<li class="ak-social-item">
						<span class="ak-social-slot ak-social-slot--instagram" role="img" aria-label="<?php esc_attr_e( 'Instagram — non disponible pour le moment', 'aihub-child-artistik' ); ?>">
							<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.16 2A3.625 3.625 0 0 0 4.02 7.82v8.41C4.02 18.93 6.06 21 8.72 21h8.53a3.627 3.627 0 0 0 3.71-3.71V8.71C21 6.06 18.93 4 16.27 4H8.62l-.03-.002h-.012M12 7.382a4.617 4.617 0 1 1 0 9.237 4.617 4.617 0 0 1 0-9.237m0 1.8a2.817 2.817 0 1 0 0 5.634 2.817 2.817 0 0 0 0-5.634Zm5.35-4.63a1.086 1.086 0 1 1 0 2.172 1.086 1.086 0 0 1 0-2.172Z"/></svg>
						</span>
					</li>
				</ul>
			</div>
		</div>
		<div class="ak-footer-cols">
			<div>
				<h6>Solutions</h6>
				<ul>
					<li><a href="#solumed">SoluMed</a></li>
					<li><a href="#lycol">LyCol</a></li>
					<li><a href="#simba">Simba</a></li>
				</ul>
			</div>
			<div>
				<h6>Société</h6>
				<ul>
					<li><a href="#solutions">Nos solutions</a></li>
					<li><a href="#contact">Contact</a></li>
				</ul>
			</div>
			<div>
				<h6>Contact</h6>
				<ul>
					<li><a href="https://artistik.cm" rel="noopener">artistik.cm</a></li>
					<li><a href="mailto:info@artistik.cm">info@artistik.cm</a></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="ak-footer-bar">
		<small>© <?php echo (int) gmdate( 'Y' ); ?> Artistik. Tous droits réservés.</small>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
