<?php
if (! defined( 'ABSPATH' )) exit;

class BookingCalendar
{
	public function __construct() {
		add_filter( 'wcsmsnDefaultSettings',  __CLASS__ .'::addDefaultSetting',1);		
		add_action( 'wpbc_booking_approved', array( $this, 'sendsms_approved_pending' ),99,2 );		
		add_action( 'wpdev_new_booking', array( $this, 'sendsms_new_booking' ),100,5 );		
		add_action( 'wpbc_booking_trash', array( $this, 'sendsms_trash' ),100,2 );		
		add_action( 'wcsmsn_addTabs', array( $this, 'addTabs' ), 10 );
	}
	
	/*add default settings to savesetting in setting-options*/
	public function addDefaultSetting($defaults=array())
	{
		$booking_statuses = array('new','pending','approved','trash');
		
		foreach($booking_statuses as $ks => $vs)
		{		
			$defaults['wcsmsn_bc_general']['customer_bc_notify_'.$vs]		= 'off';
			$defaults['wcsmsn_bc_message']['customer_sms_bc_body_'.$vs]	= '';
			$defaults['wcsmsn_bc_general']['admin_bc_notify_'.$vs]		= 'off';
			$defaults['wcsmsn_bc_message']['admin_sms_bc_body_'.$vs]		= '';
		}
		return $defaults;
	}
	
	/*add tabs to wcsmsn settings at backend*/
	public static function addTabs($tabs=array())
	{
		$customer_param=array(
			'checkTemplateFor'	=> 'bc_customer',
			'templates'			=> self::getCustomerTemplates(),
		);

		$admin_param=array(
			'checkTemplateFor'	=>'bc_admin',
			'templates'			=>self::getAdminTemplates(),
		);
		
		$tabs['booking_calendar_cust']['title']		 	= 'Booking Calendar Cust Temp';
		$tabs['booking_calendar_cust']['tab_section'] 	= 'bookingcalendarcusttemplates';
		$tabs['booking_calendar_cust']['tabContent']	= 'views/message-template.php';
		$tabs['booking_calendar_cust']['icon']		 	= 'dashicons-calendar-alt';
		$tabs['booking_calendar_cust']['params']		= $customer_param;

		$tabs['booking_calendar_admin']['title']		= 'Booking Calendar Admin Temp';
		$tabs['booking_calendar_admin']['tab_section'] 	= 'bookingcalendaradmintemplates';
		$tabs['booking_calendar_admin']['tabContent']	= 'views/message-template.php';
		$tabs['booking_calendar_admin']['icon']		 	= 'dashicons-calendar-alt';
		$tabs['booking_calendar_admin']['params']		 	= $admin_param;
		return $tabs;
	}
	
	public static function getCustomerTemplates()
	{		
		$booking_statuses = array(
			//'[new]' 		=> 'New',
			'[pending]' 	=> 'Pending',
			'[approved]' 	=> 'Approved',
			'[trash]' 		=> 'Trash',
		);

		$templates 			= array();
		foreach($booking_statuses as $ks  => $vs){

			$current_val 		= wcsmsn_get_option( 'customer_bc_notify_'.strtolower($vs), 'wcsmsn_bc_general', 'on');

			$checkboxNameId		= 'wcsmsn_bc_general[customer_bc_notify_'.strtolower($vs).']';
			$textareaNameId		= 'wcsmsn_bc_message[customer_sms_bc_body_'.strtolower($vs).']';
			
			$default_template 	= wcsmsnMessages::showMessage('DEFAULT_BOOKING_CALENDAR_CUSTOMER_'.strtoupper($vs));
			
			$text_body 			= wcsmsn_get_option('customer_sms_bc_body_'.strtolower($vs), 'wcsmsn_bc_message', (($default_template!='') ? $default_template : wcsmsnMessages::showMessage('DEFAULT_BOOKING_CALENDAR_CUSTOMER')));

			$templates[$ks]['title'] 			= 'When customer booking is '.ucwords($vs);
			$templates[$ks]['enabled'] 			= $current_val;
			$templates[$ks]['status'] 			= $vs;
			$templates[$ks]['text-body'] 		= $text_body;
			$templates[$ks]['checkboxNameId'] 	= $checkboxNameId;
			$templates[$ks]['textareaNameId'] 	= $textareaNameId;
			$templates[$ks]['token'] 			= self::getBookingCalendarvariables();
		}
		return $templates;
	}
	
