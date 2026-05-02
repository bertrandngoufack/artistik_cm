<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<optgroup label="<?php echo esc_attr( $optgroup_label ); ?>">
<?php
foreach ( $current_list_array as $list ) {
	if ( ! $list->dynamic && ! empty( $list->listId ) ) { // phpcs:ignore WordPress.NamingConventions
		?>
		<option value="<?php echo esc_attr( $list->listId ); // phpcs:ignore WordPress.NamingConventions ?>"
			<?php selected( $list_id, $list->listId ); // phpcs:ignore WordPress.NamingConventions ?>>
			<?php echo esc_html( FrmAppHelper::truncate( $list->name, 40 ) ); ?>
		</option>
		<?php
	}
}
?>
</optgroup>
