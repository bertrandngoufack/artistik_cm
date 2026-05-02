<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
class FrmQuizzesFieldOption extends FrmFieldOption {

	/**
	 * Set the saved value
	 *
	 * @since 2.03.05
	 *
	 * @return void
	 */
	protected function set_saved_value() {
		$this->saved_value = $this->option_key;
	}
}
