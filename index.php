<?php
/*
   Plugin Name: Recommended Products Manager
   Version: 1.0
   Author: JC Vela
   Description: Display a list of recommended products.
   Text Domain: recom
   License: GPLv3
  */


/****************************************** JQUERY ********************************************/


function custom_scripts() {

	// wp_deregister_script('jquery');
	// wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js', array(), null, true);
    
    wp_register_script('ajax_script', plugins_url( 'js/products_script.js', __FILE__ ), array(), rand(111,9999), 'all' );
    wp_enqueue_script( 'ajax_script' );

    wp_enqueue_script( 'ajax_script' );
    wp_localize_script('ajax_script', 'myAjax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

}

add_action( 'wp_enqueue_scripts', 'custom_scripts' );
add_action( 'admin_enqueue_scripts', 'custom_scripts' );


function load_JQuislider(){

    wp_enqueue_script('jquery');
     wp_enqueue_script('jquery-ui-core');
     wp_enqueue_script('jquery-ui-slider');
     wp_enqueue_script('jquery-ui-draggable');
     wp_enqueue_script('jquery-ui-droppable');
    
    //Enqueue the jQuery UI theme css file from google:
     $wp_scripts = wp_scripts();
     
     wp_enqueue_style(
     'jquery-ui-theme-smoothness', //select ui theme: base...
     sprintf(
     'https://ajax.googleapis.com/ajax/libs/jqueryui/%s/themes/smoothness/jquery-ui.css',
     $wp_scripts->registered['jquery-ui-core']->ver
     )
     );
     
    }
 
    add_action( 'wp_enqueue_scripts', 'load_JQuislider' );
    add_action( 'admin_enqueue_scripts', 'load_JQuislider' );

/**************************************** CREATE DB ********************************************/

register_activation_hook( __FILE__, 'create_db_2459' );

function create_db_2459() {

    global $wpdb;
    global $current_user;
    get_currentuserinfo();
    $user = $current_user->ID;

    $charset_collate = $wpdb->get_charset_collate();

    $wpdb->products = "recom_product";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $sql = "CREATE TABLE $wpdb->products ( "
    ."`id` int(11) NOT NULL autoincrement, "
    ."`item_id` int(11) NOT NULL, "
    .") ENGINE=InnoDB DEFAULT CHARSET=latin1";

    dbDelta( $sql );

    $wpdb->query($sql);

    $sql = "ALTER TABLE $wpdb->products ADD PRIMARY KEY (`id`); ";
    $wpdb->query($sql);

}

/*******************************  FRONT END  **********************************/


function test_plugin_setup_menu_2459(){
	
    $page_title = 'Recommended Products';
    $menu_title = 'Recommended Products';
    $capability = "manage_options";
    $slug = 'recom_products';
    $callback = 'plugin_settings_page_content_2459';
    $icon = plugins_url( 'icons/icon.png?'.rand(111,9999), __FILE__ );
    $position = 100;

    add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon);
}

add_action('admin_menu', 'test_plugin_setup_menu_2459');



