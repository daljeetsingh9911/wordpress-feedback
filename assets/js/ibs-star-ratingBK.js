

(function ($) {
    "use strict";
    $(document).ready(function () {
        $('#ibs-rating-table').DataTable({
            ajax: {
                url: ajaxurl,
                type: 'POST',
                data(d) {
                    d.action = 'fetch_review_data';
                }
            },
        });
    });

}(jQuery));