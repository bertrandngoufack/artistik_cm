<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmCouponsMigrate {

	/**
	 * @var int
	 */
	private $db_version = 1;

	/**
	 * @var string
	 */
	private $option_name = 'frm_coupons_version';

	/**
	 * @return void
	 */
	private function __construct() {
		if ( $this->needs_migration() ) {
			// For now, there are no migrate functions.
			// We just need to update the stylesheet with every new version.
			self::update_stylesheet();
			$this->update_version();
		}
	}

	/**
	 * @return bool
	 */
	private function needs_migration() {
		$needs_upgrade = FrmAppController::compare_for_update(
			array(
				'option'             => $this->option_name,
				'new_db_version'     => $this->db_version,
				'new_plugin_version' => FrmCouponsAppHelper::plugin_version(),
			)
		);

		return $needs_upgrade;
	}

	/**
	 * @return void
	 */
	private function update_version() {
		update_option( $this->option_name, FrmCouponsAppHelper::plugin_version() . '-' . $this->db_version );
	}

	/**
	 * @return void
	 */
	public static function init() {
		new self();
	}

	/**
	 * @return void
	 */
	private static function update_stylesheet() {
		$frm_style = new FrmStyle();
		$frm_style->update( 'default' );
	}
}
