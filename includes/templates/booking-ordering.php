<?php
//if ( isset( $_SESSION ) && isset( $_SESSION['slots'] ) && !is_wp_error($_SESSION['slots']) ) {
//	$slots = $_SESSION['slots'];
//} else {
    $args = array(
            "min_date" => "2024-12-08T00:00",
            "max_date" => "2024-12-09T00:00",
            "per_page" => 9999
    );
	$url = get_site_url() . "/wp-json/wc-bookings/v1/products/slots";
	$slots = wp_remote_get( $url );
	$_SESSION['slots'] = $slots;
//}

if ( ! is_wp_error( $slots ) ) {
	echo '<pre>';
	var_dump(
		json_decode( $slots['body'] )->records
	);
	echo '</pre>';
} else {
	echo '<pre>';
	var_dump(
		$slots
	);
	echo '</pre>';
}

?>
<div class="wc-book-order">
    <button class="wc-book-order__btn">Search</button>
</div>