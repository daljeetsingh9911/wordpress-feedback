<?php
/*
Plugin Name:IBS Star Rating Reviews
Description: Receive star rating reviews from users.
Version: 1.0
Author: Daljeetsingh
*/

register_activation_hook(__FILE__, 'activate_plugin_function');
register_deactivation_hook(__FILE__, 'deactivate_plugin_function');

function activate_plugin_function() {
    global $wpdb;   
    $table_name = $wpdb->prefix . 'IBS_star_rating_reviews';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        ID INT(11) NOT NULL AUTO_INCREMENT,
        comment TEXT,
        rating INT(2),
        unique_id VARCHAR(255),
        user_id INT(11),
        appointment_id INT(11),
        status VARCHAR(20),
        times_submitted INT(11),
        is_blocked TINYINT(1),
        PRIMARY KEY (ID)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

}

function deactivate_plugin_function() {
    // Perform cleanup tasks on plugin deactivation.
    // global $wpdb;
    // $table_name = $wpdb->prefix . 'IBS_star_rating_reviews';
    // $wpdb->query("DROP TABLE IF EXISTS $table_name");
}


// Add the menu item to the admin sidebar
function add_star_rating_menu() {
    add_menu_page(
        'Star Rating Reviews', // Page title
        'IBS Star Rating',          // Menu title
        'manage_options',       // Capability required to access
        'ibs-star-rating',    // Menu slug (unique identifier)
        'star_rating_page',     // Callback function to display the page
        'dashicons-star-filled', // Icon for the menu (you can change this)
        30                     // Menu position
    );
}

add_action('admin_menu', 'add_star_rating_menu');

// Callback function to display your plugin page
function star_rating_page() {
    // Add your page content here
    require_once(__DIR__.'/details.php');
}


function create_shortcode(){
    $token = sanitize_text_field($_GET['token']);
    $bcaId = sanitize_text_field($_GET['bcaId']);

    $customAppointmentToken = verifyAppointmentToken($token,$bcaId);
    if(empty($customAppointmentToken)){
        require_once(__DIR__.'/invalid-link.php');
    }else if($customAppointmentToken->status == 'done' && !empty(verifyToken())){
        require_once(__DIR__.'/submitted.php');
    }else{
        require_once(__DIR__.'/rating-form.php');
    }

}

add_shortcode('ibs_star_rating_url', 'create_shortcode');




// Enqueue CSS and JS files
function enqueue_plugin_assets() {
    $plugin_dir_url = plugin_dir_url(__FILE__);

    // Enqueue CSS
    wp_enqueue_style('ibs-styles', $plugin_dir_url . 'assets/css/ibs-star-rating.css');
    wp_enqueue_style('ibs-datatableStyle', $plugin_dir_url . 'assets/css/datatable.css');
    wp_enqueue_style('ibs-rating-style', $plugin_dir_url . 'assets/css/star-rating-svg.css');


    // Enqueue JS
    wp_enqueue_script('ibs-datatable', $plugin_dir_url . 'assets/js/datatable.js',['jquery']);
    wp_enqueue_script('ibs-rating-script', $plugin_dir_url . 'assets/js/jquery.star-rating-svg.js',['jquery']);
    wp_enqueue_script('ibs-scripts', $plugin_dir_url . 'assets/js/ibs-star-rating.js',['jquery','ibs-datatable','ibs-rating-script']);

}


function enqueue_plugin_assets_backend(){
    $plugin_dir_url = plugin_dir_url(__FILE__);
    // Enqueue CSS
    wp_enqueue_style('ibs-styles', $plugin_dir_url . 'assets/css/ibs-star-rating.css');
    wp_enqueue_style('ibs-datatableStyle', $plugin_dir_url . 'assets/css/datatable.css');
    wp_enqueue_style('ibs-rating-style', $plugin_dir_url . 'assets/css/star-rating-svg.css');


    // Enqueue JS
    wp_enqueue_script('ibs-datatable', $plugin_dir_url . 'assets/js/datatable.js',['jquery']);
    wp_enqueue_script('ibs-rating-script', $plugin_dir_url . 'assets/js/jquery.star-rating-svg.js',['jquery']);
    wp_enqueue_script('ibs-scripts', $plugin_dir_url . 'assets/js/ibs-star-ratingBK.js',['jquery','ibs-datatable']);
    
}


add_action('wp_enqueue_scripts', 'enqueue_plugin_assets');
add_action('admin_enqueue_scripts', 'enqueue_plugin_assets_backend');


