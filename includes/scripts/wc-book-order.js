jQuery( function($) {

    /** Datepicker */
    const dateInput = $('#wc-book-datepicker');
    dateInput.flatpickr({
        dateFormat: "Y-m-d",
    });

    /** REST API request **/
    $('.wc-book-search__btn').on( 'click', requestSlots );

    function requestSlots() {
        let targetDate = dateInput.val();

        if ( targetDate.length === 10 ) {
            targetDate = new Date( targetDate );

            const [ minDate, maxDate ] = constructDates(targetDate);

            if ( minDate && maxDate ) {
                const loader = $('.wc-book-loading');
                loader.fadeIn().css('display', 'flex');



                $.ajax({
                    method: 'POST',
                    dataType: 'json',
                    url: elementorFrontendConfig.urls.ajaxurl || '/wp-admin/admin-ajax.php',
                    data: {
                        action: 'wc_book_order_by_date',
                        minDate: minDate,
                        maxDate: maxDate,
                    }
                })
                    .done(function( res ) {
                        buildHtml(res);
                    })
                    .fail(function( req, textStatus, errorThrown ) {
                        console.log(req, textStatus, errorThrown);
                    })
                    .always(function(){
                        loader.fadeOut();
                    })
            } else {
                console.error('Invalid dates are specified.');
            }
        } else {
            console.error('Invalid request.')
        }
    }

    function constructDates(start) {
        if ( isNaN( start.getTime() ) ) {
            return [false, false];
        }

        let end = new Date(start);
        end.setDate(end.getDate() + 1);

        const startDate = (start.getDate()).toString().padStart(2, '0');
        const startMonth = (start.getMonth() + 1).toString().padStart(2, '0');

        const endMonth = (end.getMonth() + 1).toString().padStart(2, '0');
        const endDate = (end.getDate()).toString().padStart(2, '0');

        start = `${start.getFullYear()}-${startMonth}-${startDate}T00:00`;
        end   = `${end.getFullYear()}-${endMonth}-${endDate}T00:00`;

        return [start, end];
    }


    let notFirstTime;

    /**
     * Builds product archive based on data passed to the function
     * @param res
     */
    function buildHtml(res) {
        /** Clear last search results */
        if (notFirstTime) {
            $('.wc-book-archive:not(.wc-book-template-archive )').remove()
        }

        const section = $('.wc-book-template-archive');
        /** Response double-check */
        if (res.success) {
            /** Iterate products sorted by category */
            for (const prop in res.data) {
                console.log(prop);
                /** Setup new section with products */
                const block = section.clone();
                block
                    .removeClass('wc-book-template-archive')
                    .find('.wc-book-archive__title').text(prop);

                const grid = block.find('.jet-listing-grid__items');
                const item = grid.find('.jet-listing-grid__item');
                const products = res.data[prop];

                if (Array.isArray(products) && products.length) {
                    /** Fill products in section */
                    products.forEach( (e, i) => {
                        const prodItem = i < 1 ? item : item.clone();
                        /** Set image and product link */
                        prodItem.find('.wc-book-prod-link')
                            .css('background-image', `url(${e.image})`)
                            .attr('href', e.url);
                        /** Set Title */
                        prodItem.find('.elementor-heading-title').text(e.title);
                        /** Set Price */
                        const currency = prodItem.find('.currency-sym');
                        currency.parent().html(currency[0].outerHTML + e.price);

                        if (i) {
                            prodItem.appendTo(grid);
                        }
                    });

                    block.appendTo(section.parent());
                }
            }
            /** Set flag after initial search to clear DOM */
            notFirstTime = true;
        } else {
            console.error('Invalid response');
        }
    }
});