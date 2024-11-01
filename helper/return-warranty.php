<?php
if (! defined( 'ABSPATH' )) exit;

class wcsmsn_Return_Warranty
{
	public function __construct() {
		add_filter( 'wcsmsnDefaultSettings',  __CLASS__ .'::addDefaultSetting',1);
		add_action( 'wcsmsn_addTabs', array( $this, 'addTabs' ), 10 );
		add_action( 'wc_warranty_settings_tabs', __CLASS__ .'::wcsmsn_warranty_tab'  );
		add_action( 'wc_warranty_settings_panels', __CLASS__ .'::wcsmsn_warranty_settings_panels'  );
		add_action( 'admin_post_wc_warranty_settings_update', array($this, 'update_wc_warranty_settings'),5 );
		add_action( 'wp_ajax_warranty_update_request_fragment', array($this, 'on_rma_status_update'),0 );
		add_action( 'wc_warranty_created',  array($this, 'on_new_rma_request'),5);
	}

	public static function getWarrantStatus()
	{
		if (!class_exists('WooCommerce_Warranty')) {
			return array();
		}

		$wc_warranty = new WooCommerce_Warranty();
		return $wc_warranty->get_default_statuses();
	}

	function update_wc_warranty_settings($data)
	{
		$options = $_POST;
		if($options['tab'] == 'wcsmsn_warranty')
		{
			foreach($options as $name => $value)
			{
				if(is_array($value))
				{
					foreach($value as $k => $v)
					{
						if(!is_array($v))
						{
							$value[$k] = stripcslashes($v);
						}
					}
				}
				update_option( $name, $value );
		    }
		}
	}

	function send_rma_status_sms($request_id,$status)
	{
		$wc_warranty_checkbox=wcsmsn_get_option('warranty_status_'.$status, 'wcsmsn_warranty','');
		$is_sms_enabled 	= ($wc_warranty_checkbox=='on')  ? true : false;
		if($is_sms_enabled)
		{
			$sms_content	= wcsmsn_get_option('sms_text_'.$status, 'wcsmsn_warranty','');
			$order_id 		= get_post_meta( $request_id, '_order_id', true );
			$rma_id 		= get_post_meta( $request_id, '_code', true );
			$order 			= wc_get_order( $order_id );
			global $wpdb;
			$products 		= $items = $wpdb->get_results( $wpdb->prepare(
							"SELECT *
							FROM {$wpdb->prefix}wc_warranty_products
							WHERE request_id = %d",
							$request_id
			), ARRAY_A );

			$item_name 		= '';
			foreach ( $products as $product ) {

				if ( empty( $product['product_id'] ) && empty( $item['product_name'] ) ) {
					continue;
				}

				if ( $product['product_id'] == 0 ) {
					$item_name .= $item['product_name'].', ';
				} else {
					$item_name .= warranty_get_product_title( $product['product_id'] ).', ';
				}
			}

			$item_name 					= rtrim($item_name, ', ');
			$sms_content 				= str_replace( '[item_name]', $item_name, $sms_content );
			$buyer_sms_data				= array();
			$buyer_mob   				= get_post_meta( $order_id, '_billing_phone', true );
			$message 					= WooCommerceCheckOutForm::pharse_sms_body($sms_content, $status, $order, '', $rma_id);
			do_action('wcsmsn_send_sms', $buyer_mob, $message);
		}
	}

	function on_new_rma_request($warranty_id)
	{
		$this->send_rma_status_sms($warranty_id,"new");
	}

	function on_rma_status_update()
	{
		$request_id = $_POST['request_id'];
		$status 	= $_POST['status'];

		$this->send_rma_status_sms($request_id,$status);
	}

	public static function wcsmsn_warranty_tab()
	{
		$active_tab = isset($_GET['tab'])?$_GET['tab']:'';
	?>
		<a href="admin.php?page=warranties-settings&tab=wcsmsn_warranty" class="nav-tab <?php echo ($active_tab == 'wcsmsn_warranty') ? 'nav-tab-active' : ''; ?>"><?php _e('WC SMS Notifications', 'wc_warranty'); ?></a>
	<?php
	}

