<?php
if (! defined( 'ABSPATH' )) exit;
	class WPResetPasswordForm extends FormInterface
	{
		private $formSessionVar = FormSessionVars::WP_DEFAULT_LOST_PWD;
		private $phoneNumberKey;

		function handleForm()
		{	
			$this->phoneNumberKey = 'billing_phone';
			add_action( 'retrieve_password', array($this,'startwcsmsnResetPasswordProcess'), 10, 1 );
			$this->routeData();
		}
		
		function routeData()
		{
			if (!empty($_REQUEST['option']) && $_REQUEST['option']=="wcsmsn-change-password-form") 
			{
				$this->_handle_wcsmsn_changed_pwd($_POST);
			} 
		}
				
		public static function isFormEnabled() 
		{
			return (wcsmsn_get_option('reset_password', 'wcsmsn_general')=="on") ? true : false;
		}
		
		function _handle_wcsmsn_changed_pwd($post_data)
		{
			wcsmsnUtility::checkSession();
			$error='';
			$new_password = !empty($post_data['wcsmsn_user_newpwd']) ? $post_data['wcsmsn_user_newpwd'] : '' ;
			$confirm_password = !empty($post_data['wcsmsn_user_cnfpwd']) ? $post_data['wcsmsn_user_cnfpwd'] : '';
			
			if ($new_password=='') {
				$error = 'Please enter your password.';
			}
			if ($new_password !== $confirm_password ){
				$error ='Passwords do not match.';
			}
			if(!empty($error))
			{
				wcsmsnAskForResetPassword($_SESSION['user_login'],$_SESSION['phone_number_mo'], $error, 'phone',false);
				
			}
			$user = get_user_by( 'login', $_SESSION['user_login'] );
			reset_password( $user, $new_password );
			$this->unsetOTPSessionVariables();
			wp_redirect( add_query_arg( 'password-reset', 'true', wc_get_page_permalink( 'myaccount' ) ) );
			exit;
		}
		
		function startwcsmsnResetPasswordProcess($user_login)
		{
			wcsmsnUtility::checkSession();	
			$user = get_user_by( 'login', $user_login );
			$phone_number = get_user_meta($user->data->ID, $this->phoneNumberKey,true);
			if(isset($_REQUEST['wc_reset_password']))
			{
				wcsmsnUtility::initialize_transaction($this->formSessionVar);
				if($phone_number!='')
				{
					$this->fetchPhoneAndStartVerification($user->data->user_login,$this->phoneNumberKey,NULL,NULL,$phone_number);
				}
			}
			return $user;
		} 

		function fetchPhoneAndStartVerification($user,$key,$username,$password,$phone_number)
		{
			if((array_key_exists($this->formSessionVar,$_SESSION) && strcasecmp($_SESSION[$this->formSessionVar],'validated')==0)) return;
			wcsmsn_site_challenge_otp($user,$username,null,$phone_number,"phone",$password,wcsmsnUtility::currentPageUrl(),false);
		}

		function handle_failed_verification($user_login,$user_email,$phone_number)
		{
			wcsmsnUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;

			if(isset($_SESSION[$this->formSessionVar])){	
				$_SESSION[$this->formSessionVar] = 'verification_failed';
				//wp_send_json( wcsmsnUtility::_create_json_response(wcsmsnMessages::INVALID_OTP,'error'));
				wcsmsn_site_otp_validation_form($user_login,$user_email,$phone_number,wcsmsnMessages::showMessage('INVALID_OTP'),"phone",FALSE);
			}
		}

		function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
		{
			wcsmsnUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			wcsmsnAskForResetPassword($_SESSION['user_login'],$_SESSION['phone_number_mo'], "Please change Your password", 'phone',false);
		}

		public function unsetOTPSessionVariables()
		{
			unset($_SESSION[$this->formSessionVar]);
		}

		public function is_ajax_form_in_play($isAjax)
		{
			wcsmsnUtility::checkSession();
			return isset($_SESSION[$this->formSessionVar]) ? FALSE : $isAjax;
		}

		function handleFormOptions()
	    {
			
	    }
	}
	new WPResetPasswordForm;