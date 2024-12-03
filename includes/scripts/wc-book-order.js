jQuery( function($) {
    $('.wc-book-order__btn').on( 'click', requestSlots );

    function requestSlots() {
        const startYear  = + $('[name=wc_bookings_field_start_date_year]').val();
        const startMonth = + $('[name=wc_bookings_field_start_date_month]').val();
        const startDay   = + $('[name=wc_bookings_field_start_date_day]').val();

        const endYear  = + $('[name=wc_bookings_field_start_date_to_year]').val();
        const endMonth = + $('[name=wc_bookings_field_start_date_to_month]').val();
        const endDay   = + $('[name=wc_bookings_field_start_date_to_day]').val();

        if ( startMonth && startDay && startYear && endMonth && endDay && endYear ) {
            const startDate = new Date( Date.UTC(startYear, startMonth - 1, startDay) );
            const endDate   = new Date( Date.UTC(endYear, endMonth - 1, endDay) );
            const [ minDate, maxDate ] = constructDates(startDate, endDate);

            if ( minDate && maxDate ) {
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
                    .done(function( data ) {
                        console.log(data);
                    })
                    .fail(function( req, textStatus, errorThrown ) {
                        console.log(req, textStatus, errorThrown);
                    })
            } else {
                console.error('Invalid dates are specified.');
            }
        } else {
            console.error('Invalid request.')
        }
    }

    function constructDates(start, end) {
        if ( isNaN( start.getTime() ) || isNaN( end.getTime() ) ) {
            return [false, false];
        }

        if (start === end) {
            end.setDate(end.getDate() + 1);
        }

        start = start.toISOString().split(':')[0] + ':00';
        end   = end.toISOString().split(':')[0] + ':00';

        return [start, end];
    }
});