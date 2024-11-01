<?php
/**
 * @package WCVirtualShop
 */
/*
Plugin Name: Virtual Shop For Woocommecre
Plugin URI: https://thevrshop.000webhostapp.com/documentation
Description: Virtual shop solution for woocommerce.
Version: 0.1.1
Author: Otaiz
Author URI: https://thevrshop.000webhostapp.com/
License: GPLv2 or later
Text Domain: wc-virtual-shop
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2016-2017 Otaiz, Inc.
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

add_shortcode( 'wc_vr_shop', 'wc_vrshop_proc' );

//wc_vrshop_validate_session();
add_action( 'init', 'wc_vrshop_validate_session');
add_action( 'init', 'set_vr_order_cookie');
add_action('woocommerce_checkout_update_order_meta', 'wc_vrshop_order_complete');

function wc_vrshop_proc() {
	global $isVrShopSession;
	
	if($isVrShopSession) {
		if(isset($_GET['get_datas'])) {
			
			return wc_vrshop_get_products();
		}
		if(isset($_GET['get_products'])) {
			
			return wc_vrshop_get_products();
		} else if(isset($_GET['create_order'])) {
			wc_vrshop_save_order();
			return;
		} else if((isset($_POST['create_session']) || isset($_GET['create_session']))) {
			wc_vrshop_make_session();
			return;
		} else {
			return wc_vrshop_main_page();
		}
	} else {
		if(isset($_GET['vr_order_key'])) {
			wc_vrshop_view_order($_GET['vr_order_key']);
			return;
		}
		else if(isset($_GET['vr_confirm_order']) && isset($_GET['vro_nonce']) && wp_verify_nonce( $_GET['vro_nonce'], 'vr_shop_confirm_order' )) {
			wc_vrshop_confirm_order(sanitize_text_field($_GET['vr_confirm_order']));
			return;
		}
		else if(isset($_GET['my_vr_order'])) {
			wc_vrshop_view_orders();
			return;
		}
		
		return wc_vrshop_main_page();
	}
}

function wc_vrshop_main_page() {
    $current_user = wp_get_current_user();
	
	$str = "";
	
	$shop_key = get_option("wc_vr_shop_shop_key");
	$allowed_roles = array('editor', 'administrator');
	if($current_user != null && array_intersect($allowed_roles, $current_user->roles )) {
		if(isset($_POST['update_wc_vr_shop']) && isset($_POST['vr_shop_update_nonce']) && wp_verify_nonce( $_POST['vr_shop_update_nonce'], 'vr_shop_update' )) {
			update_option("wc_vr_shop_shop_key", sanitize_text_field($_POST['wc_vr_shop_shop_key']));
			$shop_key = get_option("wc_vr_shop_shop_key");
		}
		if(isset($_POST['wc_vr_shop_copy_page_template']) && isset($_POST['vr_shop_ctemplate_nonce']) && wp_verify_nonce( $_POST['vr_shop_ctemplate_nonce'], 'vr_shop_copy_template' )) {
			wc_vrshop_copy_blank_template();
		}
		
		$str .= "<h2>Admin Setting</h2>";
		$str .= '<h4>Shop Key</h4>
					<form method="post">
						<input type="hidden" name="vr_shop_update_nonce" value="'.wp_create_nonce( 'vr_shop_update' ).'" />
						<table class="shop_table" style="width:100%">
							<tr>
								<td style="width:70%"><input type="text" style="width:100%" name="wc_vr_shop_shop_key" value="'.$shop_key.'"></td>
								<td><input class="button" type="submit" name="update_wc_vr_shop" value="Update" style="width:100%"></td>
							</tr>
						</table>
					</form>
					<table class="shop_table">
						<tr>
							<th width="35%">Session Processor URL</th>
							<td>'.htmlentities(get_permalink()).'</td>
						</tr>
						<!--tr>
							<th>Product Processor URL</th>
							<td>'.htmlentities(get_permalink()).'</td>
						</tr>
						<tr>
							<th>Cart Processor URL</th>
							<td>'.htmlentities(get_permalink()).'</td>
						</tr-->
						<tr>
							<th>Checkout Processor URL</th>
							<td>'.htmlentities(get_permalink().'?create_order=1').'</td>
						</tr>
						<tr>
							<th>Product Database Path</th>
							<td>'.htmlentities(get_permalink().'?get_products=1').'</td>
						</tr>
					</table>
					<form method="post">
						<input type="hidden" name="vr_shop_ctemplate_nonce" value="'.wp_create_nonce( 'vr_shop_copy_template' ).'" />
						<input class="button" style="font-size:1em;width:100%" type="submit" name="wc_vr_shop_copy_page_template" value="Copy Template To Current Theme">
					</form>
					<hr />';
		$str .= '<h2>UI Key</h2>
				<table class="shop_table">
					<tr>
						<th>Key</th>
						<th>Data Type</th>
						<th>Description</th>
					</tr>
					<tr>
						<th>product_id</th>
						<td>Number</td>
						<td>Product ID</td>
					</tr>
					<tr>
						<th>product_name</th>
						<td>Text</td>
						<td>Name of product</td>
					</tr>
					<tr>
						<th>product_type</th>
						<td>Text</td>
						<td>Product internal type</td>
					</tr>
					<tr>
						<th>product_sku</th>
						<td>Text</td>
						<td>SKU (Stock-keeping unit) - product unique ID</td>
					</tr>
					<tr>
						<th>product_description</th>
						<td>Text</td>
						<td>Product description</td>
					</tr>
					<tr>
						<th>product_price</th>
						<td>Number</td>
						<td>Product\'s active price</td>
					</tr>
					<tr>
						<th>product_regular_price</th>
						<td>Number</td>
						<td>Product\'s regular price</td>
					</tr>
					<tr>
						<th>product_sale_price</th>
						<td>Number</td>
						<td>Product\'s sale price</td>
					</tr>
					<tr>
						<th>product_image</th>
						<td>URL</td>
						<td>Product image url</td>
					</tr>
				</table>
				<hr />';
		$str .= '<h4>Need Help?</h4><a target="_blank" class="button" style="font-size:1em;width:100%" href="'.esc_url('https://thevrshop.000webhostapp.com/documentation').'">Documentation/How To Use</a><hr />';
	}
	
	$shopKey = "UaaaaaU::".$shop_key;
	$shopQR = '{"channel_key":"UaaaaaU","project_key":"'.$shop_key.'"}';
	$shopQrLink = esc_url("https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=".urlencode($shopQR));
	
	$str .= '<center><h4>Download free apps <a target="_blank" href="'.esc_url('https://thevrshop.000webhostapp.com/download').'" class="button">here</a> and scan QRCode below.</h4>';
	$str .= '<img src="'.$shopQrLink.'" width=200 />';
	$str .= '<br /><h4>Or enter World Code</h4>';
	$str .= '<h2 style="border:2px solid #CCC">'.$shopKey.'</h2>';
	$str .= '<h4>Your VR Orders is <a style="font-size:0.7em" class="button" href="'.esc_url(get_permalink().'?my_vr_order').'">Here</a></h4>';
	$str .= '<hr />';
	$str .= '<h3><a target="_blank" style="font-size:0.7em" class="button" href="'.esc_url('https://thevrshop.000webhostapp.com/download').'">Download Free Apps Now</a></h3>';
	$str .= '<h3><a target="_blank" style="font-size:0.7em" class="button" href="'.esc_url('https://thevrshop.000webhostapp.com/documentation').'">How To Use</a></h3>';
	$str .= '<h3><a target="_blank" style="font-size:0.7em" class="button" href="'.esc_url('https://thevrshop.000webhostapp.com/').'">More Info</a></h3></center>';
	
	return "<div class=\"woocommerce\">".$str."</div>";
}

function wc_vrshop_copy_blank_template() {
	$file = plugin_dir_path( __FILE__ )."/wc_vrshop_blank-page.php";
	$newfile = get_template_directory()."/wc_vrshop_blank-page.php";
	copy($file, $newfile);
}

/////////////////////////////////////////
//	Auth
/////////////////////////////////////////

function wc_vrshop_validate_session() {
	global $wpdb, $isVrShopSession;
	
	$shop_key = get_option("wc_vr_shop_shop_key");
	$session_key = $_POST['session_key'];
	if($session_key == "") {
		$session_key = $_GET['session_key'];
	}
	
	$session_key = sanitize_text_field(str_replace("'", "", $session_key));
	
	if($session_key == "" && isset($shop_key) && $shop_key != "")
	{
		if(($shop_key == $_POST['project_key'] || $shop_key == $_GET['project_key'])) {
			$isVrShopSession = true;
		} else {
			$isVrShopSession = false;
		}
		return false;
	}
	else if($session_key != "" && (!isset($_POST['wp_nonce']) || !wp_verify_nonce( $_POST['wp_nonce'], "vrsession_" . $session_key ))) {
		$isVrShopSession = false;
		return false;
	}
	
	$mysession = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."wc_vr_shop_session WHERE session_key = '".$session_key."'");
	
	if($mysession != null && count($mysession) > 0) {
		$isVrShopSession = true;
		wc_vrshop_update_session($session_key);
		return true;
	} else {
		$isVrShopSession = false;
		return false;
	}
}

function wc_vrshop_update_session($session_key) {
	global $wpdb;
	
	$wpdb->update( 
		$wpdb->prefix."wc_vr_shop_session", 
		array( 
			'last_update' => current_time('mysql')
		), 
		array( 'session_key' => $session_key )
	);
}

function wc_vrshop_make_session() {
	global $wpdb, $isVrShopSession;
	
	$db_version = get_option("wc_vr_shop_session_db_version");
	if($db_version == null || $db_version == "" || $db_version+0 < 1.0) {
		wc_vrshop_create_session_table();
	}
	
	$ukey = str_replace("'", "", time().md5(uniqid()));
	$nonce = wp_create_nonce("vrsession_" . $ukey);
	
	$dataIns = array( 
			'session_key' => $ukey, 
			'ip_address' => $_POST['ip_addr'], 
			'device_id' => sanitize_text_field($_POST['device_id']), 
			'start_time' => current_time('mysql'), 
			'last_update' => current_time('mysql')
		);
	
	$wpdb->insert( 
		$wpdb->prefix.'wc_vr_shop_session', 
		$dataIns
	);
	
	$mysession = $wpdb->get_row( "SELECT `session_key` FROM ".$wpdb->prefix."wc_vr_shop_session WHERE session_key = '".$ukey."'");
	$isVrShopSession = true;
	
	$mysession->other_data_key = "wp_nonce";
	$mysession->other_data = $nonce;
	
	echo json_encode($mysession);
}

/////////////////////////////////////////
//	Product
/////////////////////////////////////////

function wc_vrshop_get_products() {
	
	if(is_int($_GET['pid']) && $_GET['pid']+0 > 0) {
		
		return get_product_detail($_GET['pid']+0);
	}
	
	$product = wc_get_products(array( "limit" => 1000000000000 ));
	
	$data = [];
	foreach($product as $row) {
		$variations = null;
		if( $row->get_type() == "variable" ) {
			$variations = wc_vrshop_get_variations($row->get_available_variations());
		}
		
		$image = wp_get_attachment_url($row->get_image_id());
		if($image == false)
			$image = "";
		
		$desc = $row->get_description();
		
		$idata = [];
		
		$idata[] = array (
				"field_id" => "product_id",
				"type" => 1,
				"integer_data" => $row->get_id()
			);
		$idata[] = array (
				"field_id" => "product_name",
				"type" => 0,
				"string_data" => html_entity_decode($row->get_name())
			);
		$idata[] = array (
				"field_id" => "product_type",
				"type" => 0,
				"string_data" => $row->get_type()
			);
		$idata[] = array (
				"field_id" => "product_sku",
				"type" => 0,
				"string_data" => $row->get_sku()
			);
		$idata[] = array (
				"field_id" => "product_description",
				"type" => 0,
				"string_data" => html_entity_decode(strip_tags($desc))
			);
		$idata[] = array (
				"field_id" => "product_short_description",
				"type" => 0,
				"string_data" => $row->get_short_description()
			);
		$idata[] = array (
				"field_id" => "product_description_html",
				"type" => 0,
				"string_data" => $desc
			);
		$idata[] = array (
				"field_id" => "product_price",
				"type" => 2,
				"number_data" => $row->get_price()
			);
		$idata[] = array (
				"field_id" => "product_regular_price",
				"type" => 2,
				"number_data" => $row->get_regular_price()
			);
		$idata[] = array (
				"field_id" => "product_sale_price",
				"type" => 2,
				"number_data" => $row->get_sale_price()
			);
		$idata[] = array (
				"field_id" => "product_image",
				"type" => 5,
				"string_data" => $image
			);
		$idata[] = array (
				"field_id" => "product_variations",
				"type" => 4,
				"jsonlist_data" => $variations
			);
		
		$_item = array (
			"row_id" => $row->get_id(),
			"_data" => $idata
		);
		
		$data[] = $_item;
	}
	
	return json_encode(array(
		"image_key" => "product_image",
		"name_key" => "product_name",
		"data" => $data
	));
}

function wc_vrshop_get_variations($variations) {
	$myVariations;
	
	for($i = 0; $i < count($variations); $i++) {
		$name = explode("-", $variations[$i]['name']);
		if(count($name) <= 1)
			$name = explode("&ndash;", $variations[$i]['name']);
		
		if(count($name) <= 1) {
			$name[0] = $variations[$i]['name'];
			$name[1] = "Option-".($i+1);
		}
		
		$desc = $variations[$i]['variation_description'];
		
		if($image == false)
			$image = "";
		
		$idata = [];
		
		$idata[] = array (
				"field_id" => "product_id",
				"type" => 1,
				"integer_data" => $variations[$i]['id']
			);
		$idata[] = array (
				"field_id" => "product_name",
				"type" => 0,
				"string_data" => html_entity_decode($variations[$i]['name'])
			);
		$idata[] = array (
				"field_id" => "product_sku",
				"type" => 0,
				"string_data" => $variations[$i]['sku']
			);
		$idata[] = array (
				"field_id" => "product_description",
				"type" => 0,
				"string_data" => html_entity_decode(strip_tags($desc))
			);
		$idata[] = array (
				"field_id" => "product_description_html",
				"type" => 0,
				"string_data" => $desc
			);
		$idata[] = array (
				"field_id" => "product_price",
				"type" => 2,
				"number_data" => $variations[$i]['regular_price']
			);
		$idata[] = array (
				"field_id" => "product_regular_price",
				"type" => 2,
				"number_data" => $variations[$i]['regular_price']
			);
		$idata[] = array (
				"field_id" => "product_sale_price",
				"type" => 2,
				"number_data" => $variations[$i]['sale_price']
			);
		$idata[] = array (
				"field_id" => "product_image",
				"type" => 0,
				"string_data" => $image
			);
		
		$_item = array (
			"row_id" => $variations[$i]['id'],
			"_data" => $idata
		);
		
		$myVariations[] = array (
			"name"	=> trim($name[1]),
			"data"	=> json_encode($_item)
		);
	}
	
	return $myVariations;
}

/////////////////////////////////////////
//	Checkout & Order
/////////////////////////////////////////

function wc_vrshop_view_order($key) {
	global $wpdb, $wp_session;
	
	$db_version = get_option("wc_vr_shop_order_db_version");
	if($db_version == null || $db_version == "" || $db_version+0 < 1.0) {
		wc_vrshop_create_vrorder_table();
	}
	
	$orderData = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wc_vr_shop_order WHERE order_key = '".$key."'", ARRAY_A);
	$orderData = $orderData[0];
	
	$completedOrderData = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wc_vr_shop_order_complete WHERE order_key = '".$key."' ORDER BY complete_date DESC", ARRAY_A);
	$completedOrderData = $completedOrderData[0];
	
	$total = 0;
	$status = 'Pending';
	echo '<div class="woocommerce"><h2>Order Detail</h2>';
	
	echo '<table class="shop_table">';
	
	echo '<tr>';
	echo '<th>Date</th>';
	echo '<td>'.$orderData['create_date'].'</td>';
	echo '</tr>';
	
	echo '<tr>';
	echo '<th>Status</th>';
	if($orderData['order_iscomplete']+0 > 0){
		$status = 'Complete';
	}
	echo '<td>'.$status.'</td>';
	echo '</tr>';
	
	echo '<tr>';
	echo '<th>Complete Date</th>';
	$cDate = '';
	$order = Array();
	$oStatus = "";
	if(isset($completedOrderData['complete_date'])){
		$cDate = $completedOrderData['complete_date'];
		$order = wc_get_order( $completedOrderData['order_completed_id']+0 );
		$oStatus = '['.$order->get_status().']';
	}
	echo '<td><b><i>'.$cDate.'</i></b><b> : '.$oStatus.' </b></td>';
	echo '</tr>';
	
	echo '<tr>';
	echo '<th>Items</th>';
	echo '<td>';
	
	if(isset($orderData['order_items'])) {
		$products = json_decode($orderData['order_items'], true);
		if(isset($products) && count($products) > 0) {
			$_pf = new WC_Product_Factory();
			echo '<ol>';
			foreach($products as $line) {
				$product = $_pf->get_product($line['manager_item_key']);
				echo '<li>'.$line['quantity'].' x '.$product->get_name().'<i>('.$line['manager_item_key'].')</i></li>';
				
				$total += $product->get_price() * $line['quantity'];
			}
			echo '</ol>';
		}
	}
	
	echo '</td>';
	echo '</tr>';
	
	echo '<tr>';
	echo '<th>Total</th>';
	echo '<th>'.get_woocommerce_currency_symbol().' '.$total.'</th>';
	echo '</tr>';
	
	echo '</table>';
	
	if($status != 'Complete') {
		$nonce = wp_create_nonce( 'vr_shop_confirm_order' );
		echo '<a href="'.esc_url(get_permalink()."?vr_confirm_order=".$key.'&vro_nonce='.$nonce).'"><button class="button button-primary button-large" style="color:white;">Confirm Order</button></a>';
	}
	else {
		$current_user = wp_get_current_user();
		echo '<a href="'.esc_url($order->get_checkout_payment_url()).'"><button class="button button-primary button-large" style="color:white;">Pay</button></a>';
		if(isset($current_user) && isset($current_user->ID) && $current_user->ID+0 > 0)
			echo '&nbsp;<a href="'.esc_url($order->get_view_order_url()).'"><button class="button button-primary button-large" style="color:white;">Detail</button></a>';
	}
	
	echo '</div>';
}

function wc_vrshop_view_orders() {
	global $wpdb;
	$current_user = wp_get_current_user();
	
	$cookieName = 'vrshop_orders';
	$_t = [];
	
	if(isset($current_user) && $current_user->ID+0 > 0) {
		$orderData = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wc_vr_shop_order WHERE wp_user_id = '".($current_user->ID+0)."'", ARRAY_A);
		
		echo '<div class="woocommerce"><h2>Mine/Claimed</h2>';
		$noMine = '<th>No Result</th>';
		echo '<table class="shop_table">';
		
		foreach($orderData as $row) {
			wc_vrshop_order_data($row);
			$_t[] = $row['order_key'];
			$noMine = '';
		}
		
		echo $noMine.'</table>';
	}
	if(isset($_COOKIE[$cookieName])) {
		echo '<hr /><h2>Others/Unclaimed</h2>';
		echo '<table class="shop_table">';
		
		$cookieValue = json_decode(stripslashes($_COOKIE[$cookieName]), true);
		$noOthers = '<th>No Result</th>';
		foreach($cookieValue as $row) {
			if(!isset($_t) || !in_array($row, $_t)) {
				$orderData = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wc_vr_shop_order WHERE order_key = '".($row)."'", ARRAY_A);
				if(count($orderData)+0 > 0 && isset($orderData[0]['order_key'])) {
					wc_vrshop_order_data($orderData[0]);
					$_t[] = $orderData[0]['order_key'];
			
					$noOthers = '';
				}
			}
		}
		
		echo $noOthers.'</table></div>';
	}
}

function wc_vrshop_order_data($row) {
	echo '<tr>
		<th valign="top">
		<a href="'.esc_url(get_permalink()).'?vr_order_key='.$row['order_key'].'">';
	echo '<b><i>'.$row['create_date'].'</i></b></a></th>';
	echo '<td>';
	$products = json_decode($row['order_items'], true);
	if(count($products)+0 > 0) {
		echo '<ul>';
		foreach($products as $line) {
			$product = wc_get_product($line['manager_item_key']+0);
			echo '<li>'.$product->get_name() . ' x ' . $line['quantity'].'</li>';
		}
		echo '</ul><br />';
	} else {
		echo "Has No Items";
	}
	echo '</td></tr>';
}

function wc_vrshop_confirm_order($key) {
	global $wpdb, $woocommerce;
	
	$db_version = get_option("wc_vr_shop_order_db_version");
	if($db_version == null || $db_version == "" || $db_version+0 < 1.0) {
		wc_vrshop_create_vrorder_table();
	}
	
	$orderData = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wc_vr_shop_order WHERE order_key = '".$key."'", ARRAY_A);
	$orderData = $orderData[0];
	
	$products = json_decode($orderData["order_items"], true);
	
	if(isset($products) && $products != null) {;
		if(isset($_GET['clear_cart']))
			$woocommerce->cart->empty_cart();
		foreach($products as $line) {
			WC()->cart->add_to_cart( $line['manager_item_key']+0, $line['quantity']+0 );
		}
	}
	
	echo '<script> window.location.href = "'.esc_url($woocommerce->cart->get_checkout_url()).'"; </script>';
}

function wc_vrshop_save_order() {
	global $wpdb;
	
	$db_version = get_option("wc_vr_shop_order_db_version");
	if($db_version == null || $db_version == "" || $db_version+0 < 1.0) {
		wc_vrshop_create_vrorder_table();
	}
	
	$ukey = sanitize_text_field(time().md5(uniqid()));
	
	$inventory = json_decode(stripslashes($_POST['product_data']), true);
	$products = $inventory["item_list"];
	$total = 0;
	
	if(isset($products) && $products != null) {
		foreach($products as $line) {
			$total += $line['quantity'] * $line['price'];
		}
	}
	
	//*
	$address_billing = array(
            'first_name'	=> $_POST["billing_first_name"],
            'last_name'		=> $_POST["billing_last_name"],
            'company'		=> $_POST["billing_company"],
            'email'			=> $_POST["billing_email"],
            'phone'			=> $_POST["billing_phone"],
            'address_1'		=> $_POST["billing_address_1"],
            'address_2'		=> $_POST["billing_address_2"], 
            'city'			=> $_POST["billing_city"],
            'state'			=> $_POST["billing_state"],
            'postcode'		=> $_POST["billing_poscode"],
            'country'		=> $_POST["billing_country"]
        );
	
	$fbAddress = $_POST["billing_first_name"] . " " . $_POST["billing_last_name"]."\n".
					$_POST["billing_address_1"].",".$_POST["billing_address_2"]."\n".
					$_POST["billing_city"].",".$_POST["billing_state"].",".$_POST["billing_poscode"]."\n".
					$_POST["billing_email"];
	
	$address_shipping = array(
            'first_name'	=> $_POST["shipping_first_name"],
            'last_name'		=> $_POST["shipping_last_name"],
            'company'		=> $_POST["shipping_company"],
            'email'			=> $_POST["shipping_email"],
            'phone'			=> $_POST["shipping_phone"],
            'address_1'		=> $_POST["shipping_address_1"],
            'address_2'		=> $_POST["shipping_address_2"], 
            'city'			=> $_POST["shipping_city"],
            'state'			=> $_POST["shipping_state"],
            'postcode'		=> $_POST["shipping_poscode"],
            'country'		=> $_POST["shipping_country"]
        );
	
	$fsAddress = $_POST["shipping_first_name"] . " " . $_POST["shipping_last_name"]."\n".
					$_POST["shipping_address_1"].",".$_POST["shipping_address_2"]."\n".
					$_POST["shipping_city"].",".$_POST["shipping_state"].",".$_POST["shipping_poscode"]."\n".
					$_POST["shipping_email"];
	
	
	$fsAddress = "Not Included";
	$fbAddress = "Not Included";
	//*/
	
	$dataIns = array( 
			'order_key' => $ukey, 
			'order_post_data' => json_encode($_POST), 
			'order_billing_address' => json_encode($address_billing),
			'order_shipping_address' => json_encode($address_shipping),
			'order_items' => json_encode($products),
			'device_id' => sanitize_text_field($_POST['device_id']), 
			'create_date' => current_time('mysql'), 
			'last_update' => current_time('mysql')
		);
	
	$wpdb->insert( 
		$wpdb->prefix.'wc_vr_shop_order', 
		$dataIns
	);
	
	$result['message'] = "Order Save Successfull\n".json_encode($postData);
	$result['return_data'][] = array ("name" => 'checkout_date', "data" => date("F j, Y, g:i a"));
	$result['return_data'][] = array ("name" => 'checkout_key', "data" => $ukey);
	$result['return_data'][] = array ("name" => 'checkout_number', "data" => $wpdb->insert_id);
	$result['return_data'][] = array ("name" => 'checkout_url', "data" => get_permalink() . "?vr_order_key=" . $ukey);
	$result['return_data'][] = array ("name" => 'checkout_customer_id', "data" => "0");
	$result['return_data'][] = array ("name" => 'checkout_note', "data" => "");
	$result['return_data'][] = array ("name" => 'checkout_billing_address', "data" => $fbAddress);
	$result['return_data'][] = array ("name" => 'checkout_shipping_address', "data" => $fsAddress);
	$result['return_data'][] = array ("name" => 'checkout_total', "data" => $total);
	$result['return_data'][] = array ("name" => 'checkout_products', "data" => json_encode(array ( "products" => $products )));
	
	echo json_encode($result);
}

