<?php
// <Internal Doc Start>
/*
*
* @description: For woo shipping
* @tags: 
* @group: 
* @name: Sri Lankan Districts
* @type: PHP
* @status: published
* @created_by: 
* @created_at: 
* @updated_at: 2023-12-25 09:22:55
* @is_valid: 
* @updated_by: 
* @priority: 10
* @run_at: all
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
<?php
/**
 * Add or modify States
 */
add_filter( 'woocommerce_states', 'custom_woocommerce_states' );

function custom_woocommerce_states( $states ) {

  $states['LK'] = array(
    'LK1' => 'Ampara', 
    'LK2' => 'Anuradhapura',
	'LK3' => 'Badulla',
	'LK4' => 'Batticaloa',
	'LK5' => 'Colombo',
	'LK6' => 'Galle',
	'LK7' => 'Gampaha',
	'LK8' => 'Hambantota',
	'LK9' => 'Jaffna',
	'LK10' => 'Kalutara',
	'LK11' => 'Kandy',
	'LK12' => 'Kegalle',
	'LK13' => 'Kilinochchi',
	'LK14' => 'Kurunegala',
	'LK15' => 'Mannar',
	'LK16' => 'Matale',
	'LK17' => 'Matara',
	'LK18' => 'Moneragala',
	'LK19' => 'Mullaitivu',
	'LK20' => 'Nuwara Eliya',
	'LK21' => 'Polonnaruwa',
	'LK22' => 'Puttalam',
	'LK23' => 'Ratnapura',
	'LK24' => 'Trincomalee',
	'LK25' => 'Vavuniya'
  );

  return $states;
}