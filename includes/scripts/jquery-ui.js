/**
 * Script is meant to be injected on a page with WC Bookings calendar
 * Prefills start and end date of booking (on the same date)
 */
jQuery(function($){
    const params = new URLSearchParams(location.search);
    if (params.size) {
        const minDate = params.get('minDate');
        if (minDate) {
            const dateNoTime = minDate.split('T')[0];

            // TODO: fix this
            setTimeout(function() {
                $('.picker').datepicker('setDate', dateNoTime).find('.ui-state-active').click()
            }, 1300)
        }
    }
})