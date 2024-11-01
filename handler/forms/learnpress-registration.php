<?php
if (! defined( 'ABSPATH' )) exit;
	class LearnpressRegistrationForm extends FormInterface
	{
		private $formSessionVar = FormSessionVars::LEARNPRESS_DEFAULT_REG;
		
		function handleForm()
		{
			add_filter('learn-press/new-user-data', array($this,'learnpress_site_registration_errors'),8,1);
			add_filter('learn-press/register-fields', array($this,'wcsmsn_learnpress_add_phone_field') );
		}
		
		public static function isFormEnabled()
		{
			return (wcsmsn_get_option('buyer_signup_otp', 'wcsmsn_general')=="on") ? true : false;
		}
		
		//this function created for updating and create a hook created on 29-01-2019
		public function wc_user_created($user_id)
		{
			$post_data = wp_unslash( $_POST );
			
			if(array_key_exists('billing_phone', $post_data))
			{
				$billing_phone = $post_data['billing_phone'];
				update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $billing_phone ) );
				do_action('wcsmsn_after_update_new_user_phone',$user_id,$billing_phone);
			}
		}
		
		function show_error_msg($error_hook=NULL,$err_msg=NULL,$type=NULL)
		{
			if(isset($_SESSION[$this->formSessionVar2]))
			{
				wp_send_json( wcsmsnUtility::_create_json_response($err_msg,$type));
			}
			else
			{
				return new WP_Error( $error_hook,$err_msg);
			}
		}
		
		function learnpress_site_registration_errors($datas=NULL,$username=NULL,$password=NULL,$email=NULL)
		{	
			wcsmsnUtility::checkSession();
			$errors=array();
			if(isset($_SESSION['wcsmsn_lpress_mobile_verified']))
			{
				add_action('user_register', array( $this, 'wc_user_created' ), 10, 1 );
				unset($_SESSION['wcsmsn_lpress_mobile_verified']);
				return $datas;
			}
			
			if(!empty($datas))
			{	
				$username = $datas['user_login'];
				$email = $datas['user_email'];
				$password = $datas['user_pass'];
				wcsmsnUtility::initialize_transaction($this->formSessionVar);
			}
			
			
			/* if(wcsmsn_get_option('allow_multiple_user', 'wcsmsn_general')!="on") {
				if( sizeof(get_users(array('meta_key' => 'billing_phone', 'meta_value' => $_POST['billing_phone']))) > 0 ) {
					if(isset($_SESSION[$this->formSessionVar2]))
					{
						$this->show_error_msg(NULL,__('An account is already registered with this mobile number. Please login.', 'error' ));
					}
					else
					{
						return $this->show_error_msg('registration-error-number-exists',__( 'An account is already registered with this mobile number. Please login.', 'woocommerce' ));
					}
				}
			} */
			
			
				if ( isset($_POST['billing_phone']) && wcsmsnUtility::isBlank( $_POST['billing_phone']) )
				{
					/* if(isset($_SESSION[$this->formSessionVar2]))
					{
						$this->show_error_msg(NULL,__('Please enter phone number.', 'error' ));
					}
					else
					{
						return $this->show_error_msg('registration-error-invalid-phone',__( 'Please enter phone number.', 'woocommerce' ));
					} */
				}
			
			//do_action( 'woocommerce_register_post', $username, $email, $errors );
			/* if($errors->get_error_code())
				throw new Exception( $errors->get_error_message() ); */
			
			
			//process and start the OTP verification process
			return $this->processFormFields($username,$email,$errors,$password); 	
		}

		function processFormFields($username,$email,$errors,$password)
		{
			global $phoneLogic;
						
			if ( !isset( $_POST['billing_phone'] ) || !wcsmsnUtility::validatePhoneNumber($_POST['billing_phone']))
				return new WP_Error( 'billing_phone_error', str_replace("##phone##",wcsmsncURLOTP::checkPhoneNos($_POST['billing_phone']),$phoneLogic->_get_otp_invalid_format_message()) );
			wcsmsn_site_challenge_otp($username,$email,$errors,$_POST['billing_phone'],"phone",$password);
		}
		
		function wcsmsn_learnpress_add_phone_field($fields)
		{
			$add_fields = array(
				'billing_phone' => array(
					'title'       => __( 'Billing Phone', 'learnpress' ),
					'type'        => 'text',
					'placeholder' => __( 'Billing Phone', 'learnpress' ),
					'id'          => 'billing_phone',
					'required'    => true
			));
			$fields = array_merge($fields,$add_fields);
			return $fields;
		}

		function handle_failed_verification($user_login,$user_email,$phone_number)
		{
			wcsmsnUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			if(isset($_SESSION[$this->formSessionVar]))
				wcsmsn_site_otp_validation_form($user_login,$user_email,$phone_number,wcsmsnUtility::_get_invalid_otp_method(),"phone",FALSE);
		}

		function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
		{
			wcsmsnUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			$_SESSION['wcsmsn_lpress_mobile_verified'] = true;
		}
		
		public function unsetOTPSessionVariables()
		{
			unset($_SESSION[$this->txSessionId]);
			unset($_SESSION[$this->formSessionVar]);
		}

		public function is_ajax_form_in_play($isAjax)
		{
			wcsmsnUtility::checkSession();
			return $isAjax;
		}

		function handleFormOptions()
		{
		}
	}
	new LearnpressRegistrationForm;