function wc_vrshop_order_complete($oid, $data) {
	global $wpdb;
	echo "Complete : ".$oid;
	$cookieName = 'vrshop_confirm_orders';
	
	$db_version = get_option("wc_vr_shop_order_complete_db_version");
	if($db_version == null || $db_version == "" || $db_version+0 < 1.0) {
		wc_vrshop_create_vrorder_completed_table();
	}
	
	if(isset($_COOKIE[$cookieName]))
	{
		$cookieValue = json_decode(stripslashes($_COOKIE[$cookieName]), true);
		foreach($cookieValue as $key) {
			// Memungkinkan masalah. Sila cek bebetol..
			$key = sanitize_text_field($key);
			//echo $key;
			$wpdb->update( 
				$wpdb->prefix."wc_vr_shop_order", 
				array( 
					'order_iscomplete' => 1
				), 
				array( 'order_key' => $key )
			);
			
			$dataIns = array( 
				'order_key' => $key, 
				'order_completed_data' => json_encode($data), 
				'order_completed_id' => $oid+0,
				'complete_date' => current_time('mysql')
			);
	
			$wpdb->insert( 
				$wpdb->prefix.'wc_vr_shop_order_complete', 
				$dataIns
			);
			
		}
	}
	
	setcookie($cookieName, "", time() + 3600 * 24 * 100, COOKIEPATH, COOKIE_DOMAIN, false);
}

