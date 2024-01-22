<?php

$path = preg_replace('/wp-content.*$/','',__DIR__);
require_once($path . 'wp-load.php');
$response = [];
$response["message"] = "Invalid Data Provided";
$response["status"] = false;

if (isset($_POST["trigger"]) && $_POST["trigger"] == 1 && isset($_POST["order_id"])) {
    $display_log = new \Mnotifysms_WooCoommerce_Logger();
    $display_log_prefix = 'ngo_sms_log';
    $order_id = $_POST["order_id"];
    $keys = wcsn_order_get_keys( $order_id );

    $display_log->add($display_log_prefix, "Trigger Keys");
    $display_log->add($display_log_prefix, $keys);

    do_action('woocommerce_order_status_completed', $order_id);
    
    $response["message"] = "Triggered";
    $response["status"] = true;
}

echo json_encode($response);