function verifyToken(){
    global $wpdb;
    $token = sanitize_text_field($_GET['token']);
    $bcaId = sanitize_text_field($_GET['bcaId']);

    $table_name = $wpdb->prefix;

    $result = $wpdb->get_results("SELECT * FROM `{$table_name}IBS_star_rating_reviews` where unique_id='$token' and id='$bcaId'");
    return current($result); 
}

function verifyAppointmentToken($token='invalid',$bcaId=0){
    global $wpdb;
    $table_name = $wpdb->prefix;

    $result = $wpdb->get_results("SELECT * FROM `{$table_name}bookly_customer_appointments` where token='$token'  and id='$bcaId'");
    return current($result); 
}


//+++++++++++++++++++++++++++++++AJAX Section +++++++++++++++++++++

add_action('wp_ajax_fetch_review_data', 'fetch_review_data_callback' );
add_action('wp_ajax_nopriv_fetch_review_data', 'fetch_review_data_callback'); 

function fetch_review_data_callback() {
	// Your database query to fetch data here
    global $wpdb;

    $result = $wpdb->get_results(
        "SELECT ibsR.rating, buser.*,bca.id as bcaId,bca.customer_id,bca.appointment_id, bca.token, bc.name as category,bs.title as comune ,ba.start_date, ba.end_date FROM `{$wpdb->prefix}bookly_customer_appointments` as bca 

            join `{$wpdb->prefix}bookly_appointments` as ba
                    on ba.id = bca.appointment_id

            join `{$wpdb->prefix}bookly_services` as bs
                        on bs.id = ba.service_id

            join `{$wpdb->prefix}bookly_categories` as bc
                on bc.id = bs.category_id

            join `{$wpdb->prefix}bookly_customers` as buser
                on buser.id = bca.customer_id

            join `{$wpdb->prefix}IBS_star_rating_reviews` as ibsR
                on ibsR.unique_id = bca.token
            
            where ibsR.unique_id = bca.token
              "
            
    );

    $dataWrapper = array();
    if(!empty($result)){
        foreach ($result as $key => $value) {
            $data[] =  $value->bcaId;
            $data[] =  $value->rating;
            $data[] =  $value->full_name;
            $data[] =  $value->category;
            $data[] =  $value->comune;
            array_push($dataWrapper,$data);
        }
    }
    $finalArray =  array('data'=>$dataWrapper);
    echo wp_json_encode( $finalArray );
	wp_die(); // this is required to terminate immediately and return a proper response
}


add_action('wp_ajax_ibs_add_review', 'ibs_add_review_callback' );
add_action('wp_ajax_nopriv_ibs_add_review', 'ibs_add_review_callback'); 

function ibs_add_review_callback(){

    global $wpdb;
    parse_str($_POST['data'], $postData);

    $table_name = $wpdb->prefix . 'IBS_star_rating_reviews';
    if(empty($postData['rating']) || empty($postData['comment'])){
        echo json_encode([
            'success'=>false,
            'message'=>__("I campi Valutazione e Commento sono obbligatori")]);   
    wp_die(); 
        
    }

    // Data to insert
    $data = array(
        'comment' =>$postData['comment'],
        'rating' => $postData['rating'],
        'unique_id' => $postData['uqid'],
        'user_id' => $postData['usrid'],
        'appointment_id' => $postData['bcaId'],
        'status' => 'done',
        'times_submitted' => 1,
        'is_blocked' => 0
    );
    $result = $wpdb->insert($table_name, $data);

    if( $result ){
        echo json_encode(['success'=>true]);
    }else{
        echo json_encode(['success'=>false]);

    }
    wp_die(); 
}

//+++++++++++++++++++++++++++++++AJAX Section +++++++++++++++++++++

// Cron job

// Schedule a cron job to run daily at 9 PM
function my_custom_cron_job() {
 
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
    
    $resp = array();
    foreach ($result as $key => $value) {
        ob_start(); //Start remembering everything that would normally be outputted, but don't quite do anything with it yet
        include(__DIR__.'/email-template.php'); //Gives whatever has been "saved"
        $html = ob_get_clean(); 
        $email = $value->email;
        if(!empty($email)){
            $resp[]  =  wp_mail( $email, 'Recensione del servizio', $html );
        }
    }

}

// Schedule the cron job to run daily at 9 PM
if (!wp_next_scheduled('my_custom_cron')) {
    // Set the time for 9 PM
    wp_schedule_event(time(), '1min', 'my_custom_cron');
}

// Hook the cron job function to the scheduled event
add_action('my_custom_cron', 'my_custom_cron_job');