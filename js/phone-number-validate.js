jQuery(document).ready(function() {
    var $ = jQuery;
    var country= $("#billing_country").val();
	
    var input = document.querySelector("#billing_phone, .phone-valid");
    var errorMap = ["Invalid number", "Invalid country code", "Please provide a valid Number", "Please provide a valid Number", "Invalid number"];
    $("#billing_phone_field").append("<p id='phone_error' class='error'></p>");
	$(".phone-valid").after("<p id='phone_error' class='error' style='display:none'></p>");
	/* var iti= window.intlTelInput(input, {
        initialCountry: country,
        utilsScript: "/utils.js"
    }); */

	jQuery("#billing_phone, .phone-valid").attr("placeholder", "Enter Number Here");
	var iti = jQuery("#billing_phone, .phone-valid").intlTelInput({		
		initialCountry: country,
		separateDialCode: true,
		nationalMode: true,
		utilsScript: "/utils.js?v=3.3.1"
	});	
	
	jQuery('#billing_country').change(function(){
		iti.intlTelInput("setCountry",$(this).val())
		onChangeCheckValidno();
	});
	
	jQuery('.woocommerce-checkout').on('checkout_place_order',function(){     
	   jQuery("#billing_phone").val(jQuery("#billing_phone").intlTelInput("getNumber"));
	});
	
	var reset = function() {
        $("#phone_error").text("");
    };	
	
	function onChangeCheckValidno()
	{
		reset();
        if (input.value.trim()) {
            if (iti.intlTelInput('isValidNumber')) {
				jQuery("#wcsmsn_otp_token_submit").attr("disabled",false);
				jQuery("#wcsmsn_bis_submit").attr("disabled",false);
            } else {
                var errorCode = iti.intlTelInput('getValidationError');
                input.focus();
                $("#phone_error").text(errorMap[errorCode]);
				jQuery("#wcsmsn_otp_token_submit").attr("disabled",true);
				$("#phone_error").removeAttr("style");
				jQuery("#wcsmsn_bis_submit").attr("disabled",true);
				
			}
        }
	}

    jQuery("#billing_phone").blur(function() {
        onChangeCheckValidno();
    });
	
	//backinstock form
	jQuery('.wcsmsn_bis_submit').click(function(){
	   jQuery(".phone-valid").val(jQuery(".phone-valid").intlTelInput("getNumber"));
	});
	jQuery(".phone-valid").blur(function() {
        onChangeCheckValidno();
    });
	
	// on keyup / change flag: reset
    jQuery("#billing_phone").change(function() {
        reset();
    });

});