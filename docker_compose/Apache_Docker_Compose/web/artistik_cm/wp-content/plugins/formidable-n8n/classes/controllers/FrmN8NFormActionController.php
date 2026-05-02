<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmN8NFormActionController
 */
class FrmN8NFormActionController {

	/**
	 * Register the n8n form action.
	 *
	 * @param array $actions The form actions.
	 *
	 * @return array The updated form actions.
	 */
	public static function register_actions( $actions ) {
		$actions['n8n'] = 'FrmN8NFormAction';
		return $actions;
	}

	/**
	 * Filter the form action saved value.
	 *
	 * @param array $post_content Form action saved value.
	 *
	 * @return array
	 */
	public static function filter_saved_value( $post_content ) {
		if ( empty( $post_content['token'] ) ) {
			$post_content['token'] = self::generate_token();
		}

		if ( empty( $post_content['mapping'] ) || empty( $post_content['mapping']['key'] ) ) {
			return $post_content;
		}

		$new_mapping = array();

		foreach ( $post_content['mapping']['key'] as $index => $key ) {
			if ( ! $key ) {
				// Skip mapping row if key is empty.
				continue;
			}

			$value = $post_content['mapping']['value'][ $index ] ?? '';

			$new_mapping[ $key ] = $value;
		}

		$post_content['mapping'] = $new_mapping;

		return $post_content;
	}

	/**
	 * Generates token.
	 *
	 * @return string
	 */
	public static function generate_token() {
		return FrmAppHelper::generate_new_key( 12 );
	}

	/**
	 * Triggers the n8n action.
	 *
	 * @param object     $form_action The form action post object.
	 * @param int|object $entry       The entry object or entry ID getting created/updated.
	 * @param int|object $form        The form object or form ID that includes the entry.
	 * @param string     $event       The trigger event.
	 *
	 * @return void
	 */
	public static function trigger_action( $form_action, $entry, $form, $event ) {
		if ( empty( $form_action->post_content['webhook'] ) ) {
			return;
		}

		$data = array(
			'token'   => $form_action->post_content['token'],
			'event'   => $event,
			'mapping' => array(),
		);

		foreach ( $form_action->post_content['mapping'] as $key => $value ) {
			$data['mapping'][ $key ] = apply_filters( 'frm_content', $value, $form, $entry );
		}

		/**
		 * Filter the request data sent to n8n.
		 *
		 * @since x.x
		 *
		 * @param array $data Request data.
		 * @param array $args Contains `form_action`, `entry`, `form`, and `event`.
		 */
		$data = apply_filters( 'frm_n8n_request_data', $data, compact( 'form_action', 'entry', 'form', 'event' ) );

		$response = wp_remote_post(
			$form_action->post_content['webhook'],
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => json_encode( $data ),
			)
		);

		$log_args                 = compact( 'form_action', 'entry', 'form', 'event', 'response' );
		$log_args['request_data'] = $data;
		self::log_results( $log_args );
	}

	/**
	 * Write a message to the FrmLog (if it exists).
	 *
	 * @param array $args Args.
	 *
	 * @return void
	 */
	private static function log_results( $args ) {
		if ( ! class_exists( 'FrmLog' ) ) {
			return;
		}

		$log = new FrmLog();
		$log->add(
			array(
				'title'   => $args['form_action']->post_title,
				'content' => (array) $args['response'],
				'fields'  => array(
					'entry'   => $args['entry']->id,
					'action'  => $args['form_action']->ID,
					'code'    => wp_remote_retrieve_response_code( $args['response'] ),
					'url'     => $args['form_action']->post_content['webhook'],
					'request' => $args['request_data'],
				),
			)
		);
	}
}
