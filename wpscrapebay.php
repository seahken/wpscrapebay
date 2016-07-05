<?php

/*
 *	Plugin Name: WP Scrap eBay Store 
 *	Plugin URI: http://beewebby.com.au
 *	Description: Scrap http://ebay.com to add/edit/remove products from Wordpress WooCommerce Websites
 *	Version: 1.0
 *	Author: Ken Seah
 *	Author URI: http://beewebby.com.au
 *	License: GPL2
 *
*/

$wpscrapebay_plugin_url = WP_PLUGIN_URL . '/wpscrapebay';
$wpscrapebay_options = array();
$wpscrapebay_display_html = false;

include('inc/functions.php');
function wpscrapebay_menu() {

	/*
	 * 	Use the add_options_page function
	 * 	add_options_page( $page_title, $menu_title, $capability, $menu-slug, $function ) 
	 *
	*/

	add_options_page(
		'WP Scrap eBay',
		'WP Scrap eBay',
		'manage_options',
		'wp-scrap-ebay',
		'wpscrapebay_options_page'
	);

}

add_action( 'admin_menu', 'wpscrapebay_menu' );

function wpscrapebay_options_page(){

	if( !current_user_can( 'manage_options' ) ) {

		wp_die( 'You do not have sufficient permissions to access this page.' );

	}

	global $wpscrapebay_plugin_url;
	global $wpscrapebay_options;
	global $wpscrapebay_display_html;

	if( isset($_POST['wpscrapebay_form_submitted'])){

		$hidden_field = esc_html($_POST['wpscrapebay_form_submitted']);

		if($hidden_field == 'Y'){
			$wpscrapebay_options = get_option('wpscrapebay');
			$database_added = $wpscrapebay_options['wpscrapebay_database_added'];
			$wpscrapebay_url = esc_html($_POST['wpscrapebay_url']);
			//$wpscrapebay_html = wpscrapebay_get_html($wpscrapebay_url);
			$wpscrapebay_tag_name = esc_html($_POST['wpscrapebay_tag_name']);
			$wpscrapebay_attribute = esc_html($_POST['wpscrapebay_attribute']);
			$wpscrapebay_class_name = esc_html($_POST['wpscrapebay_class_name']);
			$database = wpscrapebay_getAttributeValueAndLink($wpscrapebay_url,$wpscrapebay_tag_name,$wpscrapebay_attribute,$wpscrapebay_class_name);

			$wpscrapebay_options['wpscrapebay_url'] = $wpscrapebay_url;
			$wpscrapebay_options['wpscrapebay_property_type'] = $wpscrapebay_property_type;
			$wpscrapebay_options['wpscrapebay_tag_name'] = $wpscrapebay_tag_name;
			$wpscrapebay_options['wpscrapebay_attribute'] = $wpscrapebay_attribute;
			$wpscrapebay_options['wpscrapebay_class_name'] = $wpscrapebay_class_name;
			$wpscrapebay_options['wpscrapebay_database'] = $database;
			$wpscrapebay_options['wpscrapebay_database_added'] = $database_added;
			$wpscrapebay_options['last_updated'] = time();

			update_option('wpscrapebay',$wpscrapebay_options);

			

		}
	}
	
	$wpscrapebay_options = get_option('wpscrapebay');

	if ($wpscrapebay_options != ''){

		$wpscrapebay_url = $wpscrapebay_options['wpscrapebay_url'];
		//$wpscrapebay_html = $wpscrapebay_options['wpscrapebay_html'];
		$wpscrapebay_property_type = $wpscrapebay_options['wpscrapebay_property_type'];
		$wpscrapebay_tag_name = $wpscrapebay_options['wpscrapebay_tag_name'];
		$wpscrapebay_attribute = $wpscrapebay_options['wpscrapebay_attribute'];
		$wpscrapebay_class_name = $wpscrapebay_options['wpscrapebay_class_name'];
		$database = $wpscrapebay_options['wpscrapebay_database'];
		$database_added = $wpscrapebay_options['wpscrapebay_database_added'];
	}

	if ( isset($_POST['add_post_submitted'])){ 

		$database_added = $database_added + $database;

		$wpscrapebay_options['wpscrapebay_database_added'] = $database_added;
		update_option('wpscrapebay',$wpscrapebay_options);		
	}

	if ( isset($_POST['add_product_submitted'])){ 

		$add_product_number = $_POST['add_product_submitted'];

		array_push($database_added, $database[$add_product_number]);

		$wpscrapebay_options['wpscrapebay_database_added'] = $database_added;
		update_option('wpscrapebay',$wpscrapebay_options);	

	}

	if ( isset($_POST['add_update_submitted'])){ 

		for ($i=0; $i<count($database_added); $i++){ 

			$post_content = wpscrapebay_getProductInfo($database_added[$i]['link']);

			$database_added[$i]['post_id'] = wpscrapebay_programmatically_create_post( $database_added[$i]['name'],
																$database_added[$i]['post_id'],
																$post_content['body'],
																$post_content['condition'],													
																$post_content['price'],
																$post_content['sale_price'],																
																$post_content['image_url'],
																$post_content['availability'],
																$post_content['category'],
																'pending'
																);
			

			// If something changed, save it to the database
		}

		$wpscrapebay_options['wpscrapebay_database_added'] = $database_added;
		update_option('wpscrapebay',$wpscrapebay_options);
		
	}

	//var_dump($database_added);

	if ( isset($_POST['remove_post_submitted'])){ 

		$remove_post_number = $_POST['remove_post_submitted'];

		unset($database_added[$remove_post_number]);
		$database_added = array_values($database_added);

		if($database_added == null){
			$database_added = array();
		}

		$wpscrapebay_options['wpscrapebay_database_added'] = $database_added;
		update_option('wpscrapebay',$wpscrapebay_options);


		
	}

	if ( isset($_POST['remove_all_submitted'])){ 

		
		unset($database_added);
		$database_added = array();

		$wpscrapebay_options['wpscrapebay_database_added'] = $database_added;
		update_option('wpscrapebay',$wpscrapebay_options);
		
	}

	if ( isset($_POST['publish_all_submitted'])){ 

		for ($i=0; $i<count($database_added); $i++){ 

			$args = [	'ID'=>$database_added[$i]['post_id'],
						'post_status'=>'publish'];

			wp_update_post($args);
			
			
		}

	}
	
	
	require('inc/wpscrapebay_view.php');
	

}

?>