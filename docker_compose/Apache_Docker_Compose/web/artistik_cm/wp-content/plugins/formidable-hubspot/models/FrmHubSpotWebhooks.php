<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmHubSpotWebhooks
 */
final class FrmHubSpotWebhooks extends WP_REST_Controller {

	/**
	 * Reject the request if the timestamp is older than 5 minutes. Otherwise, proceed with validating the signature.
	 * 5 minutes in milliseconds.
	 */
	const MAX_ALLOWED_TIMESTAMP = 300000;

	/**
	 * Rest base name.
	 *
	 * @var string
	 */
	protected $rest_base = 'hubspot/webhooks';

	/**
	 * Entry ID which contains matched HubSpot field.
	 *
	 * @var int
	 */
	private $entry_id;

	/**
	 * HubSpot field id.
	 *
	 * @var int
	 */
	private $hubspot_field_id;

	/**
	 * Registers routes for urls.
	 *
	 * @since 2.0
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {
		register_rest_route(
			'frm/v2',
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'process_webhook_request' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => array( $this, 'signature_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Callback for the proxy.
	 *
	 * @since 2.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error on failure.
	 */
	public function process_webhook_request( WP_REST_Request $request ) {
		$email_field_id      = '';
		// Get all hubspot actions for forms and check which actions is responsible for incoming webhook.
		$form_id = FrmDb::get_var( 'frm_items', array( 'id' => $this->entry_id ), 'form_id' );
		$actions = FrmFormAction::get_action_for_form( $form_id, 'hubspot' );
		foreach ( $actions as $k => $action ) {
			if ( (int) $this->hubspot_field_id === (int) $action->post_content['hubspot_hidden_field'] ) {
				$email_field_id = $action->post_content['fields']['email'];
				$email_value    = FrmEntryMeta::get_entry_meta_by_field( $this->entry_id, $action->post_content['fields']['email'] );
				break;
			}
		}

		/**
		 * Filters whether email should be erased or return otherwise.
		 *
		 * @since 2.0
		 *
		 * @param string $email_value email value or __return_false to cancel the edit.
		 */
		$email_value = apply_filters( 'frm_hubspot_webhook_deletion', '' );

		if ( false === $email_value || '' === $email_field_id ) {
			return new WP_Error( 'action_not_allowed', __( 'Not allowed!', 'formidable-hubspot' ), array( 'status' => 304 ) );
		}

		FrmEntryMeta::update_entry_meta( $this->entry_id, $email_field_id, null, $email_value );

		$response = array(
			'entry_id' => $this->entry_id,
			'field_id' => $email_field_id,
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Validation of Hubspot Signature.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @since 2.0
	 *
	 * @return bool|WP_Error
	 */
	public function signature_permissions_check( WP_REST_Request $request ) {
		// Prevents webhook route to resolve unless OAuth2 enabled.
		if ( ! FrmHubSpotAppHelper::get_active_authorization_class() instanceof FrmHubSpotOAuth ) {
			return new WP_Error( 'unauthorized', __( '401 Unauthorized!', 'formidable-hubspot' ), array( 'status' => 401 ) );
		}

		// Early exit when there is no matching field.
		$this->match_hubspot_contact_object( $request );
		if ( ! $this->entry_id || ! $this->hubspot_field_id ) {
			return new WP_Error( 'action_not_allowed', __( 'No Content!', 'formidable-hubspot' ), array( 'status' => 204 ) );
		}

		// Send REST request to rely endpoint ensure incoming request is valid.
		$remote_request = new FrmHubSpotRemoteRequest( true, 20, 1 );

		$url = add_query_arg(
			array(
				'portalId'  => $request->get_param( 'portalId' ),
				'objectId'  => $request->get_param( 'objectId' ),
				'signature' => $request->get_param( 'signature' ),
			),
			FrmHubSpotAppHelper::get_rely_endpoint()
		);

		try {
			$response = $remote_request->post( $url );
		} catch ( Exception $exception ) {
			/* translators: %1$s: the fetched URL, %2$s the error message that was returned */
			return new WP_Error( 'http_error', sprintf( __( 'Failed to fetch: %1$s ', 'formidable-hubspot' ), $exception->getMessage() ) );
		}

		$response = json_decode( wp_remote_retrieve_body( (array) $response ), true );
		if ( is_array( $response ) && $response['delete'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves the query params for the rely.
	 *
	 * @since 2.0
	 *
	 * @return array Query parameters for the collection.
	 */
	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		$query_params['portalId'] = array(
			'description'       => __( 'portal id.', 'formidable-hubspot' ),
			'required'          => true,
			'type'              => 'integer',
			'validate_callback' => array( $this, 'validate_callback' ),
			'sanitize_callback' => 'absint',
		);

		$query_params['objectId'] = array(
			'description'       => __( 'object id.', 'formidable-hubspot' ),
			'required'          => true,
			'type'              => 'integer',
			'validate_callback' => array( $this, 'validate_callback' ),
			'sanitize_callback' => 'absint',
		);

		$query_params['signature'] = array(
			'description'       => __( 'signature', 'formidable-hubspot' ),
			'required'          => true,
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		);

		return $query_params;
	}

	/**
	 * Callback to validate integers.
	 *
	 * @since 2.0
	 *
	 * @param string $value Value to be validated.
	 * @return true|WP_Error
	 */
	public function validate_callback( $value ) {
		if ( ! $value || ! is_numeric( $value ) ) {
			return new WP_Error( 'rest_invalid_portalid', __( 'Invalid Portal or Object ID', 'formidable-hubspot' ), array( 'status' => 400 ) );
		}

		return true;
	}

	/**
	 * Match incoming object id with existing hubspot field.
	 *
	 * @since 2.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return void
	 */
	private function match_hubspot_contact_object( $request ) {
		// Find meta matched with hubspot contact id.
		$hubspot_object_meta = FrmDb::get_row( 'frm_item_metas', array( 'meta_value' => absint( $request->get_param( 'objectId' ) ) ) );
		$this->entry_id            = isset( $hubspot_object_meta->item_id ) ? $hubspot_object_meta->item_id : false;
		$this->hubspot_field_id    = isset( $hubspot_object_meta->field_id ) ? $hubspot_object_meta->field_id : false;
	}
}