	public static function wcsmsn_warranty_settings_panels()
	{
		$active_tab	= isset($_GET['tab'])?$_GET['tab']:'';

		if($active_tab == 'wcsmsn_warranty')
		{
			$return_warranty_param=array(
				'checkTemplateFor'	=> 'return_warranty',
				'templates'			=> self::getReturnWarrantyTemplates(),
			);
			echo get_wcsmsn_template('views/message-template.php',$return_warranty_param);
		}
	}
	
	/*add default settings to savesetting in setting-options*/
	public function addDefaultSetting($defaults=array())
	{
		$wc_warrant_status 	= self::getWarrantStatus();

		foreach($wc_warrant_status as $ks => $vs)
		{
			$vs 					= str_replace(' ', '-', strtolower($vs));			
			$defaults['wcsmsn_warranty']['warranty_status_'.$vs]	= 'off';
			$defaults['wcsmsn_warranty']['sms_text_'][$vs] 		= '';
		}
		return $defaults;
	}

	/*add tabs to wcsmsn settings at backend*/
	public static function addTabs($tabs=array())
	{
		$return_warranty_param=array(
			'checkTemplateFor'	=> 'return_warranty',
			'templates'			=> self::getReturnWarrantyTemplates(),
		);

		$tabs['return_warranty']['title']		= __("Return & Warranty",wcsmsnConstants::TEXT_DOMAIN);
		$tabs['return_warranty']['tab_section']	= 'return_warranty';
		$tabs['return_warranty']['tabContent']	= 'views/message-template.php';
		$tabs['return_warranty']['icon']		= 'dashicons-products';
		$tabs['return_warranty']['params']		= $return_warranty_param;
		return $tabs;
	}

	public static function getReturnWarrantyTemplates()
	{
		$wc_warrant_status 	= self::getWarrantStatus();
		$variables = array(
			'[order_id]' 			=> 'Order Id',
			'[rma_number]' 			=> 'RMA Number',
			'[rma_status]' 			=> 'RMA Status',
			'[order_amount]' 		=> 'Order Total',
			'[billing_first_name]' 	=> 'First Name',
			'[item_name]' 			=> 'Product Name',
			'[store_name]' 			=> 'Store Name',
		);
		$templates 			= array();

		foreach($wc_warrant_status as $ks  => $vs){

			$vs 				= str_replace(' ', '-', strtolower($vs));
			$wc_warranty_text 	= wcsmsn_get_option('sms_text_'.$vs, 'wcsmsn_warranty','');
			$current_val 		= wcsmsn_get_option('warranty_status_'.$vs, 'wcsmsn_warranty','on');
			
			$checkboxNameId		= 'wcsmsn_warranty[warranty_status_'.$vs.']';
			$textareaNameId		= 'wcsmsn_warranty[sms_text_'.$vs.']';

			$text_body 			= wcsmsn_get_option('sms_text_'.$vs, 'wcsmsn_warranty', '') ? wcsmsn_get_option('sms_text_'.$vs, 'wcsmsn_warranty', '') : sprintf(__('Hello %s, status of your warranty request no. %s against %s with %s has been changed to %s.',wcsmsnConstants::TEXT_DOMAIN), '[billing_first_name]', '[rma_number]', '[order_id]', '[store_name]', '[rma_status]');

			$templates[$ks]['title'] 			= 'When RMA is '.ucwords($vs);
			$templates[$ks]['enabled'] 			= $current_val;
			$templates[$ks]['status'] 			= $ks;
			$templates[$ks]['text-body'] 		= $text_body;
			$templates[$ks]['checkboxNameId'] 	= $checkboxNameId;
			$templates[$ks]['textareaNameId'] 	= $textareaNameId;
			$templates[$ks]['token'] 			= $variables;
		}
		return $templates;
	}
}
new wcsmsn_Return_Warranty;
?>