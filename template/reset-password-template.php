<?php
if(!headers_sent())
		header('Content-Type: text/html; charset=utf-8');
		echo '<html>
				<head>
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<link rel="stylesheet" type="text/css" href="' . $css_url . '" />
				</head>
				<body>
					<div class="mo-modal-backdrop">
						<div class="mo_customer_validation-modal" tabindex="-1" role="dialog" id="mo_site_otp_form">
							<div class="mo_customer_validation-modal-backdrop"></div>
							<div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
								<div class="login mo_customer_validation-modal-content">
									<div class="mo_customer_validation-modal-header">
										<b>'.__("Change Password").'</b>
										<a class="close" href="#" onclick="mo_validation_goback();" style="box-shadow: none;">&larr; '.__( 'Go Back' ).'</a>
									</div>
									<div class="mo_customer_validation-modal-body center">
										<div>'.$message.'</div><br /> ';
										if(!wcsmsnUtility::isBlank($user_email) || !wcsmsnUtility::isBlank($phone_number))
										{
		echo'								<div class="mo_customer_validation-login-container">
												<form name="f" method="post" action="">
													<input type="hidden" name="option" value="wcsmsn-change-password-form" />
													<label>New password</label>
													<input type="password" name="wcsmsn_user_newpwd"  autofocus="true" placeholder="" id="wcsmsn_user_pwd" required="true" title="Enter Your New password" />
													
													<label>Confirm password</label>
													<input type="password" name="wcsmsn_user_cnfpwd"  autofocus="true" placeholder="" id="wcsmsn_user_cnfpwd" required="true" title="Confirm password" />
													
													<br /><input type="submit" name="wcsmsn_reset_password_btn" id="wcsmsn_reset_password_btn" class="miniorange_otp_token_submit" value="'.__("Change Password").'" />
													<input type="hidden" name="otp_type" value="'.$otp_type.'">';
													
													
													wcsmsn_extra_post_data();
		echo'									</form>
											</div>';
										}
		echo'						</div>
								</div>
							</div>
						</div>
					</div>
					
					<form name="f" method="post" action="" id="validation_goBack_form">
						<input id="validation_goBack" name="option" value="validation_goBack" type="hidden"></input>
					</form>
					
					<style> 
						.mo_customer_validation-modal{ display: block !important; } 
						input[type="password"]{background: #FBFBFB none repeat scroll 0% 0%;font-family: "Open Sans",sans-serif;font-size: 24px;width: 100%;border: 1px solid #DDD;padding: 3px;margin: 2px 6px 16px 0px;}
					</style>
					<script>
						function mo_validation_goback(){
							document.getElementById("validation_goBack_form").submit();
						}
					</script>
				</body>
		    </html>';
?>			