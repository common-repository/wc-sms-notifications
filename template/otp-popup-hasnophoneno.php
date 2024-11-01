<?php
echo'	<html>
					<head>
						<meta http-equiv="X-UA-Compatible" content="IE=edge">
						<meta name="viewport" content="width=device-width, initial-scale=1">
						<link rel="stylesheet" type="text/css" href="' . $css_url . '" />
						<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
					</head>
					<body>
						<div class="mo-modal-backdrop">
							<div class="mo_customer_validation-modal" tabindex="-1" role="dialog" id="mo_site_otp_choice_form">
								<div class="mo_customer_validation-modal-backdrop"></div>
								<div class="mo_customer_validation-modal-dialog mo_customer_validation-modal-md">
									<div class="login mo_customer_validation-modal-content">
										<div class="mo_customer_validation-modal-header">
											<b>'.__("Validate your Phone Number",wcsmsnConstants::TEXT_DOMAIN).'</b>
											<a class="close" href="#" onclick="window.location =\''.$goBackURL.'\'" > &larr;
												'.__("Go Back",wcsmsnConstants::TEXT_DOMAIN).'</a>
										</div>
										<div class="mo_customer_validation-modal-body center">
											<div id="message">'.__($message,wcsmsnConstants::TEXT_DOMAIN).'</div><br /> ';
											if(!wcsmsnUtility::isBlank($user_email))
											{
		echo'									<div class="mo_customer_validation-login-container">
													<form name="f" id="validate_otp_form" method="post" action="">
														<input id="validate_phone" type="hidden" name="option" value="wcsmsn_ajax_form_validate" />
														<input type="hidden" name="form" value="'.$form.'" />
														<input type="text" name="mo_phone_number"  autofocus="true" placeholder="" 
															id="mo_phone_number" required="true" class="mo_customer_validation-textbox" 
															autofocus="true" pattern="^[\+]\d{1,4}\d{7,12}$|^[\+]\d{1,4}[\s]\d{7,12}$" 
															title="'.__("Enter a number in the following format",wcsmsnConstants::TEXT_DOMAIN).': 9xxxxxxxxx"/>
														<div id="mo_message" hidden="" 
															style="background-color: #f7f6f7;padding: 1em 2em 1em 1.5em;color:black;"></div><br/>
														<div id="mo_validate_otp" hidden>
															'.__("Verify Code ",wcsmsnConstants::TEXT_DOMAIN).' <input type="number" 
															name="wcsmsn_customer_validation_otp_token"  autofocus="true" placeholder="" 
															id="wcsmsn_customer_validation_otp_token" required="true" 
															class="mo_customer_validation-textbox" autofocus="true" pattern="[0-9]{4,8}" 
															title="'.wcsmsnMessages::showMessage('OTP_RANGE').'"/>
														</div>
														<input type="button" hidden id="validate_otp" name="otp_token_submit" 
															class="miniorange_otp_token_submit"  value="Validate" />
														<input type="button" id="send_otp" class="miniorange_otp_token_submit" 
															value="'.wcsmsnMessages::showMessage('SEND_OTP').'" />';
														wcsmsn_extra_post_data($usermeta);
		echo'										</form>
												</div>';
											}
		echo'							</div>
									</div>
								</div>
							</div>
						</div>
						<style> .mo_customer_validation-modal{ display: block !important; } </style>
						<script>
							jQuery(document).ready(function() {
							    $mo = jQuery;
							    $mo("#send_otp").click(function(o) {
							        var e = $mo("input[name=mo_phone_number]").val();
							        $mo("#mo_message").empty(), $mo("#mo_message").append("'.$img.'"), $mo("#mo_message").show(), $mo.ajax({
							            url: "'.site_url().'/?option=wcsmsn-ajax-otp-generate",
							            type: "POST",
							            data: {billing_phone:e},
							            crossDomain: !0,
							            dataType: "json",
							            success: function(o) {
							                if (o.result == "success") {
							                    $mo("#mo_message").empty(), $mo("#mo_message").append(o.message), 
							                    $mo("#mo_message").css("background-color", "#8eed8e"), 
							                    $mo("#validate_otp").show(), $mo("#send_otp").val("Resend OTP"), 
							                    $mo("#mo_validate_otp").show(), $mo("input[name=mo_validate_otp]").focus()
							                } else {
							                    $mo("#mo_message").empty(), $mo("#mo_message").append(o.message), 
							                    $mo("#mo_message").css("background-color", "#eda58e"), 
							                    $mo("input[name=mo_phone_number]").focus()
							                };
							            },
							            error: function(o, e, n) {}
							        })
							    });
								$mo("#validate_otp").click(function(o) {
							        var e = $mo("input[name=wcsmsn_customer_validation_otp_token]").val();
							        var f = $mo("input[name=mo_phone_number]").val();
							        var r = $mo("input[name=redirect_to]").val();
							        $mo("#mo_message").empty(), $mo("#mo_message").append("'.$img.'"), $mo("#mo_message").show(), $mo.ajax({
							            url: "'.site_url().'/?option=wcsmsn-ajax-otp-validate",
							            type: "POST",
							            data: {wcsmsn_customer_validation_otp_token: e,billing_phone:f,redirect_to:r},
							            crossDomain: !0,
							            dataType: "json",
							            success: function(o) {
							                if (o.result == "success") {
							                    $mo("#mo_message").empty(), $mo("#mo_message").append(o.message), $mo("#validate_phone").remove(), $mo("#validate_otp_form").submit()
							                } else {
							                    $mo("#mo_message").empty(), $mo("#mo_message").append(o.message), 
							                    $mo("#mo_message").css("background-color", "#eda58e"), 
							                    $mo("input[name=validate_otp]").focus()
							                };
							            },
							            error: function(o, e, n) {}
							        })
							    });
							});
						</script>
					</body>
			    </html>';
?>