<?php

$path = preg_replace('/wp-content.*$/', '', __DIR__);
require_once($path . 'wp-load.php');

$response = [];
$response["message"] = "Invalid Data Provided";
$response["status"] = false;

if (isset($_POST['send']) && isset($_POST["order_id"])) {
    $response["message"] = "Order";
    $response["status"] = true;
    $order_id = $_POST["order_id"];
    $order = wc_get_order($order_id);
    $response['order_keys'] = wcsn_order_get_keys($order_id);
    $response['data'] = get_serial_associated_with_order_test($order_id);

}

function bard_function($order_id)
{

		$keys = wcsn_order_get_keys( $order_id );
		if ( empty( $keys )) {
			return [];
		}
        $results = [];

		foreach ( $keys as $key ) {
			$results[] = $key->get_serial_key();
		}

        return $results;

}

function get_serial_associated_with_order_test($order_id)
{
        $serial = "";
		$keys = wcsn_order_get_keys( $order_id );
		if ( empty( $keys )) {
			return $serial;
		}
      
		foreach ( $keys as $key ) {
			$serial .= $key->get_serial_key();
		}

        return $serial;

}



echo json_encode($response);

