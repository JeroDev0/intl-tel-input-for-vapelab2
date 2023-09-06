<?php 
/*********************************************************************/
/* PROGRAM    (C) 2022 VapeLab                                       */
/* PROPERTY   MÉXICO                                                 */
/* OF         + (52) 56 1720 2964                                    */
/*********************************************************************/

namespace VapeLab\WooCommerce\Admin;

//declare(strict_types=1);

defined('ABSPATH') || exit;

// make sure that we will include shared class only once
if (!class_exists(__NAMESPACE__ . '\\InternationalPhoneInput')):

class InternationalPhoneInputAdmin
{
	protected static $instance = null;
	protected $mainMenuId;
	protected $author;
	protected $isRegistered;
	protected $settings = array();
	protected $optionKey;

	static public function instance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new InternationalPhoneInputAdmin();
		}

		return self::$instance;
	}

	public function __construct()
	{
		$this->mainMenuId = 'vapelab';
		$this->author = 'vapelab';
		$this->isRegistered = false;
	}

	public function register($settings,$optionKey)
	{
		if ($this->isRegistered) {
			return;
		}

		$this->isRegistered = true;
		
		$this->settings = $settings;

		$this->optionKey = $optionKey;
		

		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );
	}



	public function add_menu_item()
	{

		
		if( empty(menu_page_url('vapelab-menu-page', false)) ){
			add_menu_page(
				'VapeLab',
				'VapeLab',
				'manage_options',
				'vapelab-menu-page',
				'',
				plugins_url('assets/images/icon.png',dirname(__DIR__) ),
				25
			);
			
			add_submenu_page( 'vapelab-menu-page', 'International Phone Input ', 'International Phone Input ', 'manage_options', 'intl-tel-input-for-vapelab', array($this,'settings_page'));
			
			remove_submenu_page( 'vapelab-menu-page','vapelab-menu-page' );
			
		}else{
			//add_submenu_page( 'vapelab-menu-page', 'International Phone Input ', 'International Phone Input ', 'manage_options', 'intl-tel-input-for-vapelab', array($this,'settings_page'));
		}
	
		
	}

	public function wc_intl_tel_input_vapelab_admin_tabs( $current = 'general' ){

		$tabs = array( 'general' => 'General' );
		echo '<div id="icon-themes" class="icon32"><br></div>';
		echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';
		foreach( $tabs as $tab => $name ){
			$class = ( $tab == $current ) ? " nav-tab-active" : "";
			
			echo "<a class='nav-tab".$class."' href='?page=intl-tel-input-for-vapelab&tab=$tab'>$name</a>";

		}
		echo '</h2>';
		
	}

	public function settings_page () {

		global $pagenow;

		$tab =  ( isset ( $_GET['tab'] ) ) ? $_GET['tab'] : 'general';

		if ( isset ( $_GET['tab'] ) ) {

			$this->wc_intl_tel_input_vapelab_admin_tabs($_GET['tab']); 

		}else{

			$this->wc_intl_tel_input_vapelab_admin_tabs('general');

		}
		
		
		
		if(isset($_POST['submit_general_tab'])){
			

			$settings = $this->settings;
			$settings['enabled_whatsapp_validation'] = $_POST['enabled_whatsapp_validation'];
			
            update_option( $this->optionKey, $settings );

			$url_parameters = isset($_GET['tab'])? 'updated=true&tab='.$_GET['tab'] : 'updated=true';
            wp_redirect(admin_url('admin.php?page=intl-tel-input-for-vapelab&'.$url_parameters));
			exit;
            
        }

		$settings = $this->settings;

		if($tab == 'general'){

			require_once 'partials/intl-tel-input-for-vapelab-general-display.php';

		}


	}



	public function onEnqueueScripts()
	{
		$styles = "
			.{$this->mainMenuId} .card {
				max-width: none;
			}

			.{$this->mainMenuId} .item {
				border-bottom: 1px solid #eee;
				margin: 0;
				padding: 10px 0;
				display: inline-block;
				width: 100%;
			}

			.{$this->mainMenuId} .card ul {
				list-style-type: inherit;
				padding: inherit;
			}

			.{$this->mainMenuId} .item:last-child {
				border-bottom: none;
			}

			.{$this->mainMenuId} .item a {
				display: inline-block;
				width: 100%;
				color: #23282d;
				text-decoration: none;
				outline: none;
				box-shadow: none;
			}

			.{$this->mainMenuId} .item .num {
				width: 40px;
				height: 40px;
				margin-bottom: 30px;
				float: left;
				margin-right: 10px;
				border-radius: 20px;
				background-color: #0079c6;
				text-align: center;
				line-height: 40px;
				color: #ffffff;
				font-weight: bold;
				font-size: 20px;
			}

			.{$this->mainMenuId} .item p {
				margin: 5px 0;
			}

			.{$this->mainMenuId} .item .title {
				font-weight: bold;
			}

			.{$this->mainMenuId} .item .extra {
				opacity: .5;
			}
		";

		$styleId = $this->mainMenuId . '_custom_css';
		wp_register_style($styleId, false);
    	wp_enqueue_style($styleId);
		wp_add_inline_style($styleId, $styles);
	}

}

endif;