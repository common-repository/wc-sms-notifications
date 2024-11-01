<?php
if (! defined( 'ABSPATH' )) exit;
	class PieRegistrationForm extends FormInterface
	{

		private $formSessionVar = FormSessionVars::PIE_REG;
		private $phoneFormID;
		private $phoneFieldKey;

		function handleForm()
		{
			$this->phoneFieldKey = 'billing_phone';
			$this->phoneFormID = $this->getPhoneFieldKey();
			add_action( 'pie_register_after_register_validate', array($this,'wcsmsn_pie_user_registration'),99,0);
		}

		public static function isFormEnabled()
		{
			return (wcsmsn_get_option('buyer_signup_otp', 'wcsmsn_general')=="on") ? true : false;
		}

		

		function wcsmsn_pie_user_registration()
		{
			wcsmsnUtility::checkSession();
			if(!array_key_exists($this->formSessionVar,$_SESSION))
			{
				$phone_field = $this->getPhoneFieldKey();
				$phone = !wcsmsnUtility::isBlank($phone_field) ? $_POST[$phone_field] : NULL;
				$this->startTheOTPVerificationProcess($_POST['username'],$_POST['e_mail'],$phone);
			}
			elseif(strcasecmp($_SESSION[$this->formSessionVar],'validated')==0)
				$_SESSION[$this->formSessionVar] = 'validationChecked';
			elseif(strcasecmp($_SESSION[$this->formSessionVar],'validationChecked')==0)
				$this->unsetOTPSessionVariables();
		}

		function startTheOTPVerificationProcess($username,$useremail,$phone)
		{
			wcsmsnUtility::initialize_transaction($this->formSessionVar);
			$errors = new WP_Error();
			wcsmsn_site_challenge_otp( $username,$useremail,$errors,$phone,"phone");
		}

		function getPhoneFieldKey()
		{
			$fields = unserialize(get_option('pie_fields'));
			$keys = (is_array($fields)) ? array_keys($fields) : array();
			foreach($keys as $key)
			{
				if(strcasecmp(trim($fields[$key]['label']),$this->phoneFieldKey)==0)
					return str_replace("-","_",sanitize_title($fields[$key]['type']."_"
						.(isset($fields[$key]['id']) ? $fields[$key]['id'] : "")));
			}
		}

		function handle_failed_verification($user_login,$user_email,$phone_number)
		{
			wcsmsnUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			wcsmsn_site_otp_validation_form($user_login,$user_email,$phone_number,wcsmsnUtility::_get_invalid_otp_method(),"phone",FALSE);
		}

		function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
		{
			wcsmsnUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			$_SESSION[$this->formSessionVar]="validated";
		}

		public function unsetOTPSessionVariables()
		{
			unset($_SESSION[$this->txSessionId]);
			unset($_SESSION[$this->formSessionVar]);
		}

		public function is_ajax_form_in_play($isAjax)
		{
			wcsmsnUtility::checkSession();
			return isset($_SESSION[$this->formSessionVar]) ? FALSE : $isAjax;
		}

		public function getPhoneNumberSelector($selector)	
		{
			wcsmsnUtility::checkSession();
			if(self::isFormEnabled()) array_push($selector, $this->phoneFormID); 
			return $selector;
		}

		function handleFormOptions()
		{
		}	
	}
	new PieRegistrationForm;