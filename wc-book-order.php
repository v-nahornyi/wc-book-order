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

/**
 * @class Main plugin class
 */
final class WcBookingOrder {
	/**
	 * Class instance
	 *
	 * @var WCBookingOrder
	 */
	private static WCBookingOrder $instance;

	/**
	 * Retrieve main instance.
	 */
	public static function get(): WCBookingOrder {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof WCBookingOrder ) ) {
			self::$instance = new WCBookingOrder();
			self::$instance->init();
		}

		return self::$instance;
	}

	private function init(): void {
		add_action( 'init', array( $this, 'add_product_ordering' ) );
	}

	public function add_product_ordering(): void {
		add_shortcode(
			'wc-booking-ordering',
			array( $this, 'wc_booking_ordering')
		);
	}

	public function wc_booking_ordering( $atts ): bool|string {
		$attributes = shortcode_atts( array(
			'title' => false,
			'limit' => 4,
		), $atts );

		ob_start();

		get_template_part( 'template-parts/booking-ordering', null, $attributes );

		return ob_get_clean();
	}
}

/**
 * Initialize the plugin
 */
WCBookingOrder::get();