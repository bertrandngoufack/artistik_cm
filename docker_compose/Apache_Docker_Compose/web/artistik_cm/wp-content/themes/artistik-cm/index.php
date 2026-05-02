<?php
/**
 * Index — délègue à la boucle par défaut (pages construites surtout avec Elementor).
 *
 * @package Artistik_CM
 */

get_header();
?>
<main id="primary" class="section">
	<div class="container">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'content' ); ?>>
					<?php the_content(); ?>
				</article>
				<?php
			endwhile;
		else :
			?>
			<p><?php esc_html_e( 'Aucun contenu pour le moment.', 'artistik-cm' ); ?></p>
			<?php
		endif;
		?>
	</div>
</main>
<?php
get_footer();
