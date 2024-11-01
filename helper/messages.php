<?php
if (! defined( 'ABSPATH' )) exit;
class wcsmsnMessages
{
	function __construct()
	{
		//created an array instead of messages instead of constant variables for Translation reasons.
		define("SALRT_MESSAGES", serialize( array(
			//General Messages
			"OTP_RANGE" 									=> __("Only digits within range 4-8 are allowed.",wcsmsnConstants::TEXT_DOMAIN),
			"SEND_OTP"  									=> __("Send OTP",wcsmsnConstants::TEXT_DOMAIN),
			"RESEND_OTP"  									=> __("Resend OTP",wcsmsnConstants::TEXT_DOMAIN),
			"VALIDATE_OTP"  								=> __("Validate OTP",wcsmsnConstants::TEXT_DOMAIN),
			"RESEND"  										=> __("Resend",wcsmsnConstants::TEXT_DOMAIN),
			"Phone"  										=> __("Phone",wcsmsnConstants::TEXT_DOMAIN),
			"INVALID_OTP"  									=> __("Invalid one time passcode. Please enter a valid passcode.",wcsmsnConstants::TEXT_DOMAIN),
			"ENTER_PHONE_CODE"  							=> __("Please enter the verification code sent to your phone.",wcsmsnConstants::TEXT_DOMAIN),			
			
			//one time use message start			
			
			"DEFAULT_BUYER_SMS_PENDING" 					=> sprintf(__('Hello %s, you are just one step away from placing your order, please complete your payment, to proceed.',wcsmsnConstants::TEXT_DOMAIN), '[billing_first_name]'),
			"DEFAULT_ADMIN_SMS_CANCELLED" 					=> sprintf(__('%s Your order %s Rs. %s. is Cancelled.',wcsmsnConstants::TEXT_DOMAIN), '[store_name]:', '#[order_id]', '[order_amount]'),
			"DEFAULT_ADMIN_SMS_PENDING" 					=> sprintf(__('%s Hello, %s is trying to place order %s value Rs. %s',wcsmsnConstants::TEXT_DOMAIN), '[store_name]:', '[billing_first_name]', '#[order_id]', '[order_amount]'),			
			"DEFAULT_ADMIN_SMS_ON_HOLD" 					=> sprintf(__('%s Your order %s Rs. %s. is On Hold Now.',wcsmsnConstants::TEXT_DOMAIN), '[store_name]:', '#[order_id]', '[order_amount]'),
			"DEFAULT_ADMIN_SMS_COMPLETED" 					=> sprintf(__('%s Your order %s Rs. %s. is completed.',wcsmsnConstants::TEXT_DOMAIN), '[store_name]:', '#[order_id]', '[order_amount]'),
			"DEFAULT_ADMIN_SMS_PROCESSING" 					=> sprintf(__('%s You have a new order %s for order value Rs. %s. Please check your admin dashboard for complete details.',wcsmsnConstants::TEXT_DOMAIN), '[store_name]:', '#[order_id]', '[order_amount]'),
			"DEFAULT_BUYER_SMS_PROCESSING"  				=> sprintf(__('Hello %s, thank you for placing your order %s with %s.',wcsmsnConstants::TEXT_DOMAIN), '[billing_first_name]', '#[order_id]', '[store_name]'),
			"DEFAULT_BUYER_SMS_COMPLETED" 					=> sprintf(__('Hello %s, your order %s with %s has been dispatched and shall deliver to you shortly.',wcsmsnConstants::TEXT_DOMAIN), '[billing_first_name]', '#[order_id]', '[store_name]'),			
			"DEFAULT_BUYER_SMS_ON_HOLD" 					=> sprintf(__('Hello %s, your order %s with %s has been put on hold, our team will contact you shortly with more details.',wcsmsnConstants::TEXT_DOMAIN), '[billing_first_name]', '#[order_id]', '[store_name]'),			
			"DEFAULT_BUYER_SMS_CANCELLED" 					=> sprintf(__('Hello %s, your order %s with %s has been cancelled due to some un-avoidable conditions. Sorry for the inconvenience caused.',wcsmsnConstants::TEXT_DOMAIN), '[billing_first_name]', '#[order_id]', '[store_name]'),			
			"DEFAULT_ADMIN_OUT_OF_STOCK_MSG" 				=> sprintf(__('%s Out Of Stock Alert For Product %s, current stock %s.',wcsmsnConstants::TEXT_DOMAIN), '[store_name]:', '[item_name]', '[item_qty]'),
			"DEFAULT_ADMIN_LOW_STOCK_MSG" 					=> sprintf(__('%s Low Stock Alert For Product %s, current stock %s.',wcsmsnConstants::TEXT_DOMAIN), '[store_name]:', '[item_name]', '[item_qty]'),
			
			"DEFAULT_AC_ADMIN_MESSAGE" 						=> sprintf(__('%s Product %s is left in cart by %s.',wcsmsnConstants::TEXT_DOMAIN), '[store_name]:', '[item_name]', '[name]', '[affiliate_id]', '#[order_id]'),			
			"DEFAULT_AC_CUSTOMER_MESSAGE" 					=> sprintf(__('Hello %s, Your Product %s is left in cart.',wcsmsnConstants::TEXT_DOMAIN), '[name]', '[item_name]'),
			"DEFAULT_ADMIN_SMS_STATUS_CHANGED" 				=> sprintf(__('%s status of order %s has been changed to %s.',wcsmsnConstants::TEXT_DOMAIN), '[store_name]:', '#[order_id]', '[order_status]'),
			//one time use message end
			
			//not in use start			
			"OTP_INVALID_NO" 								=> sprintf(__('your verification code is %s. Only valid for %s min.',wcsmsnConstants::TEXT_DOMAIN), '[otp]', '15'),
			"OTP_ADMIN_MESSAGE" 							=> sprintf(__('You have a new Order%sThe %s is now %s',wcsmsnConstants::TEXT_DOMAIN), PHP_EOL, '[order_id]', '[order_status]'.PHP_EOL),
			"OTP_BUYER_MESSAGE" 							=> sprintf(__('Thanks for purchasing%sYour %s is now %sThank you',wcsmsnConstants::TEXT_DOMAIN), PHP_EOL, '[order_id]', '[order_status]'.PHP_EOL),			
			//not in use end
			
			//two time and three time start			
			"DEFAULT_BUYER_SMS_STATUS_CHANGED" 				=> sprintf(__('Hello %s, status of your order %s with %s has been changed to %s.',wcsmsnConstants::TEXT_DOMAIN), '[billing_first_name]', '#[order_id]', '[store_name]', '[order_status]'),
			"DEFAULT_BUYER_NOTE" 							=> sprintf(__('Hello %s, a new note has been added to your order %s %s',wcsmsnConstants::TEXT_DOMAIN), '[billing_first_name]', '#[order_id]:', '[note]'),
			"DEFAULT_BUYER_OTP" 							=> sprintf(__('Your verification code is %s',wcsmsnConstants::TEXT_DOMAIN), '[otp]'),
			"OTP_SENT_PHONE" 								=> sprintf(__('A OTP (One Time Passcode) has been sent to %sphone%s . Please enter the OTP in the field below to verify your phone.',wcsmsnConstants::TEXT_DOMAIN), '##', '##'),			
			"DEFAULT_WPAM_ADMIN_SMS_STATUS_CHANGED" 		=> sprintf(__('%s status of order %s has been changed to %s.',wcsmsnConstants::TEXT_DOMAIN), '[store_name]:', '#[affiliate_id]', '[affiliate_status]'),
			"DEFAULT_WPAM_BUYER_SMS_TRANS_STATUS_CHANGED" 	=> sprintf(__('Hello %s,commission has been %s for %s to your affiliate account %s against order %s.',wcsmsnConstants::TEXT_DOMAIN), '[first_name]', '[transaction_type]', '[commission_amt]', '[affiliate_id]', '#[order_id]'),
			"DEFAULT_WPAM_ADMIN_SMS_TRANS_STATUS_CHANGED" 	=> sprintf(__('%s commission has been %s for %s to affiliate account %s against order %s.',wcsmsnConstants::TEXT_DOMAIN), '[store_name]:', '[transaction_type]', '[commission_amt]', '[affiliate_id]', '#[order_id]'),			
			"DEFAULT_ADMIN_NEW_USER_REGISTER"     			=> sprintf(__('%s New user signup.%sName: %sEmail: %sPhone: %s',wcsmsnConstants::TEXT_DOMAIN), '[store_name]:', PHP_EOL, '[username]'.PHP_EOL, '[email]'.PHP_EOL, '[billing_phone]'),
			"PHONE_NOT_FOUND" 								=> __('Sorry, but you do not have a registered phone number.',wcsmsnConstants::TEXT_DOMAIN),			
			"PHONE_MISMATCH" 								=> __('The phone number OTP was sent to and the phone number in contact submission do not match.',wcsmsnConstants::TEXT_DOMAIN),
			//two time and three time end
		
		
			"DEFAULT_USER_COURSE_ENROLL" 					=> sprintf(__('Congratulation %s, you have enrolled course - %s',wcsmsnConstants::TEXT_DOMAIN), '[username]', '[course_name]'),
			"DEFAULT_NEW_USER_REGISTER" 					=> sprintf(__('Hello %s, Thank you for registering with %s.',wcsmsnConstants::TEXT_DOMAIN), '[username]', '[store_name]'),			
			"DEFAULT_ADMIN_COURSE_FINISHED" 				=> sprintf(__('Hi Admin %s has finished course - %s',wcsmsnConstants::TEXT_DOMAIN), '[username]', '[course_name]'),			
			"DEFAULT_USER_COURSE_FINISHED" 					=> sprintf(__('Congratulation you have finished course - %s',wcsmsnConstants::TEXT_DOMAIN), '[course_name]'),			
			"DEFAULT_ADMIN_NEW_TEACHER_REGISTER" 			=> sprintf(__('Hi admin, an instructor %s has been joined.',wcsmsnConstants::TEXT_DOMAIN), '[username]'),			
			"DEFAULT_ADMIN_COURSE_ENROLL" 					=> sprintf(__('Hi Admin %s has enrolled course - %s',wcsmsnConstants::TEXT_DOMAIN), '[username]', '[course_name]'),
			"DEFAULT_NEW_TEACHER_REGISTER" 					=> sprintf(__('Congratulation %s, You have become an instructor.',wcsmsnConstants::TEXT_DOMAIN), '[username]'),

			"DEFAULT_BOOKING_CALENDAR_CUSTOMER" 			=> sprintf(__('Congratulation %s, You have become an instructor.',wcsmsnConstants::TEXT_DOMAIN), '[username]'),
			"DEFAULT_BOOKING_CALENDAR_CUSTOMER_PENDING" 	=> sprintf(__('Dear %s, thank you for scheduling your booking with us on %s.',wcsmsnConstants::TEXT_DOMAIN), '[name]', '[date]'),
			"DEFAULT_BOOKING_CALENDAR_CUSTOMER_APPROVED" 	=> sprintf(__('Dear %s, your booking is confirmed for %s with us.',wcsmsnConstants::TEXT_DOMAIN), '[name]', '[date]'),
			"DEFAULT_BOOKING_CALENDAR_CUSTOMER_TRASH" 		=> sprintf(__('Dear %s, we are sorry to inform your booking for %s has been rejected.',wcsmsnConstants::TEXT_DOMAIN), '[name]','[date]'),
			
			"DEFAULT_BOOKING_CALENDAR_ADMIN" 				=> sprintf(__('Congratulation %s, You have become an instructor.',wcsmsnConstants::TEXT_DOMAIN), '[username]'),
			"DEFAULT_BOOKING_CALENDAR_ADMIN_PENDING" 		=> sprintf(__('Dear Admin, you have a new booking from %s for %s. Please check admin dashboard for complete details.',wcsmsnConstants::TEXT_DOMAIN), '[name]','[date]'),
			"DEFAULT_BOOKING_CALENDAR_ADMIN_APPROVED" 		=> sprintf(__('Dear Admin, booking for %s is confirmed for %s.',wcsmsnConstants::TEXT_DOMAIN), '[name]','[date]'),
			"DEFAULT_BOOKING_CALENDAR_ADMIN_TRASH" 			=> sprintf(__('Dear Admin, booking from %s for %s has been rejected.',wcsmsnConstants::TEXT_DOMAIN), '[name]','[date]'),

			/*translation required*/
		)));
	}

	public static function showMessage($message , $data=array())
	{
		$displayMessage = "";
		$messages = explode(" ",$message);
		$msg = unserialize(SALRT_MESSAGES);
		//return __($msg[$message],wcsmsnConstants::TEXT_DOMAIN);
		return (!empty($msg[$message]) ? $msg[$message] : '');
		/* foreach ($messages as $message)
		{
			if(!wcsmsnUtility::isBlank($message))
			{
				//$formatMessage = constant( "self::".$message );
				$formatMessage = $msg[$message];
			    foreach($data as $key => $value)
			    {
			        $formatMessage = str_replace("{{" . $key . "}}", $value ,$formatMessage);
			    }
			    $displayMessage.=$formatMessage;
			}
		}
	    return $displayMessage; */
	}
}
new wcsmsnMessages;