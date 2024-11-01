<?php 
if (! defined( 'ABSPATH' )) exit;
class PhoneLogic extends LogicInterface
{
	public function _handle_logic($user_login,$user_email,$phone_number,$otp_type,$form)
	{
		$match = preg_match(wcsmsnConstants::getPhonePattern(),$phone_number);
		switch ($match) 
		{
			case 0:
				$this->_handle_not_matched($phone_number,$otp_type,$form);						break;
			case 1:
				$this->_handle_matched($user_login,$user_email,$phone_number,$otp_type,$form);	break;
		}
	}

	public function _handle_matched($user_login,$user_email,$phone_number,$otp_type,$form)
	{
		$content = (array)json_decode(wcsmsncURLOTP::wcsmsn_send_otp_token($form, '', $phone_number), true);
		//$content = array_key_exists('status',$content) ? $content['status'] : '';//commented 17-07-2019
		$status = array_key_exists('status',$content) ? $content['status'] : '';//added 17-07-2019
		//switch ($content) //commented 17-07-2019
		switch ($status) 
		{
			case 'success':
				$this->_handle_otp_sent($user_login,$user_email,$phone_number,$otp_type,$form,$content); 		break;
			default:
				$this->_handle_otp_sent_failed($user_login,$user_email,$phone_number,$otp_type,$form,$content);break;
		}
	}

	public function _handle_not_matched($phone_number,$otp_type,$form)
	{
		wcsmsnUtility::checkSession();
		$message = str_replace("##phone##",wcsmsncURLOTP::checkPhoneNos($phone_number),self::_get_otp_invalid_format_message());
		if(self::_is_ajax_form())
			wp_send_json(wcsmsnUtility::_create_json_response($message,wcsmsnConstants::ERROR_JSON_TYPE));
		else
			wcsmsn_site_otp_validation_form(null,null,null,$message,$otp_type,$form);
	}

	public function _handle_otp_sent_failed($user_login,$user_email,$phone_number,$otp_type,$form,$content)
	{
		wcsmsnUtility::checkSession();
		if(isset($content['description']['desc']))
			$message =$content['description']['desc'];//added 17-07-2019
		else
			$message = str_replace("##phone##",wcsmsncURLOTP::checkPhoneNos($phone_number),self::_get_otp_sent_failed_message());
		
		if(self::_is_ajax_form())
			wp_send_json(wcsmsnUtility::_create_json_response($message,wcsmsnConstants::ERROR_JSON_TYPE));
		else
			wcsmsn_site_otp_validation_form(null,null,null,$message,$otp_type,$form);
	}

	public function _handle_otp_sent($user_login,$user_email,$phone_number,$otp_type,$form,$content)
	{
		wcsmsnUtility::checkSession();
		
		$message = str_replace("##phone##",wcsmsncURLOTP::checkPhoneNos($phone_number),self::_get_otp_sent_message());
		if(self::_is_ajax_form())
			wp_send_json(wcsmsnUtility::_create_json_response($message,wcsmsnConstants::SUCCESS_JSON_TYPE));
		else
			wcsmsn_site_otp_validation_form($user_login, $user_email,$phone_number,$message,$otp_type,$form);
	}
	
	public function _get_otp_sent_message()
	{
		return wcsmsnMessages::showMessage('OTP_SENT_PHONE');
	}

	public function _get_otp_sent_failed_message()
	{
		return __("There was an error in sending the OTP to the given Phone Number. Please Try Again or contact site Admin.",wcsmsnConstants::TEXT_DOMAIN);
	}

	public function _get_otp_invalid_format_message()
	{
		return sprintf(__('%sphone%s is not a valid phone number. Please enter a valid Phone Number',wcsmsnConstants::TEXT_DOMAIN), '##', '##');
	}
}