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
										<b>'.__("Validate OTP (One Time Passcode)").'</b>
										<a class="close" href="#" onclick="mo_validation_goback();" style="box-shadow: none;">&larr; '.__( 'Go Back' ).'</a>
									</div>
									<div class="mo_customer_validation-modal-body center">
										<div>'.$message.'</div><br /> ';
										if(!wcsmsnUtility::isBlank($user_email) || !wcsmsnUtility::isBlank($phone_number))
										{
		echo'								<div class="mo_customer_validation-login-container">
												<form name="f" method="post" action="">
													<input type="hidden" name="option" value="wcsmsn-validate-otp-form" />
													<input type="number" name="wcsmsn_customer_validation_otp_token"  autofocus="true" placeholder="" id="wcsmsn_customer_validation_otp_token" required="true" class="mo_customer_validation-textbox" autofocus="true" pattern="[0-9]{4,8}" title="'.wcsmsnMessages::showMessage('OTP_RANGE').'" />
													<br /><input type="submit" name="wcsmsn_otp_token_submit" id="wcsmsn_otp_token_submit" class="miniorange_otp_token_submit"  value="'.__("Validate OTP").'" />
													<input type="hidden" name="otp_type" value="'.$otp_type.'">';
													if(!$from_both){
		echo'											<input type="hidden" id="from_both" name="from_both" value="false" />
														<a style="float:right" id="verify_otp" onclick="mo_otp_verification_resend();">'.wcsmsnMessages::showMessage('RESEND_OTP').'</a>
														<span id="timer" style="min-width:80px; float:right">00:00 sec</span>';
													}else{
		echo'											<input type="hidden" id="from_both" name="from_both" value="true" />
														<a style="float:right" id="verify_otp" onclick="mo_select_goback();">'.wcsmsnMessages::showMessage('RESEND_OTP').'</a>
														<span id="timer" style="min-width:80px; float:right">00:00 sec</span>';
													}
													
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
					
					<form name="f" method="post" action="" id="verification_resend_otp_form">
						<input id="verification_resend_otp" name="option" value="verification_resend_otp_'.$otp_type.'" type="hidden" />'.PHP_EOL;
						if(!$from_both)
		echo'				<input type="hidden" id="from_both" name="from_both" value="false" />'.PHP_EOL;
						else
		echo'				<input type="hidden" id="from_both" name="from_both" value="true" />'.PHP_EOL;
						
						wcsmsn_extra_post_data();
						
		echo'		</form>

					<form name="f" method="post" action="" id="goBack_choice_otp_form">
						<input id="verification_resend_otp" name="option" value="verification_resend_otp_both" type="hidden" />
						<input type="hidden" id="from_both" name="from_both" value="true" />';
						
						wcsmsn_extra_post_data();
					
		echo'		</form>

					<style> 
					.mo_customer_validation-modal{ display: block !important; } 
					
					#verify_otp{pointer-events: none; cursor: not-allowed; opacity: .5;text-decoration:none;box-shadow: none;}
					.displaynone{display:none;}
					input[type="number"].mo_customer_validation-textbox {background: #FBFBFB none repeat scroll 0% 0%;font-family: "Open Sans",sans-serif;font-size: 24px;width: 100%;border: 1px solid #DDD;padding: 3px;margin: 2px 6px 16px 0px;}
					</style>
					<script>
						function mo_validation_goback(){
							document.getElementById("validation_goBack_form").submit();
						}
						
						function mo_otp_verification_resend(){
							document.getElementById("verification_resend_otp_form").submit();
						}

						function mo_select_goback(){
							document.getElementById("goBack_choice_otp_form").submit();
						}
						var timer = function(secs){
							var sec_num = parseInt(secs, 10)    
							var hours   = Math.floor(sec_num / 3600) % 24
							var minutes = Math.floor(sec_num / 60) % 60
							var seconds = sec_num % 60    
							
							hours = hours < 10 ? "0" + hours : hours;
							minutes = minutes < 10 ? "0" + minutes : minutes;
							seconds = seconds < 10 ? "0" + seconds : seconds;
							return [hours,minutes,seconds].join(":")
						};
						//counter otp
						var counter = '.$otp_resend_timer.';
						var interval = setInterval(function() {
							counter--;
							document.getElementById("timer").innerHTML = timer(counter)+ " sec";
							if (counter == 0) {
								clearInterval(interval);
								document.getElementById("timer").style.display = "none";
								var cssString = "pointer-events: auto; cursor: pointer; opacity: 1; float:right"; 
								document.getElementById("verify_otp").style.cssText = cssString;
							}
						}, 1000);
					</script>
				</body>
		    </html>';
?>			