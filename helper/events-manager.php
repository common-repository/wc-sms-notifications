<?php
if (! defined( 'ABSPATH' )) exit;

class wcsmsnEMBooking
{
	public function __construct() {
		require_once WP_PLUGIN_DIR.'/events-manager/classes/em-booking.php';
		add_filter('wcsmsnDefaultSettings',  __CLASS__ .'::addDefaultSetting',1);
		add_action( 'em_bookings_added',  array($this, 'send_sms_wpbc_booking_created')); // Booking Hook
		add_filter( 'em_booking_set_status',  array($this, 'send_sms_wpbc_booking_modify'),1,2); //Changing Status Hook
		add_action( 'wcsmsn_addTabs', array( $this, 'addTabs' ), 10 );
	}

	/*add tabs to wcsmsn settings at backend*/
	public static function addTabs($tabs=array())
	{
		$customer_param=array(
			'checkTemplateFor'	=> 'em_customer',
			'templates'			=> self::getCustomerTemplates(),
		);

		$admin_param=array(
			'checkTemplateFor'	=>'em_admin',
			'templates'			=>self::getAdminTemplates(),
		);

		$tabs['em_customer']['title']		= 'EMBooking Cust.';
		$tabs['em_customer']['tab_section'] = 'embkcsttemplates';
		$tabs['em_customer']['tabContent']	= 'views/message-template.php';
		$tabs['em_customer']['icon']		= 'dashicons-admin-users';
		$tabs['em_customer']['params']		= $customer_param;

		$tabs['em_admin']['title']			= 'EMBooking Admin';
		$tabs['em_admin']['tab_section'] 	= 'embkadmintemplates';
		$tabs['em_admin']['tabContent']		= 'views/message-template.php';
		$tabs['em_admin']['icon']			= 'dashicons-list-view';
		$tabs['em_admin']['params']			= $admin_param;
		return $tabs;
	}

	/*add default settings to savesetting in setting-options*/
	public function addDefaultSetting($defaults=array())
	{
		$embk_booking_statuses 	= self::em_booking_statuses();

		foreach($embk_booking_statuses as $ks => $vs)
		{
			$defaults['wcsmsn_embk_general']['embk_admin_notification_'.$vs]	= 'off';
			$defaults['wcsmsn_embk_general']['embk_order_status_'.$vs]		= 'off';
			$defaults['wcsmsn_embk_message']['embk_admin_sms_body_'.$vs]		= '';
			$defaults['wcsmsn_embk_message']['embk_sms_body_'.$vs]			= '';
		}
		return $defaults;
	}

	public static function getCustomerTemplates()
	{
		$embk_booking_statuses = wcsmsnEMBooking::em_booking_statuses();

		$templates 			= array();
		foreach($embk_booking_statuses as $ks  => $vs){

			$ks 				= $vs;
			$ks 				= str_replace(' ', '_', $ks);
			$current_val 		= (is_array($embk_booking_statuses) && array_key_exists($vs, $embk_booking_statuses)) ? $embk_booking_statuses[$vs] : $vs;

			$current_val 		= ($current_val==$vs)?"on":'off';

			$checkboxNameId		= 'wcsmsn_embk_general[embk_order_status_'.$vs.']';
			$textareaNameId		= 'wcsmsn_embk_message[embk_sms_body_'.$vs.']';

			$text_body 			= wcsmsn_get_option('embk_sms_body_'.$vs, 'wcsmsn_embk_message', sprintf(__('Hello %s, status of your booking %s with %s has been changed to %s.',wcsmsnConstants::TEXT_DOMAIN), '[#_BOOKINGNAME]', '[#_BOOKINGID]', '[#_EVENTNAME]', '[#_BOOKINGSTATUS]'));

			$templates[$ks]['title'] 			= 'When Order is '.ucwords($vs);
			$templates[$ks]['enabled'] 			= $current_val;
			$templates[$ks]['status'] 			= $ks;
			$templates[$ks]['text-body'] 		= $text_body;
			$templates[$ks]['checkboxNameId'] 	= $checkboxNameId;
			$templates[$ks]['textareaNameId'] 	= $textareaNameId;
			$templates[$ks]['token'] 			= self::getEMBookingvariables(true);
		}
		return $templates;
	}

