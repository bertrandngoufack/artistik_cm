<?php
/**
 * Footer simplifié — Artistik (thème enfant AIHub).
 *
 * @package AIHub_Child_Artistik
 */
?>
<footer class="ak-footer" role="contentinfo">
	<div class="ak-footer-inner">
		<div>
			<strong><?php bloginfo( 'name' ); ?></strong>
			<p class="ak-muted">Solutions logicielles métier — Santé · Éducation · Immobilier.</p>
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
