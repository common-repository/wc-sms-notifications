=== WooCommerce SMS Notifications - Order Alerts and Notifications, 2FA ===

Contributors: mbomnda @ freebulksmsonline.com
Tags: order notification, order SMS, SMS order, notification, order notifications, seller notification, sms, transactional sms, sms alert, sms india, one-time sms, woocommerce sms, woocommerce order sms, woocommerce order notification, woocommerce sms, sms integration, sms plugin, wcsmsn – WooCommerce, sms notification, two-step-verification, otp, mobile verification, verification, mobile, phone, sms, one time, password, sms verification, woocommerce, one time passcode, passcode
Requires at least: 4.6
Tested up to: 5.6
Stable tag: 1.6.1
Requires PHP: 5.6
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a plugin to send SMS notifications to both customers and website owner when order status changes for WooCommerce using SMS service provided by freebulksmsonline.com

== Description ==

This is a WooCommerce add-on Powered by freebulksmsonline.com. By Using this plugin admin and buyer can get notification about their order via sms freebulksmsonline.com Gateway. This add-on service sends order notifications to both customers and admins. You can sign up for free to get demo credits or use the demo credentials provided in the admin/settings panel.

The WooCommerce Order SMS Notification plugin for WordPress is very useful, when you want to get notified via SMS after placing an order. Buyer and seller both can get SMS notification after an order is placed. SMS notification options can be customized in the admin panel very easily.

SMS Notifications Service is provided by freebulksmsonline.com. This service requires registration on the freebulksmsonline.com website or the use of the demo credentials provided in this plugin. The privacy policy of freebulksmsonline.com can be found on this link.

https://freebulksmsonline.com/privacy/

= WooCommerce SMS Notifications (Key Features) =

