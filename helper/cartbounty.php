<?php
if (! defined( 'ABSPATH' )) exit;

class WCAbandonedCart
{
	public function __construct() {
		add_filter( 'wcsmsnDefaultSettings',  __CLASS__ .'::addDefaultSetting',1);
		add_action( 'cartbounty_notification_sendout_hook', array( $this, 'wcsmsn_send_sms' ),1 );
		add_action( 'wcsmsn_addTabs', array( $this, 'addTabs' ), 10 );
	}

	/*add tabs to wcsmsn settings at backend*/
	public static function addTabs($tabs=array())
	{
		$cartbounty_param = array(
			'checkTemplateFor'	=> 'cartbounty',
			'templates'			=> self::getCartbountyTemplates(),
		);

		$tabs['cartbounty']['title']		= 'CartBounty';
		$tabs['cartbounty']['tab_section']  = 'cartbountytemplates';
		$tabs['cartbounty']['tabContent']	= 'views/message-template.php';
		$tabs['cartbounty']['icon']		 	= 'dashicons-cart';
		$tabs['cartbounty']['params']		= $cartbounty_param;
		return $tabs;
	}

	/*add default settings to savesetting in setting-options*/
	public function addDefaultSetting($defaults=array())
	{
		$defaults['wcsmsn_ac_general']['customer_notify']	= 'off';
		$defaults['wcsmsn_ac_message']['customer_notify']	= '';
		$defaults['wcsmsn_ac_general']['admin_notify']	= 'off';
		$defaults['wcsmsn_ac_message']['admin_notify']	= '';
		return $defaults;
	}

	public static function getCartbountyTemplates()
	{
		//customer template
		$current_val 		= wcsmsn_get_option( 'customer_notify', 'wcsmsn_ac_general', 'on');
		$checkboxNameId		= 'wcsmsn_ac_general[customer_notify]';
		$textareaNameId		= 'wcsmsn_ac_message[customer_notify]';
		$text_body 			= wcsmsn_get_option( 'customer_notify', 'wcsmsn_ac_message', wcsmsnMessages::showMessage('DEFAULT_AC_CUSTOMER_MESSAGE') );

		$templates 			= array();

		$templates['cartbounty-cust']['title'] 			= 'Send msg to customer when product is left in cart';
		$templates['cartbounty-cust']['enabled'] 		= $current_val;
		$templates['cartbounty-cust']['status'] 		= 'cartbounty-cust';
		$templates['cartbounty-cust']['text-body'] 		= $text_body;
		$templates['cartbounty-cust']['checkboxNameId'] = $checkboxNameId;
		$templates['cartbounty-cust']['textareaNameId'] = $textareaNameId;
		$templates['cartbounty-cust']['token'] 			= self::getAbandonCartvariables();

		//admin template
		$current_val 		= wcsmsn_get_option('admin_notify', 'wcsmsn_ac_general', 'on');
		$checkboxNameId		= 'wcsmsn_ac_general[admin_notify]';
		$textareaNameId		= 'wcsmsn_ac_message[admin_notify]';
		$text_body 			= wcsmsn_get_option('admin_notify', 'wcsmsn_ac_message', wcsmsnMessages::showMessage('DEFAULT_AC_ADMIN_MESSAGE'));

		$templates['cartbounty-admin']['title'] 		= 'Send msg to admin when product is left in cart';
		$templates['cartbounty-admin']['enabled'] 		= $current_val;
		$templates['cartbounty-admin']['status'] 		= 'cartbounty-admin';
		$templates['cartbounty-admin']['text-body'] 	= $text_body;
		$templates['cartbounty-admin']['checkboxNameId']= $checkboxNameId;
		$templates['cartbounty-admin']['textareaNameId']= $textareaNameId;
		$templates['cartbounty-admin']['token'] 		= self::getAbandonCartvariables();

		return $templates;
	}

