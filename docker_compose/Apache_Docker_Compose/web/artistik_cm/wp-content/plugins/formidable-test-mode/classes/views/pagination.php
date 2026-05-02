<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_test_mode_pagination">
<?php
if ( ! empty( $pages ) ) {
	foreach ( $pages as $page_number => $page_data ) {
		$class = $page_data['class'];

		// This current page is always disabled.
		if ( isset( $page_data['aria-disabled'] ) ) {
			$class .= ' frm_test_mode_active_page';
		}

		if ( false !== strpos( $class, 'frm_page_skip' ) ) {
			$class .= ' frm-button-secondary';
		}

		$input_attrs = array(
			'type'       => 'button',
			'value'      => $page_number,
			'data-page'  => $page_data['data-page'],
			'class'      => $class,
			'data-field' => $page_data['data-field'],
		);

		if ( isset( $page_data['aria-disabled'] ) ) {
			$input_attrs['aria-disabled'] = $page_data['aria-disabled'];
		}
		?>
		<input <?php FrmAppHelper::array_to_html_params( $input_attrs, true ); ?> />
		<?php
	}
}
?>
</div>
