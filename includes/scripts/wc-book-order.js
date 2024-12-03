jQuery( function($) {
    $('.wc-book-order__btn').on( 'click', requestSlots );

    function requestSlots() {
        const startYear = $('[name=wc_bookings_field_start_date_year]').val();
        const startMonth = $('[name=wc_bookings_field_start_date_month]').val();
        const startDay = $('[name=wc_bookings_field_start_date_day]').val();

        const endYear = $('[name=wc_bookings_field_start_date_to_year]').val();
        const endMonth = $('[name=wc_bookings_field_start_date_to_month]').val();
        const endDay = $('[name=wc_bookings_field_start_date_to_day]').val();

        if ( startMonth && startDay && startYear && endMonth && endDay && endYear ) {
            const startDate = [startYear, startMonth, startDay].join('-');
            const endDate   = [endYear, endMonth, endDay].join('-');
            const { minDate, maxDate } = constructDates(startDate, endDate);

            $.ajax({
                action: 'wc_book_order_by_date',
                url: elementorFrontendConfig.urls.ajaxurl || '/wp-admin/admin-ajax.php',
                data: {
                    'minDate': '',
                    'maxDate': '',
                }
            })
                .done(function( data ) {
                    console.log(data);
                })
                .fail(function( req, textStatus, errorThrown ) {
                    console.log(req, textStatus, errorThrown);
                })
        }
    }

    function constructDates(start, end) {
        const regex = /^\d{4}-\d{2}-\d{2}$/;

        if (start.length !== 10 || end.length !== 10 || ! start.test(regex) || ! end.test(regex) ) {
            return [false, false];
        }
    }
});