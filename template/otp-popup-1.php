<?php
echo '<style>.modal{display:none;position:fixed;z-index:999999999999;padding-top:100px;left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgb(0,0,0);background-color:rgba(0,0,0,0.4);}.modal-content{position:relative;background-color:#fefefe;margin:auto;padding:0;border:1px solid #888;width:40%;box-shadow:04px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);-webkit-animation-name:zoomIn;-webkit-animation-duration:0.3s;animation-name:zoomIn;animation-duration:0.3s}[name=wcsmsn_otp_validate_submit]{width:100%}@media  only screen and (max-width: 767px){.modal-content{width:100%}}@-webkit-keyframes zoomIn {from {opacity: 0;-webkit-transform: scale3d(0.3, 0.3, 0.3);transform: scale3d(0.3, 0.3, 0.3);}50% {opacity: 1;}}@keyframes zoomIn {from {opacity: 0;-webkit-transform: scale3d(0.3, 0.3, 0.3);transform: scale3d(0.3, 0.3, 0.3);}50% {opacity: 1;}}.zoomIn {-webkit-animation-name: zoomIn;animation-name: zoomIn;}.modal-header{background-color:#5cb85c;color:white;}.modal-footer{background-color:#5cb85c;color:white;}.close{float:none;text-align: right;font-size: 25px;cursor: pointer;text-shadow: 0 1px 0 #fff;line-height: 1;font-weight: 400;padding: 0px 5px 0px;}.close:hover {color: #999;}.otp_input{margin-bottom:12px;}.otp_input[type="number"]::-webkit-outer-spin-button, .otp_input[type="number"]::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}
.otp_input[type="number"] {-moz-appearance: textfield;}.otp_input{width:100%}
form.wcsmsn_popup {overflow:hidden}
form.wcsmsn_popup .modal{padding-top:0;}
form.wcsmsn_popup .modal-content{width:100%;height:100%;}

form.wcsmsn_popup + .sa-lwo-form .wcsmsnModal{padding-top:0 !important}
form.wcsmsn_popup + .sa-lwo-form .modal-content{width:100% !important}

</style>';

$otp_input = (!empty($otp_input_field_nm)) ? $otp_input_field_nm : 'wcsmsn_customer_validation_otp_token';
echo '<div  class="modal wcsmsnModal">
   <div class="modal-content">
      <div class="close" >x</div>
      <div class="modal-body">
         <div style="margin:1em;" class="sa-message woocommerce-message">EMPTY</div>
         <div class="wcsmsn_validate_field" style="margin:1em"><input type="number" name="'.$otp_input.'" autofocus="true" placeholder="" id="'.$otp_input.'" class="input-text otp_input" pattern="[0-9]{4,8}" title="'.wcsmsnMessages::showMessage('OTP_RANGE').'"><a style="pointer-events: none; cursor: default; opacity: 1; float:right" class="wcsmsn_resend_btn" onclick="saResendOTP(this)">'.wcsmsnMessages::showMessage('RESEND').'</a><span class="wcsmsn_timer" style="min-width:80px; float:right">00:00:02 sec</span><br><button type="button" name="wcsmsn_otp_validate_submit" style="color:grey; pointer-events:none;" class="wcsmsn_otp_validate_submit" value="'.wcsmsnMessages::showMessage('VALIDATE_OTP').'">'.wcsmsnMessages::showMessage('VALIDATE_OTP').'</button></div>
      </div>
   </div>
</div>';

echo '<script>

jQuery("form .wcsmsnModal").on("focus", "input[type=number]", function (e) {
	jQuery(this).on("wheel.disableScroll", function (e) {
		e.preventDefault();
	});
});
jQuery("form .wcsmsnModal").on("blur", "input[type=number]", function (e) {
jQuery(this).off("wheel.disableScroll");
});
</script>';
?>