function set_vr_order_cookie() {
	global $wpdb;
	$current_user = wp_get_current_user();
	
	if(isset($_GET['vr_order_key'])) {
		$key = sanitize_text_field($_GET['vr_order_key']);
		
		$cookieName = 'vrshop_orders';
	}
	else if(isset($_GET['vr_confirm_order'])) {
		$key = sanitize_text_field($_GET['vr_confirm_order']);
		
		$cookieName = 'vrshop_confirm_orders';
	}
	else
		return;
	
	$orderData = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wc_vr_shop_order WHERE order_key = '".$key."'", ARRAY_A);
	
	if(!isset($orderData) || count($orderData) < 0 || ($orderData[0]['wp_user_id']+0 > 0 && isset($current_user) && $current_user->ID+0 != $orderData[0]['wp_user_id']+0))
		return;
		
	$cookieValue = wc_vrshop_get_cookie_value($cookieName, $key);

	wc_vrshop_set_order_user($key);

	setcookie($cookieName, json_encode($cookieValue), time() + 3600 * 24 * 100, COOKIEPATH, COOKIE_DOMAIN, false);
}

function wc_vrshop_set_order_user($key) {
	global $wpdb;
	$current_user = wp_get_current_user();
	
	if(isset($current_user) && isset($current_user->ID) && $current_user->ID+0 > 0) {
		$wpdb->update( 
			$wpdb->prefix."wc_vr_shop_order", 
			array( 
				'wp_user_id' => $current_user->ID+0
			), 
			array( 'order_key' => $key )
		);
	}
}

