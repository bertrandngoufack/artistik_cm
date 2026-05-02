<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.0
 */
class FrmQuizzesFieldValueSelector extends FrmProFieldValueSelector {

	protected function set_options() {
		parent::set_options();
		$this->options = wp_list_pluck(
			FrmQuizzesFormActionHelper::get_quiz_outcomes_for_form( $this->db_row->form_id, true ),
			'post_title',
			'ID'
		);
		$this->trigger_options_filter();
	}

	/**
	 * Get an instance of FrmFieldOption
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return FrmQuizzesFieldOption
	 */
	protected function get_single_field_option( $key, $value ) {
		return new FrmQuizzesFieldOption( $key, $value );
	}
}
