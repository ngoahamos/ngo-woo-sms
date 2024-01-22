<?php

$path = preg_replace('/wp-content.*$/','',__DIR__);
require_once($path . 'wp-load.php');

/**
 * Plugin Name: NGO WOO SMS
 * Description: A simple Woo Commerce Order Complete SMS sender using mnotify
 * Author: Amos Ngoah
 * License: GPLv3
 * PluginURI: https://github.com/ngoahamos/ngo_woo_sms
 * Author URI: https://github.com/ngoahamos/ngo_woo_sms
 * Version: 1.0.0
 */




 function ngo_send_customer_notification($order_id) {
    $log = new \Mnotifysms_WooCoommerce_Logger();
    $log_prefix = 'ngo_sms_log';

    $log->add($log_prefix, "initialization");
        try {
            $log->add($log_prefix, 'Order Id => ' . $order_id);
           
            $order_details = new \WC_Order($order_id);
            $log->add($log_prefix, 'Order retrieved');

        $message = 'Order Completed successfully. [serial_key]. Retrieve delayed cards at https://resultcheckerpin.com/retrieve-checker/ .Support 0241656373';
        $serial = "";

        // $log->add($log_prefix, 'About to get keys');

        // $keys = wcsn_order_get_keys( $order_id );

        // if ( empty( $keys ) ) {
        //     $log->add($log_prefix, 'No Keys Found');
		// 	return;
		// }

        // foreach ( $keys as $key ) {
        //     $serial =  $key->get_serial_key();
        // }

        $order_items = $order_details->get_items();

        foreach ($order_items as $item_id=> $item) {

            $product_id = $item->get_product_id();

            $serial = ngo_display_order_item_meta($item_id,$item,$product_id);
            
        }

        $api_key = ngo_woo_get_option("mnotifysms_woocommerce_api_key", 'mnotifysms_setting', '');
        $sms_from = ngo_woo_get_option("mnotifysms_woocommerce_sms_from", 'mnotifysms_setting', '');

        if ($serial == "") {
            // let's notify chris
            $cust_phone = $order_details->get_billing_phone();
            ngo_send_sms("0241656373", "Dear Admin Check this order $order_id. No keys associated with it. Customer Phone $cust_phone", $api_key, $sms_from);


        }
       

        $log->add($log_prefix, 'got the keys ' . $serial);

       $message = str_replace("[serial_key]",$serial,$message);
       $message = str_replace("<code>","",$message);
       $message = str_replace("</code>","",$message);

       $log->add($log_prefix, "message formatted <=> $message");

       $customer_phone_no = "";
        
        try{

            $customer_phone_no = $order_details->get_billing_phone();
            $customer_phone_no = str_replace("+", "", $customer_phone_no);
            $customer_phone_no = trim($customer_phone_no);

            $log->add($log_prefix, "customer phone <=> $customer_phone_no");

        }
        catch (Exception $e) {
            $log->add($log_prefix, "Error Occurred");
            $log->add($log_prefix, $e);
        }

        if ($customer_phone_no) {
            

            $log->add($log_prefix, "KEYS $api_key");
            
            if($api_key == '' || $api_key == '') {
                $log->add($log_prefix, "Existing because of no KEYS");
                return;
            }
            if($sms_from == '') $sms_from = 'SMS';
           
            ngo_send_sms($customer_phone_no, $message, $api_key, $sms_from);
        }
        } catch (\Throwable $th) {
            //throw $th;
            $log->add($log_prefix, "Exception Occurred in the code");
            $log->add($log_prefix, $th);


        }

      
 }

 function ngo_send_sms($phone, $message,$api_key, $sms_from) {
    try {
        $log = new \Mnotifysms_WooCoommerce_Logger();
        $log_prefix = 'ngo_sms_log';
        $log->add($log_prefix, "Sending SMS ... | $phone | $message");
    
        $baseurl = "https://apps.mnotify.net/smsapi";
        $query ="?key=".$api_key."&to=$phone&msg=$message.&sender_id=".$sms_from."";
        $final_uri = $baseurl.$query;
        $response = file_get_contents($final_uri);
        header ("Content-Type:text/xml");       
      
    } catch (Exception $e) {
        $log->add($log_prefix, "SMS API Failed");
        $log->add($log_prefix, $e);
    }

 }

 function ngo_display_order_item_meta( $item_id, $item, $product_id ) {
    try {
        $display_log = new \Mnotifysms_WooCoommerce_Logger();
        $display_log_prefix = 'ngo_sms_log';

        $display_log->add($display_log_prefix, 'inside display');
        $order_id = wc_get_order_id_by_order_item_id( $item_id );

        $keys = wcsn_get_keys(
            array(
                'order_id'   => $order_id,
                'product_id' => $product_id,
                'limit'      => - 1,
            )
        );

  
    
        if ( empty( $keys ) ) {
            $display_log->add($display_log_prefix, 'no keys inside display');
            return;
        }
    
        $data = [];
        $serial_numers = "";
        foreach ( $keys as $index => $key ) {
            $data = array(
                'key'              => array(
                    'label' => __( 'Key', 'wc-serial-numbers' ),
                    'value' => '<code>' . $key->get_key() . '</code>',
                ),
                'expire_date'      => array(
                    'label' => __( 'Expire date', 'wc-serial-numbers' ),
                    'value' => $key->get_expire_date() ? $key->get_expire_date() : __( 'Lifetime', 'wc-serial-numbers' ),
                ),
                'activation_limit' => array(
                    'label' => __( 'Activation limit', 'wc-serial-numbers' ),
                    'value' => $key->get_activation_limit() ? $key->get_activation_limit() : __( 'Unlimited', 'wc-serial-numbers' ),
                ),
                'status'           => array(
                    'label' => __( 'Status', 'wc-serial-numbers' ),
                    'value' => $key->get_status_label(),
                ),
            );
            $serial_numers .= $data['key']['value'];
    
        }
        $display_log->add($display_log_prefix, "serial from display $serial_numers");
    } catch (\Throwable $th) {
        //throw $th;
        $display_log->add($display_log_prefix, "display exception");
        $display_log->add($display_log_prefix,$th);
        return "";
    }

    return $serial_numers;
}

function ngo_woo_get_option($option, $section, $default = '') {

    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }

    return $default;
}

add_action( 'woocommerce_order_status_completed', 'ngo_send_customer_notification', 10, 1);




?>