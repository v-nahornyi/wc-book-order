<div class="wc-book-order">
    <input type="text"
           id="wc-book-datepicker"
           class="wc-book-datepicker"
           placeholder="<?php _e( 'Select booking date', 'wc-book-order' ); ?>"
    >

    <button class="wc-book-search__btn">Search</button>

    <div class="wc-book-loading">
        <div><?php _e( 'Loading', 'wc-book-order' ); ?>...</div>
    </div>
</div>
<div class="wc-book-archive-parent">
    <div class="wc-book-archive wc-book-template-archive wc-book-archive-section">
        <h2 class="wc-book-archive__title"></h2>
        <div class="jet-listing-grid__items grid-col-desk-3 grid-col-tablet-2 grid-col-mobile-1">
            <div class="jet-listing-grid__item">
                <a href="" class="wc-book-prod-link elementor-element e-flex e-con-boxed e-con e-parent">
                    <div class="e-con-inner">
                        <div class="elementor-element elementor-widget elementor-widget-heading">
                            <div class="elementor-widget-container">
                                <h4 class="elementor-heading-title elementor-size-default">Rampage 19′ Dual Lane
                                    Waterslide</h4>
                            </div>
                        </div>

                        <div class="bottom-element elementor-element e-con-full e-flex e-con e-child">
                            <div class="elementor-element elementor-widget__width-auto elementor-widget elementor-widget-woocommerce-product-price">
                                <div class="elementor-widget-container">
                                    <p class="price"><?php _e( 'From' ) ?>:
                                        <span class="woocommerce-Price-amount amount">
                                            <bdi><span class="currency-sym"><?php echo get_woocommerce_currency_symbol(); ?></span>400.00</bdi>
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
        </div>
    </div>
</div>
