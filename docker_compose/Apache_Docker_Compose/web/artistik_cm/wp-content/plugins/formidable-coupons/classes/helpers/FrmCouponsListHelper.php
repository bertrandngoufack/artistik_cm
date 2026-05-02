<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmCouponsListHelper extends FrmListHelper {

	/**
	 * Prepare the list helper that displays the coupons list.
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function __construct( $args ) {
		parent::__construct( $args );
		$this->screen->set_screen_reader_content(
			array(
				'heading_list' => esc_html__( 'Coupons list', 'formidable' ),
			)
		);
	}

	/**
	 * @return void
	 */
	public function prepare_items() {
		global $wpdb;

		$orderby = FrmAppHelper::get_param( 'orderby', 'ID', 'get', 'sanitize_title' );
		$order   = FrmAppHelper::get_param( 'order', 'DESC', 'get', 'sanitize_text_field' );
		if ( ! in_array( strtoupper( $order ), array( 'ASC', 'DESC' ), true ) ) {
			$order = 'DESC';
		}

		$page        = $this->get_pagenum();
		$per_page    = $this->get_items_per_page( 'formidable_page_formidable_coupons_per_page' );
		$start       = ( $page - 1 ) * $per_page;
		$start       = FrmAppHelper::get_param( 'start', $start, 'get', 'absint' );
		$query       = $this->get_table_query();
		$order_query = FrmDb::esc_order( "ORDER BY p.{$orderby} $order" );

		// @codingStandardsIgnoreStart
		$this->items = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT p.* ' . $query . $order_query . ' LIMIT %d, %d',
				$start,
				$per_page
			)
		);
		$total_items = $wpdb->get_var( 'SELECT COUNT(*) ' . $query );
		// @codingStandardsIgnoreEnd

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * @return string
	 */
	private function get_table_query() {
		global $wpdb;

		$search_term = $this->get_search_term();
		$query       = "FROM `$wpdb->posts` p WHERE p.post_type = 'frm_coupon'";

		if ( '' !== $search_term ) {
			$query .= $wpdb->prepare(
				' AND (p.post_title LIKE %s OR p.post_excerpt LIKE %s)',
				'%' . $wpdb->esc_like( $search_term ) . '%',
				'%' . $wpdb->esc_like( $search_term ) . '%'
			);
		}

		return $query;
	}

	/**
	 * @return string
	 */
	private function get_search_term() {
		return FrmAppHelper::simple_get( 's' );
	}

	/**
	 * Handle empty state.
	 *
	 * @return void
	 */
	public function no_items() {
		if ( '' === $this->get_search_term() ) {
			include FrmCouponsAppHelper::path() . '/classes/views/coupons-empty-state.php';
			return;
		}

		esc_html_e( 'No coupons match this search.', 'formidable-coupons' );
	}

	/**
	 * @return array
	 */
	public function get_columns() {
		return FrmCouponsListsController::coupon_columns();
	}

	/**
	 * Allow sorting the coupons list by Name and Code.
	 * Other columns are stored in serialized data, so they are more difficult to sort.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'name' => 'post_title',
			'code' => 'post_excerpt',
		);
	}

	/**
	 * @param array $args
	 *
	 * @return void
	 */
	public function display( $args = array() ) {
		$message = FrmAppHelper::simple_get( 'message' );
		if ( 'deleted' === $message ) {
			echo '<div class="frm_updated_message">' . esc_html__( 'Coupon deleted successfully.', 'formidable' ) . '</div>';
		}

		if ( $this->has_items() || '' !== $this->get_search_term() ) {
			$this->show_search();
		}

		parent::display( $args );
	}

	/**
	 * @since 1.0
	 *
	 * @return void
	 */
	private function show_search() {
		$search_term = $this->get_search_term();
		include FrmCouponsAppHelper::path() . '/classes/views/coupon-search.php';
	}

	/**
	 * @return void
	 */
	public function display_rows() {
		$date_format = FrmTransLiteAppHelper::get_date_format();
		$alt         = 0;
		$args        = compact( 'date_format' );

		foreach ( $this->items as $item ) {
			echo '<tr id="payment-' . esc_attr( $item->ID ) . '" ';

			$is_alternate = 0 === $alt % 2;
			++$alt;

			if ( $is_alternate ) {
				echo 'class="alternate"';
			}

			echo '>';
			$this->display_columns( $item, $args );
			echo '</tr>';

			unset( $item );
		}
	}

	/**
	 * @param object $item
	 * @param array  $args
	 *
	 * @return void
	 */
	private function display_columns( $item, $args ) {
		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$attributes          = self::get_row_classes( compact( 'column_name', 'hidden' ) );
			$args['column_name'] = $column_name;
			$val                 = $this->get_column_value( $item, $args );

			echo '<td ' . $attributes . '>' . $val . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			unset( $val );
		}
	}

	/**
	 * @param object $item
	 * @param array  $args
	 */
	private function get_column_value( $item, $args ) {
		$column_name   = $args['column_name'];
		$function_name = 'get_' . $column_name . '_column';

		if ( method_exists( $this, $function_name ) ) {
			$val = $this->$function_name( $item, $args );
		} else {
			$val = $item->$column_name ? $item->$column_name : '';

			if ( strpos( $column_name, '_date' ) !== false ) {
				if ( ! empty( $item->$column_name ) && '0000-00-00' !== $item->$column_name ) {
					$val = FrmTransLiteAppHelper::format_the_date( $item->$column_name, $args['date_format'] );
				} else {
					$val = '';
				}
			}
		}

		return $val;
	}

	/**
	 * @param array $atts
	 *
	 * @return string
	 */
	private function get_row_classes( $atts ) {
		$class = 'column-' . $atts['column_name'];

		if ( in_array( $atts['column_name'], $atts['hidden'] ) ) {
			$class .= ' frm_hidden';
		}

		return 'class="' . esc_attr( $class ) . '"';
	}

	/**
	 * @param object $item
	 * @param string $field
	 *
	 * @return string
	 */
	private function get_action_column( $item, $field ) {
		$link = $this->get_edit_coupon_link( $item, $field );
		return '<strong>' . $link . '</strong><br />' . $this->row_actions( $this->get_row_actions( $item ) );
	}

	/**
	 * @since 1.0
	 *
	 * @param object $item
	 * @param string $field
	 *
	 * @return string
	 */
	private function get_edit_coupon_link( $item, $field ) {
		$link_params = array(
			'class' => 'rot-title',
			'href'  => esc_url( $this->get_url_to_coupon( $item->ID, 'edit' ) ),
			'title' => __( 'Edit', 'formidable' ),
		);
		return '<a ' . FrmAppHelper::array_to_html_params( $link_params ) . '>' . esc_html( $item->{ $field } ) . '</a>';
	}

	/**
	 * @param int|string $coupon_id
	 * @param string     $action Supports 'show' and 'edit'.
	 *
	 * @return string
	 */
	private function get_url_to_coupon( $coupon_id, $action = 'edit' ) {
		return add_query_arg(
			array(
				'action' => $action . '-coupon',
				'id'     => $coupon_id,
				'page'   => FrmAppHelper::simple_get( 'page' ),
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * @param object $item
	 *
	 * @return array
	 */
	private function get_row_actions( $item ) {
		$base_link   = '?page=formidable-payments&action=';
		$edit_link   = $base_link . 'edit-coupon&id=' . $item->ID;
		$delete_link = $base_link . 'destroy-coupon&id=' . $item->ID;

		$actions           = array();
		$actions['edit']   = '<a href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Edit', 'formidable' ) . '</a>';
		$actions['delete'] = '<a href="' . esc_url( wp_nonce_url( $delete_link ) ) . '" data-frmverify="' . esc_attr__( 'Permanently delete this coupon?', 'formidable' ) . '" data-frmverify-btn="frm-button-red">' . esc_html__( 'Delete', 'formidable' ) . '</a>';

		return $actions;
	}

	/**
	 * @param object $item
	 *
	 * @return string
	 */
	private function get_name_column( $item ) {
		return $this->get_action_column( $item, 'post_title' );
	}

	/**
	 * @param object $item
	 *
	 * @return string
	 */
	private function get_code_column( $item ) {
		return esc_html( $item->post_excerpt ) . $this->get_copy_code_button( $item );
	}

	/**
	 * @param object $item
	 *
	 * @return string
	 */
	private function get_copy_code_button( $item ) {
		return FrmAppHelper::clip(
			function () {
				?>
				<a href="#" class="frm-copy-coupon-code">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_clipboard_icon frm_svg12' ); ?>
				</a>
				<?php
			},
			false
		);
	}

	/**
	 * @param object $item
	 *
	 * @return string
	 */
	private function get_amount_column( $item ) {
		$amount = self::get_json_value( $item, 'amount' );

		if ( ! is_numeric( $amount ) ) {
			// Leave the value as-is for a percentage.
			return esc_html( $amount );
		}

		// Format dollar amount values as currency.
		return (string) FrmProCurrencyHelper::format_amount_for_currency( 0, $amount );
	}

	/**
	 * @param object $item
	 *
	 * @return string
	 */
	private function get_usage_column( $item ) {
		$used = FrmCouponsAppHelper::get_coupon_uses_by_id( $item->ID );
		$max  = self::get_json_value( $item, 'limit' );
		if ( $max ) {
			return absint( $used ) . ' / ' . esc_html( $max );
		}
		return absint( $used ) . ' / ' . esc_html__( 'Unlimited', 'formidable-coupons' );
	}

	/**
	 * @param object $item
	 *
	 * @return string
	 */
	private function get_forms_column( $item ) {
		$forms = $this->get_forms_for_coupon( $item->ID );
		if ( ! $forms ) {
			return '';
		}

		$count      = count( $forms );
		$first_form = reset( $forms );

		if ( 1 === $count ) {
			return $this->link_to_form( $first_form );
		}

		return FrmAppHelper::clip(
			function () use ( $first_form, $count, $forms ) {
				echo wp_kses_post( $this->link_to_form( $first_form ) );

				$anchor_params = array(
					'href'             => '#',
					'class'            => 'frm-coupon-show-extra-forms',
					'data-extra-forms' => json_encode( $this->prepare_extra_forms( array_slice( $forms, 1 ) ) ),
				);
				?>
				<a <?php FrmAppHelper::array_to_html_params( $anchor_params, true ); ?>>
					<?php
					// translators: %s is the number of additional forms.
					printf( esc_html__( '(+%s more)', 'formidable-coupons' ), absint( $count - 1 ) );
					?>
				</a>
				<?php
			},
			false
		);
	}

	/**
	 * @since 1.0
	 *
	 * @param array $extra_forms
	 *
	 * @return array
	 */
	private function prepare_extra_forms( $extra_forms ) {
		$extra_forms = array_map(
			function ( $form ) {
				if ( '' === $form->name ) {
					$form->name = FrmFormsHelper::get_no_title_text();
				}
				return $form;
			},
			$extra_forms
		);

		usort(
			$extra_forms,
			function ( $a, $b ) {
				return strcmp( $a->name, $b->name );
			}
		);

		return $extra_forms;
	}

	/**
	 * @since 1.0
	 *
	 * @param stdClass $form
	 *
	 * @return string
	 */
	private function link_to_form( $form ) {
		$name = '' === $form->name ? FrmFormsHelper::get_no_title_text() : $form->name;
		return '<a href="' . esc_url( admin_url( 'admin.php?page=formidable&frm_action=edit&id=' . $form->id ) ) . '" target="_blank">' . esc_html( $name ) . '</a>';
	}

	/**
	 * @since 1.0
	 *
	 * @param int $coupon_id
	 *
	 * @return array
	 */
	private function get_forms_for_coupon( $coupon_id ) {
		$allowed_form_ids = FrmCouponsAppHelper::get_allowed_form_ids( $coupon_id );
		if ( ! $allowed_form_ids ) {
			return array();
		}

		return FrmDb::get_results(
			'frm_forms',
			array(
				'id' => $allowed_form_ids,
			),
			'id, name',
			array(
				'order_by' => 'id DESC',
			)
		);
	}

	/**
	 * @param object $item
	 *
	 * @return string
	 */
	private function get_status_column( $item ) {
		return FrmCouponsAppHelper::render_coupon_status( $item->ID, false );
	}

	/**
	 * @param object $item
	 *
	 * @return string
	 */
	private function get_start_date_column( $item ) {
		$start_date = self::get_json_value( $item, 'start' );
		if ( $start_date ) {
			return FrmTransLiteAppHelper::format_the_date( $start_date, FrmTransLiteAppHelper::get_date_format() );
		}
		return '';
	}

	/**
	 * @param object $item
	 *
	 * @return string
	 */
	private function get_end_date_column( $item ) {
		$end_date = self::get_json_value( $item, 'end' );
		if ( $end_date ) {
			return FrmTransLiteAppHelper::format_the_date( $end_date, FrmTransLiteAppHelper::get_date_format() );
		}
		return '';
	}

	/**
	 * @param object $item
	 * @param string $key
	 *
	 * @return string
	 */
	private static function get_json_value( $item, $key ) {
		$coupon_data = json_decode( $item->post_content );
		return $coupon_data->$key;
	}
}
