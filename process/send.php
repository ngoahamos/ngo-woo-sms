<?php

$path = preg_replace('/wp-content.*$/','',__DIR__);
require_once($path . 'wp-load.php');

$response = [];
$response["message"] = "Invalid Data Provided";
$response["status"] = false;


$api_key = ngo_woo_send_get_option("mnotifysms_woocommerce_api_key", 'mnotifysms_setting', '');
$sms_from = ngo_woo_send_get_option("mnotifysms_woocommerce_sms_from", 'mnotifysms_setting', '');

if (isset($_POST['send']) && isset($_POST['phone']) && isset($_POST['message'])) {
    $phone = $_POST['phone'];
    $phone = trim($phone);
    $message = $_POST['message'];

    ngo_send_sms_flow($phone, $message, $api_key, $sms_from);
    $response['status'] = true;
    $response['message'] = 'SMS Sent!';
    
}

echo json_encode($response);


function ngo_send_sms_flow($phone, $message,$api_key, $sms_from) {
    try {
        $log = new \Mnotifysms_WooCoommerce_Logger();
        $log_prefix = 'ngo_sms_log';
        $log->add($log_prefix, "Sending SMS ... | $phone | $message");
    
        $baseurl = "https://apps.mnotify.net/smsapi";
        $query ="?key=".$api_key."&to=$phone&msg=$message.&sender_id=".$sms_from."";
        $final_uri = $baseurl.$query;
        $response = file_get_contents($final_uri);
        header ("Content-Type:text/xml");
        $log->add($log_prefix, "SMS Response");
        $log->add($log_prefix, $response);       
      
    } catch (Exception $e) {
        $log->add($log_prefix, "SMS API Failed");
        $log->add($log_prefix, $e);
    }

}

function ngo_woo_send_get_option($option, $section, $default = '') {

    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }

    return $default;
}

 