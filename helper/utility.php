<?php
if (! defined( 'ABSPATH' )) exit;
class wcsmsnUtility
{	
	public static function get_hidden_phone($phone)
	{
		$hidden_phone = 'xxxxxxx' . substr($phone,strlen($phone) - 3);
		return $hidden_phone;
	}
	
	public static function isBlank( $value )
	{
		if( ! isset( $value ) || empty( $value ))
		//if((! isset( $value ) || empty( $value )) || (is_array($value) && in_array('',$value)))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function _create_json_response($message,$type)
	{
		return array( 'message' => $message, 'result' => $type);
	}
	
	public static function mo_is_curl_installed()
	{
		if  (in_array  ('curl', get_loaded_extensions()))
			return 1;
		else 
			return 0;
	}

	public static function currentPageUrl()
	{
		$pageURL = 'http';

		if ((isset($_SERVER["HTTPS"])) && ($_SERVER["HTTPS"] == "on"))
			$pageURL .= "s";

		$pageURL .= "://";

		if ($_SERVER["SERVER_PORT"] != "80")
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];

		else
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

		if ( function_exists('apply_filters') ) apply_filters('wppb_curpageurl', $pageURL);

        return $pageURL;
	}
	
	public static function mo_get_hiden_email($email)
	{
        if(!isset($email) || trim($email)===''){
			return "";
		}
		$emailsize = strlen($email);
		$partialemail = substr($email,0,1);
		$temp = strrpos($email,"@");
		$endemail = substr($email,$temp-1,$emailsize);
		for($i=1;$i<$temp;$i++){
			$partialemail = $partialemail . 'x';
		}
		$hiddenemail = $partialemail . $endemail;
               
        return $hiddenemail;
    }

	public static function validatePhoneNumber($phone)
	{
		if(!preg_match(wcsmsnConstants::getPhonePattern(),$phone,$matches))
			return false;
		else
			return true;
	}
	
	public static function checkSession()
	{
		if(version_compare(phpversion(), '5.4.0', '>='))
		{
			$session_enabled = ((session_status() !== PHP_SESSION_ACTIVE) || (session_status() === PHP_SESSION_NONE)) ? FALSE : TRUE;
		}
		else
		{
			$session_enabled= (session_id() === '') ? FALSE : TRUE;
		}			
		if (!$session_enabled){
			session_start();
		}
		
		/* if (session_id() == '' || !isset($_SESSION)){
			session_start();
		} */
	}
	
	// date format 29/04/20
	public function parseAttributesFromTag($tag){
		$pattern = '/(\w+)=[\'"]([^\'"]*)/';

		preg_match_all($pattern,$tag,$matches,PREG_SET_ORDER);

		$result = array();
		foreach($matches as $match){
			$attrName = $match[1];
			$attrValue = is_numeric($match[2])? (int)$match[2]: trim($match[2]);

			$result[$attrName] = $attrValue;
		}
		return $result;
	}

	public static function initialize_transaction($form,$sessionValue = true)
	{
		wcsmsnUtility::checkSession();
		$reflect = new ReflectionClass('FormSessionVars');
		foreach ($reflect->getConstants()  as $key => $value)
			unset($_SESSION[$value]);
		$_SESSION[$form] = $sessionValue;
	}

	public static function _get_invalid_otp_method()
	{
		return wcsmsnMessages::showMessage('INVALID_OTP');
	}
	
	public static function get_otp_length(){
		$otp_template = wcsmsn_get_option( 'sms_otp_send', 'wcsmsn_message', '');
		
		if (strpos($otp_template, 'length') !== false) {
			$position 	= strpos($otp_template,"length");
			$otp_length = substr($otp_template,$position+8,1);
			return $otp_length;
		}		
		return 4;
	}
}