function wc_vrshop_get_cookie_value($cookieName, $orderKey) {
	
		$cookieValue = [];
		if(isset($_COOKIE[$cookieName]))
		{
			$cookieValue = json_decode(stripslashes($_COOKIE[$cookieName]), true);
		}
		else {
			$cookieValue = [];
		}
		
		if(!isset($cookieValue) || !in_array($orderKey, $cookieValue))
			$cookieValue[] = $orderKey;
		
		return $cookieValue;
}

/////////////////////////////////////////
//	Table
/////////////////////////////////////////
function wc_vrshop_create_vrorder_table() {
	global $wpdb;
	$table_name = $wpdb->prefix."wc_vr_shop_order";
	if ($wpdb->get_var('SHOW TABLES LIKE '.$table_name) != $table_name) {
		$sql = 'CREATE TABLE '.$table_name.'(
			`order_id` bigint(20) NOT NULL  AUTO_INCREMENT,
			`order_post_data` TEXT DEFAULT NULL,
			`order_billing_address` TEXT DEFAULT NULL,
			`order_shipping_address` TEXT DEFAULT NULL,
			`order_items` TEXT DEFAULT NULL,
			`order_link` varchar(64) DEFAULT NULL,
			`order_iscomplete` int(1) DEFAULT 0,
			`order_key` varchar(64) DEFAULT NULL,
			`device_id` varchar(64) DEFAULT NULL,
			`wp_user_id`  bigint(20) NOT NULL DEFAULT 0,
			`create_date` datetime NOT NULL,
			`last_update` datetime NOT NULL,
			PRIMARY KEY (`order_id`)
		)';

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		update_option("wc_vr_shop_order_db_version", "1.0");
	}
}

