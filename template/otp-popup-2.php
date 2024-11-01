<?php
$otp_length = wcsmsnUtility::get_otp_length();
echo '<style>.modal{display:none;position:fixed;z-index:999999999999;padding-top:100px;left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgb(0,0,0);background-color:rgba(0,0,0,0.4);}.modal-content{position:relative;background-color:#fefefe;margin:auto;padding:0;border:1px solid #888;width:40%;box-shadow:0px 0px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);-webkit-animation-name:zoomIn;-webkit-animation-duration:0.3s;animation-name:zoomIn;animation-duration:0.3s;border-radius: 8px;}[name=wcsmsn_otp_validate_submit]{width:100%;margin-top:15px}@media  only screen and (max-width: 767px){.modal-content{width:100%}}@-webkit-keyframes zoomIn {from {opacity: 0;-webkit-transform: scale3d(0.3, 0.3, 0.3);transform: scale3d(0.3, 0.3, 0.3);}50% {opacity: 1;}}@keyframes zoomIn {from {opacity: 0;-webkit-transform: scale3d(0.3, 0.3, 0.3);transform: scale3d(0.3, 0.3, 0.3);}50% {opacity: 1;}}.zoomIn {-webkit-animation-name: zoomIn;animation-name: zoomIn;}.modal-header{background-color:#5cb85c;color:white;}.modal-footer{background-color:#5cb85c;color:white;}.close{float:none;text-align: right;font-size: 25px;cursor: pointer;text-shadow: 0 1px 0 #fff;line-height: 1;font-weight: 400;padding: 0px 5px 0px;}.close:hover {color: #999;}.otp_input{margin-bottom:12px;}.otp_input[type="number"]::-webkit-outer-spin-button, .otp_input[type="number"]::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}
.otp_input[type="number"] {-moz-appearance: textfield;}.otp_input{width:100%}
form.wcsmsn_popup {overflow:hidden}
form.wcsmsn_popup .modal{padding-top:0;}
form.wcsmsn_popup .modal-content{width:100%;height:100%;}
form.wcsmsn_popup + .sa-lwo-form .wcsmsnModal{padding-top:0 !important}
form.wcsmsn_popup + .sa-lwo-form .modal-content{width:100% !important}
form .otp_input{display:none;}
</style>';
echo ' <div class="modal wcsmsnModal">
			<div class="modal-content">
					<div class="close">x</div>
			<div class="modal-body" style="padding:1em">
			<div class="sa-message woocommerce-message" style="width:100%">EMPTY</div>
			<div class="wcsmsn_validate_field digit-group">

<input type="text" class="otp-number" id="digit-1" name="digit-1" onkeyup="return digitGroup(this);" data-next="digit-2" style="margin-right: 5px;"/>';

$j = $otp_length -1;
for($i=1; $i <$otp_length; $i++){

?>
<input type="text" class="otp-number" id="digit-<?php echo $i + 1?>" name="digit-<?php echo $i + 1?>" data-next="digit-<?php echo $i + 2?>" onkeyup="return digitGroup(this);" data-previous="digit-<?php echo $otp_length - $j--?>" />

<?php }
$otp_input = (!empty($otp_input_field_nm)) ? $otp_input_field_nm : 'wcsmsn_customer_validation_otp_token';

echo'
<input type="number" name="'.$otp_input.'" autofocus="true" placeholder="" id="'.$otp_input.'" class="input-text otp_input" pattern="[0-9]{'.$otp_length.'}" title="'.wcsmsnMessages::showMessage('OTP_RANGE').'"/>
';

echo '<br /><a style="float:right" class="wcsmsn_resend_btn" onclick="saResendOTP(this)">'.wcsmsnMessages::showMessage('RESEND').'</a><span class="wcsmsn_timer" style="min-width:80px; float:right">00:00 sec</span><br /><button type="button" name="wcsmsn_otp_validate_submit" style="color:grey; pointer-events:none;" class="wcsmsn_otp_validate_submit" value="'.wcsmsnMessages::showMessage('VALIDATE_OTP').'">'.wcsmsnMessages::showMessage('VALIDATE_OTP').'</button></div></div></div></div>';


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

<script>
jQuery(".otp_input").attr('minlength', 4);
jQuery(".otp_input").removeAttr('maxlength');
</script>
<style>
form .digit-group{margin:0 0em}
.digit-group input[type=text] { width: 40px;height: 50px;border: 1px solid currentColor;line-height: 50px;text-align: center;font-size: 24px;margin: 0 1px;display:initial;padding:0px;}
</style>