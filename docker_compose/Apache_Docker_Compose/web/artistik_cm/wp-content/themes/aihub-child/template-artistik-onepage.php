<?php
/**
 * Template Name: Artistik — Solutions (one-page)
 *
 * Page d'accueil one-page pilotée à 100 % depuis l'admin :
 *  - Hero / stats / contact via le Customizer
 *  - Solutions via le CPT « Solution »
 *  - Pages additionnelles via Pages → Ajouter
 *  - Menu via Apparence → Menus
 *
 * @package AIHub_Child_Artistik
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="artistik-onepage" class="artistik-op" role="main">
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			$content = get_the_content();
			if ( trim( wp_strip_all_tags( $content ) ) !== '' ) {
				echo '<section class="ak-section ak-section--page-content"><div class="ak-container ak-prose">';
				the_content();
				echo '</div></section>';
			}
		}
	}

	if ( function_exists( 'artistik_render_onepage' ) ) {
		artistik_render_onepage();
	}
	?>
</main>

<?php
get_footer();