	public static function getAdminTemplates()
	{
		$embk_booking_statuses = wcsmsnEMBooking::em_booking_statuses();

		$templates = array();
		foreach($embk_booking_statuses as $ks  => $vs){

			$current_val 		= wcsmsn_get_option( 'embk_admin_notification_'.$vs, 'wcsmsn_embk_general', 'on');

			$checkboxNameId		= 'wcsmsn_embk_general[embk_admin_notification_'.$vs.']';
			$textareaNameId		= 'wcsmsn_embk_message[embk_admin_sms_body_'.$vs.']';

			$text_body 			= wcsmsn_get_option('embk_admin_sms_body_'.$vs, 'wcsmsn_embk_message', sprintf(__('%s status of order %s has been changed to %s.',wcsmsnConstants::TEXT_DOMAIN), '[#_BOOKINGNAME]', '[#_BOOKINGID]', '[#_EVENTNAME]', '[#_BOOKINGSTATUS]'));

			$templates[$ks]['title'] 			= 'When Order is '.ucwords($vs);
			$templates[$ks]['enabled'] 			= $current_val;
			$templates[$ks]['status'] 			= $ks;
			$templates[$ks]['text-body'] 		= $text_body;
			$templates[$ks]['checkboxNameId'] 	= $checkboxNameId;
			$templates[$ks]['textareaNameId'] 	= $textareaNameId;
			$templates[$ks]['token'] 			= self::getEMBookingvariables(true);
		}
		return $templates;
	}

	/*Get Booking Status*/
	public static function em_booking_statuses()
	{
		if(class_exists('EM_Booking'))
		{
			$booking = new EM_Booking();
			$status = $booking->status_array;
			return $status;
		}
	}

