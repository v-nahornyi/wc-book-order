<?php
/**
 * Template to output available products for specific date
 * or current week's Friday-Sunday
 * using WC Bookings REST API
 *
 * @file promo-slider.php
 */

$now        = new DateTimeImmutable();
$today      = $now->format( 'Y-m-d' );
$presetDate = get_field( 'date_for_units_available_slider', 'option' );

if ( $presetDate && $presetDate >= $today ) {
	$minDate = $presetDate;
	$maxDate = ( new DateTime( $minDate ) )->modify( '+1 day' )->format( 'Y-m-d' );
} else {
	$friday   = $now->modify( 'this friday' )->format( 'Y-m-d' );
	$saturday = $now->modify( 'this saturday' )->format( 'Y-m-d' );
	$sunday   = $now->modify( 'this sunday' )->format( 'Y-m-d' );

	$minDate = max( $today, $friday );
	$maxDate = $sunday;
}

$products = get_transient( "wc_book_order_by_date-$minDate-$maxDate" );

if ( false === $products ) {
	$products = WCBookingOrder::get()->wc_book_order_by_date( $minDate, $maxDate, true );
}

?>
<style>
    .wc-promo-slide {
        width: fit-content;
        height: 350px;
    }

    .wc-promo-filter {
        --theme-dark-blue: #1b2298;
        width: 12.5rem;
        margin: 0 auto 1rem;
        color: var(--theme-dark-blue);
        background: #fff;
        border: 2px solid var(--theme-dark-blue);
        font-weight: 600;
    }

    @media (max-width: 48rem) {
        .wc-promo-slide {
            height: 300px;
        }
    }
</style>
<select id="wc-promo-filter" class="wc-promo-filter">
    <option value="all" class="wc-promo-filter-item"><?php _e( 'All' ) ?></option>
	<?php foreach ( $products as $product_cat_key => $product_cat_value ) { ?>
        <option value="<?= $product_cat_key ?>" class="wc-promo-filter-item"><?= $product_cat_key ?></option>
	<?php } ?>
</select>
<div class="wc-promo-slider swiper">
    <div class="swiper-wrapper">
		<?php
		foreach ( $products as $product_cats ) {
			foreach ( $product_cats as $product ) { ?>
                <div class="wc-promo-slide jet-listing-grid__item swiper-slide">
                    <a href="<?= $product['url'] ?>"
                       style="background-image: url('<?= $product['image'] ?>');"
                       class="wc-book-prod-link elementor-element e-flex e-con-boxed e-con e-parent">
                        <div class="e-con-inner">
                            <div class="elementor-element elementor-widget elementor-widget-heading">
                                <div class="elementor-widget-container">
                                    <h4 class="elementor-heading-title elementor-size-default"><?= $product['title'] ?></h4>
                                </div>
                            </div>

                            <div class="bottom-element elementor-element e-con-full e-flex e-con e-child">
                                <div class="elementor-element elementor-widget__width-auto elementor-widget elementor-widget-woocommerce-product-price">
                                    <div class="elementor-widget-container">
                                        <p class="price"><?php _e( 'From' ) ?>:
                                            <span class="woocommerce-Price-amount amount">
											<bdi><span class="currency-sym"><?= get_woocommerce_currency_symbol() ?></span><?= $product['price'] ?></bdi>
										</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="elementor-elementproduct-cat-archive-card elementor-widget elementor-widget-heading"
                                     data-element_type="widget" data-widget_type="heading.default">
                                    <div class="elementor-widget-container">
                                        <p class="elementor-size-default wc-book-reserve">Reserve</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
			<?php } ?>
		<?php } ?>
    </div>
</div>
<script type="text/javascript">
    const products = <?= json_encode( $products ) ?>;
    const $ = jQuery;
    $(function () {
        let allProducts = Object.values(products).flat();
        let initialArrayLength = allProducts.length;
        let slider = initPromoSlider(initialArrayLength);
        function initPromoSlider(length, slidesPerViewValue = false) {
            if (length < 3) {
                slidesPerViewValue = 'auto';
            }

            return new Swiper('.wc-promo-slider', {
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true
                },
                slidesPerView: 1,
                spaceBetween: 20,
                speed: 500,
                loop: !slidesPerViewValue,
                breakpoints: {
                    768: {
                        slidesPerView: slidesPerViewValue || 2,
                        spaceBetween: 10
                    },
                    1200: {
                        slidesPerView: slidesPerViewValue || 3,
                        spaceBetween: 30
                    }
                }
            });
        }

        const $filter = $('#wc-promo-filter'),
              $productGrid = $('.wc-promo-slider .swiper-wrapper');

        $filter.on('change', (e) => {
            let $currentCat = e.target.value === 'all' ? allProducts : products[e.target.value];

            if (Array.isArray($currentCat)) {
                slider.destroy();
                let slideExample = $($('.wc-promo-slide')[0]);
                $productGrid.children().remove()

                $currentCat.forEach((el) => {
                    const html = slideExample.clone();

                    /** Set image and product link */
                    html.find('.wc-book-prod-link')
                        .css('background-image', `url(${el.image})`)
                        .attr('href', el.url);
                    /** Set Title */
                    html.find('.elementor-heading-title').text(el.title);
                    /** Set Price */
                    const currency = html.find('.currency-sym');
                    currency.parent().html(currency[0].outerHTML + el.price);

                    html.appendTo($productGrid);
                });

                slider = initPromoSlider($productGrid.children().length);
            }
        });
    });
</script>
