<?php
/**
 * App controller
 *
 * @package FrmAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FrmAIAppController
 */
class FrmAIAppController {

	/**
	 * Shows the incompatible notice.
	 *
	 * @return void
	 */
	public static function show_incompatible_notice() {
		if ( FrmAIAppHelper::is_compatible() ) {
			return;
		}
		?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'You are running an outdated version of Formidable Forms.', 'formidable-ai' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Initializes plugin translation.
	 *
	 * @return void
	 */
	public static function init_translation() {
		load_plugin_textdomain( 'formidable-ai', false, FrmAIAppHelper::plugin_folder() . '/languages/' );
	}

	/**
	 * Includes addon updater.
	 *
	 * @return void
	 */
	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			FrmAIUpdate::load_hooks();
		}
	}

	/**
	 * Trigger a CSS update when the plugin is activated.
	 *
	 * @return void
	 */
	public static function update_stylesheet_on_activation() {
		if ( ! function_exists( 'load_formidable_forms' ) || ! function_exists( 'get_filesystem_method' ) ) {
			return;
		}

		// This is run before other hooks, so we need to manually load the CSS.
		add_action( 'frm_include_front_css', array( __CLASS__, 'load_css' ) );

		$frm_style = new FrmStyle();
		$frm_style->update( 'default' );
	}

	/**
	 * Tell Formidable where to find the new field type.
	 *
	 * @param string $class The name of the class that extends FrmFieldType.
	 * @param string $field_type The type of field.
	 * @return string The name of the new class that extends FrmFieldType.
	 */
	public static function get_field_type_class( $class, $field_type ) {
		if ( $field_type === 'ai' ) {
			$class = 'FrmAIField';
		}
		return $class;
	}

	/**
	 * Add the AI field to the list of available fields.
	 *
	 * @param array $fields The list of available fields.
	 * @return array
	 */
	public static function add_new_field( $fields ) {
		$fields['ai'] = array(
			'name' => 'AI',
			'icon' => 'frm_icon_font frm_eye_icon',
		);
		return $fields;
	}

	/**
	 * Switch the watch_ai IDs when a field is imported.
	 * TODO: Handle this in the core plugin like the form actions.
	 *
	 * @param array $values The field values to save.
	 * @return array
	 */
	public static function switch_ids_after_import( $values ) {
		global $frm_duplicate_ids;

		$setting      = 'watch_ai';
		$old_field_id = isset( $values['field_options'][ $setting ] ) ? $values['field_options'][ $setting ] : 0;
		if ( ! $old_field_id || ! is_array( $old_field_id ) ) {
			return $values;
		}

		$values['field_options'][ $setting ] = array();
		foreach ( $old_field_id as $old_id ) {
			$values['field_options'][ $setting ][] = isset( $frm_duplicate_ids[ $old_id ] ) ? $frm_duplicate_ids[ $old_id ] : $old_id;
		}

		return $values;
	}

	/**
	 * Include the AI field js in form builder.
	 *
	 * @return void
	 */
	public static function enqueue_builder_scripts() {
		if ( ! FrmAppHelper::is_form_builder_page() ) {
			return;
		}
		$version = FrmAIAppHelper::$plug_version;
		wp_enqueue_script( 'formidable_ai_admin', FrmAIAppHelper::plugin_url() . '/js/admin.js', array( 'formidable_dom' ), $version, true );
	}

	/**
	 * Include the AI field js for ajax forms.
	 *
	 * @param array $scripts The list of scripts to allow.
	 * @return array
	 */
	public static function ajax_load_script( $scripts ) {
		$scripts[] = 'frmai';
		return $scripts;
	}

	/**
	 * Add a Watch fields row in the field options (when the + or Watch Fields link is clicked)
	 *
	 * @return void
	 */
	public static function add_watch_ai_row() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$row_key  = FrmAppHelper::get_post_param( 'row_key', '', 'absint' );
		$field_id = FrmAppHelper::get_post_param( 'field_id', '', 'absint' );
		$form_id  = FrmAppHelper::get_post_param( 'form_id', '', 'absint' );

		$selected_field = '';
		$watch_fields   = FrmField::get_all_for_form( $form_id, '', 'include', 'exclude' );

		ob_start();
		include FrmAIAppHelper::plugin_path() . '/classes/views/watch-row.php';
		$return = ob_get_contents();
		ob_end_clean();
		wp_send_json_success( $return );
	}

	/**
	 * The ajax endpoint for the AI requests.
	 *
	 * @return void
	 */
	public static function get_ai_response() {
		$data = file_get_contents( 'php://input' );
		if ( $data ) {
			$data = (array) json_decode( $data, true );
		}

		if ( empty( $data ) || empty( $data['id'] ) ) {
			wp_send_json_error( 'No form ID provided' );
			return; // Let PHPStan exclude a few parameter types.
		}

		// Set the token as a POST variable so that the anti-spam check will work.
		$_POST['antispam_token'] = $data['token'];
		if ( self::is_spam( absint( $data['id'] ) ) ) {
			wp_send_json_error( 'Spam detected' );
		}

		FrmAIChatGPT::get_json_response( $data );
	}

	/**
	 * Check if the form submission is spam using the js token.
	 *
	 * @param int $form_id The ID of the current form.
	 * @return bool
	 */
	private static function is_spam( $form_id ) {
		$aspm = new FrmAntiSpam( $form_id );
		return is_string( $aspm->validate() );
	}

	/**
	 * Add the CSS into the main FF CSS file.
	 *
	 * @param array $args The arguments for the CSS.
	 * @return void
	 */
	public static function load_css( $args ) {
		$bg_color = FrmStylesHelper::adjust_brightness( $args['defaults']['border_color'], 45 );
		include FrmAIAppHelper::plugin_path() . '/css/front-end.css.php';
	}
}
