<?php
if (! defined( 'ABSPATH' )) exit;
	class UserRegistrationForm extends FormInterface
	{  
        public static $response_array = array();
	
		private $formSessionVar = FormSessionVars::UR_DEFAULT_REG;
		private $phoneFormID 	= "input[name^='billing_phone']";
		
		function handleForm()
		{
			add_action( 'user_registration_before_register_user_action', array( $this, 'wcsmsn_um_registration_validation' ), 9, 3 );
			
			add_action( 'user_registration_after_register_user_action', array( $this, 'wcsmsn_um_registration_complete' ), 9, 3 );
			
			add_action( 'user_registration_after_form_fields', array( $this, 'my_predefined_fields' ), 9, 3 );
			
		    add_action( 'user_registration_after_form_buttons',	array($this,'wcsmsn_ur_handle_js_script') );
			$this->routeData();
		}
		
	function wcsmsn_ur_handle_js_script()
	{	
		if(wcsmsn_get_option('buyer_signup_otp', 'wcsmsn_general')=="on")
		{
			$otp_resend_timer = wcsmsn_get_option( 'otp_resend_timer', 'wcsmsn_general', '15');
				
			echo '<style>.sa-ur-hide{display:none !important}</style><script>
	        jQuery(".ur-submit-button").addClass("sa-ur-hide");
			jQuery(document).on("click", ".sa-otp-btn-init",function(){
				jQuery(this).parents(".wcsmsnModal").hide();
				var e = jQuery(this).parents("form").find("#billing_phone").val();
				var data = {user_phone:e};
				var action_url = "'.site_url().'/?option=wcsmsn-ur-ajax-verify";
				saInitOTPProcess(this,action_url, data,'.$otp_resend_timer.');
			});
								
				 
			jQuery(document).on("click", ".user-registration .wcsmsn_otp_validate_submit",function(){
				var current_form = jQuery(this).parents("form");
				var action_url = "'.site_url().'/?option=wcsmsn-validate-otp-form";
				var data = current_form.serialize()+"&otp_type=phone&from_both=";
				wcsmsn_validateOTP(this,action_url,data,function(){
                 current_form.find(".sa-ur-hide						").trigger("click")
				});
				return false;		   
			});
			</script>';
			
		}
	 }
		
	function routeData()
	{
		if(!array_key_exists('option', $_GET)) return;
		switch (trim($_GET['option']))
		{
			case "wcsmsn-ur-ajax-verify":
				$this->_send_otp_ur_ajax_verify($_POST);
				exit();
				break;
		}
	}
	
	function _send_otp_ur_ajax_verify($getdata)
	{
		wcsmsnUtility::checkSession();
		wcsmsnUtility::initialize_transaction($this->formSessionVar);

		if(array_key_exists('user_phone', $getdata) && !wcsmsnUtility::isBlank($getdata['user_phone']))
		{
			$_SESSION[$this->formSessionVar] = trim($getdata['user_phone']);
			$message = str_replace("##phone##",$getdata['user_phone'],wcsmsnMessages::showMessage('OTP_SENT_PHONE'));
			wcsmsn_site_challenge_otp('test',null,null,trim($getdata['user_phone']),"phone",null,null,true);
		}
		else
		{
			wp_send_json_error(
				array(
					'message' => __( 'Enter a number in the following format : 9xxxxxxxxx', 'user-registration' ),
				)
			);
		}
	}
		
		public static function my_predefined_fields(  $args, $form_id  ) 
		{
			if(wcsmsn_get_option('buyer_signup_otp', 'wcsmsn_general')=="on")
		    {
			  $otp_template_style =  wcsmsn_get_option( 'otp_template_style', 'wcsmsn_general', 'otp-popup-1.php');
			
			   $template = get_wcsmsn_template('template/'.$otp_template_style,$params=array());
			   echo '<input class="sa-otp-btn-init ninja-forms-field nf-element wcsmsn_otp_btn_submit" value="Verify & Submit" type="button">'.$template;
			}
		}
		
		function wcsmsn_um_registration_validation( $args, $form_id ) 
		{
			if(wcsmsn_get_option('allow_multiple_user', 'wcsmsn_general')!="on" && !wcsmsnUtility::isBlank( $args['billing_phone']->value ) ) {
					
				if(sizeof(get_users(array('meta_key' => 'billing_phone', 'meta_value' => $args['billing_phone']->value ))) > 0 ) 
				{
					wp_send_json_error(
						array(
							'message' => __( 'An account is already registered with this mobile number. Please login.', 'user-registration' ),
						)
					);
				}
			}
		}
		
		function wcsmsn_um_registration_complete( $args, $form_id, $user_id ) {
			$user_phone = (array_key_exists('billing_phone',$args)) ? $args['billing_phone']->value : '';
			do_action('wcsmsn_after_update_new_user_phone', $user_id, $user_phone);
		}

		public static function isFormEnabled() 
		{
			return (wcsmsn_get_option('buyer_signup_otp', 'wcsmsn_general')=="on") ? true : false;
		}


	function handle_failed_verification($user_login,$user_email,$phone_number)
	{
		wcsmsnUtility::checkSession();
		if(!isset($_SESSION[$this->formSessionVar])) return;
		if(!empty($_REQUEST['option']) && $_REQUEST['option']=='wcsmsn-validate-otp-form')
		{
			wp_send_json( wcsmsnUtility::_create_json_response(wcsmsnMessages::showMessage('INVALID_OTP'),'error'));
			exit();
		}
		else
		{
			$_SESSION[$this->formSessionVar] = 'verification_failed';
		}
	}

	function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
	{
		wcsmsnUtility::checkSession();
		if(!isset($_SESSION[$this->formSessionVar])) return;
		if(!empty($_REQUEST['option']) && $_REQUEST['option']=='wcsmsn-validate-otp-form')
		{
			wp_send_json( wcsmsnUtility::_create_json_response("OTP Validated Successfully.",'success'));
			exit();
		}
		else
		{
			$_SESSION[$this->formSessionVar] = 'validated';
		}
	}

		public function unsetOTPSessionVariables()
		{
			unset($_SESSION[$this->txSessionId]);
			unset($_SESSION[$this->formSessionVar]);
		}

		public function is_ajax_form_in_play($isAjax)
		{
			wcsmsnUtility::checkSession();
			return isset($_SESSION[$this->formSessionVar]) ? true : $isAjax;
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
	new UserRegistrationForm;