function shortcode_recommended_products_2549($atts, $content=""){

    global $wpdb;
    $wpdb->products = "recom_product";
    $sql = "SELECT * FROM $wpdb->products";
    
    $result = $wpdb->get_results($sql);
    $recommended_array = array_column($result, 'item_id');
    
    $args = array(

        'include' => $recommended_array,
    ); 
    
    $products = wc_get_products( $args ); 

    
    $result = new WP_Query( $products );
    
    $output = '';
    $output .=  '<div style="width: 750px;float:right;clear:both;!important;">';
    $output .=  "<p><h1>Recommended Products</h1><p>";
    $output .=  '<div style="width: 680px;height: auto;min-height:250px;padding:10px;">';
    
    foreach ( $products as $product ){
        
        
        $output .=  '<div style="border:1px solid #ddd;background-color:#e7eff2;width:110px;height:240px;padding:10px;margin:5px;float:left;">';
        
        $output .=  '<div style="height:60px;"><span style="font-weight:bold;font-size:14px;line-height:12px;">'. wordwrap($product->get_title(), 10, "<br />\n") . '</span></div>';    // Product ID
        
        //echo 'ID: '. $product->get_id() . '<br>';    // Product ID
        $output .=  $product->get_image(array( 100, 160 )); // Product title
        $output .=  '<b><div style="font-size:14px;text-align:center;width:90px;margin:0;padding-top:8px;padding-bottom:8px;">€ ' . $product->get_price(). '</div></b>';          // Product price
        
        $id = $product->get_id();
        
        

        $output .=  '<a href="?add-to-cart='.$id.'" data-quantity="1" ';
        $output .=  'style="font-size:11px;background-color:#3a70ac;color:white;" ';
        $output .=  'class="button product_type_simple add_to_cart_button ajax_add_to_cart button-primary" ';
        $output .=  'data-product_id="'. $id . '"'; 
        $output .=  'data-product_sku="'. $product->get_sku() . '"'; 
        $output .=  'aria-label="Add '. $product->get_title() . ' to your cart" '; 
        $output .=  'rel="nofollow">Add to Cart</a>';
       

        
        $output .=  '</div>'; 
        
    }
    $output .=  '</div>'; 
    $output .=  '</div>'; 

    return $output;
}   

function register_my_shortcodes() {
    add_shortcode('recommended', 'shortcode_recommended_products_2549');
}
add_action('init', 'register_my_shortcodes');
//add_shortcode('greeting', 'wpb_demo_shortcode');


/************************************   MAIN ADMIN PAGE  ***********************************/



function plugin_settings_page_content_2459() {

    /** GET ALL AVAILABLE PRODUCTS */
    global $wpdb;
    $sql = "SELECT MAX(meta_value), post_id from {$wpdb->prefix}postmeta where meta_key = '_price'";
    $all_product_data = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "posts` where post_type='product' and post_status = 'publish'");
    $result = $wpdb->get_results($sql);
    $_product = wc_get_product( $result[0]->post_id );    
    $all_products = wc_get_products( array( 'status' => 'publish', 'limit' => -1 ) );

    /* GET STORED RECOMMENDED PRODUCTS */
    $wpdb->products = "recom_product";
    $sql = "SELECT * FROM $wpdb->products";
    $result2 = $wpdb->get_results($sql);
    $recommended_array = array_column($result2, 'item_id');
    
    $args = array(

        'include' => $recommended_array,
    ); 
    
    $recom_products = wc_get_products( $args ); 
    //$recommended_result = new WP_Query( $recom_products );

    

?>


    
        
<h1>Recommended Products Manager</h1>

<?php settings_errors(); ?> 
        
<div class="postbox-container">

<? //echo print_r($products) ?>