	public static function getAdminTemplates()
	{		
		$booking_statuses = array(
			//'[new]' 		=> 'New',
			'[pending]' 	=> 'Pending',
			'[approved]' 	=> 'Approved',
			'[trash]' 		=> 'Trash',
		);

		$templates 			= array();
		foreach($booking_statuses as $ks  => $vs){

			$current_val 		= wcsmsn_get_option( 'admin_bc_notify_'.strtolower($vs), 'wcsmsn_bc_general', 'on');
			$checkboxNameId		= 'wcsmsn_bc_general[admin_bc_notify_'.strtolower($vs).']';
			$textareaNameId		= 'wcsmsn_bc_message[admin_sms_bc_body_'.strtolower($vs).']';

			$text_body 			= wcsmsn_get_option('admin_sms_bc_body_'.strtolower($vs), 'wcsmsn_bc_message', sprintf(__('%s status of booking has been changed to %s.',wcsmsnConstants::TEXT_DOMAIN), '[store_name]:', $vs));
			
			$default_template 	= wcsmsnMessages::showMessage('DEFAULT_BOOKING_CALENDAR_ADMIN_'.strtoupper($vs));
			
			$text_body 			= wcsmsn_get_option('admin_sms_bc_body_'.strtolower($vs), 'wcsmsn_bc_message', (($default_template!='') ? $default_template : wcsmsnMessages::showMessage('DEFAULT_BOOKING_CALENDAR_ADMIN')));

			$templates[$ks]['title'] 			= 'When admin change status to '.ucwords($vs);
			$templates[$ks]['enabled'] 			= $current_val;
			$templates[$ks]['status'] 			= $vs;
			$templates[$ks]['text-body'] 		= $text_body;
			$templates[$ks]['checkboxNameId'] 	= $checkboxNameId;
			$templates[$ks]['textareaNameId'] 	= $textareaNameId;
			$templates[$ks]['token'] 			= self::getBookingCalendarvariables();
		}
		return $templates;
	}
	
	public function sendsms_new_booking($booking_id)
	{
		if (function_exists( 'wpbc_api_get_booking_by_id' ) )
		{
			$buyer_sms_data 	= array();
			$booking 			= wpbc_api_get_booking_by_id($booking_id);
			$buyer_number   	= $booking['formdata']['phone1'];
			
			$customer_message 	= wcsmsn_get_option('customer_sms_bc_body_pending', 'wcsmsn_bc_message','');			
			$customer_bc_notify = wcsmsn_get_option('customer_bc_notify_pending', 'wcsmsn_bc_general','on');
			
			if($customer_bc_notify == 'on' && $customer_message != ''){
			
				$buyer_message  = $this->parse_sms_body($booking, $customer_message);
				do_action('wcsmsn_send_sms', $buyer_number, $buyer_message);
			}
			
			// send msg to admin
			$admin_phone_number = wcsmsn_get_option( 'sms_admin_phone', 'wcsmsn_message', '' );
			
			$nos 				= explode(",",$admin_phone_number);
			$admin_phone_number = array_diff($nos,array("postauthor","post_author"));
			$admin_phone_number = implode(",",$admin_phone_number);
			
			if (!empty($admin_phone_number)){
				
				$admin_bc_notify 	= wcsmsn_get_option( 'admin_bc_notify_pending', 'wcsmsn_bc_general', 'on');				
				$admin_message 	 	= wcsmsn_get_option('admin_sms_bc_body_pending', 'wcsmsn_bc_message','');
				
				if($admin_bc_notify == 'on' && $admin_message != ''){
					
					$admin_message 	= $this->parse_sms_body($booking,$admin_message);
					do_action('wcsmsn_send_sms', $admin_phone_number, $admin_message);
				}
			}
		}
	}
	