function wc_vrshop_create_vrorder_completed_table() {
	global $wpdb;
	$table_name = $wpdb->prefix."wc_vr_shop_order_complete";
	if ($wpdb->get_var('SHOW TABLES LIKE '.$table_name) != $table_name) {
		$sql = 'CREATE TABLE '.$table_name.'(
			`order_id` bigint(20) NOT NULL  AUTO_INCREMENT,
			`order_key` varchar(64) DEFAULT NULL,
			`order_completed_id` bigint(20) NOT NULL,
			`order_completed_data` TEXT DEFAULT NULL,
			`complete_date` datetime NOT NULL,
			PRIMARY KEY (`order_id`)
		)';

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		update_option("wc_vr_shop_order_complete_db_version", "1.0");
	}
}

function wc_vrshop_create_session_table() {
	global $wpdb;
	$table_name = $wpdb->prefix."wc_vr_shop_session";
	if ($wpdb->get_var('SHOW TABLES LIKE '.$table_name) != $table_name) {
		$sql = 'CREATE TABLE '.$table_name.'(
			`session_id` bigint(20) NOT NULL  AUTO_INCREMENT,
			`device_id` varchar(64) DEFAULT NULL,
			`session_key` varchar(64) DEFAULT NULL,
			`ip_address` varchar(64) DEFAULT NULL,
			`start_time` datetime NOT NULL,
			`last_update` datetime NOT NULL,
			PRIMARY KEY (`session_id`)
		)';

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		update_option("wc_vr_shop_session_db_version", "1.0");
	}
}

