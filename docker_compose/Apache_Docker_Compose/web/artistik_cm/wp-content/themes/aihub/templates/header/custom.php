<?php
/**
 * Default header template
 *
 * @package Hub
 */

$header = liquid_get_header_layout();

if( function_exists( 'icl_object_id' ) ) {
    $header['id'] = icl_object_id( $header['id'], 'page', false, ICL_LANGUAGE_CODE );
}
if ( function_exists( 'pll_get_post' ) ) {
    $header['id'] = pll_get_post( $header['id'] );
}


?>
<header <?php liquid_helper()->attr( 'header', $header['attributes'] ); ?>>

    <?php liquid_helper()->get_elementor_edit_cpt( $header['id'], 'Header' ); ?>

    <div id="lqd-page-header" data-lqd-view="liquidPageHeader">
        <?php
            liquid_action( 'before_header_tag' );

            if ( defined( 'ELEMENTOR_VERSION' ) ){
                echo Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $header['id'] );
            }

            liquid_action( 'after_header_tag' );
        ?>
    </div>
</header>