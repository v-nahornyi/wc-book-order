<?php
/**
 *      * @wordpress-plugin
 *      * Plugin Name:       WC Bookings Product Order
 *      * Version:           1.0.2
 *      * Description:       Order products by date via WC Bookings
 *      * Author:            Vladyslav Nahornyi
 *      * Author URI:        https://github.com/b851TYiytNCk
 *      * Update URI:        https://github.com/b851TYiytNCk/wc-book-order
 *      * License:           GPL-3.0+
 *      * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'ABSPATH' ) || exit;

/**
 * @class Main plugin class
 */
final class WcBookingOrder {
	/**
	 * Class instance
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

	/** Register logic and components */
	public function add_product_ordering_by_date(): void {
		$this->enqueue_assets();

		add_action( 'wp_ajax_nopriv_wc_book_order_by_date', array( $this, 'wc_book_order_by_date' ) );
		add_action( 'wp_ajax_wc_book_order_by_date', array( $this, 'wc_book_order_by_date' ) );

		/** UI components */
		add_shortcode( 'wc_book_date_ordering', array( $this, 'wc_book_order_shortcode' ) );
	}

	private function enqueue_assets(): void {
		add_action( 'wp_enqueue_scripts', function() {
			wp_enqueue_script(
				'flatpickr',
				plugins_url( 'includes/third-party/scripts/flatpickr.js', __FILE__ ),
				array( 'jquery' ),
				'',
				array(
					'in_footer' => true,
					'strategy'  => 'defer',
				)
			);

			wp_enqueue_script(
				'wc-book-order',
				plugins_url( 'includes/scripts/wc-book-order.js', __FILE__ ),
				array( 'jquery' ),
				'1.0.0',
				array(
					'in_footer' => true,
					'strategy'  => 'defer',
				)
			);

			wp_enqueue_style(
				'flatpickr',
				plugins_url( 'includes/third-party/styles/flatpickr.min.css', __FILE__ ),
				'',
				'',
			);

			wp_enqueue_style(
				'wc-book-order',
				plugins_url( 'includes/styles/wc-book-order.css', __FILE__ ),
				'',
				'1.0.0'
			);
		} );
	}

	public function wc_book_order_by_date(): void {
		$minDate = esc_sql( $_POST['minDate'] );
		$maxDate = esc_sql( $_POST['maxDate'] );

		$args = array(
			"min_date" => $minDate,
			"max_date" => $maxDate,
			"per_page" => 9999
		);

		$query    = http_build_query( $args );
		$url      = get_site_url() . "/wp-json/wc-bookings/v1/products/slots?" . $query;
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response );
		} else {
			$slots = json_decode( $response['body'] )->records;

			if ( is_array( $slots ) && ! empty( $slots ) ) {
				$products   = array();
				$productIds = array();
				$allCats    = get_terms(
					array(
						'taxonomy' => 'product_cat',
						'exclude'  => '43', // Add-ons
						'fields'   => 'names'
					)
				);

				unset( $allCats[ array_search( 'Uncategorized', $allCats ) ] );

				foreach ( $allCats as $cat ) {
					$products[ $cat ] = array();
				}

				foreach ( $slots as $slot ) {
					if ( $slot->available && ! in_array( $slot->product_id, $productIds, true ) ) {
						$productIds[] = $slot->product_id;
						$product = wc_get_product( $slot->product_id ); // Object || False
						if ( $product ) {
							/**
							 * Here must be correct traversal but it is not implemented as it is known
							 * that all the products would have only 1 category
							 * TODO: implement categories traversal with get_ancestors()
							 */
							$category = get_the_terms( $product->get_id(), 'product_cat' )[0]->name;

							if ( $category === 'Add-ons' || $category === 'Uncategorized' ) {
								continue;
							}
							/**
							 * Product data to insert in HTML
							 */
							$productData = array(
								'image' => wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' ),
								'url'   => $product->get_permalink(),
								'title' => $product->get_title(),
								'price' => $product->get_price()
							);

							if ( isset( $products[ $category ] ) ) {
								$products[ $category ][] = $productData;
							}
						}
					}
				}

				wp_send_json_success( $products, 200 );

			} else {
				wp_send_json_error( 'No products available.', 200 );
			}
		}
	}

	/**
	 * Shortcode returning product ordering logic and template
	 *
	 * @return bool|string
	 */
	public function wc_book_order_shortcode(): bool|string {
		ob_start();

		require_once( 'includes/templates/booking-ordering.php' );

		return ob_get_clean();
	}
}

/**
 * Initialize the plugin
 */
WCBookingOrder::get();