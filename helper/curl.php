<?php
if (! defined( 'ABSPATH' )) exit;
class wcsmsncURLOTP
{	
	public static function sendtemplatemismatchemail($template)
	{
		$username = wcsmsn_get_option( 'wcsmsn_name', 'wcsmsn_gateway', '');
		$To_mail=wcsmsn_get_option( 'alert_email', 'wcsmsn_general', '');
		
		//Email template with content
		$params = array(
                'template' => nl2br($template),
                'username' => $username,
                'server_name' => $_SERVER['SERVER_NAME'],
                'admin_url' => admin_url(),
        );
		$emailcontent = get_wcsmsn_template('template/emails/mismatch_template.php',$params);
		wp_mail( $To_mail, '❗ ✱ WC SMS Notifications ✱ Template Mismatch', $emailcontent,'content-type:text/html');
	}
	
	public static function checkPhoneNos($nos=NULL,$force_prefix=true)
	{
		$country_code = wcsmsn_get_option( 'default_country_code', 'wcsmsn_general' );
		
		$nos = explode(',',$nos);
		$valid_no=array();
		if(is_array($nos))
		{			
			foreach($nos as $no){
				$no = ltrim(ltrim($no, '+'),'0'); //remove leading + and 0
				if(!$force_prefix)
				{
					$no = (substr($no,0,strlen($country_code))==$country_code) ? substr($no,strlen($country_code)) : $no;
				}
				else
				{
					$no = (substr($no,0,strlen($country_code))!=$country_code) ? $country_code.$no : $no;
				}
				
				
				
				
				
				$match = preg_match(wcsmsnConstants::getPhonePattern(),$no);
				if($match)
				{
					$valid_no[] = $no;
				}
			}
		}
		
		if(sizeof($valid_no)>0)
		{
			return implode(',',$valid_no);
		}
		else
		{
			return false;
		}
	}

	public static function sendsms($sms_data) 
	{
        $response = false;
        $username = wcsmsn_get_option( 'wcsmsn_name', 'wcsmsn_gateway' );
        $password = wcsmsn_get_option( 'wcsmsn_password', 'wcsmsn_gateway' );
        $senderid = wcsmsn_get_option( 'wcsmsn_api', 'wcsmsn_gateway' );
		$enable_short_url = wcsmsn_get_option( 'enable_short_url', 'wcsmsn_general');
		
        $phone = self::checkPhoneNos($sms_data['number']);
		if($phone===false)
		{
			$data=array();
			$data['status']= "error";
			$data['description']= "phone number not valid";
			return json_encode($data);
		}
        $text = htmlspecialchars_decode($sms_data['sms_body']);
        //bail out if nothing provided
        if ( empty( $password ) || empty( $text ) ) {
            return $response;
        }

		$url = base64_decode("aHR0cHM6Ly9mcmVlYnVsa3Ntc29ubGluZS5jb20vYXBpL3YxLw==");
		$fields = array('user'=>$username, 'token'=>$password, 'number'=>$phone, 'sender'=>$senderid, 'message'=>$text);
		
		if($enable_short_url=='on'){$fields['shortenurl']=1;}
		$json 			= json_encode($fields);		
		$fields 		= apply_filters('wcsmsn_before_send_sms', $fields);
		$response 		= self::callAPI($url, $fields, null);
		$response_arr	= json_decode($response,true);
		
		apply_filters('wcsmsn_after_send_sms', $response_arr);
		
		if($response_arr['status']=='error') {
			$error = (is_array($response_arr['description'])) ? $response_arr['description']['desc'] : $response_arr['description'];
			if($error == "Invalid Template Match")
			{
				self::sendtemplatemismatchemail($text);
			}
		}
        return $response;
    }
	
	public static function wcsmsn_send_otp_token($form, $email='', $phone='')
	{
		$phone = self::checkPhoneNos($phone);
		$cookie_value = get_wcsmsn_cookie($phone);
		$max_otp_resend_allowed = wcsmsn_get_option( 'max_otp_resend_allowed', 'wcsmsn_general');
		if(get_wcsmsn_cookie($phone)>$max_otp_resend_allowed)
		{
			$data=array();
			$data['status']= "error";
			$data['description']['desc']= wcsmsnMessages::showMessage('MAX_OTP_LIMIT');
			return json_encode($data);
		}
		
		
		
		$response = false;
		$username = wcsmsn_get_option( 'wcsmsn_name', 'wcsmsn_gateway' );
        $password = wcsmsn_get_option( 'wcsmsn_password', 'wcsmsn_gateway' );
        $senderid = wcsmsn_get_option( 'wcsmsn_api', 'wcsmsn_gateway' );
		$template = wcsmsn_get_option( 'sms_otp_send', 'wcsmsn_message', wcsmsnMessages::showMessage('DEFAULT_BUYER_OTP'));
		if($phone===false)
		{
			$data=array();
			$data['status']= "error";
			$data['description']['desc']= "phone number not valid";
			return json_encode($data);
		}
		
		
        if ( empty( $password ) ) {
            return $response;
        }
		
		$url = base64_decode("aHR0cHM6Ly9mcmVlYnVsa3Ntc29ubGluZS5jb20vYXBpL3YxL3ZlcmlmeS8=");

		$fields = array('user'=>$username, 'token'=>$password, 'number'=>$phone, 'sender'=>$senderid, 'template'=>$template);
		$json = json_encode($fields);
		$response = self::callAPI($url, $fields, null);	
		$response_arr = (array)json_decode($response,true);
		if(array_key_exists('status',$response_arr) && $response_arr['status']=='error') {
			$error = (is_array($response_arr['description'])) ? $response_arr['description']['desc'] : $response_arr['description'];
			if($error == "Invalid Template Match")
			{
				self::sendtemplatemismatchemail($template);
			}
		}
		else
		{
			create_wcsmsn_cookie($phone,$cookie_value+1);
		}
		
		return $response;
	}
	
