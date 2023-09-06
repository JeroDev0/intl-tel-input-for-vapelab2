<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Int_Tel_Input_Connector
 * @subpackage Int_Tel_Input_Connector/admin/partials
 */
?>

<form method="post" action="<?php admin_url( '?page=intl-tel-input-for-vapelab' ); ?>">
<?php
	
wp_nonce_field( "wc_vapelab_sheets_connector_settings" );

if ( $pagenow == 'admin.php' && $_GET['page'] == 'intl-tel-input-for-vapelab' ){
   
   if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab'];
   else $tab = 'general';
   
   echo '<h2>Settings</h2>';
   echo '<table class="form-table">'; ?> 

   <tr>
      <th>Whatsapp Validation</th>
      <td class="forminp forminp-select">
         <select  name="enabled_whatsapp_validation"  style="width:400px" tabindex="-1" aria-hidden="true" >
            <option <?php selected($settings['enabled_whatsapp_validation'], 'yes') ?> value="yes">Yes</option>
            <option <?php selected($settings['enabled_whatsapp_validation'], 'no') ?> value="no">No</option>
         </select>
      </td>
   </tr>
      
   

        

<?php 
   echo '</table>';
}

?>
   <p class="submit" style="clear: both;">
        <input type="submit" name="submit_general_tab"  class="button-primary" value="Update Settings" />
   </p>
</form>