> + OTP for order confirmation(with option to enable OTP only for COD orders)
> + OTP verification for registration
> + Login with OTP
> + Reset password with OTP
> + OTP verification for login(option to enable OTP only for selected roles)
> + SMS to Customer and Admin on new user registration/signup
> + Admin/Post Author can get Order SMS notifications
> + Buyer can get order sms notifications supports custom template
> + Sending Order Details ( order no, order status, order items and order amount ) in SMS text
> + Different SMS template corresponding to different Order Status
> + Directly contact with buyer via SMS through order notes, and custom sms available on order detail page
> + All order status supported(Pending, On Hold, Completed, Cancelled)
> + Block multiple user registration with same mobile number
> + Supports wordpress Multisite
> + Custom Low Balance Alert
> + Option to disable sending OTP to a particular after n resends
> + Daily SMS Balance on Email
> + Sync Customers to Group on [www.freebulksmsonline.com](https://freebulksmsonline.com/)
> + Auto Shorten URL

= Compatibility =

👉 [Sequential Order Numbers Pro](https://woocommerce.com/products/sequential-order-numbers-pro/)
👉 [WooCommerce Order Status Manager](https://woocommerce.com/products/woocommerce-order-status-manager/)
👉 [Admin Custom Order Fields](https://woocommerce.com/products/admin-custom-order-fields/)
👉 [Shipment Tracking](https://woocommerce.com/products/shipment-tracking/)
👉 [Advanced Shipment Tracking for WooCommerce](https://wordpress.org/plugins/woo-advanced-shipment-tracking/)
👉 [Aftership - WooCommerce Tracking](https://wordpress.org/plugins/aftership-woocommerce-tracking/)
👉 [Ultimate Member](https://wordpress.org/plugins/ultimate-member/)
👉 [Pie Register](https://wordpress.org/plugins/pie-register/)
👉 [WP-Members Membership Plugin](https://wordpress.org/plugins/wp-members/)
👉 [Dokan Multivendor Marketplace](https://wordpress.org/plugins/dokan-lite/)
👉 [WC Marketplace](https://wordpress.org/plugins/dc-woocommerce-multi-vendor/)
👉 [WooCommerce PDF Invoices & Packing Slips](https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/) to send invoice on SMS
👉 [Claim GST for Woocommerce](https://wordpress.org/plugins/claim-gst/) for Input tax credit
👉 [Order Delivery Date for WooCommerce](https://wordpress.org/plugins/order-delivery-date-for-woocommerce/)
👉 [WooCommerce Multi-Step Checkout](https://wordpress.org/plugins/wp-multi-step-checkout/)

= Integrations =

👨 [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) to send notification to customer and admins, and verify mobile number through OTP
👨 [Gravity Forms](https://www.gravityforms.com/) to send notification to customer and admins
👨 [Returns and Warranty Requests](https://woocommerce.com/products/warranty-requests/) to send RMA status update to customer
👨 [Easy Digital Downloads](https://wordpress.org/plugins/easy-digital-downloads/) to send notification to customer
👨 [Affiliates Manager](https://wordpress.org/plugins/affiliates-manager/) to send notification to Affiliates and admin
👨 [WooCommerce Bookings](https://woocommerce.com/products/woocommerce-bookings/) to send booking confirmation to customers and admin
👨 [LearnPress – WordPress LMS Plugin](https://wordpress.org/plugins/learnpress/) to send notifications to student and admin
👨 [Events Manager](https://wordpress.org/plugins/events-manager/) to send event booking confirmation to customer and admin

== Installation ==

= New Install via FTP =

1. Unzip the package WC SMS Notifications.zip on you computer.
2. Upload the unzipped folder the /wp-content/plugins/ directory.
3. Activate the plugin through the Plugins menu in WordPress.
4. Goto Woocommerce settings and configure your WC SMS Notifications.

= New Install via the WordPress uploader =

1. Click Plugins > Add New inside of your WordPress install.
2. Click Upload and select the package WC-SMS-Notifications.zip
3. Activate the plugin through the Plugins menu in WordPress.
4. Goto Woocommerce settings and configure your WC SMS Notifications.

= Updating via the FTP =

1. If you've made any custom changes to the core you'll need to merge those changes into the new package.
2. Unzip the package WC SMS Notifications.zip on you computer.
3. Replace the current folder in your plugins folder with the new unzipped folder from your computer.

== Frequently Asked Questions ==

= Can i integrate my own sms gateway? =

There is no provision to integrate any other SMS Gateway, we only support [Free SMS BulkSMS API](http://www.freebulksmsonline.com/) SMS Gateway.

= How do i change Sender id? =

You can request the sender id after login to your [Free SMS BulkSMS API](http://www.freebulksmsonline.com/) account, from manage sender id.

Sender id is only available for transactional account.


= I am unable to login to my wordpress admin =

This can happen in two cases like you do not have sms credits in your sms alert account, or your admin profile has some other number registered, for both cases you can rename the plugin directory in your wordpress plugin directory via FTP, to disable the plugin

= Which all countries do you support sms? =

We support sms to over 200 countries:


= Can i send sms to multiple countries from one account? =

Yes, you can send sms to multiple countries.

= How can i use my custom variables in sms templates? =

The plugin supports custom order post meta, if your post meta key is '_my_custom_key', then you can access it in sms templates as [my_custom_key]

= Can i extend the functionality of this plugin? =

Sure, you can use our below hooks.

**To Send SMS**

~~~~
do_action('wcsmsn_api_send_sms', '918010551055', 'Here is the sms.');
~~~~

**To Modify Parameters before sending any SMS**

~~~~
function modify_sms_text($params)
{    
    //do your stuff here
	return $params;    
}
add_filter('wcsmsn_before_send_sms', 'modify_sms_text');
~~~~

**To get WC SMS Notifications Service Response after Send SMS**

~~~~
function get_wcsmsn_response($params)
{ 
	//do your stuff here
	return $params;
}
add_filter('wcsmsn_after_send_sms', 'get_wcsmsn_response');
~~~~

= Can you customise the plugin for me? =

Please use wordpress [support forum](https://wordpress.org/support/plugin/wc-sms-notifications) for new feature request, our development team may consider it in future updates. Please note we do not have any plans to develop any integrations for any paid plugins, if still you need it someone like you must sponser the update :-)

== Screenshots ==

1. General Settings - Login with your www.freebulksmsonline.com username and password.
2. General Settings - Successful Login.
3. Account Credits
4. Customer Templates - Set sms templates for every order status, these will be sent to the customers.
5. Admin SMS Templates - Set sms templates that admin will receive, set admin mobile number from advanced settings.
6. Advanced Settings - Enable or disable daily balance alert, low balance alert, admin mobile number, and many other advanced options.
7. Buy account credits from freebulksmsonline.com

== Changelog ==

= 1.0 =
* Initial version released

= 1.5 =
* added strings for translation
* Bugfix: low balance alert for international users
* order date now accepts custom format in parameter
* Integration with Delivery Drivers for WooCommerce
* Show country code selector on phone field
* Auto fill logged in users mobile number for back in stock subscription
* added action wcsmsn_wc_order_sms_before_send for plugin extension
* Bugfix: some order status sms were not working if OTP was disabled
* Integration with Ninja Forms
* supports dynamic variables from order item meta
* Disabled multiple message for sub order(for multivendor stores)
* Plugin version is now hardcoded, for better performance
* Login with OTP and standard login, both can work now simultaneously
* Bugfix: force prefix not adding in back in stock notifier
* Bugfix: Multivendor SMS not going to vendor in case ordered from only one vendor
* Bugfix: Eventbooking hooks changed as per latest version
* Compatibility check with woocommerce v-4.3.1
* Back in stock notifier compatibility fix with variable product template
* Dynamic variable explorer for woocommerce
* Country code enable/disable moved to advanced settings
* EDD: added download link variable
* Role Based SMS template for signup
* Bugfix: Low balance alert email, credit not showing
* Bugfix: Multivendor, sms not going to admin, when enabled for both admin and vendor
* Back in stock notifier minor design changes
* Bugfix: Dynamic variable explorer for woocommerce, nested values
* Integration with User Registration Plugin
* Integration with Booking Calendar
* Translation plugins compatibility fix
* Added country code selector for login with otp
* Code cleanup

= 1.5.1 =
* Bugfix: Notification not sent

= 1.6 =
* Bugfix: Notification not sent
* Bugfix: language

= 1.6.1 =
* Wordpress Update


== Support ==

Since this plugin is dependent on www.freebulksmsonline.com, we provide 24X7 email support for this plugin via info@freebulksmsonline.com. For new feature requests please use wordpress [support forum](https://wordpress.org/support/plugin/wc-sms-notifications).

== Translations ==

* English - default