	public function wcsmsn_send_sms()
	{
		global $wpdb;
		$table_name 	= $wpdb->prefix . CARTBOUNTY_TABLE_NAME;

		//$user_settings_notification_frequency = get_option('cartbounty_notification_frequency');

		$timezone 		=  wp_timezone_string();
		$datetime 		=  get_gmt_from_date('UTC'.$timezone);
		$time_interval 	= date('Y-m-d H:i:s',strtotime('-'.CARTBOUNTY_STILL_SHOPPING.' Minutes',strtotime($datetime)));

		//send msg to user
		$rows_to_phone 	= $wpdb->get_results(
			"SELECT * FROM ". $table_name ." WHERE mail_sent = 0 AND cart_contents != '' AND time < '". $time_interval."'", ARRAY_A );

		if ($rows_to_phone){
			$wcsmsn_ac_customer_notify 	= wcsmsn_get_option( 'customer_notify', 'wcsmsn_ac_general', 'on');
			$wcsmsn_ac_customer_message 	= wcsmsn_get_option( 'customer_notify', 'wcsmsn_ac_message', '' );

			if($wcsmsn_ac_customer_notify == 'on' && $wcsmsn_ac_customer_message != ''){
				foreach ( $rows_to_phone as $data ) {
					do_action('wcsmsn_send_sms', $data['phone'], $this->parse_sms_body($data,$wcsmsn_ac_customer_message));
				}
			}

			//send msg to admin
			$sms_admin_phone 				= wcsmsn_get_option( 'sms_admin_phone', 'wcsmsn_message', '' );
			if (!empty($sms_admin_phone)){
				$wcsmsn_ac_admin_notify 	= wcsmsn_get_option( 'admin_notify', 'wcsmsn_ac_general', 'on');
				$wcsmsn_ac_admin_message 	= wcsmsn_get_option( 'admin_notify', 'wcsmsn_ac_message', '' );

				if($wcsmsn_ac_admin_notify == 'on' && $wcsmsn_ac_admin_message != ''){
					$sms_admin_phone 		= explode(",",$sms_admin_phone);
					foreach($sms_admin_phone as $phone ) {
						do_action('wcsmsn_send_sms', $phone, $this->parse_sms_body($data,$wcsmsn_ac_admin_message));
					}
				}
			}
		}
	}

	public static function getAbandonCartvariables()
	{
		$variables = array(
			'[name]' 			=> 'Name',
			'[surname]' 		=> 'Surname',
			'[email]'  			=> 'Email',
			'[phone]'			=> 'Phone',
			'[location]' 		=> 'Location',
			'[cart_total]' 		=> 'Cart Total',
			'[currency]' 		=> 'Currency',
			'[time]' 			=> 'Time',
			'[item_name]' 		=> 'Item name',
			'[item_name_qty]' 	=> 'Item with Qty',
			'[store_name]' 		=> 'Store Name',
		);
		return $variables;
	}

	public function parse_sms_body($data=array(),$content=null)
	{
		$cart_items 		= (array)unserialize($data['cart_contents']);
		$item_name			= implode(", ",array_map(function($o){return $o['product_title'];},$cart_items));
		$item_name_with_qty	= implode(", ",array_map(function($o){return sprintf("%s [%u]", $o['product_title'], $o['quantity']);},$cart_items));

		$find = array(
            '[item_name]',
            '[item_name_qty]',
            '[store_name]',
        );

		$replace = array(
			wp_specialchars_decode($item_name),
			$item_name_with_qty,
			get_bloginfo(),
		);

        $content = str_replace( $find, $replace, $content );

		$order_variables		= self::getAbandonCartvariables();
		foreach ($order_variables as $key => $value) {
			foreach ($data as $dkey => $dvalue) {
				if(trim($key,'[]')==$dkey){
					$array_trim_keys[$key] = $dvalue;
				}
			}
		}
		$content = str_replace( array_keys($order_variables), array_values($array_trim_keys), $content );

		return $content;
	}
}
new WCAbandonedCart;