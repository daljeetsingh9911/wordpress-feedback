<?php
 require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
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

        where DATE(bca.status_changed_at) = DATE(NOW())
        and bca.status = 'done'
"
);
echo "<pre>";
$resp = array();
foreach ($result as $key => $value) {
    ob_start(); //Start remembering everything that would normally be outputted, but don't quite do anything with it yet
    include(__DIR__.'/email-template.php'); //Gives whatever has been "saved"
    $html = ob_get_clean(); 
    $email = $value->email;
    if(!empty($email)){
      
        $resp[]  =  wp_mail( 'daljeetsingh9911@gmail.com', 'Recensione del servizio', $html );
    }
}
