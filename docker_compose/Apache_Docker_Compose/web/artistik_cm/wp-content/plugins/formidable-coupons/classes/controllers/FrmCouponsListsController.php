<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmCouponsListsController {

	/**
	 * @since 1.0
	 *
	 * @param bool $list_displayed
	 *
	 * @return bool
	 */
	public static function on_coupon_list_displayed( $list_displayed ) {
		self::render_list();
		return true;
	}

	/**
	 * Render coupons list.
	 *
	 * @return void
	 */
	private static function render_list() {
		FrmTransLiteListHelper::render_tabs( 'coupons' );

		$wp_list_table = new FrmCouponsListHelper( self::list_page_params() );
		$wp_list_table->prepare_items();
		$wp_list_table->display();
	}

	/**
	 * @return array
	 */
	private static function list_page_params() {
		$values = array();
		foreach ( array(
			'id'    => '',
			'paged' => 1,
			'sort'  => '',
			'sdir'  => '',
		) as $var => $default ) {
			$values[ $var ] = FrmAppHelper::get_param( $var, $default );
		}

		return $values;
	}

	/**
	 * @return void
	 */
	public static function admin_head() {
		$unread_count = FrmEntriesHelper::get_visible_unread_inbox_count();
		$hook_name    = 'manage_' . sanitize_title( FrmAppHelper::get_menu_name() ) . ( $unread_count ? '-' . $unread_count : '' ) . '_page_formidable-payments_columns';

		add_filter( $hook_name, self::class . '::coupon_columns', 11 );
		add_filter( 'screen_options_show_screen', self::class . '::remove_screen_options', 10, 2 );
	}

	/**
	 * @param array $columns
	 *
	 * @return array
	 */
	public static function coupon_columns( $columns = array() ) {
		if ( 'coupons' !== FrmAppHelper::simple_get( 'action' ) ) {
			return $columns;
		}

		$columns = array();

		add_screen_option(
			'per_page',
			array(
				'label'   => esc_html__( 'Coupons', 'formidable' ),
				'default' => 20,
				'option'  => 'formidable_page_formidable_coupons_per_page',
			)
		);

		$columns['name']       = esc_html__( 'Name', 'formidable' );
		$columns['code']       = esc_html__( 'Code', 'formidable-coupons' );
		$columns['amount']     = esc_html__( 'Amount', 'formidable' );
		$columns['usage']      = esc_html__( 'Uses', 'formidable-coupons' );
		$columns['forms']      = esc_html__( 'Forms', 'formidable' );
		$columns['status']     = esc_html__( 'Status', 'formidable' );
		$columns['start_date'] = esc_html__( 'Start Date', 'formidable-coupons' );
		$columns['end_date']   = esc_html__( 'End Date', 'formidable-coupons' );

		return $columns;
	}

	/**
	 * Prevent the "screen options" tab from showing when
	 * editing or creating an entry
	 *
	 * @since 1.0
	 *
	 * @param bool   $show_screen
	 * @param object $screen
	 *
	 * @return bool
	 */
	public static function remove_screen_options( $show_screen, $screen ) {
		$menu_name    = sanitize_title( FrmAppHelper::get_menu_name() );
		$unread_count = FrmEntriesHelper::get_visible_unread_inbox_count();

		if ( $screen->id !== $menu_name . ( $unread_count ? '-' . $unread_count : '' ) . '_page_formidable-payments' ) {
			return $show_screen;
		}

		if ( in_array( FrmAppHelper::simple_get( 'action' ), array( 'new-coupon', 'edit-coupon' ), true ) ) {
			$show_screen = false;
		}

		return $show_screen;
	}

	/**
	 * @param mixed      $save
	 * @param string     $option
	 * @param int|string $value
	 *
	 * @return mixed
	 */
	public static function save_per_page( $save, $option, $value ) {
		if ( 'formidable_page_formidable_coupons_per_page' === $option ) {
			$save = absint( $value );
		}
		return $save;
	}
}
