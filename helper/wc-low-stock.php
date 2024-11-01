<?php

if (! defined( 'ABSPATH' )) exit;

class WCLowStock
{
	public function __construct() {
		add_action( 'woocommerce_low_stock', array( $this, 'wcsmsn_send_msg_low_stock' ), 11 );
		add_action( 'woocommerce_no_stock', array( $this, 'wcsmsn_send_msg_out_of_stock' ), 10 );
	}

	public function wcsmsn_send_msg_low_stock($product)
	{
		$message 	= wcsmsn_get_option( 'sms_body_admin_low_stock_msg', 'wcsmsn_message', '' );
        $message 	= $this->parse_sms_body($product,$message);
		
		$sms_admin_phone = wcsmsn_get_option( 'sms_admin_phone', 'wcsmsn_message', '' );

		$wcsmsn_notification_low_stock_admin_msg = wcsmsn_get_option( 'admin_low_stock_msg', 'wcsmsn_general', 'on');

		if($wcsmsn_notification_low_stock_admin_msg == 'on' && $message != ''){
			do_action('wcsmsn_send_sms', $sms_admin_phone, $message);
		}
	}
	
	public function wcsmsn_send_msg_out_of_stock($product)
	{
		$message 	= wcsmsn_get_option( 'sms_body_admin_out_of_stock_msg', 'wcsmsn_message', '' );		
		$message 	= $this->parse_sms_body($product,$message);

		$sms_admin_phone = wcsmsn_get_option( 'sms_admin_phone', 'wcsmsn_message', '' );

		$wcsmsn_notification_out_of_stock_admin_msg = wcsmsn_get_option( 'admin_out_of_stock_msg', 'wcsmsn_general', 'on');

		if($wcsmsn_notification_out_of_stock_admin_msg == 'on' && $message != ''){
			do_action('wcsmsn_send_sms', $sms_admin_phone, $message);
		}
	}
	
	public function parse_sms_body($product, $message){
		
		$item_name 	= $product->get_name();
		$item_qty 	= $product->get_stock_quantity();

		$find = array(
            '[item_name]',
            '[item_qty]',
            '[store_name]',
        );

		$replace = array(
			$item_name,
			$item_qty,
			get_bloginfo(),
		);

        $message 	= str_replace($find, $replace, $message);
		return $message;
	}
}
new WCLowStock;