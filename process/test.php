<?php

$path = preg_replace('/wp-content.*$/','',__DIR__);
require_once($path . 'wp-load.php');

$response = [];
$response["message"] = "Invalid Data Provided";
$response["status"] = false;

if (isset($_POST['send']) && isset($_POST["order_id"]) ) {
    $order_id = $_POST["order_id"];
    $response['data'] =  get_post_meta($order_id);
    
}

echo json_encode($response);

