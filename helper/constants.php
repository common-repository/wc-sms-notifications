<?php
if (! defined( 'ABSPATH' )) exit;
class wcsmsnConstants
{
	const SUCCESS				= "SUCCESS";
	const FAILURE				= "FAILURE";
	const TEXT_DOMAIN 			= "wc-sms-notifications";
	const PATTERN_PHONE			= '/^(\+)?(country_code)?0?\d+$/'; //'/^\d{10}$/';//'/\d{10}$/';
	const ERROR_JSON_TYPE 		= 'error';
	const SUCCESS_JSON_TYPE 	= 'success';
	const USERPRO_AJAX_CHECK	= "mo_phone_validation";
	const USERPRO_VER_FIELD_META= "verification_form";
	const wcsmsn_VERSION = "3.3.5";	
	
	function __construct()
	{
		$this->define_global();
	}
	
	public static function getPhonePattern()
	{
		$country_code = wcsmsn_get_option( 'default_country_code', 'wcsmsn_general' );
		$wcsmsn_mobile_pattern = wcsmsn_get_option( 'wcsmsn_mobile_pattern', 'wcsmsn_general','/^(\+)?(country_code)?0?\d{10}$/' );
		$pattern = ($wcsmsn_mobile_pattern!='') ? $wcsmsn_mobile_pattern:self::PATTERN_PHONE;
		$country_code = str_replace('+', '', $country_code);
		$pattern_phone = str_replace("country_code",$country_code,$pattern);
		return $pattern_phone;
	}	
	
	function define_global()
	{
		global $phoneLogic;
		$phoneLogic = new PhoneLogic();
		define('wcsmsn_MOV_DIR', plugin_dir_path(dirname(__FILE__)));
		define('wcsmsn_MOV_URL', plugin_dir_url(dirname(__FILE__)));
		define('wcsmsn_MOV_CSS_URL', wcsmsn_MOV_URL . 'css/wcsmsn_customer_validation_style.css?v=3.3.1');
		define('wcsmsn_MOV_LOADER_URL', wcsmsn_MOV_URL . 'images/ajax-loader.gif');
	}
}
new wcsmsnConstants;