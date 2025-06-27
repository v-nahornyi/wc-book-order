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

	private static array $product_category_dictionary = [
		'Waterslides'     => 10,
		'Combos'          => 9,
		'Bounce Houses'   => 8,
		'Obstacle Course' => 7
	];

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
		add_action( 'wp_enqueue_scripts', function () {
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
				'1.0.1',
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

	/**
	 * @throws Exception
	 */
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

				date_default_timezone_set('America/New_York');

				foreach ( $slots as $slot ) {
					if ( $slot->available && ! in_array( $slot->product_id, $productIds, true ) ) {
						$productIds[] = $slot->product_id;
						$product = get_wc_product_booking( $slot->product_id );
						if ( $product ) {
							/**
							 * Here must be correct traversal
							 * it is not implemented here because it is known
							 * that all the products would have only 1 category
							 * TODO: implement categories traversal with get_ancestors()
							 */
							$category = get_the_terms( $slot->product_id, 'product_cat' )[0]->name;

							if ( $category === 'Add-ons' || $category === 'Uncategorized' ) {
								continue;
							}

							$buffer = $product->get_buffer_period();

							if ( $buffer > 0 ) {
								$minDateObj = new \DateTime( $minDate );
								$minDateObj->setDate(
									$minDateObj->format( 'Y' ),
									$minDateObj->format( 'm' ),
									(int) $minDateObj->format( 'd' ) - $buffer
								);

								$currentMinDate = $minDateObj->format('Y-m-d');

								$maxDateObj = new \DateTime( $minDate );
								$maxDateObj->setDate(
									$maxDateObj->format( 'Y' ),
									$maxDateObj->format( 'm' ),
									(int) $maxDateObj->format( 'd' ) + $buffer + 1
								);

								$currentMaxDateConstraint = $maxDateObj->format('Y-m-d');

								$currentArgs = array(
									"min_date" => $currentMinDate,
									"max_date" => $currentMaxDateConstraint,
									"per_page" => 9999,
									'product_ids' => $slot->product_id,
								);

								$currentQuery    = http_build_query( $currentArgs );
								$currentUrl      = get_site_url() . "/wp-json/wc-bookings/v1/products/slots?" . $currentQuery;
								$currentResponse = wp_remote_get( $currentUrl );
								$currentSlots = json_decode( $currentResponse['body'] )->records;

								$not_available = false;

								foreach ( $currentSlots as $slot ) {
									if ( $slot->available < 1 ) {
										$not_available = true;
										break;
									}
								}

								if ( $not_available ) {
									continue;
								}
							}

							/**
							 * Product data to insert in HTML
							 */
							$productData = array(
								'image' => wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' ),
								'url'   => $product->get_permalink() . "?minDate={$minDate}",
								'title' => $product->get_title(),
								'price' => $product->get_price()
							);

							if ( ! isset( $products[ $category ] ) ) {
								$products[ $category ] = array();
							}

							$products[ $category ][] = $productData;
						}
					}
				}

				uksort( $products, [ $this, 'sort_categories' ] );

				wp_send_json_success( $products, 200 );

			} else {
				wp_send_json_error( 'No products available.', 200 );
			}
		}
	}

	private function sort_categories( $a, $b ) {
		return self::$product_category_dictionary[ $b ] - self::$product_category_dictionary[ $a ];
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