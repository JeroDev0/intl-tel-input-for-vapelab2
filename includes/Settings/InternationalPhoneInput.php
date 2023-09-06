<?php 
/*********************************************************************/
/* PROGRAM    (C) 2022 VapeLab                                       */
/* PROPERTY   MÉXICO                                                 */
/* OF         + (52) 56 1720 2964                                    */
/*********************************************************************/

namespace VapeLab\WooCommerce\Settings;

defined('ABSPATH') || exit;

if (!class_exists(__NAMESPACE__ . '\\InternationalPhoneInput')):

class InternationalPhoneInput
{
	protected $id;
	protected $mainMenuId;
	protected $adapterName;
	protected $title;
	protected $description;
	protected $optionKey;
	protected $settings;
	protected $pluginSettings;
	protected $pluginPath;
	protected $version;
	protected $adapter;
	protected $settingsFormHooks;
	protected $logger;
	protected $pageDetector;
	protected $cartProxy;
	protected $sessionProxy;

    public function __construct($pluginPath, $adapterName, $description = '', $version = null) 
    {
		$this->id =  basename($pluginPath, '.php');
		$this->pluginPath = $pluginPath;
		$this->adapterName = $adapterName;
		$this->title = 'International Phone Input';
		$this->description = $description;
		$this->version = $version;
		$this->optionKey = sprintf('wc_%s_settings', str_replace("-","_",$this->id));
		$this->settings = array();
		$this->pluginSettings = array();
		
		$this->mainMenuId = 'vapelab';
		$this->adapter = null;
		$this->settingsFormHooks = null;

	}


	public function register()
	{
		if (!function_exists('is_plugin_active')) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		// do not register when WooCommerce is not enabled
		if (!is_plugin_active('woocommerce/woocommerce.php')) {
			return;
		}
		
		$this->loadSettings();

		if (is_admin()) {

			\VapeLab\WooCommerce\Admin\InternationalPhoneInputAdmin::instance()->register($this->settings,$this->optionKey);
		

			//\VapeLab\WooCommerce\Admin\VapeLab::instance()->register();
			//add_action('admin_menu', array($this, 'onAdminMenu'));
		}else{
			
			$scriptId = $this->mainMenuId . '_intl_tel_input_js';
			wp_register_script(
				$scriptId,
				plugins_url('assets/js/intlTelInput.min.js',VL_IPI_PLUGIN),
				array('jquery-core')
			);
			wp_enqueue_script($scriptId );
			

			wp_register_script(
				$this->mainMenuId . '_intl_tel_input_mask',
				plugins_url('assets/js/jquery.mask.min.js',VL_IPI_PLUGIN),
				array('jquery-core')
			);

			wp_enqueue_script($this->mainMenuId . '_intl_tel_input_mask' );

			wp_register_script(
				$this->mainMenuId . '_intl_tel_input_main',
				plugins_url('assets/js/index.js',VL_IPI_PLUGIN),
				array($scriptId),
				'170520230329'
			);
			
			wp_enqueue_script($this->mainMenuId . '_intl_tel_input_main' );
			wp_localize_script( $this->mainMenuId . '_intl_tel_input_main',  $this->mainMenuId . '_intl_tel_utils', plugins_url('assets/js/utils.js',VL_IPI_PLUGIN) );
			

			//$url = plugins_url( , WPCF7_PLUGIN );
			$styleId = $this->mainMenuId . '_intl_tel_input_css';
			wp_register_style($styleId, plugins_url('assets/css/intlTelInput.min.css',VL_IPI_PLUGIN));
			wp_enqueue_style($styleId);
			
			
			$styles = "
				.iti--allow-dropdown{
					width:100% !important;
				}
			";

			$styleCustomId = $this->mainMenuId . '_custom_css';
			wp_register_style($styleCustomId, false);
			wp_enqueue_style($styleCustomId);
			wp_add_inline_style($styleCustomId, $styles);

			add_action('woocommerce_after_order_notes' , array($this, 'custom_override_checkout_fields') );
			//add_action('woocommerce_after_checkout_validation',  array($this, 'vapelab_woocommerce_after_checkout_validation'),10,1);	
			//add_action('woocommerce_checkout_process',  array($this, 'vapelab_woocommerce_checkout_process') );	
			add_action( 'woocommerce_checkout_create_order',  array($this, 'vapelab_woocommerce_checkout_create_order'), 10, 2 );

			add_filter( 'woocommerce_checkout_fields', array($this, 'vapelab_woocommerce_checkout_fields') );
			
			add_action('woocommerce_checkout_update_order_meta',array($this, 'vapelab_save_custom_billing_field') );


			add_action('init', array($this, 'init_callback'));
		

		}

		
	}

