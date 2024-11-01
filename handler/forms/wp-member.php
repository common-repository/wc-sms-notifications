<?php
if (! defined( 'ABSPATH' )) exit;
	class WpMemberForm extends FormInterface
	{	
		private $formSessionVar = FormSessionVars::WPMEMBER_REG;
		private $formPhoneVer   = FormSessionVars::WPM_PHONE_VER;
		private $phoneFieldKey  = 'phone1';
		private $phoneFormID 	= 'input[name=phone1]';
		
		function handleForm()
		{
			add_filter('wpmem_register_form_rows', array($this,'wpmember_add_button'),99,2);
			add_action('wpmem_pre_register_data', array($this,'validate_wpmember_submit'),99,1);
			add_filter( 'wpmem_admin_tabs',array($this,'wpmem_add_wcsmsn_tab'),99,1);
			add_action( 'wpmem_admin_do_tab',array($this,'wpmem_wcsmsn_panel'),999,1);
			$this->routeData();
		}
		
		function wpmem_add_wcsmsn_tab( $tabs ) {
			return array_merge( $tabs, array( 'wcsmsn' => __( 'wcsmsn', 'wp-members' ) ) );
		}
		
		function wpmem_wcsmsn_panel()
		{
			echo '<div id="wcsmsn-wpmem-panel" >
			<h3>OTP FOR WPMember FORM</h3>
	<fieldset>
		<legend>Please follow the below steps to enable OTP for WP Member Registration Form:</legend>
		
					<ol >
						<li>
							Enable phone field with meta key <strong>phone1</strong> for your form and keep it required.
						</li>
						<li>
							Create a new text field for Verification Code with meta key <strong>wcsmsn_customer_validation_otp_token</strong>.
						</li>
					</ol>
					
			</fieldset>
			
			<hr/>
			</div>
			';
		}
		
		
		function routeData()
		{
			if(!array_key_exists('option', $_REQUEST)) return;
			switch (trim($_REQUEST['option'])) 
			{
				case "wcsmsn-wpmember-form":
					$this->_handle_wp_member_form($_POST);		break;
			}
		}

		public static function isFormEnabled()                                
		{
			return (wcsmsn_get_option('buyer_signup_otp', 'wcsmsn_general')=="on") ? true : false;
		}

		function _handle_wp_member_form($data)
		{		
			wcsmsnUtility::checkSession();
			wcsmsnUtility::initialize_transaction($this->formSessionVar);

			$this->processPhoneAndStartOTPVerificationProcess($data);
			$this->sendErrorMessageIfOTPVerificationNotStarted();
		}

		function processPhoneAndStartOTPVerificationProcess($data)
		{
			if(!array_key_exists('user_phone', $data) || !isset($data['user_phone'])) return;

			$_SESSION[$this->formPhoneVer] = $data['user_phone'];
			wcsmsn_site_challenge_otp(null,'',null,$data['user_phone'],"phone",null,null,false);
		}

		

		function sendErrorMessageIfOTPVerificationNotStarted()
		{
			wp_send_json( wcsmsnUtility::_create_json_response( wcsmsnMessages::showMessage('ENTER_PHONE_CODE'),wcsmsnConstants::ERROR_JSON_TYPE) );
		}

		function wpmember_add_button($rows, $tag)
		{
			foreach($rows as $key=>$field)
			{
				if($key=="phone1")
				{
					$rows[$key]['field'] .= $this->_add_shortcode_to_wpmember("phone",$field['meta']);
					break;
				}			
			}
			return $rows;
		}

		function validate_wpmember_submit($fields)
		{
			global $wpmem_themsg; 
			wcsmsnUtility::checkSession();
			
			if(!$this->validate_submitted($fields)) return;

			do_action('wcsmsn_validate_otp',NULL,$fields['wcsmsn_customer_validation_otp_token']);
		}

		function validate_submitted($fields)
		{
			global $wpmem_themsg;
			wcsmsnUtility::checkSession();
			if(array_key_exists($this->formPhoneVer, $_SESSION) && strcasecmp($_SESSION[$this->formPhoneVer], $fields[$this->phoneFieldKey])!=0)
			{	
				$wpmem_themsg =  wcsmsnMessages::showMessage('INVALID_OTP');
				return false;
			}
			else
				return true;
		}

		function _add_shortcode_to_wpmember($mo_type,$field) 
		{
			$field_content  = "<div style='margin-top: 2%;'><button type='button' class='button alt' style='width:100%;";
			$field_content .= "font-family: Roboto;font-size: 12px !important;' id='wcsmsn_otp_token_submit' ";
			$field_content .= "title='Please Enter an '".$mo_type."'to enable this.'>Click Here to Verify ". $mo_type."</button></div>";
			$field_content .= "<div style='margin-top:2%'><div id='mo_message' hidden='' style='background-color: #f7f6f7;padding: ";
			$field_content .= "1em 2em 1em 3.5em;'></div></div>";
			$field_content .= '<script>jQuery(document).ready(function(){$mo=jQuery;$mo("#wcsmsn_otp_token_submit").click(function(o){ ';
			$field_content .= 'var e=$mo("input[name='.$field.']").val(); $mo("#mo_message").empty(),$mo("#mo_message").append("Sending OTP..."),';
			$field_content .= '$mo("#mo_message").show(),$mo.ajax({url:"'.site_url().'/?option=wcsmsn-wpmember-form",type:"POST",';
			$field_content .= 'data:{user_'.$mo_type.':e},crossDomain:!0,dataType:"json",success:function(o){ ';
			$field_content .= 'if(o.result=="success"){$mo("#mo_message").empty(),$mo("#mo_message").append(o.message),';
			$field_content .= '$mo("#mo_message").css("border-top","3px solid green"),$mo("input[name=email_verify]").focus()}else{';
			$field_content .= '$mo("#mo_message").empty(),$mo("#mo_message").append(o.message),$mo("#mo_message").css("border-top","3px solid red")';
			$field_content .= ',$mo("input[name=phone_verify]").focus()} ;},error:function(o,e,n){}})});});</script>';

			return $field_content;
		}

		function handle_failed_verification($user_login,$user_email,$phone_number)
		{
			global $wpmem_themsg; 
			wcsmsnUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			$wpmem_themsg =  wcsmsnUtility::_get_invalid_otp_method();
		}

		function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
		{
			wcsmsnUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			$this->unsetOTPSessionVariables();
		}

		public function unsetOTPSessionVariables()
		{
			unset($_SESSION[$this->formSessionVar]);
			unset($_SESSION[$this->formPhoneVer]);
		}

		public function is_ajax_form_in_play($isAjax)
		{
			wcsmsnUtility::checkSession();
			return isset($_SESSION[$this->formSessionVar]) ? TRUE : $isAjax;
		}

		public function getPhoneNumberSelector($selector)	
		{
			wcsmsnUtility::checkSession();
			array_push($selector, $this->phoneFormID); 
			return $selector;
		}

		function handleFormOptions()
		{
			
		}
	}
	new WpMemberForm;

