<?php
/**
 *      * @wordpress-plugin
 *      * Plugin Name:       WC Bookings Product Order
 *      * Version:           1.0.0
 *      * Description:       Order products by date via WC Bookings
 *      * Author:            Vladyslav Nahornyi
 *      * Author URI:        https://github.com/b851TYiytNCk
 *      * Update URI:        https://github.com/b851TYiytNCk/wc-book-order
 *      * License:           GPL-3.0+
 *      * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 */

defined('ABSPATH') || exit;

// TODO: remove commented code

/**
 * @class Main plugin class
 */
final class WcBookingOrder {
	/**
	 * Class instance
	 *
	 * @var WCBookingOrder
	 */
	private static ?WCBookingOrder $instance = null;

	/**
	 * Retrieve main instance.
	 *
	 * Ensure one instance is loaded or can be loaded
	 */
	public static function get(): WCBookingOrder {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof WCBookingOrder ) ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}

	private function init(): void {
		add_action( 'init', array( $this, 'add_product_ordering_by_date' ) );
	}

	/**
	 * Register logic and compontents
	 */
	public function add_product_ordering_by_date(): void {
		/**
		 * Product filtering
		 */
		add_filter( 'http_request_timeout', 'increase_timeout' );
		function increase_timeout( $time ) {
			// Default timeout is 5
			return 40;
		}

		add_action('wp_ajax_nopriv_wc_book_order_by_date', 'wc_book_order_by_date' );
		add_action('wp_ajax_wc_book_order_by_date', 'wc_book_order_by_date' );

		/**
		 * UI compontents
		 */
		add_shortcode(
			'wc_book_date_ordering',
			array( $this, 'wc_book_order_shortcode')
		);
	}

	/**
	 * Shortcode returning product ordering logic and template
	 *
	 * @return bool|string
	 */
	public function wc_book_order_shortcode(): bool|string {
		ob_start();

		require_once( 'includes/templates/booking-ordering.php');

		return ob_get_clean();
	}
}

/**
 * Initialize the plugin
 */
WCBookingOrder::get();