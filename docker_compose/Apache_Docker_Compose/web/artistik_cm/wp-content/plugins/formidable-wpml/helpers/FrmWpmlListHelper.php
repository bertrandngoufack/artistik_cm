<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FrmWpmlListHelper
 *
 * @since 1.13
 *
 * @package formidable-wpml
 */

/**
 * Provides methods to use for pagination navigation and list table in general.
 *
 * @since 1.13
 */
class FrmWpmlListHelper extends FrmListHelper {

	/**
	 * A method that sets all the necessary pagination arguments.
	 *
	 * @since 1.13
	 * @access public
	 *
	 * @param array $args An associative array with information about the pagination.
	 * @return void
	 */
	public function call_set_pagination_args( $args ) {
		parent::set_pagination_args( $args );
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 1.13
	 * @access public
	 *
	 * @param string $which
	 * @return void
	 */
	public function call_display_tablenav( $which ) {
		parent::display_tablenav( $which );
	}
}