	public function vapelab_save_custom_billing_field($order_id){
	
		
		if (!empty($_POST['billing_notes'])) {
			$custom_field_value = sanitize_text_field($_POST['billing_notes']);
			update_post_meta($order_id, 'billing_notes', $custom_field_value);
		}

		if (!empty($_POST['shipping_notes'])) {
			$custom_field_value = sanitize_text_field($_POST['shipping_notes']);
			update_post_meta($order_id, 'shipping_notes', $custom_field_value);
		}
		// Función para guardar el valor del input check en los metadatos del pedido
        if (isset($_POST['confirm_phone'])) {
			$confirm_phone_value = $_POST['confirm_phone'] ? 'sí' : 'no';
			update_post_meta($order_id, 'confirm_phone', sanitize_text_field($confirm_phone_value));
		}

	}
	
	public function init_callback(){

		wp_localize_script($this->mainMenuId . '_intl_tel_input_main', 'vl_ajax',
			array( 
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'is_user_logged_in' => function_exists('is_user_logged_in') ? (int)is_user_logged_in() : 0,
			)
		);

	}

	public function getCacheKey($numberPhone)
	{
		
		return $cacheKey;
	}

	public static function validate_wa_callback(){
		
		$result = false;

		$settings = get_option('wc_intl_tel_input_for_vapelab_settings');

		if($settings['enabled_whatsapp_validation'] == 'no'){
			echo wp_json_encode(array("response" => true, 'whatsapp_validation' => false));
			wp_die();
		}
		
		
		if ( isset( $_POST['wa_number'] ) && !empty( $_POST['wa_number'] ) ) {

			$cacheKey = md5($_POST['wa_number']) . '_intl_tel_input_vapelab';
			$cacheValue = get_transient($cacheKey);
			
			if (!$cacheValue) {
				$url = "https://script.google.com/macros/s/AKfycbw4XUtL03YeBIbN4e90VT2YLYFkXO8yFjeaqh4GVvNl1vNeG3NM-Luosn1sPDlYdaNjRg/exec";

				$args = array(
					'func' => "Abay.checkNumber2",
					'receiver' => $_POST['wa_number'],
				);
				
				$curl = curl_init();

				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($args));
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_TIMEOUT, 20);

				$data = curl_exec($curl);
				
				curl_close($curl);
				
				if (!is_null($data) && json_decode($data)) {
					$data_arr = json_decode($data, true);
				
					if (isset($data_arr['data'][0]['status'])) {
						$wa_status = $data_arr['data'][0]['status'];

						if ($wa_status == "number register") {

							$result = true;
							set_transient($cacheKey, $_POST['wa_number'], 86400 * 365);
						}
					}
				}else{
					wp_mail(get_bloginfo('admin_email'), "Error en la validación de WhatsApp", "Ha ocurrido un error durante la validación de whatsapp del número ".$_POST['wa_number']);
					echo wp_json_encode(array("response" => false, "error" => "No pudimos validar el número de WhatsApp", 'whatsapp_validation' => true ));
					wp_die();
				}
			}else{
				$result = true;
			}
		}
		echo wp_json_encode(array("response" => $result, "cache_key" => $cacheKey, "cache_value" => $cacheValue, 'whatsapp_validation' => true));
		
		wp_die();

	}


	function vapelab_woocommerce_checkout_create_order($order,$data){
		
		
		if( isset($_POST['billing_phone_full']) && ! empty($_POST['billing_phone_full']) ){

			$customer_id = $order->get_customer_id();
			$customer = new \WC_Customer( $customer_id );

			$customer->update_meta_data( 'default_country_code', $_POST['billing_phone_code'] );
    		$customer->save();

			$country_phone_code = str_replace($_POST['billing_phone'],'',$_POST['billing_phone_full'] );
			$country_phone_code = str_replace('+','',$country_phone_code);
			if($country_phone_code == '52'){
				$country_phone_code = $country_phone_code.'1';
			}
			
			$order->update_meta_data( 'billing_country_code', sanitize_text_field( $_POST['billing_phone_code'] ) );
			$order->update_meta_data( 'shipping_country_code', sanitize_text_field(  $_POST['billing_phone_code'] ) );
			$order->update_meta_data( 'billing_country_phone_code', sanitize_text_field( $country_phone_code ) );
			$order->update_meta_data( 'shipping_country_phone_code', sanitize_text_field( $country_phone_code ) );
			
			$order->save();
			
			
			

		}
        
	}



	function vapelab_woocommerce_checkout_process(){
		
		
		if ( isset( $_POST['billing_phone'] ) && ! empty( $_POST['billing_phone'] ) ) {

			$url = "https://script.google.com/macros/s/AKfycbw4XUtL03YeBIbN4e90VT2YLYFkXO8yFjeaqh4GVvNl1vNeG3NM-Luosn1sPDlYdaNjRg/exec";
		
			$args = array(
				'func' => "Abay.checkNumber2",
				'receiver' => $_POST['billing_phone_full'],
			);
			
			$curl = curl_init();
		
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_POST, TRUE);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($args));
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_TIMEOUT, 15);
			
			$data = curl_exec($curl);

			curl_close($curl);

			if( !is_null($data) && json_decode($data) ){

				$data_arr = json_decode($data,true);
				if(isset($data_arr['data'][0]['status'])){
					
					$wa_status = $data_arr['data'][0]['status'];

					if($wa_status != "number register"){
						$msg = "El número no es un número de WhatsApp válido.";
						wc_add_notice( sprintf('El campo <strong>Teléfono con Whatsapp</strong> es inválido. %s',$msg), 'error',array('id'=> 'billing_phone') );
					}
				}

			}
		}

	}

	function vapelab_woocommerce_checkout_fields($fields){
		
	
		$fields['billing']['billing_phone']['label'] = 'Teléfono con WhatsApp';

		$fields['billing']['billing_country_phone_code'] = array(
			'label' => '',
			'placeholder' => '',
			'required' => false, // if field is required or not
			'clear' => false, // add clear or not
			'type' => 'hidden', // add field type
		);

		$default_country_code = 'mx';

		
		if(get_current_user_id() > 0){

			$customer = new \WC_Customer( get_current_user_id() );
			
			if ( $customer->get_meta( 'default_country_code' ) ) {
				$default_country_code = $customer->get_meta( 'default_country_code' );
				
			}
			
		}
	
		// Add the metadata to the checkout fields
		$fields['billing']['default_country_code'] = array(
			'label' => '',
			'placeholder' => '',
			'required' => false,
			'default' => $default_country_code,
			'type' => 'hidden', // add field type
		);

		$fields['billing']['billing_notes'] = array(
			'label' => 'Departamento, interior o notas',
			'placeholder' => 'Departamento, interior o notas',
			'class' => array('notes'),
			'required' => false,
			'type' => 'textarea', // add field type
			'priority' => 55,
			'clear'    => true,
		);

		$fields['shipping']['shipping_notes'] = array(
			'label' => 'Departamento, interior o notas',
			'placeholder' => 'Departamento, interior o notas',
			'class' => array('notes'),
			'required' => false,
			'type' => 'textarea', // add field type
			'priority' => 55,
			'clear'    => true,
		);
		//echo '<pre>';var_dump($fields);echo '</pre>';exit();
		unset($fields['order']['order_comments']);
		
		// campo de checbok en mostrado en checkout
		$fields['billing']['confirm_phone'] = array(
			'label' => '¿Do you want to receive messages in English?',
			'required' => false,
			'type' => 'checkbox',
			'class' => array('form-row-wide'),
			'clear' => true,
			'priority' => 101
		);

		return $fields;

		
	}

	function custom_override_checkout_fields( $checkout ) {
		
		woocommerce_form_field( 'billing_phone_full', array(
			'type'          => 'hidden',
			'class'		    => 'intl_tel_input_billing_phone_full' 
			), $checkout->get_value( 'billing_phone_full' ));

		woocommerce_form_field( 'billing_phone_code', array(
			'type'          => 'hidden',
			), "");

   }


	public function onPluginActionLinks($links)
	{
		$link = sprintf('<a href="%s">%s</a>', admin_url('admin.php?page=wc-settings&tab=shipping&section=' . $this->id), __('Settings', $this->id));
		array_unshift($links, $link);
		return $links;
	}

	public function getPluginSettings()
	{
		return $this->pluginSettings;
	}



	protected function initSettings()
	{

		$this->settings = array(
			'enabled_whatsapp_validation' => 'yes'
		);

	}

	protected function loadSettings()
	{		
		$this->initSettings();
		
		$this->settings = array_merge($this->settings, (array)get_option($this->optionKey, array()));
		

	}

};

endif;
// Función para mostrar el valor del input check en el panel de administración
function display_confirm_phone_in_admin( $order ) {
	$confirm_phone = get_post_meta( $order->get_id(), 'confirm_phone', true );
	if ( $confirm_phone ) {
		echo '<p><strong>¿Mensajes en ingles?:</strong> ' . esc_html( $confirm_phone ) . '</p>';
	}
}
add_action( 'woocommerce_admin_order_data_after_billing_address', '\\VapeLab\\WooCommerce\\Settings\\display_confirm_phone_in_admin', 10, 1 );