	public static function validate_otp_token($mobileno,$otpToken)
	{
        $response = false;
		$username = wcsmsn_get_option( 'wcsmsn_name', 'wcsmsn_gateway' );
        $password = wcsmsn_get_option( 'wcsmsn_password', 'wcsmsn_gateway' );
        $senderid = wcsmsn_get_option( 'wcsmsn_api', 'wcsmsn_gateway' );
		$mobileno = self::checkPhoneNos($mobileno);
		if($mobileno===false)
		{
			$data=array();
			$data['status']= "error";
			$data['description']= "phone number not valid";
			return json_encode($data);
		}
		
        if ( empty( $password ) ) {
            return $response;
        }
		$url = base64_decode("aHR0cHM6Ly9mcmVlYnVsa3Ntc29ubGluZS5jb20vYXBpL3YxL3ZlcmlmeS8=");

		$fields = array('user'=>$username, 'token'=>$password, 'number'=>$mobileno, 'code'=>$otpToken);
		
		$response    = self::callAPI($url, $fields, null);
		$content = json_decode($response,true);
		if(isset($content['description']['desc']) && strcasecmp($content['description']['desc'], 'Code Matched successfully.') == 0) {
			clear_wcsmsn_cookie($mobileno);
		}
		
		
		return $response;
	}
	
	public static function get_senderids( $username=NULL, $password = NULL)
    {
	   if ( empty( $password ) ) {
			return '';
       }
               
       $url = base64_decode("aHR0cHM6Ly9mcmVlYnVsa3Ntc29ubGluZS5jb20vYXBpL3YxL3NlbmRlcmxpc3QucGhw");

		$fields = array('user'=>$username, 'token'=>$password);

		$response = self::callAPI($url, $fields, null);
		return $response;
    }
	
	public static function get_templates( $username=NULL, $password = NULL)
    {
	   if ( empty( $username ) || empty( $password ) ) {
			return '';
       }
       $url = base64_decode("aHR0cHM6Ly9mcmVlYnVsa3Ntc29ubGluZS5jb20vYXBpL3YxL3RlbXBsYXRlbGlzdC5waHA=");

		$fields = array('user'=>$username, 'token'=>$password);

		$response = self::callAPI($url, $fields, null);
		return $response;
    }
	
	public static function get_credits()
    {
       $response = false;
	   $username = wcsmsn_get_option( 'wcsmsn_name', 'wcsmsn_gateway' );
       $password = wcsmsn_get_option( 'wcsmsn_password', 'wcsmsn_gateway' );
	   
	   if ( empty( $password ) ) {
			return $response;
       }
               
       $url = base64_decode("aHR0cHM6Ly9mcmVlYnVsa3Ntc29ubGluZS5jb20vYXBpL3YxL2NyZWRpdHN0YXR1cy5waHA=");

		$fields = array('user'=>$username, 'token'=>$password);
		$response    = self::callAPI($url, $fields, null);
		return $response;
	} 
	
	public static function group_list()
    {
       $username = wcsmsn_get_option( 'wcsmsn_name', 'wcsmsn_gateway' );
       $password = wcsmsn_get_option( 'wcsmsn_password', 'wcsmsn_gateway' );
	   
	   if ( empty( $username ) || empty( $password ) ) {
			return '';
       }
               
       $url = base64_decode("aHR0cHM6Ly9mcmVlYnVsa3Ntc29ubGluZS5jb20vYXBpL3YxLw==");

		$fields = array('user'=>$username, 'token'=>$password);

		$response    = self::callAPI($url, $fields, null);
		return $response;
    }

	public static function country_list()
    {
        $url = base64_decode("aHR0cHM6Ly9mcmVlYnVsa3Ntc29ubGluZS5jb20vYXBpL3YxL2NvdW50cnlsaXN0LnBocA==");
		$response    = self::callAPI($url, null, null);
		return $response;
    }	
		
