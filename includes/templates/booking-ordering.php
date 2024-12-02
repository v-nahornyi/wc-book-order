<?php
$args  = array(
	"min_date" => "2024-12-08T00:00",
	"max_date" => "2024-12-09T00:00",
	"per_page" => 9999
);
$query = http_build_query( $args );
$url   = get_site_url() . "/wp-json/wc-bookings/v1/products/slots?" . $query;
$response = wp_remote_get( $url );

if ( is_wp_error( $response ) ) {
	wp_send_json_error( $response );
} else {
	$slots = json_decode( $response['body'] )->records;

	if ( is_array( $slots ) && ! empty( $slots ) ) {
        $products = array();
		$allCats = get_terms(
            array(
                'taxonomy' => 'product_cat',
                'exclude' => '43',
                'fields' => 'names'
            )
        );

        foreach( $allCats as $cat ) {
            $products[$cat] = array();
        }

        foreach( $slots as $slot ) {
            if ( $slot->available ) {

                $product = wc_get_product( $slot->product_id ); // Object || False
                if ( $product ) {
	                /**
	                 * Here must be correct traversal but it is not implemented as it is known
	                 * that all the products would have only 1 category
	                 * TODO: implement categories traversal with get_ancestors()
	                 */
	                $category = get_the_terms( $product->id, 'product_cat' )[0]->name;

                    if ( $category === 'Add-ons' ) {
                        continue;
                    }
	                /**
	                 * Product data to insert in HTML
	                 */
                    $productData = array(
                        'image' => $product->get_image(),
                        'url'   => $product->get_permalink(),
                        'title' => $product->get_title(),
                    );

	                if ( isset( $products[$category] ) ) {
		                array_push( $products[$category], $productData);
	                }
                }
            }
        }

		echoPre($products);
    }
}

function echoPre( $el ) {
	echo '<pre>';
	var_dump( $el );
	echo '</pre>';
}
?>
<div class="wc-book-order">
    <button class="wc-book-order__btn">Search</button>
</div>