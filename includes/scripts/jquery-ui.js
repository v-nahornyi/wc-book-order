/**
 * Script is meant to be injected on a page with WC Bookings calendar
 * Prefills start and end date of booking (on the same date)
 */
jQuery(function($){
    const params = new URLSearchParams(location.search);
    if (params.size) {
        const minDate = params.get('minDate');
        if (minDate) {
            const dateNoTime = minDate.split('T')[0].split('-');

            $('[name=wc_bookings_field_start_date_year]').val(dateNoTime[0]);
            $('[name=wc_bookings_field_start_date_month]').val(dateNoTime[1]);
            const startDay = $('[name=wc_bookings_field_start_date_day]');
            startDay.val(dateNoTime[2])

            $('[name=wc_bookings_field_start_date_to_year]').val(dateNoTime[0]);
            $('[name=wc_bookings_field_start_date_to_month]').val(dateNoTime[1]);
            const endDay = $('[name=wc_bookings_field_start_date_to_day]');
            endDay.val(dateNoTime[2])

            setTimeout(function() {
                startDay.trigger('change');
                endDay.trigger('change');
            }, 0)
        }
    }
})