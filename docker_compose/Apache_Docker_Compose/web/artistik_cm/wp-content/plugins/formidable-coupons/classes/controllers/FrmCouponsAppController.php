<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmCouponsAppController {

	/**
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'init', array( self::class, 'load_lang' ) );

		add_filter( 'frm_pro_available_fields', array( 'FrmCouponsFieldController', 'remove_coupon_upgrade_icon' ) );
		add_filter( 'frm_get_field_type_class', array( 'FrmCouponsFieldController', 'set_coupon_field_class' ), 10, 2 );
		add_action( 'frm_get_field_scripts', array( 'FrmCouponsFieldController', 'load_coupon_field_scripts' ) );

		add_action(
			'frm_include_front_css',
			/**
			 * Make sure that the generated formidableforms.css file includes the Coupon styles.
			 *
			 * @return void
			 */
			function () {
				readfile( FrmCouponsAppHelper::path() . '/css/frontend.css' );
			}
		);

		add_action(
			'wp_ajax_pro_fields_css',
			/**
			 * Make sure that the front end CSS is loaded when editing an entry on
			 * the admin page to edit entries.
			 *
			 * @return void
			 */
			function () {
				readfile( FrmCouponsAppHelper::path() . '/css/frontend.css' );
			},
			11
		);

		add_filter(
			'frm_field_total_expected_sum',
			/**
			 * @param float          $sum
			 * @param array|stdClass $field
			 * @param array|stdClass $form
			 */
			function ( $sum, $field, $form ) {
				return self::maybe_change_expected_sum( $sum, $field, $form );
			},
			10,
			3
		);

		add_action(
			'frm_after_create_entry',
			/**
			 * @param int   $entry_id
			 * @param int   $form_id
			 * @param array $args
			 *
			 * @return void
			 */
			function ( $entry_id, $form_id, $args ) {
				if ( ! empty( $args['is_child'] ) ) {
					return;
				}

				self::maybe_add_coupon_code_to_entry_meta( $entry_id, $form_id );
			},
			10,
			3
		);

		add_filter(
			'frm_posted_field_shortcode_value',
			/**
			 * Support shortcodes in HTML field descriptions.
			 *
			 * @param false|string $value
			 * @param int          $field_id
			 * @param array        $atts
			 *
			 * @return false|string
			 */
			function ( $value, $field_id, $atts ) {
				if ( 'coupon' !== FrmField::get_type( $field_id ) ) {
					return $value;
				}

				$show = $atts['show'] ?? '';
				if ( 'code' === $show ) {
					return FrmAppHelper::get_param( $field_id . '_code', false, 'post', 'wp_kses_post' );
				}

				$value = FrmAppHelper::get_param( 'item_meta[' . $field_id . ']', false, 'post', 'wp_kses_post' );

				if ( ! empty( $atts['format'] ) ) {
					if ( 'number' === $atts['format'] ) {
						return $value;
					}
					if ( 'currency' === $atts['format'] ) {
						return FrmCouponsAppHelper::format_amount_as_currency_for_coupon_field( $value, FrmField::getOne( $field_id ) );
					}
				}

				$coupon_code = FrmAppHelper::get_param( $field_id . '_code', false, 'post', 'wp_kses_post' );
				if ( ! $coupon_code ) {
					return $value;
				}

				$coupon_field = FrmField::getOne( $field_id );
				$field_object = new FrmCouponsFieldCoupon( $coupon_field );

				return $field_object->format_coupon_code_and_discount_values( $coupon_code, $value );
			},
			10,
			3
		);

		add_action( 'frm_insert_extra_hidden_fields', array( 'FrmCouponsFieldController', 'maybe_insert_hidden_coupon_code_field' ) );

		add_filter(
			'frm_should_format_value_as_currency_on_display',
			array( 'FrmCouponsFieldController', 'frm_should_format_value_as_currency_on_display' ),
			10,
			3
		);

		add_filter( 'frm_views_is_field_sort_option', array( 'FrmCouponsViewsController', 'is_field_sort_option' ), 10, 2 );
		add_filter( 'frm_views_order_by_field', array( 'FrmCouponsViewsController', 'order_by_field' ), 10, 6 );

		add_filter(
			'frm_should_get_field_id_from_where_opt_split_val',
			array( 'FrmCouponsViewsController', 'should_get_field_id_from_where_opt_split_val' ),
			10,
			2
		);

		add_filter( 'frm_field_key_for_field_query', array( 'FrmCouponsViewsController', 'field_key_for_field_query' ), 10, 3 );

		add_filter(
			'frm_views_should_add_where_to_frm_items_query_and_continue',
			array( 'FrmCouponsViewsController', 'should_add_where_to_frm_items_query_and_continue' ),
			10,
			2
		);

		add_filter( 'frm_where_filter', array( 'FrmCouponsViewsController', 'modify_where_filter' ), 10, 2 );

		add_filter( 'frm_graph_data', 'FrmCouponsGraphController::graph_data', 10, 2 );

		add_action( 'wc_fp_product_add_cart_item', array( 'FrmCouponsWooController', 'on_add_cart_item' ), 10, 3 );

		add_action(
			'plugins_loaded',
			function () {
				// WC_Formidable_App_Helper::plugin_version was added in the same version that added support for a second parameter.
				if ( is_callable( 'WC_Formidable_App_Helper::plugin_version' ) ) {
					add_action( 'wc_fp_addons_cart_option', array( 'FrmCouponsWooController', 'on_addons_cart_option' ), 10, 2 );
				}
			}
		);

		if ( is_admin() ) {
			self::load_admin_hooks();
		}
	}

	/**
	 * @return void
	 */
	private static function load_admin_hooks() {
		add_filter( 'frm_coupons_list_displayed', array( 'FrmCouponsListsController', 'on_coupon_list_displayed' ) );

		add_filter(
			'frm_coupons_list_button',
			function ( $publish ) {
				FrmAppHelper::include_svg();
				return array(
					'FrmAppHelper::add_new_item_link',
					array(
						'new_link' => admin_url( 'admin.php?page=formidable-payments&action=new-coupon' ),
					),
				);
			}
		);

		add_action( 'admin_head', 'FrmCouponsListsController::admin_head' );

		$apply_coupon_callback = function () {
			$coupon_code = FrmAppHelper::get_post_param( 'coupon_code', '', 'sanitize_text_field' );
			if ( '' === $coupon_code ) {
				wp_send_json_error( 'No coupon code provided' );
			}

			$form_id = FrmAppHelper::get_post_param( 'form_id', '', 'absint' );
			if ( ! $form_id ) {
				wp_send_json_error( 'No form ID provided' );
			}

			$total_value = FrmAppHelper::get_post_param( 'total_value', '', 'sanitize_text_field' );
			if ( ! $total_value ) {
				$total_value = '0.00';
			}

			$entry_id = FrmAppHelper::get_post_param( 'entry_id', 0, 'absint' );
			$discount = FrmCouponsAppHelper::get_discount_for_coupon( $coupon_code, $form_id, $total_value, $entry_id );

			if ( '0.00' === FrmCouponsAppHelper::get_last_coupon_raw_amount() ) {
				wp_send_json_error( 'Invalid coupon code' );
			}

			$coupon_field = FrmProFormsHelper::has_field( 'coupon', $form_id );
			if ( ! $coupon_field ) {
				wp_send_json_error( 'No coupon field found' );
			}

			$coupon_field_object = new FrmCouponsFieldCoupon( $coupon_field );

			wp_send_json_success(
				array(
					'message'               => 'Coupon applied successfully',
					'discount'              => $discount,
					'discountHtml'          => $coupon_field_object->get_discount_html( $discount, $coupon_code ),
					'minimumOrderValueNote' => $coupon_field_object->get_minimum_order_value_note( $discount ),
				)
			);
		};
		add_action( 'wp_ajax_frm_apply_coupon', $apply_coupon_callback );
		add_action( 'wp_ajax_nopriv_frm_apply_coupon', $apply_coupon_callback );

		add_action( 'wp_ajax_frm_save_coupon', 'FrmCouponsRouteController::handle_save_coupon' );

		add_filter( 'frm_trans_lite_route', 'FrmCouponsRouteController::handle_route', 10, 2 );

		add_action(
			'frm_enqueue_builder_scripts',
			function () {
				self::enqueue_admin_scripts();
			}
		);

		add_filter( 'frm_default_field_options', array( 'FrmCouponsFieldController', 'default_field_options' ) );

		add_filter( 'frm_should_show_floating_links', array( 'FrmCouponsRouteController', 'maybe_hide_floating_links' ) );

		add_action(
			'admin_init',
			function () {
				self::admin_init();
			}
		);

		/**
		 * Add currency format to coupon fields.
		 *
		 * @since 1.0
		 *
		 * @param array $field Field data.
		 *
		 * @return void
		 */
		add_action(
			'frm_after_format_dropdown_template',
			array( 'FrmCouponsFieldController', 'add_currency_format_to_coupon_field' )
		);

		add_filter(
			'frm_format_options_view_path',
			/**
			 * Handle the format dropdown in this plugin.
			 * When the field is not a coupon, the Pro path is used instead.
			 *
			 * @param string $path
			 *
			 * @return string
			 */
			function ( $path ) {
				return FrmCouponsAppHelper::path() . '/classes/views/format-dropdown-options.php';
			},
			11
		);

		add_filter(
			'frm_views_field_select_template_options',
			array( 'FrmCouponsViewsController', 'add_coupon_code_to_field_select_options' ),
			10,
			2
		);

		add_filter( 'set-screen-option', 'FrmCouponsListsController::save_per_page', 10, 3 );
	}

	/**
	 * Load the plugin textdomain.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public static function load_lang() {
		$plugin_folder_name = basename( FrmCouponsAppHelper::path() );
		load_plugin_textdomain( 'formidable-coupons', false, $plugin_folder_name . '/languages/' );
	}

	/**
	 * @since 1.0
	 *
	 * @param int $entry_id
	 * @param int $form_id
	 *
	 * @return void
	 */
	private static function maybe_add_coupon_code_to_entry_meta( $entry_id, $form_id ) {
		$coupon_field = FrmProFormsHelper::has_field( 'coupon', $form_id );
		if ( ! $coupon_field ) {
			return;
		}

		$coupon_code = self::check_post_data_for_coupon_code( $coupon_field->id );
		if ( false === $coupon_code ) {
			return;
		}

		FrmEntryMeta::add_entry_meta( $entry_id, 0, '', compact( 'coupon_code' ) );
	}

	/**
	 * @since 1.0
	 *
	 * @param float          $sum
	 * @param array|stdClass $field
	 * @param array|stdClass $form
	 *
	 * @return float
	 */
	private static function maybe_change_expected_sum( $sum, $field, $form ) {
		$form_id      = is_object( $form ) ? $form->id : $form['id'];
		$coupon_field = FrmProFormsHelper::has_field( 'coupon', $form_id );
		if ( ! $coupon_field ) {
			return $sum;
		}

		$coupon_code = self::check_post_data_for_coupon_code( $coupon_field->id );
		if ( false === $coupon_code ) {
			return $sum;
		}

		$discount = FrmCouponsAppHelper::get_discount_for_coupon( $coupon_code, $form_id, (string) $sum );
		if ( '0.00' === $discount ) {
			return $sum;
		}

		$is_percent_discount = '%' === substr( $discount, -1 );
		if ( $is_percent_discount ) {
			$percent_discount = floatval( substr( $discount, 0, -1 ) );
			$discount         = $sum * ( $percent_discount / 100 );
			$discount         = round( $discount, 2 );
		} else {
			$discount = floatval( $discount );
		}

		return $sum - $discount;
	}

	/**
	 * @since 1.0
	 *
	 * @param int $coupon_field_id
	 *
	 * @return false|string
	 */
	private static function check_post_data_for_coupon_code( $coupon_field_id ) {
		$code = FrmAppHelper::get_post_param( $coupon_field_id . '_code', '', 'sanitize_text_field' );
		return '' !== $code ? $code : false;
	}

	/**
	 * Handle logic for routes that need to trigger early (before the headers are sent or the page is being rendered).
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	private static function admin_init() {
		self::include_updater();
		FrmCouponsMigrate::init();

		$page = FrmAppHelper::simple_get( 'page' );
		if ( 'formidable-payments' !== $page ) {
			return;
		}

		$action = FrmAppHelper::simple_get( 'action' );
		switch ( $action ) {
			case 'destroy-coupon':
				FrmCouponsRouteController::handle_destroy_coupon();
				break;
		}
	}

	/**
	 * @return void
	 */
	private static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			include FrmCouponsAppHelper::path() . '/classes/models/FrmCouponsUpdate.php';
			FrmCouponsUpdate::load_hooks();
		}
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @return void
	 */
	private static function enqueue_admin_scripts() {
		if ( ! in_array( FrmAppHelper::simple_get( 'action' ), array( 'coupons', 'new-coupon', 'edit-coupon' ), true ) ) {
			if ( FrmAppHelper::is_form_builder_page() ) {
				$plugin_url = FrmCouponsAppHelper::plugin_url();
				$version    = FrmCouponsAppHelper::plugin_version();
				wp_enqueue_style( 'frm-coupon-admin', $plugin_url . '/css/admin.css', array(), $version );
				wp_enqueue_script( 'frm-coupon-admin', $plugin_url . '/js/builder.js', array( 'wp-hooks' ), $version, true );
			}

			return;
		}

		if ( FrmProAppHelper::use_jquery_datepicker() ) {
			FrmProStylesController::enqueue_jquery_css();
		}

		if ( is_callable( 'FrmProDatepickerAssetsHelper::init_admin_js_and_css' ) ) {
			FrmProDatepickerAssetsHelper::init_admin_js_and_css();
		}

		wp_enqueue_script( 'formidable_admin' );

		// For the unit dropdown.
		wp_enqueue_style( 'formidable-settings-components' );
		wp_enqueue_script( 'formidable-settings-components' );

		$plugin_url = FrmCouponsAppHelper::plugin_url();
		$version    = FrmCouponsAppHelper::plugin_version();
		$script_url = $plugin_url . '/js/admin' . FrmCouponsAppHelper::js_suffix() . '.js';

		wp_enqueue_script( 'frm-coupon-admin', $script_url, array(), $version, true );
		wp_enqueue_style( 'frm-coupon-admin', $plugin_url . '/css/admin.css', array(), $version );

		wp_localize_script(
			'frm-coupon-admin',
			'frmCouponAdminVars',
			array(
				'formBaseUrl'                => admin_url( 'admin.php?page=formidable&frm_action=edit&id=' ),
				'formsText'                  => __( 'Forms', 'formidable-coupons' ),
				'newTabText'                 => __( 'View in new tab', 'formidable-coupons' ),
				'unlimitedText'              => __( 'Unlimited', 'formidable-coupons' ),
				'limitNotNumber'             => __( 'Max Uses setting must be a number.', 'formidable-coupons' ),
				'limitNegative'              => __( 'Max Uses setting must be a positive number.', 'formidable-coupons' ),
				'minimumOrderValueNotNumber' => __( 'Minimum Order Value setting must be a number.', 'formidable-coupons' ),
				'minimumOrderValueNegative'  => __( 'Minimum Order Value setting must be a positive number.', 'formidable-coupons' ),
			)
		);
	}

	/**
	 * @return void
	 */
	public static function save_button() {
		?>
		<a href="#" class="button button-primary frm-button-primary frm-save-coupon-button">
			<?php esc_html_e( 'Save Coupon', 'formidable-coupons' ); ?>
		</a>
		<?php
	}
}
