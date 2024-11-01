<?php
if (! defined( 'ABSPATH' )) exit;
	class ContactForm7 extends FormInterface
	{
		private $formSessionVar 	= FormSessionVars::CF7_FORMS;
		private $formPhoneVer 		= FormSessionVars::CF7_PHONE_VER;
		private $formFinalPhoneVer 	= FormSessionVars::CF7_PHONE_SUB;
		private $phoneFormID;
		private $phoneFieldKey;
		private $formSessionTagName;

		function handleForm()
		{
			$this->phoneFieldKey = 'billing_phone';
			$this->phoneFormID = 'input[name='.$this->phoneFieldKey.']';

			add_filter( 'wpcf7_validate_text*'	, array($this,'validateFormPost'), 1 , 2 );
			add_filter( 'wpcf7_validate_tel*'	, array($this,'validateFormPost'), 1 , 2 );
			add_filter( 'wpcf7_validate_billing_phone*' , array($this,'validateFormPost'), 10 , 2 );
			add_filter( 'wpcf7_validate_wcsmsn_otp_input*' , array($this,'validateFormPost'), 1 , 2 );
			add_shortcode('wcsmsn_verify_phone',array($this,'_cf7_phone_shortcode'));
			$this->routeData();
			add_filter( 'wpcf7_editor_panels' , array($this, 'new_menu_wcsmsn'),98);
			add_action( 'wpcf7_after_save', array( &$this, 'save_form' ) );
			add_action( 'wpcf7_before_send_mail', array($this, 'sendsms_c7' ) );
			add_action( 'wpcf7_admin_init',  array($this, 'add_wcsmsn_phone_tag'), 20, 0 );
			add_action( 'wpcf7_init',  array($this, 'wcsmsn_wpcf7_add_shortcode_phonefield_frontend'));
			add_action( 'wp_footer',  array($this, 'wcsmsn_cf7_otp_js_script'));


		}

		function wcsmsn_wpcf7_add_shortcode_phonefield_frontend() {
			wpcf7_add_form_tag(
				array( 'billing_phone','billing_phone*','wcsmsn_otp_input','wcsmsn_otp_input*'),
				array($this,'wcsmsn_wpcf7_shortcode_handler'), true );
		}

		function wcsmsn_wpcf7_shortcode_handler( $tag )
		{
			$tag = new WPCF7_FormTag( $tag );
			if ( empty( $tag->name ) )
				return '';

			$validation_error = wpcf7_get_validation_error( $tag->name );

			$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-wcsmsn' );
			if ( $validation_error )
				$class .= ' wpcf7-not-valid';

			//add on 18/05/2020
			if($tag->has_option( 'otp_enabled_popup' ))
				$class .=' wpcf7-wcsmsn-otp-enabled';

			$atts = array();

			$atts['size'] = $tag->get_size_option( '40' );
			$atts['maxlength'] = $tag->get_maxlength_option();
			$atts['minlength'] = $tag->get_minlength_option();

			if ( $atts['maxlength'] && $atts['minlength'] && $atts['maxlength'] < $atts['minlength'] ) {
				unset( $atts['maxlength'], $atts['minlength'] );
			}

			$atts['class'] = $tag->get_class_option( $class );
			$atts['id'] = $tag->get_id_option();
			$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

			if ( $tag->has_option( 'readonly' ) )
				$atts['readonly'] = 'readonly';

			if ( $tag->is_required() )
				$atts['aria-required'] = 'true';

			$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

			$value = (string) reset( $tag->values );

			if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
				$atts['placeholder'] = $value;
				$value = '';
			}
			$value = $tag->get_default_option( $value );

			$value = wpcf7_get_hangover( $tag->name, $value );

			$scval = do_shortcode('['.$value.']');
			if( $scval != '['.$value.']' ){
				$value = esc_attr( $scval );
			}

			$atts['value'] 	= $value;
			$atts['type'] 	= 'text';
			$atts['name'] 	= $tag->name;
			$atts 			= wpcf7_format_atts( $atts );

			$html = sprintf(
				'<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
				sanitize_html_class( $tag->name ), $atts, $validation_error );

			if ( $tag->has_option( 'otp_enabled_popup' )) {
				$html .= $this->hide_send($tag);
			}
			elseif ( $tag->has_option( 'otp_enabled' ))
			{
				$html .= '<div style="margin-bottom:3%">
				<input class="wcsmsn_cf7_otp_btn" type="button" class="button alt" style="width:100%" title="Please Enter a phone number to enable this." value="Click here to verify your Phone"><div id="mo_message" style="background-color: #f7f6f7;padding: 1em 2em 1em 3.5em;"></div>
				</div>';

				$html.=$this->_cf7_phone_shortcode();
			}
			return $html;
		}

		public function add_wcsmsn_phone_tag() {
			if (class_exists( 'WPCF7_TagGenerator' ) )
			{
				$tag_generator = WPCF7_TagGenerator::get_instance();
				$tag_generator->add( 'billing_phone', __( 'wcsmsn PHONE', 'contact-form-7' ), array($this, 'wcsmsn_wpcf7_tag_generator_text') );
				//$tag_generator->add( 'wcsmsn_otp_input', __( 'wcsmsn OTP TXT', 'contact-form-7' ), array($this, 'wcsmsn_wpcf7_tag_generator_text') );
			}
		}

		function wcsmsn_wpcf7_tag_generator_text($contact_form , $args = '')
		{
			$args = wp_parse_args( $args, array() );
			$type = $args['id'];
		?>
		<div class="control-box">
		<fieldset>

		<table class="form-table">
		<tbody>
			<tr>
			<th scope="row"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></th>
			<td>
				<fieldset>
				<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></legend>
				<label><input type="checkbox" name="required" checked="checked"/> <?php echo esc_html( __( 'Required field', 'contact-form-7' ) ); ?></label>
				</fieldset>
			</td>
			</tr>

			<tr>
			<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></label></th>

			<?php
			if($type=='wcsmsn_otp_input'){$field_name = 'wcsmsn_customer_validation_otp_token';}
			else{$field_name = 'billing_phone';}
			?>
			<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" value="<?php echo $field_name;?>" /></td>
			</tr>

			<tr>
				<th scope="row"></th>
				<td>
				<?php if($type=='billing_phone'){?>
				<label><input type="checkbox" name="otp_enabled_popup" class="option" /> <?php echo esc_html( __( 'Use this field for sending OTP to Mobile Number', 'contact-form-7' ) ); ?></label>
				<?php }?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'contact-form-7' ) ); ?></label></th>
				<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
			</tr>

			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'contact-form-7' ) ); ?></label></th>
				<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
			</tr>

		</tbody>
		</table>
		</fieldset>
		</div>

		<div class="insert-box">
			<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

			<div class="submitbox">
			<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
			</div>

			<br class="clear" />

			<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
		</div>
		<?php
		}

		public static function isFormEnabled()
		{
			return true;
		}

		function routeData()
		{
			if(!array_key_exists('option', $_GET)) return;

			switch (trim($_GET['option']))
			{
				case "wcsmsn-cf7-contact":
					$this->_handle_cf7_contact_form($_POST);	break;
			}
		}

		function _handle_cf7_contact_form($getdata)
		{
			wcsmsnUtility::checkSession();
			wcsmsnUtility::initialize_transaction($this->formSessionVar);

			if(array_key_exists('user_phone', $getdata) && !wcsmsnUtility::isBlank($getdata['user_phone']))
			{
				$_SESSION[$this->formPhoneVer] = trim($getdata['user_phone']);
				$message = str_replace("##phone##",$getdata['user_phone'],wcsmsnMessages::showMessage('OTP_SENT_PHONE'));
				wcsmsn_site_challenge_otp('test',null,null,trim($getdata['user_phone']),"phone",null,null,true);
			}
			else
			{
				wp_send_json( wcsmsnUtility::_create_json_response(__("Enter a number in the following format : 9xxxxxxxxx",wcsmsnConstants::TEXT_DOMAIN),wcsmsnConstants::ERROR_JSON_TYPE) );
			}
		}

		function validateFormPost($result, $tag)
		{
			wcsmsnUtility::checkSession();
			$tag = new WPCF7_FormTag( $tag );
			$name = $tag->name;
			$value = isset( $_POST[$name] ) ? trim( wp_unslash( strtr( (string) $_POST[$name], "\n", " " ) ) ) : '';


			//if ( 'tel' == $tag->basetype && $name==$this->phoneFieldKey) $_SESSION[$this->formFinalPhoneVer]  = $value;
			//changed on 17-09-2019
			if(in_array($tag->basetype,array('billing_phone','wcsmsn_otp_input')))
			{
				if ( $tag->is_required() && '' == $value ) {
					$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
				}
			}
			//changed on 17-09-2019
			if ( in_array($tag->basetype,array('number','text','tel','billing_phone','wcsmsn_otp_input')) && $name==$this->phoneFieldKey) $_SESSION[$this->formFinalPhoneVer]  = $value;

			//if ( 'text' == $tag->basetype && $name=='wcsmsn_customer_validation_otp_token')
			//changed on 17-09-2019
			if ( in_array($tag->basetype,array('number','text','billing_phone','wcsmsn_otp_input')) && $name=='wcsmsn_customer_validation_otp_token' && '' != $value)
			{
					$_SESSION[$this->formSessionTagName] = $name;
					//check if the otp verification field is empty
					if($this->checkIfVerificationCodeNotEntered($name))
						$result->invalidate($tag, wpcf7_get_message('invalid_required'));


					//check if the session variable is not true i.e. OTP Verification flow was not started
					if($this->checkIfVerificationNotStarted())
						$result->invalidate($tag, _e(wcsmsnMessages::showMessage('VALIDATE_OTP')) );

					//validate otp if no error
					if(empty($result->invalid_fields)) {
					if(!$this->processOTPEntered())
						$result->invalidate( $tag, wcsmsnUtility::_get_invalid_otp_method());
					else
						$this->unsetOTPSessionVariables();
					}
			}
			return $result;
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

		function validateOTPRequest()
		{
			do_action('wcsmsn_validate_otp',$_SESSION[$this->formSessionTagName],NULL);
		}

		function processOTPEntered()
		{
			$this->validateOTPRequest();
			return strcasecmp($_SESSION[$this->formSessionVar],'validated')!=0 ? FALSE : TRUE;
		}

		function checkIfVerificationNotStarted()
		{
			return !array_key_exists($this->formSessionVar,$_SESSION);
		}

		function checkIfVerificationCodeNotEntered($name)
		{
			return !isset($_REQUEST[$name]);
		}

		function _cf7_phone_shortcode()
		{
			$html  = '<script>jQuery(window).load(function(){	$mo=jQuery;$mo(".wcsmsn_cf7_otp_btn,#wcsmsn_customer_validation_otp_token").unbind().click(function(o){';
			$html .= ' var target = $mo(this); var e=target.parents("form").find("input[name='.$this->phoneFieldKey.']").val();
			target.parents("form").find("#mo_message").empty(),target.parents("form").find("#mo_message").append("Loading..!Please wait"),';
			$html .= 'target.parents("form").find("#mo_message").show(),$mo.ajax({url:"'.site_url().'/?option=wcsmsn-cf7-contact",type:"POST",data:{user_phone:e},';
			$html .= 'crossDomain:!0,dataType:"json",success:function(o){
				if(o.result=="success"){target.parents("form").find("#mo_message").empty(),';
			$html .= 'target.parents("form").find("#mo_message").append(o.message),target.parents("form").find("#mo_message").css("border-top","3px solid green"),';
			$html .= 'target.parents("form").find("input[name=email_verify]").focus()}else{target.parents("form").find("#mo_message").empty(),target.parents("form").find("#mo_message").append(o.message),';
			$html .= 'target.parents("form").find("#mo_message").css("border-top","3px solid red"),target.parents("form").find("input[name=wcsmsn_customer_validation_otp_token]").focus()} ;},';
			$html .= 'error:function(o,e,n){console.log("error"+o)}})});$mo("[name=wcsmsn_customer_validation_otp_token]").on("change",function(){ $mo(this).find("#mo_message").empty().css("border-top","none")});});</script>';

			return $html;
		}

		public function unsetOTPSessionVariables()
		{
			unset($_SESSION[$this->txSessionId]);
			unset($_SESSION[$this->formSessionVar]);
			unset($_SESSION[$this->formPhoneVer]);
			unset($_SESSION[$this->formFinalPhoneVer]);
			unset($_SESSION[$this->formSessionTagName]);
		}

		public function is_ajax_form_in_play($isAjax)
		{
			wcsmsnUtility::checkSession();
			return isset($_SESSION[$this->formSessionVar]) ? TRUE : $isAjax;
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

		/*
	* Function for WC SMS Notifications integration with contact form 7.
	*/

		public function new_menu_wcsmsn ($panels) {
			$panels['wc-sms-notifications-sms-panel'] = array(
					'title' => __('WC SMS Notifications'),
					'callback' => array($this, 'add_panel_wcsmsn')
			);
			return $panels;
		}

		public function add_panel_wcsmsn($form) {
			if ( wpcf7_admin_has_edit_cap() ) {
			  $options = get_option( 'wcsmsn_sms_c7_' . (method_exists($form, 'id') ? $form->id() : $form->id) );
			  if( empty( $options ) || !is_array( $options ) ) {
				$options 		= array( 'phoneno' => '', 'text' => '', 'visitorNumber' => '','visitorMessage' => '','wcsmsn_group' => '','wcsmsn_name' => '');
			  }
			  $options['form'] 	= $form;
			  $data 			= $options;
			  include(plugin_dir_path( __DIR__ ).'../template/cf7-template.php');
			}
		}

		public function save_form( $form ) {
			update_option( 'wcsmsn_sms_c7_' . (method_exists($form, 'id') ? $form->id() : $form->id), $_POST['wpcf7wcsmsn-settings'] );
		}

		public function get_cf7_tagS_To_String($value,$form){
				if(function_exists('wpcf7_mail_replace_tags')) {
					$return = wpcf7_mail_replace_tags($value);
				} elseif(method_exists($form, 'replace_mail_tags')) {
					$return = $form->replace_mail_tags($value);
				} else {
					return;
				}
				return $return;
		}

		public function sendsms_c7($form)
		{
			$options 			= get_option( 'wcsmsn_sms_c7_' . (method_exists($form, 'id') ? $form->id() : $form->id)) ;
			$sendToAdmin 		= false;
			$sendToVisitor 		= false;
			$adminNumber 		= '';
			$adminMessage 		= '';
			$visitorNumber 		= '';
			$visitorMessage 	= '';
			if(isset($options['phoneno']) && $options['phoneno'] != '' && isset($options['text']) && $options['text'] != ''){
				$adminNumber 	= $this->get_cf7_tagS_To_String($options['phoneno'],$form);
				$adminMessage 	= $this->get_cf7_tagS_To_String($options['text'],$form);
				$sendToAdmin 	= true;
			}
			
			$visitorNumber 	= $this->get_cf7_tagS_To_String($options['visitorNumber'],$form);

			if(isset($options['visitorNumber']) && $options['visitorNumber'] != '' &&
			   isset($options['visitorMessage']) && $options['visitorMessage'] != ''){
				$visitorMessage = $this->get_cf7_tagS_To_String($options['visitorMessage'],$form);
				$sendToVisitor 	= true;
			}

			if($sendToAdmin){
				do_action('wcsmsn_send_sms', $adminNumber, $adminMessage);
			}

			if($sendToVisitor){
				do_action('wcsmsn_send_sms', $visitorNumber, $visitorMessage);
			}
			
			$obj=array();$extra_fields=array();
			$group_name 	= $this->get_cf7_tagS_To_String($options['wcsmsn_group'],$form);
			$obj[0]['person_name'] = $this->get_cf7_tagS_To_String($options['wcsmsn_name'],$form);
			$obj[0]['number'] = $visitorNumber;
			$ContactForm = WPCF7_ContactForm::get_instance( $form->id() );
			$form_fields = $ContactForm->scan_form_tags();
			if(!empty($form_fields))
			{
				foreach($form_fields as $form_field)
				{
					$field = json_decode(json_encode($form_field), true);
					if($field['name']!='' && '['.$field['name'].']'!=$options['wcsmsn_name'] && '['.$field['name'].']'!=$options['visitorNumber'])
					{
				      $extra_fields[$field['name']] = $this->get_cf7_tagS_To_String('['.$field['name'].']',$form);
					}
				}
			}
			$resp = wcsmsncURLOTP::create_contact($obj,$group_name,$extra_fields);
		}

		//add on 18/05/2020
		function hide_send($tag){

			if($tag->has_option( 'otp_enabled_popup' )){

				$otp_resend_timer = wcsmsn_get_option( 'otp_resend_timer', 'wcsmsn_general', '15');
				$otp_template_style =  wcsmsn_get_option( 'otp_template_style', 'wcsmsn_general', 'otp-popup-1.php');
				return get_wcsmsn_template('template/'.$otp_template_style,array());
			}
		}

		function wcsmsn_cf7_otp_js_script()
		{
			$otp_resend_timer = wcsmsn_get_option( 'otp_resend_timer', 'wcsmsn_general', '15');
			wp_register_script( 'wcsmsn-auth', wcsmsn_MOV_URL . 'js/otp-sms.min.js', array('jquery'), wcsmsnConstants::wcsmsn_VERSION, true );
			wp_enqueue_script('wcsmsn-auth');
			
		?>
		<script>
		var verify_button = '<input class="wcsmsn_cf7_otp_btn_popup sa-otp-btn-init" type="submit" value="Send" class="wpcf7-form-control wpcf7-submit">';
		jQuery(window).load(function(){

			jQuery(".wpcf7-wcsmsn-otp-enabled").parents("form").find(".wpcf7-submit").hide();
			jQuery(verify_button).insertAfter(jQuery(".wpcf7-wcsmsn-otp-enabled").parents("form").find(".wpcf7-submit"));
			
			jQuery(".wcsmsn_cf7_otp_btn_popup.sa-otp-btn-init").unbind("click").click(function(o) {
				var current_form = jQuery(this).parents("form");
				var data = {user_phone:current_form.find(".wpcf7-billing_phone").val()};
				var action_url = "<?php echo site_url()?>/?option=wcsmsn-cf7-contact";
				saInitOTPProcess(this,action_url, data,<?php echo $otp_resend_timer;?>);
				return false;
			});

			$mo = jQuery;
			$mo(".wpcf7-form .wcsmsn_otp_validate_submit").unbind("click").click(function(){
				var current_form = jQuery(this).parents("form");
				var action_url = "<?php echo site_url();?>/?option=wcsmsn-validate-otp-form";
				var data = current_form.serialize()+"&from_both=both";
				wcsmsn_validateOTP(this,action_url,data,function(){
					current_form.find(".wpcf7-submit").trigger("click")
					
				});
				
				return false;
			});


		});
		</script>
		<?php
		}



	}
	new ContactForm7;