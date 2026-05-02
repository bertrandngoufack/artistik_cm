<?php
/**
 * Action controller
 *
 * @package FrmConvertKit
 */

/**
 * Class FrmConvertKitActionController
 */
class FrmConvertKitActionController {

	/**
	 * Triggers the action.
	 *
	 * @param stdClass $action Action object.
	 * @param stdClass $entry  Entry object.
	 * @return void
	 */
	public static function trigger_action( $action, $entry ) {
		// Do not run if API secret is empty.
		if ( empty( ( new FrmConvertKitSettings() )->settings->api_secret ) ) {
			return;
		}

		$settings = $action->post_content;
		$settings['email'] = self::prepare_value( $settings['email'], $entry );
		if ( ! $settings['email'] || ! is_email( $settings['email'] ) ) {
			return;
		}

		self::prepare_mapped_values( $settings, $entry );

		if ( is_callable( array( __CLASS__, $action->post_content['api_action'] ) ) ) {
			call_user_func( array( __CLASS__, $action->post_content['api_action'] ), $settings, compact( 'entry', 'action' ) );
			return;
		}

		self::subscribe( $settings, compact( 'entry', 'action' ) );
	}

	/**
	 * Runs unsubscribe action.
	 *
	 * @param array $settings Form action settings.
	 * @param array $args     Contains `entry` and `action` objects.
	 * @return void
	 */
	private static function unsubscribe( $settings, $args ) {
		$api = new FrmConvertKitAPI();
		$api->unsubscribe( $settings['email'] );
	}

	/**
	 * Runs remove tag action.
	 *
	 * @param array $settings Form action settings.
	 * @param array $args     Contains `entry` and `action` objects.
	 * @return void
	 */
	private static function remove_tag( $settings, $args ) {
		$api = new FrmConvertKitAPI();

		$tags     = explode( ',', $settings['tags'] );
		$cvk_tags = $api->get_tags();
		if ( ! $cvk_tags || ! is_array( $cvk_tags ) ) {
			return;
		}

		foreach ( $tags as $tag_name ) {
			$tag_name = trim( $tag_name );
			if ( ! $tag_name ) {
				continue;
			}

			$cvk_tag = self::get_array_item( $cvk_tags, $tag_name );
			if ( $cvk_tag && is_object( $cvk_tag ) && ! empty( $cvk_tag->id ) ) {
				$api->remove_tag_from_subscriber( $cvk_tag->id, $settings['email'] );
			}
		}
	}

	/**
	 * Runs subscribe action.
	 *
	 * @param array $settings Form action settings.
	 * @param array $args     Contains `entry` and `action` objects.
	 * @return void
	 */
	private static function subscribe( $settings, $args ) {
		$api = new FrmConvertKitAPI();

		if ( 'subscribe_sequence' === $settings['api_action'] ) {
			$endpoint = 'sequences';
			$list_id  = self::prepare_value( $settings['sequence_id'], $args['entry'] );
		} else {
			$endpoint = 'forms';
			$list_id  = self::prepare_value( $settings['form_id'], $args['entry'] );
		}

		if ( ! is_numeric( $list_id ) ) {
			return;
		}

		$subscriber = self::build_subscriber_data( $settings );

		$api->add_subscriber_to_list( $endpoint, intval( $list_id ), $subscriber, $args );
	}

	/**
	 * Builds subscriber data.
	 *
	 * @param array $settings Form action settings.
	 * @return array
	 */
	private static function build_subscriber_data( $settings ) {
		$subscriber = array(
			'email'      => $settings['email'],
			'first_name' => $settings['first_name'],
			'fields'     => $settings['fields'],
			'tags'       => array(),
		);

		$api = new FrmConvertKitAPI();

		if ( $settings['tags'] ) {
			$cvk_tags = $api->get_tags();
			$tags     = explode( ',', $settings['tags'] );
			foreach ( $tags as $tag_name ) {
				$tag_name = trim( $tag_name );
				if ( ! $tag_name ) {
					continue;
				}

				$cvk_tag = is_array( $cvk_tags ) ? self::get_array_item( $cvk_tags, $tag_name ) : false;

				if ( ! $cvk_tag ) {
					// Create new ConvertKit tag.
					$cvk_tag = $api->create_tag( $tag_name );
				}

				if ( $cvk_tag && is_object( $cvk_tag ) && ! empty( $cvk_tag->id ) ) {
					$subscriber['tags'][] = $cvk_tag->id;
				}
			}
		}

		if ( $subscriber['tags'] ) {
			// The first tag is omitted in API call, so we add an empty tag at the first.
			array_unshift( $subscriber['tags'], '' );
		}

		return $subscriber;
	}

	/**
	 * Gets an array item.
	 *
	 * @param array  $items Array of items.
	 * @param mixed  $value Check value.
	 * @param string $key   Item key to compare.
	 * @return mixed Found item, or `false` if not found.
	 */
	private static function get_array_item( $items, $value, $key = 'name' ) {
		foreach ( $items as $item ) {
			if ( is_array( $item ) ) {
				$value_to_check = $item[ $key ];
			} elseif ( is_object( $item ) ) {
				$value_to_check = $item->$key;
			} else {
				$value_to_check = $value;
			}

			if ( $value_to_check === $value ) {
				return $item;
			}
		}
		return false;
	}

	/**
	 * Prepares value for action.
	 *
	 * @param string   $value Value filled in the settings.
	 * @param stdClass $entry Entry object.
	 * @return mixed
	 */
	private static function prepare_value( $value, $entry ) {
		if ( strpos( $value, '[' ) === false ) {
			return $value;
		}

		$value = apply_filters( 'frm_content', $value, $entry->form_id, $entry );
		return is_string( $value ) ? do_shortcode( $value ) : $value;
	}

	/**
	 * Prepares setting values.
	 *
	 * @param array    $settings Action settings.
	 * @param stdClass $entry    Entry object.
	 * @return void
	 */
	private static function prepare_mapped_values( &$settings, $entry ) {
		foreach ( array( 'first_name', 'tags', 'form_id', 'sequence_id' ) as $key ) {
			$settings[ $key ] = self::prepare_value( $settings[ $key ], $entry );
		}

		if ( empty( $settings['fields'] ) || ! is_array( $settings['fields'] ) ) {
			return;
		}

		$new_fields = array();
		foreach ( $settings['fields'] as $custom_field ) {
			if ( empty( $custom_field['key'] ) ) {
				continue;
			}

			$new_fields[ $custom_field['key'] ] = self::prepare_value( $custom_field['value'], $entry );
		}

		$settings['fields'] = $new_fields;
	}
}
