<?php 
// Hook in
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

// Our hooked in function - $fields is passed via the filter!
function custom_override_checkout_fields( $fields ) {
     unset($fields['billing']['billing_country']);
     unset($fields['shipping']['shipping_country']);
     $fields['billing']['billing_address_1']['placeholder'] = 'Vej, nr, etage, side';
     $fields['billing']['billing_address_2']['placeholder'] = '';
     $fields['billing']['billing_postcode']['label'] = 'Postnummer';
     $fields['billing']['billing_postcode']['placeholder'] = 'Postnummer';
     $fields['billing']['billing_city']['label'] = 'By';
     $fields['billing']['billing_city']['placeholder'] = 'By';
     $fields['shipping']['shipping_address_1']['placeholder'] = 'Vej, nr, etage, side';
     $fields['shipping']['shipping_address_2']['placeholder'] = '';
     $fields['shipping']['shipping_postcode']['label'] = 'Postnummer';
     $fields['shipping']['shipping_postcode']['placeholder'] = 'Postnummer';
     $fields['shipping']['shipping_city']['label'] = 'By';
     $fields['shipping']['shipping_city']['placeholder'] = 'By';

	$fields['billing']['billing_ordertype']['options'] = array(
    'label'     => __('Begravelse?', 'woocommerce'),
    'placeholder'   => _x('Phone', 'placeholder', 'woocommerce'),
    'required'  => false,
    'class'     => array('form-row-wide'),
    'clear'     => true,
    'option_1' => 'Option 1 text',
  	'option_2' => 'Option 2 text'
     );
     return $fields;
}
 ?>