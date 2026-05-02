<?php
/**
 * Plugin Name: Formidable WooCommerce
 * Plugin URI: https://formidableforms.com
 * Description: Use Formidable Forms on individual WooCommerce product pages to create customizable products. Requires the Formidable Forms plugin.
 * Author: Strategy11
 * Author URI: https://formidableforms.com
 * Version: 1.14
 * Text Domain: formidable-woocommerce
 * Domain Path: /languages/
 * WC requires at least: 3.0.0
 * WC tested up to: 8.8.3
 *
 * @package   WC-Formidable
 * @author    Strategy11
 * @copyright Copyright (c) 2015, Strategy11
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! class_exists( 'WC_Formidable' ) ) {

	/**
	 * Formidable WooCommerce main class.
	 *
	 * @since 1.0
	 */
	class WC_Formidable {

		/**
		 * Instance of this class.
		 *
		 * @var WC_Formidable
		 */
		protected static $instance;

		/**
		 * Error holder.
		 *
		 * @var array<string>
		 */
		private $errors = array();

		/**
		 * Return an instance of this class.
		 *
		 * @return WC_Formidable A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Initialize the plugin.
		 */
		private function __construct() {
			// Check requirements.
			if ( ! $this->requirements() ) {
				add_action( 'admin_notices', array( $this, 'required_plugins_error' ) );
				if ( ! in_array( 'formidable', $this->errors, true ) ) {
					$page = FrmAppHelper::get_param( 'page', '', 'get', 'sanitize_text_field' );
					if ( 'formidable' === $page ) {
						add_filter( 'frm_message_list', array( $this, 'required_plugins_error' ) );
					}
				}
				return;
			}

			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			add_action(
				'before_woocommerce_init',
				function() {
					// Declare compatibility with High-Performance order storage.
					if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
					}
				}
			);

			$dir = dirname( __FILE__ );
			require_once $dir . '/helpers/class-wc-formidable-app-helper.php';
			require_once $dir . '/classes/class-wc-formidable-admin.php';
			require_once $dir . '/classes/class-wc-formidable-product.php';

			add_action( 'admin_init', array( $this, 'include_updater' ), 1 );

			new WC_Formidable_Admin();
			new WC_Formidable_Product();

			if ( class_exists( 'FrmRegShortcodesController' ) ) {
				require_once $dir . '/classes/class-wc-formidable-settings.php';
				new WC_Formidable_Settings();
			}
		}

		/**
		 * Checks if the system requirements are met.
		 *
		 * @since    1.11
		 *
		 * @return bool True if system requirements are met, false if not.
		 */
		private function requirements() {
			if ( ! function_exists( 'load_formidable_forms' ) ) {
				$this->errors[] = 'formidable';
			}

			if ( ! function_exists( 'load_formidable_pro' ) ) {
				$this->errors[] = 'formidable_pro';
			}

			if ( ! function_exists( 'WC' ) ) {
				$this->errors[] = 'woocommerce';
			}

			if ( ! empty( $this->errors ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Display an error to the user that the plugin could not get activated.
		 *
		 * @since 1.11
		 * @param array<string> $messages used for formidable.
		 *
		 * @return array<string>|void
		 */
		public function required_plugins_error( $messages ) {
			/* translators: %s: required plugin(s) */
			$notice = sprintf( esc_html__( 'Formidable WooCommerce addon requires an active version of %s.', 'formidable-woocommerce' ), ucwords( implode( ', ', str_replace( '_', ' ', $this->errors ) ) ) );

			if ( 'admin_notices' === current_filter() ) {
				?>
				<div class="error">
					<p><?php echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</div>
				<?php
			} else {
				$messages['frm_woocommerce_pro_missing'] = $notice;

				return $messages;
			}
		}

		/**
		 * Include plugin updater.
		 *
		 * @return void
		 */
		public function include_updater() {
			if ( class_exists( 'FrmAddon' ) ) {
				include_once dirname( __FILE__ ) . '/woo-includes/FrmWooUpdate.php';
				FrmWooUpdate::load_hooks();
			}
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @return void
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'formidable-woocommerce' );

			load_textdomain( 'formidable-woocommerce', trailingslashit( WP_LANG_DIR ) . 'woocommerce-formidable-product-addons/woocommerce-formidable-product-addons-' . $locale . '.mo' );
			load_plugin_textdomain( 'formidable-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
	}

	add_action( 'plugins_loaded', array( 'WC_Formidable', 'get_instance' ) );

	// constants.
	define( 'WC_FP_PRODUCT_ADDONS_PLUGIN_FILE', __FILE__ );

}
