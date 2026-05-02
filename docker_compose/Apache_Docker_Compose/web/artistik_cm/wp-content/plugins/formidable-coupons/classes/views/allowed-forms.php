<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

?>

<div class="frm-coupon-settings-title"><?php esc_html_e( 'Allowed Forms', 'formidable-coupons' ); ?></div>

<span class="frm-coupon-setting-description">
	<?php esc_html_e( 'Only forms that have a coupon field will be shown here.', 'formidable-coupons' ); ?>
</span>

<div id="frm-coupon-allowed-forms-search-wrapper">
	<p class="frm-search">
		<label class="screen-reader-text" for="allowed-forms-list-search-input">
			Search:
		</label>
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_search_icon frm_svg20' ); ?>
		<input type="search" id="allowed-forms-list-search-input" name="s" placeholder="<?php esc_attr_e( 'Search Forms', 'formidable-coupons' ); ?>" class="frm-search-input frm-auto-search" data-tosearch="frm-coupon-form-option" autocomplete="off" />
	</p>
</div>

<?php
$forms = FrmCouponsAppHelper::get_allowed_forms_form_options();

if ( ! $forms ) {
	?>
	<div class="frm-allowed-forms-empty-state">
		<?php esc_html_e( 'No forms with a coupon field exist.', 'formidable-coupons' ); ?>
	</div>
	<?php
	return;
}

$allowed_form_ids = FrmCouponsAppHelper::get_allowed_form_ids( $coupon_id ?? 0 );
?>
<div class="frm-coupon-form-header-row">
	<div>
		<?php
		$all_selected = true;
		foreach ( $forms as $form ) {
			if ( ! in_array( (int) $form->id, $allowed_form_ids, true ) ) {
				$all_selected = false;
				break;
			}
		}
		FrmHtmlHelper::toggle(
			'frm_coupon_form_all_allowed_forms_toggle',
			'',
			array(
				'checked' => $all_selected,
				'echo'    => true,
			)
		);
		?>
		<h5><?php esc_html_e( 'Form', 'formidable-coupons' ); ?></h5>
	</div>
	<div>
		<h5><?php esc_html_e( 'Entries', 'formidable-coupons' ); ?></h5>
	</div>
	<div>
		<h5><?php esc_html_e( 'Coupon Uses', 'formidable-coupons' ); ?></h5>
	</div>
</div>
<?php

$index            = 0;
foreach ( $forms as $form ) {
	$even_odd_class = 0 === $index % 2 ? 'frm-even' : 'frm-odd';
	?>
	<div class="frm-coupon-form-option <?php echo esc_attr( $even_odd_class ); ?>">
		<div>
			<?php
			$name = '' === $form->name ? FrmFormsHelper::get_no_title_text() : $form->name;
			FrmHtmlHelper::toggle(
				'frm_coupon_form_' . $form->id,
				'allowed_forms[' . $form->id . ']',
				array(
					'checked'     => in_array( (int) $form->id, $allowed_form_ids, true ),
					'echo'        => true,
					'show_labels' => true,
					'on_label'    => '<a class="frm-coupon-form-link" href="' . esc_url( admin_url( 'admin.php?page=formidable&frm_action=edit&id=' . $form->id ) ) . '" target="_blank">' . esc_html( $name ) . '</a>',
				)
			);
			?>
		</div>
		<div>
			<?php echo esc_html( (string) FrmEntry::getRecordCount( $form->id ) ); ?>
		</div>
		<div>
			<?php
			if ( ! empty( $coupon ) && '' !== $coupon->post_excerpt ) {
				echo esc_html( (string) FrmCouponsAppHelper::get_coupon_uses_by_code( $coupon->post_excerpt, $form->id ) );
			} else {
				echo 0;
			}
			?>
		</div>
	</div>
	<?php
	++$index;
}