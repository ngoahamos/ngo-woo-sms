<?php

function get_serial_associated_with_order($order_id)
{
        $serial = "";
		$keys  = wcsn_order_get_keys( $order_id );
		if ( empty( $keys )) {
			return $serial;
		}
        

		foreach ( $keys as $key ) {
			$serial .= $key->get_serial_key();
		}

        return $serial;

}

