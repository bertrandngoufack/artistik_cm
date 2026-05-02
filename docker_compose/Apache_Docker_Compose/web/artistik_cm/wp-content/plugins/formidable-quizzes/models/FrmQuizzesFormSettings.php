<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to access this file directly.' );
}

class FrmQuizzesFormSettings {

	protected static $setting_name = 'frm_quiz_keys';

	private $form;

	/**
	 * Shows randomize Quiz settings on the form settings page.
	 *
	 * @since 3.1.7
	 *
	 * @param array $values Form values.
	 * @return void
	 */
	public static function after_form_description( $values ) {
		( new FrmQuizzesFormSettings() )->add_quizzes_settings( $values['id'] );
	}

	/**
	 * Gets the field ID.
	 *
	 * @since 3.1.7
	 *
	 * @param string $field_name Field name.
	 * @return string
	 */
	public function get_field_id( $field_name ) {
		return $field_name;
	}

	/**
	 * Adds quiz settings to the form settings page.
	 *
	 * @since 3.1.7
	 *
	 * @param int $form_id Form ID.
	 * @return void
	 */
	public function add_quizzes_settings( $form_id ) {
		$this->set_form_object( $form_id );
		$settings = $this->get_common_settings();
		include_once FrmQuizzesAppController::path() . '/views/shared/settings.php';
	}

	/**
	 * Sets the form object.
	 *
	 * @since 3.1.7
	 *
	 * @param int $form_id Form ID.
	 * @return void
	 */
	protected function set_form_object( $form_id ) {
		if ( empty( $form_id ) ) {
			return;
		}
		$this->form = FrmForm::getOne( $form_id );
	}

	/**
	 * Gets quiz action common settings.
	 *
	 * @since 3.1.7
	 *
	 * @return array
	 */
	protected function get_common_settings() {
		return array(
			'random_questions' => array(
				'label'   => __( 'Randomize fields', 'formidable-quizzes' ),
				'type'    => 'toggle',
				'help'    => __( 'Show the fields in a random order each time the form is loaded', 'formidable-quizzes' ),
				'default' => '',
			),
			'random_options'   => array(
				'label'   => __( 'Randomize field options', 'formidable-quizzes' ),
				'type'    => 'toggle',
				'help'    => __( 'Change the order of options in a field with multiple options (radio, checkbox, select)', 'formidable-quizzes' ),
				'default' => '',
			),
		);
	}

	/**
	 * @since 3.1.7
	 *
	 * @param string $field_name Field name.
	 * @return string
	 */
	public function get_field_name( $field_name ) {
		return "options[quiz][$field_name]";
	}

	/**
	 * Gets setting value.
	 *
	 * @since 3.1.7
	 *
	 * @param string $key Setting key.
	 * @return array|string
	 */
	protected function get_setting_value( $key ) {
		$form_options = $this->form->options;
		if ( isset( $form_options['quiz'] ) ) {
			return isset( $form_options['quiz'][ $key ] ) ? $form_options['quiz'][ $key ] : '';
		}
		$quiz_action = FrmQuizzesFormActionHelper::get_quiz_action_from_form( $this->form->id );
		$value       = FrmQuizzesFormActionHelper::get_setting_value( $quiz_action, $key );
		return $value;
	}

	/**
	 * Add Form Setting to set Quiz Key
	 *
	 * @param array $values
	 * @return void
	 *
	 * @deprecated 2.0
	 */
	public static function add_setting( $values ) {
		_deprecated_function( __METHOD__, '2.0' );

		$form_id = $values['id'];
		$quiz_key = self::get_key_for_form( $form_id );
		if ( ! $quiz_key ) {
			return;
		}

		include_once FrmQuizzesAppController::path() . '/views/form-settings/settings.php';
	}

	/**
	 * Old: get the first 20 entries in a form.
	 *
	 * @param int $form_id
	 *
	 * @deprecated 2.0
	 */
	public static function get_entries( $form_id ) {
		_deprecated_function( __METHOD__, '2.0' );

		$where = array(
			'form_id' => $form_id,
		);
		return FrmEntry::getAll( $where, '', 20 );
	}

	/**
	 * No longer used.
	 *
	 * @param int $form_id
	 *
	 * @deprecated 2.0
	 */
	public static function add_entry_message( $form_id ) {
		_deprecated_function( __METHOD__, '2.0' );
	}

	/**
	 * Save Quiz Key( Entry id )
	 *
	 * @param array $options
	 *
	 * @return array $options
	 *
	 * @deprecated 2.0
	 */
	public static function save_setting( $options ) {
		_deprecated_function( __METHOD__, '2.0' );
		return $options;
	}

	/**
	 * The old way to get the entry key for scoring.
	 *
	 * @param int $form_id
	 *
	 * @deprecated 2.0
	 */
	public static function get_key_for_form( $form_id ) {
		_deprecated_function( __METHOD__, '2.0' );
		$quiz_keys = self::get_setting();
		$has_key = isset( $quiz_keys[ $form_id ] ) && ! empty( $quiz_keys[ $form_id ] ) && is_numeric( $quiz_keys[ $form_id ] );
		return $has_key ? $quiz_keys[ $form_id ] : '';
	}

	/**
	 * The old way to get the settings for a quiz.
	 *
	 * @deprecated 2.0
	 */
	public static function get_setting() {
		_deprecated_function( __METHOD__, '2.0' );
		$quiz_keys = get_option( self::$setting_name );
		if ( empty( $quiz_keys ) ) {
			$quiz_keys = array();
		}
		return $quiz_keys;
	}
}
