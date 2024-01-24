<?php

$path = preg_replace('/wp-content.*$/','',__DIR__);
require_once($path . 'wp-load.php');

$response = [];
$response["message"] = "Invalid Data Provided";
$response["status"] = false;

if (isset($_POST['send']) && isset($_POST["order_id"]) ) {
    $response["message"] = "Order";
    $response["status"] = true;
    $order_id = $_POST["order_id"];
    $order = wc_get_order($order_id);
    $response['order_keys'] = wcsn_order_get_keys( $order_id );
    $response['data']  = apply_filters( 'woocommerce_email_order_meta_fields', array(), $false, $order );
    
}

echo json_encode($response);