	public static function creategrp()
    {
       $username = wcsmsn_get_option( 'wcsmsn_name', 'wcsmsn_gateway' );
       $password = wcsmsn_get_option( 'wcsmsn_password', 'wcsmsn_gateway' );
	   
	   if ( empty( $username ) || empty( $password ) ) {
			return '';
       }
               
       $url = base64_decode("aHR0cHM6Ly9mcmVlYnVsa3Ntc29ubGluZS5jb20vYXBpL3YxLw==");

		$fields = array('user'=>$username, 'token'=>$password, 'name'=>$_SERVER['SERVER_NAME']);

		$response    = self::callAPI($url, $fields, null);
		return $response;
    } 	
	
	public static function create_contact($sms_datas=null,$group_name,$extra_fields=array())
	{
	
		if(is_array($sms_datas) && sizeof($sms_datas) == 0)
			return false;
		
		$username 		= wcsmsn_get_option( 'wcsmsn_name', 'wcsmsn_gateway' );
        $password 		= wcsmsn_get_option( 'wcsmsn_password', 'wcsmsn_gateway' );
		
		//$group_name 	= wcsmsn_get_option( 'group_auto_sync', 'wcsmsn_general', '');
		$xmlstr = <<<XML
<?xml version='1.0' encoding='UTF-8'?>
<group>
</group>
XML;

		$msg = new SimpleXMLElement($xmlstr);
		$user = $msg->addChild('user');
		$user->addAttribute('username', $username);
		$user->addAttribute('password', $password);
		$user->addAttribute('grp_name', $group_name);
		$members = $msg->addChild('members');
		foreach($sms_datas as $sms_data)
		{
			$member = $members->addChild('member');
			$member->addAttribute('name', $sms_data['person_name']);
			$member->addAttribute('number', $sms_data['number']);
			
			if(!empty($extra_fields))
			{
				$memb = $member->addChild('meta-data');
				foreach($extra_fields as $key=>$value)
				{
				  $memb->addAttribute($key, $value);
				}
			}
			
		}	
		$xmldata = $msg->asXML();
		$url = base64_decode("aHR0cHM6Ly9mcmVlYnVsa3Ntc29ubGluZS5jb20vYXBpL3YxLw==");
		$fields = array('data'=>$xmldata);
		$response   = self::callAPI($url, $fields, null);
		return $response;
	}
	
	public  function send_sms_xml($sms_datas)
	{		
		if(is_array($sms_datas) && sizeof($sms_datas) == 0)
			return false;
		
		$username = wcsmsn_get_option( 'wcsmsn_name', 'wcsmsn_gateway' );
        $password = wcsmsn_get_option( 'wcsmsn_password', 'wcsmsn_gateway' );
        $senderid = wcsmsn_get_option( 'wcsmsn_api', 'wcsmsn_gateway' );
		
		$xmlstr = <<<XML
<?xml version='1.0' encoding='UTF-8'?>
<message>
</message>
XML;
		$msg = new SimpleXMLElement($xmlstr);
		$user = $msg->addChild('user');
		$user->addAttribute('username', $username);
		$user->addAttribute('password', $password);
		
		foreach($sms_datas as $sms_data){
			$phone = self::checkPhoneNos($sms_data['number']);
			if($phone!==false)
			{
				$sms = $msg->addChild('sms');
				$sms->addAttribute('text', $sms_data["sms_body"]);
				
				$address = $sms->addChild('address');
				$address->addAttribute('from', $senderid);
				$address->addAttribute('to', $phone);
			}	
		}

		if($msg->count() <= 1)
			return false;
		
		$xmldata = $msg->asXML();
		$url = base64_decode("aHR0cHM6Ly9mcmVlYnVsa3Ntc29ubGluZS5jb20vYXBpL3YxLw==");
			
		$fields 	= array('data'=>$xmldata);		
		$response   = self::callAPI($url, $fields, null);
		return $response;
	}
	
	public static function callAPI($url, $params, $headers = array("Content-Type: application/json"))
	{
		$extra_params 	= array('pgid'=>"WooCommerce ". $_SERVER['HTTP_REFERER'] ." ". $_SERVER['HTTP_USER_AGENT'], 'website'=>$_SERVER['SERVER_NAME']);
		$params 		= (!is_null($params)) ? array_merge($params, $extra_params) : $extra_params;			
		$args			= array('body'=>$params, 'timeout'=>15);
		$request 		= wp_remote_post($url,$args);
		
		if (is_wp_error($request))
		{
			$data					= array();
			$data['status'] 		= "error";
			$data['description']	= $request->get_error_message();
			return json_encode($data);
		}
		
		return wp_remote_retrieve_body( $request );
	}
}