	public function sendsms_approved_pending($booking_id,$is_approve_or_pending)
	{
		if (function_exists( 'wpbc_api_get_booking_by_id' ) )
		{
			$buyer_sms_data = array();
			$booking 		= wpbc_api_get_booking_by_id($booking_id);
			$buyer_number   = $booking['formdata']['phone1'];
			
			if($booking['is_new'] == 1){
				exit();
			}
			
			if($is_approve_or_pending == 1){
				$customer_message = wcsmsn_get_option('customer_sms_bc_body_approved', 'wcsmsn_bc_message','');
			}else{
				$customer_message = wcsmsn_get_option('customer_sms_bc_body_pending', 'wcsmsn_bc_message','');
			}
			
			$customer_bc_pending_notify 	= wcsmsn_get_option('customer_bc_notify_pending', 'wcsmsn_bc_general','on');			
			$customer_bc_approved_notify 	= wcsmsn_get_option('customer_bc_notify_approved', 'wcsmsn_bc_general','on');
			
			if(($customer_bc_approved_notify == 'on' && $customer_message != '') || ($customer_bc_pending_notify == 'on' && $customer_message != '')){
				
				$buyer_message   = $this->parse_sms_body($booking, $customer_message);
				do_action('wcsmsn_send_sms', $buyer_number, $buyer_message);
			}
			
			// send msg to admin
			$admin_phone_number 			= wcsmsn_get_option( 'sms_admin_phone', 'wcsmsn_message', '' );
			
			if (!empty($admin_phone_number)){
				
				$wcsmsn_bc_admin_pending_notify 	= wcsmsn_get_option( 'admin_bc_notify_pending', 'wcsmsn_bc_general', 'on');				
				$wcsmsn_bc_admin_approve_notify 	= wcsmsn_get_option( 'admin_bc_notify_approved', 'wcsmsn_bc_general', 'on');				
				
				if($is_approve_or_pending == 1){
					$admin_message = wcsmsn_get_option('admin_sms_bc_body_approved', 'wcsmsn_bc_message','');
				}else{
					$admin_message = wcsmsn_get_option('admin_sms_bc_body_pending', 'wcsmsn_bc_message','');
				}
				
				$nos 				= explode(",",$admin_phone_number);
				$admin_phone_number = array_diff($nos,array("postauthor","post_author"));
				$admin_phone_number = implode(",",$admin_phone_number);
				
				if($wcsmsn_bc_admin_pending_notify == 'on' && $admin_message != '' && $is_approve_or_pending == 0){
					$admin_message 	= $this->parse_sms_body($booking,$admin_message);
					do_action('wcsmsn_send_sms', $admin_phone_number, $admin_message);
				}
				
				if($wcsmsn_bc_admin_approve_notify == 'on' && $admin_message != '' && $is_approve_or_pending == 1){
					$admin_message 	= $this->parse_sms_body($booking,$admin_message);
					do_action('wcsmsn_send_sms', $admin_phone_number, $admin_message);
				}
			}
		}		
	}
	
	public function sendsms_trash($booking_id,$is_trash){
		
		if (function_exists( 'wpbc_api_get_booking_by_id' ) )
		{
			$buyer_sms_data 			= array();
			$booking 					= wpbc_api_get_booking_by_id($booking_id);
			$buyer_sms_data['number']   = $booking['formdata']['phone1'];
			
			$customer_message 			= wcsmsn_get_option('customer_sms_bc_body_trash', 'wcsmsn_bc_message','');			
			$customer_bc_notify 		= wcsmsn_get_option('customer_bc_notify_trash', 'wcsmsn_bc_general','on');
			
			if($customer_bc_notify == 'on' && $customer_message != ''){
				$buyer_sms_data['sms_body']   = $this->parse_sms_body($booking, $customer_message);
				wcsmsncURLOTP::sendsms( $buyer_sms_data );
			}
			
			// send msg to admin
			$admin_phone_number = wcsmsn_get_option( 'sms_admin_phone', 'wcsmsn_message', '' );
			
			$nos 				= explode(",",$admin_phone_number);
			$admin_phone_number = array_diff($nos,array("postauthor","post_author"));
			$admin_phone_number = implode(",",$admin_phone_number);
			
			if (!empty($admin_phone_number)){
				
				$admin_bc_notify 	= wcsmsn_get_option( 'admin_bc_notify_trash', 'wcsmsn_bc_general', 'on');				
				$admin_message 		= wcsmsn_get_option('admin_sms_bc_body_trash', 'wcsmsn_bc_message','');
				
				if($admin_bc_notify == 'on' && $admin_message != ''){
					$admin_message = $this->parse_sms_body($booking,$admin_message);
					do_action('wcsmsn_send_sms', $admin_phone_number, $admin_message);
				}
			}
		}
	}
	
	public function parse_sms_body($data=array(),$content=null)
	{		
		$name 			= $data['formdata']['name1'];
		$secondname 	= $data['formdata']['secondname1'];
		$email 			= $data['formdata']['email1'];
		$visitor		= $data['formdata']['visitors1'];
		$phone 			= $data['formdata']['phone1'];
		$details 		= $data['formdata']['details1'];
		$booking_date 	= $data['booking_date'];

		$find = array(
            '[name]',
            '[secondname]',
            '[email]',
            '[visitors]',
            '[phone]',
            '[details]',
            '[date]',
        );

		$replace = array(
			$name,
			$secondname,
			$email,
			$visitor,
			$phone,
			$details,
			$booking_date,
		);

        $content = str_replace( $find, $replace, $content );
		return $content;
	}
	
	public static function getBookingCalendarvariables()
	{
		$variables = wpbc_get_form_fields_free();
		
		$variable  = array();
		foreach($variables as $vk => $vv ){
			$variable['['.$vk.']'] = $vv;
		}
		$variable['[date]'] = "Booking Date";
		return $variable;
	}
}
new BookingCalendar;