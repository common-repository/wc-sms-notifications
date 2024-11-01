<?php
if (! defined( 'ABSPATH' )) exit;
	if(!class_exists('WPAM_Pages_AffiliatesRegister')){
		return;
	}
	//require_once WPAM_BASE_DIRECTORY . "/source/Pages/AffiliatesRegister.php";
	class AffiliateManagerForm extends FormInterface
	{
		private $formSessionVar = FormSessionVars::AFFILIATE_MANAGER_REG;
		private $formPhoneVer   = FormSessionVars::AFFILIATE_MANAGER_PHONE_VER;
		private $phoneFieldKey  = '_phoneNumber';
		private $phoneFormID 	= 'input[name=_phoneNumber]';
		private $HomePageId 	= 'wpam_home_page_id';

		function wcsmsn_update_billing_phone( $user_id ) {
			if ( isset( $_POST[$this->phoneFieldKey] ) )
				update_user_meta($user_id, 'billing_phone', $_POST[$this->phoneFieldKey]);
		}

		function handleForm()
		{
			if (session_status() == PHP_SESSION_NONE || session_id() == '') {
				session_start();
			}
			add_action('wpam_front_end_registration_form_submitted', array($this,'_handle__wpam_register_form'),10,1);
			add_filter('wcsmsnDefaultSettings', array($this,'addDefaultSetting'),2);
			add_action('woocommerce_order_status_processing', array($this, 'handleCommission'),10,1);
			add_action('woocommerce_order_status_refunded', array($this, 'handleCommission'),10,1);
			add_action('woocommerce_order_status_cancelled', array($this, 'handleCommission'),10,1);
			add_action('user_register', array($this, 'wcsmsn_update_billing_phone'), 10, 1 );

			$this->routeData();
		}

		function routeData()
		{
			if(!array_key_exists('handler', $_REQUEST)) return;
			switch (trim($_REQUEST['handler']))
			{
				case "approveApplication":
				case "blockApplication":
				case "declineApplication":
				case "activateAffiliate":
				case "deactivateAffiliate":
					$this->_after_changed_wpam_status($_REQUEST); break;
				case "addTransaction":
					$this->_after_added_wpam_transaction($_REQUEST);break;
			}
		}

		/*when order status changed at woocommerce >> orders*/
		public static function handleCommission($order_id)
		{
			$txn_record = self::getTransactionDetail($order_id);
			if($txn_record!= null)
			{
				$args					= array();
				$args['affiliateId']	= $txn_record->affiliateId;
				$args['amount']			= $txn_record->amount;
				$args['type']			= $txn_record->type;
				$args['referenceId']	= $txn_record->referenceId;
				self::_after_added_wpam_transaction($args);
			}
		}

		/*add default settings to savesetting in setting-options*/
		public function addDefaultSetting($defaults=array())
		{
			$wpam_statuses		= self::get_affiliate_statuses();
			$wpam_transaction	= self::get_affiliate_transaction();
			$wpam_statuses 		= array_merge($wpam_statuses,$wpam_transaction);
			foreach($wpam_statuses as $ks => $vs)
			{
				$defaults['wcsmsn_wpam_general']['wpam_admin_notification_'.$vs]	= 'off';
				$defaults['wcsmsn_wpam_general']['wpam_order_status_'.$vs]		= 'off';
				$defaults['wcsmsn_wpam_message']['wpam_admin_sms_body_'.$vs]		= '';
				$defaults['wcsmsn_wpam_message']['wpam_sms_body_'.$vs]			= '';
			}
			return $defaults;
		}
		/*get last transaction detail for sending sms*/
		public static function getTransactionDetail($order_id=NULL)
		{
			global $wpdb;
			$query = "
				SELECT *
				FROM ".$wpdb->prefix ."wpam_transactions
				WHERE referenceId = %s order by transactionId desc ";
			$txn_record = $wpdb->get_row($wpdb->prepare($query, $order_id));
			return $txn_record;
		}

		public static function pharseSmsBody($data=array(),$content='')
		{
			return str_replace( array_keys($data), array_values($data), $content );
		}
		/*list affiliate statuses*/
		public static function get_affiliate_statuses()
		{
			return array(
				'approveApplication'	=> 'approveApplication',
				'blockApplication'		=> 'blockApplication',
				'declineApplication'	=> 'declineApplication',
				'activateAffiliate'		=> 'activateAffiliate',
				'deactivateAffiliate'	=> 'deactivateAffiliate',
			);
		}
		/*list affiliate transaction status*/
		public static function get_affiliate_transaction()
		{
			return array(
				'credit'			=> 'credit',
				'refund'			=> 'refund',
				'payout'			=> 'payout',
				'adjustment'		=> 'adjustment',
			);
		}
		/*display tokens for sms content at woocommerce >> wcsmsn >> affiliate templates*/
		public static function getWPAMvariables($type='')
		{
			$variables = array(
				'[affiliate_id]' 	=> 'Affiliate Id',
				'[store_name]' 		=> 'Store Name',
				'[first_name]' 		=> 'First Name',
				'[last_name]' 		=> 'Last Name',
			);

			if($type=='affiliate')
			{
				$variables += array('[affiliate_status]' 	=> 'Affiliate Status');
			}
			if($type=='transaction')
			{
				$variables += array(
					'[transaction_type]' 	=> 'Transaction Type',
					'[commission_amt]' 		=> 'Commission Amount',
					'[order_id]' 			=> 'Order Id',
				);
			}
			// $ret_string = '';
			// foreach($variables as $vk => $vv)
			// {
				// $ret_string .= sprintf( "<a href='#' val='%s'>%s</a> | " , $vk , __($vv,wcsmsnConstants::TEXT_DOMAIN));
			// }
			return $variables;
	   }

		public static function getAffiliateById($affiliateId=NULL)
		{
			global $wpdb;
			$dbFields 	= $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."wpam_affiliates where affiliateId =".$affiliateId,ARRAY_A);
			$response 	= array_shift($dbFields);
			return $response;
		}
		/**
		*trigger sms when a transaction is performed through order status changed/ manually
		*order_id will be null if commission is awarded through manually
		*params | array| affiliateId,amount,type are mandatory field.
		**/
		public static function _after_added_wpam_transaction($data=array())
		{
			$affiliateId 		= $data['affiliateId'];
			$am_user 			= self::getAffiliateById($affiliateId);
			$status 			= $data['type'];
			$amount 			= $data['amount'];
			$order_id 			= isset($data['referenceId'])?$data['referenceId']:'';

			$buyer_sms_notify 	= wcsmsn_get_option( 'wpam_order_status_'.$status, 'wcsmsn_wpam_general', 'on' );
			$admin_sms_notify 	= wcsmsn_get_option( 'wpam_admin_notification_'.$status, 'wcsmsn_wpam_general', 'on' );

			$buyer_sms_content 	= wcsmsn_get_option( 'wpam_sms_body_'.$status, 'wcsmsn_wpam_message', wcsmsnMessages::showMessage('DEFAULT_WPAM_BUYER_SMS_TRANS_STATUS_CHANGED') );

			$admin_sms_content 	= wcsmsn_get_option( 'wpam_admin_sms_body_'.$status, 'wcsmsn_wpam_message', wcsmsnMessages::showMessage('DEFAULT_WPAM_ADMIN_SMS_TRANS_STATUS_CHANGED') );
			if(sizeof($am_user)>0)
			{
				$username 		=  $am_user['email'];
				$billing_phone 	= $am_user['phoneNumber'];

				$token_val = array(
					'[affiliate_id]' 	=> $affiliateId,
					'[store_name]' 		=> get_bloginfo(),
					'[first_name]' 		=> $am_user['firstName'],
					'[last_name]' 		=> $am_user['lastName'],
					'[transaction_type]'=> $status,
					'[commission_amt]' 	=> $amount,
					'[order_id]' 		=> $order_id,
				);

				//do_action('wcsmsn_send_sms', $billing_phone, self::pharseSmsBody($token_val,$buyer_sms_content));
				$wpam_user				=array();
				$wpam_user['number'] 	= $billing_phone;
				$wpam_user['sms_body'] 	= self::pharseSmsBody($token_val,$buyer_sms_content);
				$response 				= wcsmsncURLOTP::sendsms( $wpam_user );

				$admin_phone_number     = wcsmsn_get_option( 'sms_admin_phone', 'wcsmsn_message', '' );
				$admin_phone_number 	= str_replace('postauthor','post_author',$admin_phone_number);
				if($admin_sms_notify=='on' && $admin_phone_number!='')
				{
					$admin_phone_number 	= str_replace('post_author','',$admin_phone_number);
					//do_action('wcsmsn_send_sms', $admin_phone_number, self::pharseSmsBody($token_val,$admin_sms_content));
					$wpam_admin=array();
					$wpam_admin['number'] 	= str_replace('post_author','',$admin_phone_number);
					$wpam_admin['sms_body'] = self::pharseSmsBody($token_val,$admin_sms_content);
					$response = wcsmsncURLOTP::sendsms( $wpam_admin );
				}
			}
		}
		/*trigger sms after changing the affiliate status*/
		public static function _after_changed_wpam_status($data=array())
		{
			$affiliateId		= $data['affiliateId'];
			$am_user 			= self::getAffiliateById($affiliateId);
			$status 			= $data['handler'];
			$buyer_sms_notify 	= wcsmsn_get_option( 'wpam_order_status_'.$status, 'wcsmsn_wpam_general', 'on' );
			$admin_sms_notify 	= wcsmsn_get_option( 'wpam_admin_notification_'.$status, 'wcsmsn_wpam_general', 'on' );

			$buyer_sms_content 	= wcsmsn_get_option( 'wpam_sms_body_'.$status, 'wcsmsn_wpam_message', sprintf(__('Hello %s, status of your affiliate account %s with %s has been changed to %s.',wcsmsnConstants::TEXT_DOMAIN), '[first_name]', '[affiliate_id]', '[store_name]', '[affiliate_status]') );

			$admin_sms_content 	= wcsmsn_get_option( 'wpam_admin_sms_body_'.$status, 'wcsmsn_wpam_message', wcsmsnMessages::showMessage('DEFAULT_WPAM_ADMIN_SMS_STATUS_CHANGED') );

			if(sizeof($am_user)>0)
			{
				$username 		=  $am_user['email'];
				$billing_phone 	= $am_user['phoneNumber'];

				if($buyer_sms_notify=='on')
				{
					$token_val = array(
						'[affiliate_id]' 	=> $affiliateId,
						'[store_name]' 		=> get_bloginfo(),
						'[affiliate_status]'=> $status,
						'[first_name]' 		=> $am_user['firstName'],
						'[last_name]' 		=> $am_user['lastName'],
					);

					//do_action('wcsmsn_send_sms', $billing_phone, self::pharseSmsBody($token_val,$buyer_sms_content));
					$wpam_user				=array();
					$wpam_user['number'] 	= $billing_phone;
					$wpam_user['sms_body'] 	= self::pharseSmsBody($token_val,$buyer_sms_content);
					$response 				= wcsmsncURLOTP::sendsms( $wpam_user );
				}

				$admin_phone_number     	= wcsmsn_get_option( 'sms_admin_phone', 'wcsmsn_message', '' );
				if($admin_sms_notify=='on' && $admin_phone_number!='')
				{
					$wpam_admin				= array();
					//$admin_phone_number 	= str_replace('post_author','',$admin_phone_number);
					//do_action('wcsmsn_send_sms', $admin_phone_number, self::pharseSmsBody($token_val,$admin_sms_content));
					$wpam_admin				= array();
					$wpam_admin['number'] 	= str_replace('post_author','',$admin_phone_number);
					$wpam_admin['sms_body'] = self::pharseSmsBody($token_val,$admin_sms_content);
					$response 				= wcsmsncURLOTP::sendsms( $wpam_admin );
				}
			}
		}

		public static function isFormEnabled()
		{
			return (wcsmsn_get_option('buyer_signup_otp', 'wcsmsn_general')=="on") ? true : false;
		}

		function _handle__wpam_register_form()
		{
			if(isset($_SESSION['wcsmsn_mobile_verified']))
			{
				unset($_SESSION['wcsmsn_mobile_verified']);

				$auto_approved = get_option('wpam_auto_aff_approve_enabled','on');
				if($auto_approved=='on'){
					$_POST['register']='Register'; //requires for creating wp user.
				}
				return $_POST;
			}

			if(!empty($_POST))
			{
				if(empty($_SESSION))
				{
					$_SESSION[$this->formSessionVar]	= 1;
					$_SESSION['user_email']				= $_POST['_email'];
					$_SESSION['user_login']				= $_POST['_email'];
					$_SESSION['user_password']			= $_POST['_phoneNumber'];
				}

				wcsmsnUtility::checkSession();
				wcsmsnUtility::initialize_transaction($this->formSessionVar);
				$this->processPhoneAndStartOTPVerificationProcess($_POST);
				//$this->sendErrorMessageIfOTPVerificationNotStarted();//23-11-2018
			}
		}

		function processPhoneAndStartOTPVerificationProcess($data)
		{
			$errors 	= new WP_Error();
			if(!array_key_exists('_phoneNumber', $data) || !isset($data['_phoneNumber'])) return;
			$_SESSION[$this->formPhoneVer] = $data['_phoneNumber'];
			$username 	= isset($data['_email']) ? $data['_email'] : '';
			$email 		= isset($data['_email']) ? $data['_email'] : '';
			wcsmsn_site_challenge_otp($username,$email,$errors,$data['_phoneNumber'],"phone",null,$data,false);
		}

		function sendErrorMessageIfOTPVerificationNotStarted()
		{
			wp_send_json( wcsmsnUtility::_create_json_response( wcsmsnMessages::showMessage('ENTER_PHONE_CODE'),wcsmsnConstants::ERROR_JSON_TYPE) );
		}

		function handle_failed_verification($user_login,$user_email,$phone_number)
		{
			wcsmsnUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			wcsmsn_site_otp_validation_form($user_login,$user_email,$phone_number,wcsmsnMessages::showMessage('INVALID_OTP'),"phone",FALSE);
		}

		function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
		{
			wcsmsnUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			$_SESSION['wcsmsn_mobile_verified']=true;
		}

		public function unsetOTPSessionVariables()
		{
			unset($_SESSION[$this->formSessionVar]);
			unset($_SESSION[$this->formPhoneVer]);
		}

		public function is_ajax_form_in_play($isAjax)
		{
			wcsmsnUtility::checkSession();
			return isset($_SESSION[$this->formSessionVar]) ? FALSE : $isAjax;
		}

		public function getPhoneNumberSelector($selector)
		{
			wcsmsnUtility::checkSession();
			array_push($selector, $this->phoneFormID);
			return $selector;
		}

		function handleFormOptions()
		{
			add_action( 'wcsmsn_addTabs', array( $this, 'addTabs' ), 10 );
		}

		/*add tabs to wcsmsn settings at backend*/
		public static function addTabs($tabs=array())
		{
			$customer_param=array(
				'checkTemplateFor'	=> 'affiliate_customer',
				'templates'			=> self::getCustomerTemplates(),
			);

			$admin_param=array(
				'checkTemplateFor'	=>'affiliate_admin',
				'templates'			=>self::getAdminTemplates(),
			);

			$tabs['wpam_customer']['title']			= 'Affiliate Cust. Temp';
			$tabs['wpam_customer']['tab_section'] 	= 'wpamcsttemplates';
			$tabs['wpam_customer']['tabContent']	= 'views/message-template.php';
			$tabs['wpam_customer']['icon']			= 'dashicons-admin-users';
			$tabs['wpam_customer']['params']			= $customer_param;

			$tabs['wpam_admin']['title']			= 'Affiliate Admin Temp';
			$tabs['wpam_admin']['tab_section'] 		= 'wpamadmintemplates';
			$tabs['wpam_admin']['tabContent']		= 'views/message-template.php';
			$tabs['wpam_admin']['icon']				= 'dashicons-list-view';
			$tabs['wpam_admin']['params']				= $admin_param;
			return $tabs;
		}

		public static function getCustomerTemplates()
		{
			$wpam_statuses 		= self::get_affiliate_statuses();
			$wpam_transaction 	= self::get_affiliate_transaction();

			$templates 			= array();

			foreach($wpam_statuses as $ks  => $vs){

				$current_val 	= wcsmsn_get_option( 'wpam_order_status_'.$vs, 'wcsmsn_wpam_general', 'on');

				$checkboxNameId	= 'wcsmsn_wpam_general[wpam_order_status_'.$vs.']';
				$textareaNameId	= 'wcsmsn_wpam_message[wpam_sms_body_'.$vs.']';

				$text_body 		= wcsmsn_get_option('wpam_sms_body_'.$vs, 'wcsmsn_wpam_message', sprintf(__('Hello %s, status of your affiliate account %s with %s has been changed to %s.',wcsmsnConstants::TEXT_DOMAIN), '[first_name]', '[affiliate_id]', '[store_name]', '[affiliate_status]'));

				$templates[$ks]['title'] 			= 'when Affiliate is '.ucwords($vs);
				$templates[$ks]['enabled'] 			= $current_val;
				$templates[$ks]['status'] 			= $ks;
				$templates[$ks]['text-body'] 		= $text_body;
				$templates[$ks]['checkboxNameId'] 	= $checkboxNameId;
				$templates[$ks]['textareaNameId'] 	= $textareaNameId;
				$templates[$ks]['token'] 			= self::getWPAMvariables('affiliate');
			}

			foreach($wpam_transaction as $ks  => $vs){

				$current_val 	= wcsmsn_get_option( 'wpam_order_status_'.$vs, 'wcsmsn_wpam_general', 'on');

				$checkboxNameId	= 'wcsmsn_wpam_general[wpam_order_status_'.$vs.']';
				$textareaNameId	= 'wcsmsn_wpam_message[wpam_sms_body_'.$vs.']';

				$text_body 		= wcsmsn_get_option('wpam_sms_body_'.$vs, 'wcsmsn_wpam_message', wcsmsnMessages::showMessage('DEFAULT_WPAM_BUYER_SMS_TRANS_STATUS_CHANGED'));

				$templates[$ks]['title'] 			= 'when Transaction is '.ucwords($vs);
				$templates[$ks]['enabled'] 			= $current_val;
				$templates[$ks]['status'] 			= $ks;
				$templates[$ks]['text-body'] 		= $text_body;
				$templates[$ks]['checkboxNameId'] 	= $checkboxNameId;
				$templates[$ks]['textareaNameId'] 	= $textareaNameId;
				$templates[$ks]['token'] 			= self::getWPAMvariables('transaction');
			}
			return $templates;
		}

		public static function getAdminTemplates()
		{
			$wpam_statuses 		= self::get_affiliate_statuses();
			$wpam_transaction 	= self::get_affiliate_transaction();

			$templates 			= array();

			foreach($wpam_statuses as $ks  => $vs){

				$current_val 	= wcsmsn_get_option( 'wpam_order_status_'.$vs, 'wcsmsn_wpam_general', 'on');

				$checkboxNameId	= 'wcsmsn_wpam_general[wpam_admin_notification_'.$vs.']';
				$textareaNameId	= 'wcsmsn_wpam_message[wpam_admin_sms_body_'.$vs.']';

				$text_body 		= wcsmsn_get_option('wpam_admin_sms_body_'.$vs, 'wcsmsn_wpam_message', wcsmsnMessages::showMessage('DEFAULT_WPAM_ADMIN_SMS_STATUS_CHANGED'));

				$templates[$ks]['title'] 			= 'when Affiliate is '.ucwords($vs);
				$templates[$ks]['enabled'] 			= $current_val;
				$templates[$ks]['status'] 			= $ks;
				$templates[$ks]['text-body'] 		= $text_body;
				$templates[$ks]['checkboxNameId'] 	= $checkboxNameId;
				$templates[$ks]['textareaNameId'] 	= $textareaNameId;
				$templates[$ks]['token'] 			= self::getWPAMvariables('affiliate');
			}

			foreach($wpam_transaction as $ks  => $vs){

				$current_val 	= wcsmsn_get_option( 'wpam_order_status_'.$vs, 'wcsmsn_wpam_general', 'on');

				$checkboxNameId	= 'wcsmsn_wpam_general[wpam_admin_notification_'.$vs.']';
				$textareaNameId	= 'wcsmsn_wpam_message[wpam_admin_sms_body_'.$vs.']';

				$text_body 		= wcsmsn_get_option('wpam_admin_sms_body_'.$vs, 'wcsmsn_wpam_message', wcsmsnMessages::showMessage('DEFAULT_WPAM_ADMIN_SMS_TRANS_STATUS_CHANGED'));

				$templates[$ks]['title'] 			= 'when Transaction is '.ucwords($vs);
				$templates[$ks]['enabled'] 			= $current_val;
				$templates[$ks]['status'] 			= $ks;
				$templates[$ks]['text-body'] 		= $text_body;
				$templates[$ks]['checkboxNameId'] 	= $checkboxNameId;
				$templates[$ks]['textareaNameId'] 	= $textareaNameId;
				$templates[$ks]['token'] 			= self::getWPAMvariables('transaction');
			}
			return $templates;
		}
	}
	new AffiliateManagerForm;
?>