<form class="woocommerce-form woocommerce-form-login login" method="post">
	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="username">Mobile Number<span class="required">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text wcsmsn_mobileno" name="username"  value="">
		<input type="hidden" class="woocommerce-Input woocommerce-Input--text input-text" name="redirect"  value="<?php echo $_SERVER['REQUEST_URI'];?>">
		
	</p>

	<p class="form-row">
		<input type="button" class="button wcsmsn_login_with_otp sa-otp-btn-init" name="wcsmsn_login_with_otp" value="Login with OTP" >
		
		<a href="javascript:void(0)" class="wcsmsn_default_login_form">Back</a>
	</p>
</form>