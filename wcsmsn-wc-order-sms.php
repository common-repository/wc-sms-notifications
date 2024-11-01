<?php
/*
 * Plugin Name: WooCommerce SMS Notifications
 * Plugin URI: https://wordpress.org/plugins/wc-sms-notifications/
 * Description: Get order notifications for your WooCommerce orders. By Using this plugin admin and buyer can get notification after placing order via sms using WC SMS Notifications by freebulksmsonline.com.
 * Version: 1.6.1
 * Author: mbomnda @ freebulksmsonline.com 
 * Author URI: https://freebulksmsonline.com/
 * WC requires at least: 2.0.0
 * WC tested up to: 4.9.1
 * Text Domain: wc-sms-notifications
 * License: GPLv2
 */

/**
 * 
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;
function wcsmsn_sanitize_array($arr) 
{
	global $wp_version;
	$older_version = ($wp_version<'4.7') ? true:false; 
	$result = array();
	foreach ($arr as $key => $val)
	{
		$result[$key] = is_array($val) ? wcsmsn_sanitize_array($val) : (($older_version) ? sanitize_text_field($val) : sanitize_textarea_field($val));
	}
	return $result;
}

function create_wcsmsn_cookie($cookie_key,$cookie_value)
{
	ob_start();
	setcookie($cookie_key,$cookie_value, time()+(15 * 60));
	ob_get_clean();
}
	
function clear_wcsmsn_cookie($cookie_key)
{	
	if(isset($_COOKIE[$cookie_key])){
		unset($_COOKIE[$cookie_key]);
		setcookie( $cookie_key, '', time() - ( 15 * 60 ) );
	}
}

function get_wcsmsn_cookie($cookie_key)
{
	if(!isset($_COOKIE[$cookie_key])) {
	  return false;
	} else {
	  return $_COOKIE[$cookie_key];
	}
}
 
function wcsmsn_get_option( $option, $section, $default = '' ) {
    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }
    return $default;
}

function get_wcsmsn_template($filepath,$datas)
{
		ob_start();
		extract($datas);
		include(plugin_dir_path( __DIR__ ).'wc-sms-notifications/'.$filepath);
		return ob_get_clean();
}

class wcsmsn_WC_Order_SMS {
	
    /**
     * Constructor for the wcsmsn_WC_Order_SMS class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses is_admin()
     * @uses add_action()
     */
	 
	public static function localization_setup() {
		load_plugin_textdomain( 'wc-sms-notifications', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	
    public function __construct() {
		
		// Instantiate necessary class
        $this->instantiate();
		add_action( 'init', array( $this, 'localization_setup' ) );
		add_action( 'init', array($this, 'register_hook_send_sms'));
        
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'buyer_notification_update_order_meta' ) );
		add_action( 'woocommerce_order_status_changed', array( 'WooCommerceCheckOutForm', 'trigger_after_order_place' ), 10, 3 );
		add_action('woocommerce_new_order', array( $this, 'wcsmsn_wc_order_place'),10,1);
		
		if ( is_plugin_active( 'gravityforms-master/gravityforms.php' ) || is_plugin_active('gravityforms/gravityforms.php' ))
		{
			require_once 'handler/forms/gravity-form.php';
		}		

		require_once 'helper/formlist.php';
		require_once 'views/common-elements.php';
		require_once 'handler/forms/FormInterface.php';
		require_once 'handler/wcsmsn_form_handler.php';
		
		if(is_admin())
		{
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta_link' ), 10, 4 );
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links') );
		}

		/*code to notify for daily balance begins */
		add_action ('wcsmsn_balance_notify',array($this, 'background_task'));
		self::wcsmsn_sync_grp_action();
	}
	
    /**
     * Instantiate necessary Class
     * @return void
     */
    function instantiate() {
		spl_autoload_register( array($this, 'wcsmsn_sms_autoload') );
        new wcsmsn_Setting_Options();
    }

	/**
	 * Autoload class files on demand
	 *
	 * @param string $class requested class name
	 */	
	function wcsmsn_sms_autoload( $class ) {

		require_once 'handler/wcsmsn_logic_interface.php';
		require_once 'handler/wcsmsn_phone_logic.php';
		require_once 'helper/sessionVars.php';
		require_once 'helper/utility.php';
		require_once 'helper/constants.php';
		require_once 'helper/messages.php';
		require_once 'helper/curl.php';
		
		if ( stripos( $class, 'wcsmsn_' ) !== false ) {

			$class_name = str_replace( array('wcsmsn_', '_'), array('', '-'), $class );
			$filename = dirname( __FILE__ ) . '/classes/' . strtolower( $class_name ) . '.php';
			
			if ( file_exists( $filename ) ) {
				require_once $filename;
				
			}
		}
		
		
	}
	
    /**
     * Initializes the wcsmsn_WC_Order_SMS() class
     *
     * Checks for an existing wcsmsn_WC_Order_SMS() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new wcsmsn_WC_Order_SMS();
        }
        return $instance;
    }
	
	function fn_wcsmsn_send_sms($number, $content)
	{
		$obj=array();
		$obj['number'] = $number;
		$obj['sms_body'] = $content;
		$response = wcsmsncURLOTP::sendsms($obj);
		return $response;
	}

	function register_hook_send_sms()
	{
		add_action( 'wcsmsn_send_sms', array($this, 'fn_wcsmsn_send_sms'), 10, 2 ); 
	}
	
    public function admin_enqueue_scripts() {

        wp_enqueue_style( 'admin-wcsmsn-styles', plugins_url( 'css/admin.css', __FILE__ ), array(),wcsmsnConstants::wcsmsn_VERSION );
        wp_enqueue_script( 'admin-wcsmsn-scripts', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), wcsmsnConstants::wcsmsn_VERSION, true );
		wp_enqueue_script( 'admin-wcsmsn-taggedinput', plugins_url( 'js/tagged-input.js', __FILE__ ), array( 'jquery' ), wcsmsnConstants::wcsmsn_VERSION, false );

        wp_localize_script( 'admin-wcsmsn-scripts', 'wcsmsn', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        ) );
    }

    public function plugin_row_meta_link( $plugin_meta, $plugin_file, $plugin_data, $status ) 
	{
        if( isset( $plugin_data['slug'] ) && ( $plugin_data['slug'] == 'wc-sms-notifications' ) && ! defined( 'wcsmsn_DIR' ) ) {			
			$plugin_meta[] = '<a href="https://freebulksmsonline.com/sms-bulksms-api/" target="_blank">'.__('Docs',wcsmsnConstants::TEXT_DOMAIN).'</a>';
			$plugin_meta[] = '<a href="https://wordpress.org/support/plugin/wc-sms-notifications/reviews/#postform" target="_blank" class="wc-rating-link">★★★★★</a>';
        }
        return $plugin_meta;
    }
	
	function add_action_links ( $links ) {
		$links[] = sprintf('<a href="%s">Settings</a>', admin_url('admin.php?page=wc-sms-notifications') );
		return $links;
	}
	
	
	
	//return only single credit i.e onlyone route(transactional credit)
	static function only_credit(){
		$trans_credit =array();
		$credits = json_decode(wcsmsncURLOTP::get_credits(),true);   //credit json
		if(is_array($credits['description']) && array_key_exists('routes', $credits['description']))
		{
			foreach($credits['description']['routes'] as $credit){
				//if($credit['route']=='transactional'){
					$trans_credit[] = $credit['credits'];
				//}
			}
		}
		return $trans_credit;
	 }
		
	static function run_on_activate()
	{
		if( !wp_next_scheduled( 'wcsmsn_balance_notify' ) )
		{
			wp_schedule_event( time(), 'hourly', 'wcsmsn_balance_notify');
		}
	}

	static function run_on_deactivate()
	{
		wp_clear_scheduled_hook('wcsmsn_balance_notify');
	}

	function background_task()
	{
		$low_bal_alert = wcsmsn_get_option( 'low_bal_alert', 'wcsmsn_general', 'off');
		$daily_bal_alert = wcsmsn_get_option( 'daily_bal_alert', 'wcsmsn_general', 'off');
		$user_authorize = new wcsmsn_Setting_Options();
		$islogged = $user_authorize->isUserAuthorised();
		$auto_sync = wcsmsn_get_option( 'auto_sync', 'wcsmsn_general', 'off');
		if($islogged == true) 
		{
			if($auto_sync == 'on')
			{
				self::sync_customers();
			}
		}
		if($low_bal_alert == 'on'){self::send_wcsmsn_balance();}
		if($daily_bal_alert == 'on'){self::daily_email_alert();}
		
	}
	
	function wcsmsn_sync_grp_action()
	{
		if(array_key_exists('option', $_GET) && $_GET['option'])
		{
			switch (trim($_GET['option'])) 
			{
				case 'wcsmsn-group-sync':
				self::sync_customers();
				exit();
				break;			
			}
		}
	}
	
	static function sync_customers()
	{
		$group_name 	= wcsmsn_get_option( 'group_auto_sync', 'wcsmsn_general', '');
		$update_id 		= wcsmsn_get_option( 'last_sync_userId','wcsmsn_sync','');
		$username 		= wcsmsn_get_option( 'wcsmsn_name', 'wcsmsn_gateway' );
		$password 		= wcsmsn_get_option( 'wcsmsn_password', 'wcsmsn_gateway' );
		if(empty($group_name))
			return;
		
		$update_id 		= ($update_id!='') ? $update_id : 0;
		global $wpdb;
		
		$sql 			= $wpdb->prepare(
		"SELECT ID FROM {$wpdb->users} WHERE {$wpdb->users}.ID > %d order by ID asc limit 100",
		$update_id
		); 
		
		$uids = $wpdb->get_col( $sql );
		if(sizeof($uids)==0)
		{
			 echo json_encode(array('status'=>'success','description'=>array('cnt_member'=>0)));
		}
		else
		{
			$user_query = new WP_User_Query( array( 'include' => $uids  ,'orderby' => 'id', 'order' => 'ASC') );
			if ( $user_query->get_results()) {
				$cnt = 0;
				$obj=array();
				foreach ( $user_query->get_results() as $ukey => $user ) 
				{
					$number = get_user_meta($user->ID, 'billing_phone', true);
					$obj[$ukey]['person_name'] 	= $user->display_name;
					$obj[$ukey]['number'] 		= $number;
					$last_sync_id = $user->ID;
					$cnt++;
				}
				$resp = wcsmsncURLOTP::create_contact($obj,$group_name);
				update_option('wcsmsn_sync',array('last_sync_userId'=>$last_sync_id));//update last_sync_id
				$result = (array)json_decode($resp,true);
				if($result['status']=='success'){
					echo json_encode(array('status'=>'success','description'=>array('cnt_member'=>$cnt)));
				}
			 } else {
				 echo json_encode(array('status'=>'success','description'=>array('cnt_member'=>0)));
				
			 }
		}
	}
	
	static function send_wcsmsn_balance()
	{
		$date = date("Y-m-d");	
		$update_dateTime = wcsmsn_get_option( 'last_updated_lBal_alert','wcsmsn_background_task','');
		
		if($update_dateTime == $date)
		{
			return;
		}
		 
		$username 		= wcsmsn_get_option( 'wcsmsn_name', 'wcsmsn_gateway', '');  //wcsmsn auth username
		$low_bal_val 	= wcsmsn_get_option( 'low_bal_val', 'wcsmsn_general', '1000');//from alert box
		$To_mail		= wcsmsn_get_option( 'alert_email', 'wcsmsn_general', '');
		$trans_credit 	= self::only_credit();
		
		if(!empty($trans_credit)){
			
			foreach($trans_credit as $credit){
				//Email template with content
				$params = array(
					'trans_credit' => $credit,
					'username' => $username,
					'admin_url' => admin_url(),
				);
				$emailcontent = get_wcsmsn_template('template/emails/wcsmsn-low-bal.php',$params);

				if($credit <= $low_bal_val)
				{
					wp_mail( $To_mail, '❗ ✱ WC SMS Notifications ✱ Low Balance Alert', $emailcontent,'content-type:text/html');
				}
			}
			
			update_option('wcsmsn_background_task',array('last_updated_lBal_alert'=>date('Y-m-d')));//update last time and date			
		}
	}

	function daily_email_alert(){
		$username = wcsmsn_get_option( 'wcsmsn_name', 'wcsmsn_gateway', '');  //wcsmsn auth username
		$date = date("Y-m-d");	
		$To_mail=wcsmsn_get_option( 'alert_email', 'wcsmsn_general', '');
		$update_dateTime = wcsmsn_get_option( 'last_updated_dBal_alert','wcsmsn_background_dBal_task','');
		
		if($update_dateTime == $date)
		{
			return;
		}
		
		$daily_credits = self::only_credit();
		
		if(!empty($daily_credits)){
			
			foreach($daily_credits as $credit){
				//email content
				$params = array(
						'daily_credits' => $credit,
						'username' => $username,
						'date' => $date,
						'admin_url' => admin_url(),
				);
				$dailyemailcontent = get_wcsmsn_template('template/emails/daily_email_alert.php',$params);
				update_option('wcsmsn_background_dBal_task',array('last_updated_dBal_alert'=>date('Y-m-d')));//update last time and date 
				wp_mail($To_mail, '✱ WC SMS Notifications ✱ Daily  Balance Alert ',$dailyemailcontent,'content-type:text/html');
			}
		}
	 }	
    /**
     * Update Order buyer notify meta in checkout page
     * @param  integer $order_id
     * @return void
     */
    function buyer_notification_update_order_meta( $order_id ) {
        if ( ! empty( $_POST['buyer_sms_notify'] ) ) {
            update_post_meta( $order_id, '_buyer_sms_notify', sanitize_text_field( $_POST['buyer_sms_notify'] ) );
        }
    }
	
	public function wcsmsn_wc_order_place($order_id) {
		if (!$order_id) {
           return;
		}
		WooCommerceCheckOutForm::trigger_after_order_place( $order_id, 'pending', 'pending' );
	}
} // wcsmsn_WC_Order_SMS

/**
 * Loaded after all plugin initialize
 */
add_action( 'plugins_loaded', 'load_wcsmsn_wc_order_sms' );

function load_wcsmsn_wc_order_sms() {
    $wcsmsn = wcsmsn_WC_Order_SMS::init();
}

register_activation_hook( __FILE__, 	array('wcsmsn_WC_Order_SMS', 'run_on_activate'));
register_deactivation_hook( __FILE__, 	array('wcsmsn_WC_Order_SMS', 'run_on_deactivate'));
?>