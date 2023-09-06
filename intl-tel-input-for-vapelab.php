<?php

 /**
 * Plugin Name:       International Phone Input for VapeLab
 * Description:       Adds a flag dropdown to the phone input field, featuring automatic formatting and validation. It simplifies the process of entering phone numbers and enhances the user experience..
 * Version:           2.4.1
 * Author:            VapeLab
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       intl-tel-input-for-vapelab
 */

namespace VapeLab\WooCommerce\Settings;

defined('ABSPATH') || exit;

define( 'VL_IPI_PLUGIN', __FILE__ );

require_once(__DIR__ . '/includes/autoloader.php');
	
(new InternationalPhoneInput(
		__FILE__, 
		'International Phone Input for VapeLab',
        'Adds a flag dropdown to phone input, displays a relevant placeholder and provides formatting/validation methods', 
		'2.4.0'
	)
)->register();

add_action( 'wp_ajax_vl_validate_wa', array('VapeLab\WooCommerce\Settings\InternationalPhoneInput', 'validate_wa_callback')  );
add_action( 'wp_ajax_nopriv_vl_validate_wa', array('VapeLab\WooCommerce\Settings\InternationalPhoneInput', 'validate_wa_callback')  );