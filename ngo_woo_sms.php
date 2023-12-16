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

 function send_customer_notification($order_id) {
        try {
            $order_details = new \WC_Order($order_id);

        $message = 'Order Completed successfully. [serial_key]. Retrieve delayed cards at https://resultcheckerpin.com/retrieve-checker/ .Support 0241656373';
        $serial = "";
        $order_items = $order_details->get_items();
        foreach ($order_items as $item_id=> $item) {

            $product_id = $item->get_product_id();

            $serial = display_order_item_meta($item_id,$item,$product_id);
            
        }

       $message = str_replace("[serial_key]",$serial,$message);
       $message = str_replace("<code>","",$message);
       $message = str_replace("</code>","",$message);

       $customer_phone_no = "";
        
        try{

            $customer_phone_no = $order_details->get_billing_phone();
            $customer_phone_no = str_replace("+", "", $customer_phone_no);
            $customer_phone_no = trim($customer_phone_no);
        }
        catch (Exception $e) {

        }

        if ($customer_phone_no) {
            $api_key = ngo_woo_get_option("mnotifysms_woocommerce_api_key", 'mnotifysms_setting', '');
            $sms_from = ngo_woo_get_option("mnotifysms_woocommerce_sms_from", 'mnotifysms_setting', '');
            
            if($api_key == '' || $api_key == '') return;
            if($sms_from == '') $sms_from = 'SMS';
           
            try {
    
    
                $baseurl = "https://apps.mnotify.net/smsapi";
                $query ="?key=".$api_key."&to=$customer_phone_no&msg=$message.&sender_id=".$sms_from."";
                $final_uri = $baseurl.$query;
                $response = file_get_contents($final_uri);
                header ("Content-Type:text/xml");       
              
            } catch (Exception $e) {
               
            }
        }
        } catch (\Throwable $th) {
            //throw $th;
        }

      
 }

function ngo_woo_get_option($option, $section, $default = '') {

    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }

    return $default;
}

add_action( 'woocommerce_order_status_completed', 'send_customer_notification', 10, 1);



?>