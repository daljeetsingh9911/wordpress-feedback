

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


        initStarRating();

        $(document).on('submit','#rating-form',function(e) {
            e.preventDefault();
            var formdata = $(this).serialize();
            $(this).find("button[type='submit'").text('Loading..');
            $(this).find("button[type='submit'").attr("disabled", true);
          // You need to use standard javascript object here
             $.post(wpfc_ajaxurl, {
                    action: 'ibs_add_review', // Action hook to process the request
                    data:formdata
                }, function (response) {
                    let rep = JSON.parse(response);

                    if(rep.success == false){
                        $('p#ibs-error-msg').text(rep.message);
                        $('#rating-form').find("button[type='submit'").text('Invia');
                        $('#rating-form').find("button[type='submit'").removeAttr("disabled");
                    }else if(rep.success == true){
                        $('#rating-form').slideUp();
                        $('.ibs-feedback-done').slideDown();

                    }
                },
             );
        })

    });// document ready ends

    function initStarRating(val=0){
        $(".my-rating").starRating({
            initialRating: val,
            strokeColor: '#894A00',
            strokeWidth: 10,
            starSize: 25,
            disableAfterRate:false,
            callback: function(currentRating, $el){
                $("#ratingInp").val(currentRating);
            }
        });
    }

}(jQuery));