	/*
		display event Manager booking variable at wcsmsn setting page
	*/
	public static function getEMBookingvariables($onlyvariable=false)
	{
		$variables = array(
			'[#_BOOKINGID]' 					=> 'Booking Id',
			'[#_BOOKINGNAME]' 					=> 'Booking Person Name',
			'[#_BOOKINGEMAIL]'  				=> 'Booking Person EMail',
			'[#_BOOKINGPHONE]'					=> 'Booking Person Phone',
			'[#_BOOKINGSPACES]' 				=> 'Booking Spaces',
			'[#_BOOKINGDATE]' 					=> 'Booking Date',
			'[#_BOOKINGTIME]' 					=> 'Booking Time',
			'[#_BOOKINGDATETIME]' 				=> 'Booking DateTime',
			'[#_BOOKINGLISTURL]' 				=> 'Booking List URL',
			'[#_BOOKINGCOMMENT]' 				=> 'Booking Comment',
			'[#_BOOKINGPRICEWITHOUTTAX]' 		=> 'Booking Price Without Tax',
			'[#_BOOKINGPRICETAX]' 				=> 'Booking Price Tax',
			'[#_BOOKINGPRICE]'					=> 'Booking Price',
			'[#_BOOKINGTICKETNAME]'				=> 'Booking Ticket Name',
			'[#_BOOKINGTICKETDESCRIPTION]' 		=> 'Booking Ticket Description',
			'[#_BOOKINGTICKETPRICEWITHTAX]' 	=> 'Booking Ticket With Tax',
			'[#_BOOKINGTICKETPRICEWITHOUTTAX]'	=> 'Booking Ticket Without Tax',
			'[#_BOOKINGTICKETTAX]'				=> 'Booking Ticket Tax',
			'[#_BOOKINGTICKETPRICE]'			=> 'Booking Ticket Price',
			'[#_BOOKINGSTATUS]'					=> 'Booking Status',
			'[#_EVENTNAME]'						=> 'Event Name',
			'[#_EVENTDATES]'					=> 'Event Date',
			'[#_EVENTTIMES]'					=> 'Event Time',
		);

		if($onlyvariable)
		{
			return $variables;
		}
		else
		{
			$ret_string = '';
			foreach($variables as $vk => $vv)
			{
				$ret_string .= sprintf( "<a href='#' val='%s'>%s</a> | " , $vk , __($vv,wcsmsnConstants::TEXT_DOMAIN));
			}
			return $ret_string;
		}
	}
	/* Send sms to customer and admin on Booking from customer side*/
	public static function send_sms_wpbc_booking_created($booking)
	{
		if (function_exists('em_get_booking'))
		{
			$booking_id 		= $booking->booking_id;
			$buyer_sms_data 	= array();
			$booking 			= em_get_booking($booking_id);
			$booking_status 	= $booking->status_array;
			$Current_booking 	= $booking->booking_status;
			$buyer_phone_number = $booking->get_person()->phone;
			$admin_phone_number = wcsmsn_get_option( 'sms_admin_phone', 'wcsmsn_message', '' );

			$is_enabled=wcsmsn_get_option( 'embk_order_status_'.$booking_status[$Current_booking], 'wcsmsn_embk_general');
			if($buyer_phone_number!='' && $is_enabled=='on')
			{
				$buyer_message	= wcsmsn_get_option( 'embk_sms_body_'.$booking_status[$Current_booking], 'wcsmsn_embk_message', '');

				$buyer_message 	= str_replace('[#_BOOKINGSTATUS]',$booking_status[$Current_booking],$buyer_message);
				do_action('wcsmsn_send_sms', $buyer_phone_number, self::parseSMSbody($buyer_message,$booking));
			}
			if(wcsmsn_get_option( 'embk_admin_notification_'.$booking_status[$Current_booking], 'wcsmsn_embk_general') == 'on' && $admin_phone_number!='')
			{
				$admin_message 	= wcsmsn_get_option( 'embk_admin_sms_body_'.$booking_status[$Current_booking], 'wcsmsn_embk_message', '');
				$admin_message 	= str_replace('[#_BOOKINGSTATUS]',$booking_status[$Current_booking],$admin_message);
				do_action('wcsmsn_send_sms', $admin_phone_number, self::parseSMSbody($admin_message,$booking));
			}
		}
		else
		{
			echo 'wpdev_booking not found';
		}
		exit();
	}

	/* Send sms to admin on Change Status from admin side*/
	function send_sms_wpbc_booking_modify($result,$booking)
	{
		if (!empty($result) && $booking->previous_status!=$booking->booking_status)
		{
			$booking_id 		= $booking->booking_id;
			$admin_sms_data 	= array();
			$booking 			= em_get_booking($booking_id);
			$booking_status 	= $booking->status_array;
			$Current_booking 	= $booking->booking_status;
			$admin_phone_number = wcsmsn_get_option( 'sms_admin_phone', 'wcsmsn_message', '' );

			if(wcsmsn_get_option( 'embk_admin_notification_'.$booking_status[$Current_booking], 'wcsmsn_embk_general') == 'on' && $admin_phone_number!='')
			{
				$admin_message 		= wcsmsn_get_option( 'embk_admin_sms_body_'.$booking_status[$Current_booking], 'wcsmsn_embk_message', '');
				$admin_message 		= str_replace('[#_BOOKINGSTATUS]',$booking_status[$Current_booking],$admin_message);
				do_action('wcsmsn_send_sms', $admin_phone_number, self::parseSMSbody($admin_message,$booking));
			}
		}
		else
		{
		echo 'wpdev_booking not found';
		}
		exit();
	}

	/* remove brackets and replace value of variables */
	public static function parseSMSbody($sms_content=null,$booking=null)
	{
		$order_variables		= self::getEMBookingvariables(true);
		foreach ($order_variables as $key => $value) {
			$array_trim_keys[] 	= trim($key,'[]');
		}
		$sms_content 			= str_replace( array_keys($order_variables), array_values($array_trim_keys), $sms_content );
		return $booking->output($sms_content);
	}
}
new wcsmsnEMBooking;