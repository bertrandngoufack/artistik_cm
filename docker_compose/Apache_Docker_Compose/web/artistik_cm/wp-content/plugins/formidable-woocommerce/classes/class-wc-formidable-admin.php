<?php
/**
 * WooCommerce Formidable Forms Product Add-ons
 *
 * @package     WC-formidable/Classes
 * @author      Strategy11
 * @copyright   Copyright (c) 2015, Strategy11
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * This class handles all of the admin interface
 */
class WC_Formidable_Admin {


	/**
	 * Initialize the WooCommerce Formidable Forms Admin class
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// Add a write panel on the product page
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'process_meta_box' ), 1, 2 );
		add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'set_order_meta_label' ), 10, 2 );
		add_filter( 'woocommerce_order_item_display_meta_value', array( $this, 'show_entry_link' ), 10, 2 );
	}

	/**
	 * Add a metabox to the edit product page
	 *
	 * @since 1.0
	 */
	public function add_meta_box() {
		global $post;
		add_meta_box( 'woocommerce-formidable-meta', __( 'Choose Form', 'formidable-woocommerce' ), array( $this, 'meta_box' ), 'product', 'side', 'default' );
	}


	/**
	 * Render the metabox on the edit product page.
	 * This includes a form dropdown for associating a Product with a form.
	 * It also includes a "Use the total in the form without adding the product price." checkbox option.
	 *
	 * @since 1.0
	 *
	 * @param WP_Post $post The current post.
	 * @return void
	 */
	public function meta_box( $post ) {
		// Get a Formidable form if it's already attached.
		$attached_form_id = get_post_meta( $post->ID, '_attached_formidable_form', true );
		$stop_price       = get_post_meta( $post->ID, '_frm_stop_woo_price', true );
		?>
		<div class="panel">
			<div class="options_group">
				<p>
					<?php FrmFormsHelper::forms_dropdown( 'formidable-form-id', $attached_form_id, array( 'blank' => __( 'None', 'formidable-woocommerce' ) ) ); ?>
				</p>
				<?php if ( $attached_form_id && is_numeric( $attached_form_id ) ) { ?>
					<p><a href="<?php echo esc_url( sprintf( '%s/admin.php?page=formidable&frm_action=edit&id=%d', get_admin_url(), $attached_form_id ) ) ?>">
						<?php esc_html_e( 'Edit', 'formidable-woocommerce' ); ?> <?php echo esc_html( wptexturize( FrmForm::getName( $attached_form_id ) ) ); ?> <?php esc_html_e( 'Formidable Form', 'formidable-woocommerce' ); ?>
					</a></p>
				<?php } ?>

				<p>
					<label for="formidable_stop_price">
						<input type="checkbox" value="1" name="formidable_stop_price" <?php checked( $stop_price, 1 ) ?> />
						<?php esc_html_e( 'Use the total in the form without adding the product price.', 'formidable-woocommerce' ) ?>
					</label>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Save the metabox options.
	 *
	 * @since 1.0
	 *
	 * @param int     $post_id The current post id.
	 * @param WP_Post $post The current post.
	 * @return void
	 */
	public function process_meta_box( $post_id, $post ) {
		// Save Formidable form data
		if ( isset( $_POST['formidable-form-id'] ) && ! empty( $_POST['formidable-form-id'] ) ) {
			$form_id = absint( $_POST['formidable-form-id'] );
			update_post_meta( $post_id, '_attached_formidable_form', $form_id );

			if ( isset( $_POST['formidable_stop_price'] ) ) {
				update_post_meta( $post_id, '_frm_stop_woo_price', true );
			} else {
				delete_post_meta( $post_id, '_frm_stop_woo_price' );
			}
		} else {
			// delete the post meta if there was no Formidable form id set
			delete_post_meta( $post_id, '_attached_formidable_form' );
			delete_post_meta( $post_id, '_frm_stop_woo_price' );
		}
	}

	/**
	 * Use a more readable label for the entry link on the WooCommerce order page.
	 *
	 * @since 1.08
	 * @return string
	 */
	public function set_order_meta_label( $display_value, $meta ) {
		if ( $this->is_entry_meta( $meta ) ) {
			$display_value = __( 'Entry', 'formidable-woocommerce' );
		}
		return $display_value;
	}

	/**
	 * Add a link to the entry from the WooCommerce order page.
	 *
	 * @since 1.08
	 * @return string
	 */
	public function show_entry_link( $display_value, $meta ) {
		if ( $this->is_entry_meta( $meta ) && is_numeric( $display_value ) ) {
			$link = admin_url( 'admin.php?page=formidable-entries&frm_action=show&id=' . $display_value );
			$display_value = '<a href="' . esc_url( $link ) . '">#' . $display_value . '</a>';
		}

		return $display_value;
	}

	/**
	 * Check if this is an order page, if the value is a Formidable entry id,
	 * and if the user can see the entry.
	 *
	 * @since 1.08
	 * @return boolean
	 */
	private function is_entry_meta( $meta ) {
		$has_permission = is_admin() && current_user_can( 'frm_view_entries' );
		return ( $meta->key === '_formidable_form_data' && $has_permission );
	}

	/**
	 * Check and see if the form has a total field. If it doesn't print out an error.
	 *
	 * @since 1.0
	 * @deprecated 1.10
	 */
	public function check_form_total_field() {
		_deprecated_function( __FUNCTION__, '1.10' );
	}

	/**
	 * Add a sample form to templates
	 *
	 * @since 1.05
	 * @deprecated 1.08
	 */
	public function initialize() {
		_deprecated_function( __METHOD__, '1.08' );
	}

	/**
	 * Add Woocommerce sample form to Formidable templates
	 *
	 * @deprecated 1.08
	 * @param array $files
	 *
	 * @return array
	 */
	public function add_default_template( $files ) {
		_deprecated_function( __METHOD__, '1.08' );
		return $files;
	}

	/**
	 * @deprecated 1.05
	 */
	public static function activation() {
		_deprecated_function( __FUNCTION__, '1.05', 'WC_Formidable_Admin->initialize()' );
	}

	/**
	 * Check to see if a form exists.
	 *
	 * @since 1.0
	 * @param string $option_name the name of the db option
	 * @return bool
	 */
	public static function get_starter_form() {
		_deprecated_function( __FUNCTION__, '1.05', 'custom code' );
	}

	/**
	 * @deprecated 1.05
	 */
	public static function add_starter_form() {
		_deprecated_function( __FUNCTION__, '1.05', 'custom code' );
	}
}
