<?php
 require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
    if(isset($_GET['token']) && !empty($_GET['token'])){
       
        $token = sanitize_text_field($_GET['token']);
        $bcaId = sanitize_text_field($_GET['bcaId']);

        
        global $wpdb;
        $result = $wpdb->get_results(
            "SELECT buser.*,bca.id as bcaId,bca.customer_id,bca.appointment_id, bca.token, bc.name as category,bs.title as comune ,ba.start_date, ba.end_date FROM `{$wpdb->prefix}bookly_customer_appointments` as bca 

                join `{$wpdb->prefix}bookly_appointments` as ba
                        on ba.id = bca.appointment_id

                join `{$wpdb->prefix}bookly_services` as bs
                            on bs.id = ba.service_id

                join `{$wpdb->prefix}bookly_categories` as bc
                    on bc.id = bs.category_id

                join `{$wpdb->prefix}bookly_customers` as buser
                    on buser.id = bca.customer_id
        
                where bca.token = '$token'
                        and bca.id= '$bcaId'
                        and bca.status = 'done'
                  "
        );
    }   
    $data = current($result);
?>

<form id="rating-form">
    <div class="my-rating"></div>
    <input type="hidden" name="rating" id="ratingInp" value="" />
    <textarea name="comment" id="" cols="30" rows="10"></textarea>
    <input type="hidden" name="bcaId" value='<?=$data->bcaId?>' >
    <input type="hidden" name="uqid" value='<?=$data->token?>' >
    <input type="hidden" name="usrid"  value='<?=$data->customer_id?>' >
    <p id="ibs-error-msg"></p>
    <button type="submit" >Invia</button>
</form>

<div class="ibs-feedback-done">
    <h4>
       Grazie per il vostro feedback 
    </h4>
</div>