<?php 


    echo '<div style="overflow-y: scroll;overflow-x: hidden;width:580px;height:auto;float:left;">';
        echo '<hr><h2>Available products</h2>';



        echo '<div id="leftDiv" style="width:570px;height:auto;float:left;">';
        
                foreach ( $all_products as $product ){
                    echo '<div style="width: 640px;">';
                    
                        echo '<div class="item item'.$product->get_id().'" item_id='.$product->get_id().' style="width:110px;height:180px;border:1px solid #ccc;background-color:#e7eff2;padding:10px;margin:5px;float:left;">';
                            echo '<p>';
                            echo '<b>'. wordwrap($product->get_title(), 10, "<br />\n") . '</b><br>';    // Product ID
                            
                            //echo 'ID: '. $product->get_id() . '<br>';    // Product ID
                            echo $product->get_image(array( 100, 160 )) . '<br>'; // Product title
                            echo '<b>€ ' . $product->get_price(). '</b> (ID: ' . $product->get_id() . ')';          // Product price
                        echo '</div>';
                    echo '</div>';
                }
        echo '</div>';
    
    echo '</div>';
   
    echo '<div style="width:640px;height:auto;margin-left:15px;float:left;">';
            
   
        echo '<div style="width:580px;height:auto;float:left;">';

            echo "<hr>";
            echo '<div style="height:60px;">';
            echo '<div style="width:250px;float:left"><h2>Recommended Products</h2></div>';
            echo '<div id="response" style="width:150px;border-top:15px;padding-top:15px;float:left"></div>';
            echo '<div style="padding-top:10px;float:left;margin-right:10px;"><input type="button" name="form_clear" id="form_clear" class="button button-secondary" value="Clear"></div>';
            echo '<div style="padding-top:10px;"><input type="submit" name="form_save" id="form_save" class="button button-primary" value="Save Changes"></div>';
            
            echo "</div>";
    
            echo '<div id="rightDiv" style="width: 640px;height: auto;min-height:250px;border:1px solid #ccc;padding:10px;">';
                    
            if (sizeof($recom_products)!= 0){
                foreach ( $recom_products as $product ){
                        
                        
                        echo '<div class="item item'.$product->get_id().'"  item_id='.$product->get_id().' style="border:1px solid #ccc;background-color:#e7eff2;width:110px;height:180px;padding:10px;margin:5px;float:left;">';
                        
                            echo '<span style="font-weight:bold;font-size:14px;line-height:16px;">'. wordwrap($product->get_title(), 10, "<br />\n") . '<br><br>';    // Product ID
                            
                            //echo 'ID: '. $product->get_id() . '<br>';    // Product ID
                            echo $product->get_image(array( 100, 160 )); // Product title
                            echo '<b><div style="font-size:14px;text-align:center;width:90px;margin:0;padding-top:8px;padding-bottom:8px;">€ ' . $product->get_price(). '</div></b>';          // Product price
                            
                            $id = $product->get_id();
                        
                        echo '</div>'; 
                        
                    }
            }        
                    
            echo '</div>';

            
        echo '</div>';   
    
    echo '</div>';
    echo '<div id="droppedItems"></div>';
    echo '</div>';
    

echo '</div> <!--postbox-container>';

}


function form_save () {


    //$droppedItems = stripslashes($_POST["droppedItems"]);
   
    // $vars = array();

    //$arr = json_decode($droppedItems);

    //$form_id = $_POST['form_id'];
    
    if (isset($_POST['droppedItems'])) {
        $droppedItems = json_encode($_POST['droppedItems']);
        $droppedItems = stripslashes($droppedItems);
        $droppedItems = str_replace('[','',$droppedItems);
        $droppedItems = str_replace(']','',$droppedItems);
        $droppedItems = str_replace('\\','',$droppedItems);
        $droppedItems = str_replace('/','',$droppedItems);
        $droppedItems = str_replace('""','"',$droppedItems);

        $droppedItems_array = explode (",", $droppedItems); 
    }
    else {
        $droppedItems_array = array();  
    }
    //$droppedItems_array = json_decode($droppedItems, true);
    //$str_json = trim($droppedItems, '"');
    //$droppedItems_array = preg_split ("/\,/", $droppedItems); 
    //$droppedItems = json_decode($droppedItems);

    global $current_user;
	get_currentuserinfo();
	$user = $current_user->ID;
	global $wpdb;
	global $result;

	$wpdb->products = "recom_product";
	$output = '';
    // //$table  = $wpdb->prefix . 'table_name';
    
    if ( sizeof($droppedItems_array)==0 ) {
        $delete = $wpdb->query("TRUNCATE TABLE $wpdb->products");	
        $wpdb->query($delete);	
    }
    else {
        $delete = $wpdb->query("TRUNCATE TABLE $wpdb->products");
        $wpdb->query($delete);		

        for ($i=0;$i< count($droppedItems_array);$i++) {
            $query = "INSERT INTO $wpdb->products (`item_id`) VALUES ($droppedItems_array[$i])";
            $wpdb->query($query);
        }
    }

	if ($wpdb->last_error !== '') {
	  	echo "Error.<br>" . $query ;
	  } 
	  else {
     	echo "Form Saved";
     }
   

    //echo print_r(droppedItems_array);
    //echo count(print_r($array));
    wp_die(); // ajax call must die to avoid trailing 0 in your response

}

add_action( "wp_ajax_form_save", "form_save" );
add_action( "wp_ajax_nopriv_form_save", "form_save" );

?>