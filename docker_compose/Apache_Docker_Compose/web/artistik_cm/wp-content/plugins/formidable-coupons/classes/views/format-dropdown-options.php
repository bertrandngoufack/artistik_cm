<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$field_type = $field['type'];

if ( 'coupon' !== $field['type'] ) {
	include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/format-dropdown-options.php';
	return;
}

FrmHtmlHelper::echo_dropdown_option(
	__( 'Currency', 'formidable' ),
	true,
	array(
		'value'           => 'currency',
		'data-dependency' => '#frm-field-format-global-currency-' . $field['id'],
	)
);
