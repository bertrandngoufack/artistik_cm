<?php
/**
 * Default footer template
 *
 * @package Hub
 */

$footer_id = liquid_get_custom_footer_id();

?>
<footer <?php liquid_helper()->attr( 'footer' ); ?>>

	<?php liquid_helper()->get_elementor_edit_cpt( $footer_id, 'Footer' ); ?>

	<div id="lqd-page-footer" data-lqd-view="liquidPageFooter">
		<?php
			if ( function_exists( 'icl_object_id' ) ) {
				$footer_id = icl_object_id( $footer_id, 'page', false, ICL_LANGUAGE_CODE );
			}
			if ( function_exists( 'pll_get_post' ) ) {
				$footer_id = pll_get_post( $footer_id );
			}

			if ( defined( 'ELEMENTOR_VERSION' ) ){
				echo Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $footer_id );
			}
		?>
	</div>
</footer>