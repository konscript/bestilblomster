<?php
/*
Plugin Name: WooCommerce Ship to Multiple Addresses
Plugin URI: http://woothemes.com/woocommerce
Description: Allow customers to ship orders with multiple products or quantities to separate addresses instead of forcing them to place multiple orders for different delivery addresses.
Version: 2.1.8
Author: 75nineteen Media
Author URI: http://www.75nineteen.com
Requires at least: 3.3.1

Copyright: © 2013 75nineteen Media.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
	require_once( 'woo-includes/woo-functions.php' );

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'aa0eb6f777846d329952d5b891d6f8cc', '18741' );

if ( is_woocommerce_active() ) {

    /**
     * Localisation
     **/
    load_plugin_textdomain( 'wc_shipping_multiple_address', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    class WC_Ship_Multiple {

        public $meta_key_order      = '_shipping_methods';
        public $meta_key_settings   = '_shipping_settings';
        public $settings            = null;
        public static $lang         = array(
            'notification'  => 'You may use multiple shipping addresses on this cart',
            'btn_items'     => 'Indtast adresser'
        );

        function __construct() {
            // install
            register_activation_hook(__FILE__, array( $this, 'install' ) );

            // load the shipping options
            $this->settings = get_option( $this->meta_key_settings, array());

            // register hooks
            // menu
            //add_action( 'admin_menu', array( $this,'menu' ), 20);

            // settings styles and scripts
            add_action( 'admin_enqueue_scripts', array( $this, 'settings_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ), 1 );

            // modify address fields
            add_filter( 'woocommerce_checkout_fields', array( $this, 'checkout_fields' ) );

            // save settings handler
            add_action( 'admin_post_wcms_update', array( $this, 'save_settings' ) );

            add_action( 'woocommerce_available_shipping_methods', array( $this, 'available_shipping_methods' ) );
            add_action( 'woocommerce_before_checkout_shipping_form', array( $this, 'before_shipping_form' ) );
            add_action( 'woocommerce_before_checkout_form', array( $this, 'before_checkout_form' ) );
            add_filter( 'woocommerce_page_settings', array( $this, 'woocommerce_settings' ) );
            add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'checkout_process' ) );
            add_action( 'woocommerce_after_checkout_validation', array( $this, 'checkout_validation' ) );

            // add item meta
            add_filter( 'woocommerce_order_item_meta', array( $this, 'add_item_meta' ), 10, 2 );

            //add_action( 'woocommerce_product_options_shipping', array( $this, 'product_options' ) );
            //add_action( 'woocommerce_process_product_meta', array( $this, 'process_metabox' ) );

            // display multiple addresses
            add_action( 'woocommerce_view_order', array( $this, 'view_order' ) );
            add_action( 'woocommerce_email_after_order_table', array( $this, 'email_shipping_table' ) );
            add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'shipping_packages' ) );

            // handle order review events
            add_action( 'woocommerce_checkout_update_order_review', array( $this, 'update_order_review' ) );
            add_action( 'woocommerce_checkout_order_review', array( $this, 'checkout_order_review' ) );
            add_action( 'woocommerce_calculate_totals', array( $this, 'calculate_totals' ) );

            // cleanup
            add_action( 'wp_logout', array( $this, 'clear_session' ) );
            add_action( 'woocommerce_cart_emptied', array( $this, 'clear_session' ) );
            add_action( 'woocommerce_cart_updated', array( $this, 'cart_updated' ) );

            // shortcode
            add_shortcode( 'woocommerce_select_multiple_addresses', array( $this, 'draw_form' ) );
            add_shortcode( 'woocommerce_account_addresses', array( $this, 'account_addresses' ) );

            // meta box
            add_action( 'add_meta_boxes', array( $this, 'order_meta_box' ) );
            add_action( 'admin_print_styles', array( $this, 'meta_box_css' ) );
            add_action( 'woocommerce_process_shop_order_meta', array( $this, 'update_order_addresses' ), 10, 2 );

            // save shipping addresses
            add_action( 'template_redirect', array( $this, 'save_addresses' ) );
            add_action( 'template_redirect', array( $this, 'address_book' ) );

            // my account
            add_action( 'woocommerce_after_my_account', array( $this, 'my_account' ) );

            // address book
            add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ) );
            add_action( 'wp_ajax_wc_save_to_address_book', array( $this, 'save_address_book' ) );
            add_action( 'wp_ajax_nopriv_wc_save_to_address_book', array( $this, 'save_address_book' ) );
            add_action( 'wp_ajax_wc_duplicate_cart', array( $this, 'duplicate_cart' ) );
            add_action( 'wp_ajax_nopriv_wc_duplicate_cart', array( $this, 'duplicate_cart' ) );

            // inline script
            add_action( 'wp_footer', array( $this, 'inline_scripts' ) );
            add_action( 'woocommerce_cart_totals_after_shipping', array(&$this, 'remove_shipping_calculator') );

            // override needs shipping method and totals
            add_action( 'woocommerce_init', array( $this, 'wc_init' ) );

            add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'order_data_shipping_address' ), 90 );

            add_action( 'plugins_loaded', array(&$this, 'plugins_loaded_hooks') );

            add_filter( 'wcms_order_shipping_packages_table', array($this, 'display_order_shipping_addresses'), 9 );

            include_once( 'multi-shipping.php' );

            $settings   = get_option( 'woocommerce_multiple_shipping_settings', array() );

            if ( isset($settings['lang_notification']) ) {
                self::$lang['notification'] = $settings['lang_notification'];
            }

            if ( isset($settings['lang_btn_items']) ) {
                self::$lang['_btn_items'] = $settings['lang_btn_items'];
            }
        }

        function plugins_loaded_hooks() {

        }

        function install() {
            global $woocommerce;

            $page_id = woocommerce_get_page_id( 'multiple_addresses' );

            if ($page_id == -1) {
                // get the checkout page
                $checkout_id = woocommerce_get_page_id( 'checkout' );

                // add page and assign
                $page = array(
                    'menu_order'        => 0,
                    'comment_status'    => 'closed',
                    'ping_status'       => 'closed',
                    'post_author'       => 1,
                    'post_content'      => '[woocommerce_select_multiple_addresses]',
                    'post_name'         => 'shipping-addresses',
                    'post_parent'       => $checkout_id,
                    'post_title'        => 'Shipping Addresses',
                    'post_type'         => 'page',
                    'post_status'       => 'publish',
                    'post_category'     => array(1)
                );

                $page_id = wp_insert_post($page);

                update_option( 'woocommerce_multiple_addresses_page_id', $page_id);
            }

            $page_id = woocommerce_get_page_id( 'account_addresses' );

            if ($page_id == -1) {
                // get the checkout page
                $account_id = woocommerce_get_page_id( 'myaccount' );

                // add page and assign
                $page = array(
                    'menu_order'        => 0,
                    'comment_status'    => 'closed',
                    'ping_status'       => 'closed',
                    'post_author'       => 1,
                    'post_content'      => '[woocommerce_account_addresses]',
                    'post_name'         => 'account-addresses',
                    'post_parent'       => $account_id,
                    'post_title'        => 'Shipping Addresses',
                    'post_type'         => 'page',
                    'post_status'       => 'publish',
                    'post_category'     => array(1)
                );

                $page_id = wp_insert_post($page);

                update_option( 'woocommerce_account_addresses_page_id', $page_id);
            }
        }

        function is_multiship_enabled() {
            return true;
        }

        function wc_init() {
            global $woocommerce;

            add_action( 'woocommerce_before_order_total', array( $this, 'display_shipping_methods' ) );
            add_action( 'woocommerce_review_order_before_order_total', array( $this, 'display_shipping_methods' ) );
        }

        public function menu() {
            add_submenu_page( 'woocommerce', __( 'Multiple Shipping Settings', 'wc_shipping_multiple_address' ),  __( 'Multiple Shipping', 'wc_shipping_multiple_address' ) , 'manage_woocommerce', 'wc-ship-multiple-products', array( $this, 'settings' ) );
        }

        public static function settings_scripts() {
            global $woocommerce;

            woocommerce_admin_scripts();

            wp_enqueue_script( 'woocommerce_admin' );
            wp_enqueue_script( 'farbtastic' );
            wp_enqueue_script( 'ajax-chosen' );
            wp_enqueue_script( 'chosen' );

            ?>
            <style type="text/css">
            .chzn-choices li.search-field .default {
                width: auto !important;
            }
            </style>
            <?php

            wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );
        }

        public function settings() {
            include 'settings.php';
        }

        public function save_settings() {
            $settings       = array();
            $methods        = (isset($_POST['shipping_methods'])) ? $_POST['shipping_methods'] : array();
            $products       = (isset($_POST['products'])) ? $_POST['products'] : array();
            $categories     = (isset($_POST['categories'])) ? $_POST['categories'] : array();
            //$zips           = (isset($_POST['zips'])) ? $_POST['zips'] : array();
            $duplication    = (isset($_POST['cart_duplication']) && $_POST['cart_duplication'] == 1) ? true : false;

            if ( isset($_POST['lang']) && is_array($_POST['lang']) ) {
                update_option( 'wcms_lang', $_POST['lang'] );
            }

            foreach ( $methods as $id => $method ) {
                //$row_zip        = (isset($zips[$id])) ? $zips[$id] : false;
                $row_products   = (isset($products[$id])) ? $products[$id] : array();
                $row_categories = (isset($categories[$id])) ? $categories[$id] : array();

                //if ( !$row_zip ) continue; // zip cannot be empty

                // there needs to be at least 1 product or category per row
                if ( empty($row_categories) && empty($row_products) ) {
                    continue;
                }

                $settings[] = array(
                    //'zip'       => $row_zip,
                    'products'  => $row_products,
                    'categories'=> $row_categories,
                    'method'    => $method
                );

                //$settings
            }

            update_option( $this->meta_key_settings, $settings );
            update_option( '_wcms_cart_duplication', $duplication );

            wp_redirect( add_query_arg( 'saved', 1, 'admin.php?page=wc-ship-multiple-products' ) );
            exit;
        }

        public function checkout_fields( $fields ) {
            //echo '<pre>'. print_r($fields, true) .'</pre>';

            $fields['shipping']['shipping_notes'] = array(
                'type'  => 'textarea',
                'label' => __( 'Delivery Notes', 'wc_shipping_multiple_address' ),
                'placeholder'   => __( 'Delivery Notes', 'wc_shipping_multiple_address' )
            );
            return $fields;
        }

        function product_options() {
            global $post, $thepostid, $woocommerce;

            $settings   = $this->settings;
            $thepostid  = $post->ID;

            $ship       = $woocommerce->shipping;

            $shipping_methods   = $woocommerce->shipping->load_shipping_methods(false);
            $ship_methods_array = array();
            $categories_array   = array();

            foreach ($shipping_methods as $id => $object) {
                if ($object->enabled == 'yes' && $id != 'multiple_shipping' ) {
                    $ship_methods_array[$id] = $object->method_title;
                }
            }

            //$origin     = $this->get_product_origin( $thepostid );
            $method     = $this->get_product_shipping_method( $thepostid );
            ?>
            <p style="border-top: 1px solid #DFDFDF;">
                <strong><?php _e( 'Shipping Options', 'periship' ); ?></strong>
            </p>
            <p class="form-field method_field">
                <label for="product_method"><?php _e( 'Shipping Methods', 'wc_shipping_multiple_address' ); ?></label>
                <select name="product_method[]" id="product_method" class="chzn-select" multiple>
                    <option value=""></option>
                    <?php
                    foreach ($ship_methods_array as $value => $label):
                        $selected = (in_array($value, $method)) ? 'selected' : '';
                    ?>
                    <option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <script type="text/javascript">jQuery("#product_method").chosen();</script>
            <?php
        }

        function process_metabox( $post_id ) {
            $settings = $this->settings;

            $method     = ( isset($_POST['product_method']) && !empty($_POST['product_method']) ) ? $_POST['product_method'] : false;

            if (! $method ) return;

            // remove all instances of this product is first
            foreach ( $settings as $idx => $setting ) {
                if ( in_array($post_id, $setting['products']) ) {
                    foreach ( $setting['products'] as $pid => $id ) {
                        if ( $id == $post_id ) unset($settings[$idx]['products'][$pid]);
                    }
                }
            }

            // look for a matching zip code
            $matched    = false;
            $zip_match  = false;
            foreach ( $settings as $idx => $setting ) {

                if ( $setting['zip'] == $zip_origin ) {
                    $zip_match = $idx;
                    // methods must match
                    if ( $method && count(array_diff($setting['method'], $method)) == 0 ) {
                        // zip and method matched
                        // add to existing setting
                        $matched = true;
                        $settings[$idx]['products'][] = $post_id;
                        break;
                    }
                }

            }

            if (! $matched ) {
                $settings[] = array(
                    'zip'       => $zip_origin,
                    'products'  => array($post_id),
                    'categories'=> array(),
                    'method'    => $method
                );
            }

            // finally, do some cleanup
            foreach ( $settings as $idx => $setting ) {
                if ( empty($setting['products']) && empty($setting['categories']) ) {
                    unset($settings[$idx]);
                }
            }
            $settings = array_merge($settings, array());

            // update the settings
            update_option( $this->meta_key_settings, $settings );
        }

        function my_account() {
            global $woocommerce;
            $user = wp_get_current_user();

            if ($user->ID == 0) return;

            $page_id = woocommerce_get_page_id( 'account_addresses' );

            echo '<header class="title">
                    <h3>'. __( 'Other Shipping Addresses', 'wc_shipping_multiple_address' ) .'</h3>
                    <a href="'. get_permalink($page_id) .'" class="edit">'. __( 'Add or Edit Addresses', 'woocommerce' ) .'</a>
                </header>';

            $otherAddr = get_user_meta($user->ID, 'wc_other_addresses', true);

            if (empty($otherAddr)) {
                echo '<i>'. __( 'No shipping addresses set up yet.', 'wc_shipping_multiple_address' ) .'</i> ';
                echo '<a href="'. get_permalink($page_id) .'">'. __( 'Set up shipping addresses', 'wc_shipping_multiple_address' ) .'</a>';
            } else {
                foreach ($otherAddr as $address) {
                    echo '<div style="float: left; width: 200px; margin-bottom: 20px;">';
                    $address = array(
                        'first_name'    => $address['shipping_first_name'],
                        'last_name'     => $address['shipping_last_name'],
                        'company'       => $address['shipping_company'],
                        'address_1'     => $address['shipping_address_1'],
                        'address_2'     => $address['shipping_address_2'],
                        'city'          => $address['shipping_city'],
                        'state'         => $address['shipping_state'],
                        'postcode'      => $address['shipping_postcode'],
                        'country'       => $address['shipping_country']
                    );
                    $formatted_address = $woocommerce->countries->get_formatted_address( $address );

                    if (!$formatted_address) _e( 'You have not set up a shipping address yet.', 'woocommerce' ); else echo '<address>'.$formatted_address.'</address>';
                    echo '</div>';
                }
                echo '<div class="clear: both;"></div>';
            }
        }

        function front_scripts() {
            global $woocommerce, $post;

            $user = wp_get_current_user();

            wp_enqueue_script( 'jquery',                null );
            wp_enqueue_script( 'jquery-ui-core',        null, array( 'jquery' ) );
            wp_enqueue_script( 'jquery-ui-mouse',       null, array( 'jquery-ui-core' ) );
            wp_enqueue_script( 'jquery-ui-draggable',   null, array( 'jquery-ui-core' ) );
            wp_enqueue_script( 'jquery-ui-droppable',   null, array( 'jquery-ui-core' ) );
            wp_enqueue_script( 'jquery-masonry',        null, array('jquery-ui-core') );
            wp_enqueue_script( 'thickbox' );
            wp_enqueue_style(  'thickbox' );

            if ($user->ID != 0) {
                wp_enqueue_script( 'multiple_shipping_script', plugins_url( 'front.js', __FILE__) );

                wp_localize_script( 'multiple_shipping_script', 'WC_Shipping', array(
                        // URL to wp-admin/admin-ajax.php to process the request
                        'ajaxurl'          => admin_url( 'admin-ajax.php' )
                    )
                );

                $page_id = woocommerce_get_page_id( 'account_addresses' );
                $url = get_permalink($page_id);
                $url = add_query_arg( 'height', '400', add_query_arg( 'width', '400', add_query_arg( 'addressbook', '1', $url)));
                ?>
                <script type="text/javascript">
                var address = null;
                var wc_ship_url = '<?php echo $url; ?>';
                </script>
                <?php
            }

            wp_enqueue_script( 'jquery-tiptip', plugins_url( 'jquery.tiptip.js', __FILE__ ), array('jquery', 'jquery-ui-core') );

            wp_enqueue_script( 'modernizr', plugins_url( 'modernizr.js', __FILE__ ) );
            wp_enqueue_script( 'multiple_shipping_checkout', plugins_url( 'woocommerce-checkout.js', __FILE__), array( 'woocommerce', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-mouse', ) );
            wp_localize_script( 'multiple_shipping_checkout', 'WCMS', array(
                    // URL to wp-admin/admin-ajax.php to process the request
                    'ajaxurl'   => admin_url( 'admin-ajax.php' ),
                    'base_url'  => plugins_url( '', __FILE__)
                )
            );

            //wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );
            wp_enqueue_style( 'multiple_shipping_styles', plugins_url( 'front.css', __FILE__) );
            wp_enqueue_style( 'tiptip', plugins_url( 'jquery.tiptip.css', __FILE__) );
            ?>
            <style type="text/css">
            .ship_address {
            	float: left;
            	width: 46%;
            	margin: 2%;
            	padding: 8px;
            	background-color: #eee;
            	border-radius: 5px;
            	-moz-border-radius: 5px;
            	-webkit-border-radius: 5px;
            	border: 1px solid #ddd;
            	-webkit-box-sizing: border-box; /* Safari/Chrome, other WebKit */
				-moz-box-sizing: border-box;    /* Firefox, other Gecko */
				box-sizing: border-box;         /* Opera/IE 8+ */
            }
            .ship_address select {
	            width: 100% !important;
            }
            #address_form .address_block {border-bottom: 1px solid #CCC;}
            </style>
            <?php
        }

        function inline_scripts() {
            global $woocommerce;

            $page_id    = woocommerce_get_page_id( 'thanks' );
            $order_id   = (isset($_GET['order'])) ? $_GET['order'] : false;

            if ($order_id):
                $order      = new WC_Order( $order_id );
                $custom     = $order->order_custom_fields;

                if ( is_page($page_id) && isset($custom['_shipping_addresses']) && isset($custom['_shipping_addresses'][0]) && !empty($custom['_shipping_addresses'][0]) ) {
                    $html       = '<div>';
                    $addresses  = unserialize($custom['_shipping_addresses'][0]);
                    $packages   = get_post_meta($order_id, '_wcms_packages', true);

                    foreach ( $packages as $package ) {
                        $html .= '<address>'. $woocommerce->countries->get_formatted_address( $package['full_address'] ) .'</address><br /><hr/>';
                    }
                    $html .= '</div>';
                    $html = str_replace( '"', '\"', $html);
                    $html = str_replace("\n", " ", $html);
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery(jQuery("address")[1]).replaceWith("<?php echo $html; ?>");
            });
            </script>
            <?php
                }
            endif;
        }

        function remove_shipping_calculator() {
            global $woocommerce;

            /*$cart = $woocommerce->cart;

            $available_methods = $woocommerce->shipping->get_available_shipping_methods();

            $sess_cart_address = wcms_session_get( 'cart_addresses' );
            $has_cart_address = (!wcms_session_isset( 'cart_addresses' ) || empty($sess_cart_address)) ? false : true;*/

            if ( isset($woocommerce->session) && isset($woocommerce->session->cart_item_addresses) ) {
                $woocommerce->add_inline_js('jQuery(document).ready(function(){
                    jQuery("tr.shipping").remove();
                });');

                echo '<tr class="multi-shipping">
                    <th>'. __( 'Shipping', 'woocommerce' ) .'</th>
                    <td>'. wp_kses_post( $woocommerce->session->shipping_label ) . ' &mdash; '. woocommerce_price($woocommerce->session->shipping_total) .'</td>
                </tr>';
            }
        }

        function save_address_book() {
            global $woocommerce;
            require_once $woocommerce->plugin_path() .'/classes/class-wc-checkout.php';

            $checkout   = new WC_Checkout();
            $user       = wp_get_current_user();
            //$shipFields = apply_filters( 'woocommerce_shipping_fields', array() );
            $shipFields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );
            $address    = $_POST['address'];

            if ( empty( $address ) ) return;

            $all_empty  = true;

            foreach ( $address as $key => $value ) {
                $new_key = str_replace( 'shipping_', '', $key);
                $address[$new_key] = $value;

                if ( $all_empty && !empty($value) ) {
                    $all_empty = false;
                }
            }

            if ( $all_empty ) {
                die(json_encode(array( 'ack' => 'ERR', 'message' => __( 'Please enter the complete address', 'wc_shipping_multiple_address' ))));
            }

            if ($user->ID == 0) {
                $id         = $_POST['id'];

                $formatted_address  = $woocommerce->countries->get_formatted_address( $address );
                $json_address       = json_encode($address);

                if (!$formatted_address) return;

                $html = '
                <div class="account-address">
                    <address>'. $formatted_address .'</address>
                    <div style="display: none;">';

                ob_start();
                foreach ($shipFields as $key => $field) :
                    $val = (isset($address[$key])) ? $address[$key] : '';
                    $key .= '_'. $id;

                    woocommerce_form_field( $key, $field, $val );
                endforeach;

                do_action( 'woocommerce_after_checkout_shipping_form', $checkout);
                $html .= ob_get_clean();

                $html .= '
                        <input type="hidden" name="addresses[]" value="'. $id .'" />
                    </div>

                    <ul class="items-column" id="items_column_'. $id .'">
                        <li class="placeholder">Drag items here</li>
                    </ul>
                </div>
                ';

                $return = json_encode(array( 'ack' => 'OK', 'id' => $id, 'html' => $html));
                die($return);
                exit;
            } else {
                $addresses  = get_user_meta($user->ID, 'wc_other_addresses', true);

                if (! $addresses) {
                    $addresses = array();
                }

                $vals = '';
                foreach ($address as $key => $value) {
                    $vals .= $value;
                }
                $md5 = md5($vals);

                foreach ($addresses as $addr) {
                    $vals = '';
                    if( !is_array($addr) ) { continue; }
                    foreach ($addr as $key => $value) {
                        $vals .= $value;
                    }
                    $addrMd5 = md5($vals);

                    if ($md5 == $addrMd5) {
                        // duplicate address!
                        die(json_encode(array( 'ack' => 'ERR', 'message' => __( 'Address is already in your address book', 'wc_shipping_multiple_address' ))));
                    }
                }

                // address is unique, save!
                $id = count($addresses);
                $addresses[] = $address;
                update_user_meta($user->ID, 'wc_other_addresses', $addresses);

                foreach ( $address as $key => $value ) {
                    $new_key = str_replace( 'shipping_', '', $key);
                    $address[$new_key] = $value;
                    //unset($address[$key]);
                }

                $formatted_address  = $woocommerce->countries->get_formatted_address( $address );
                $json_address       = json_encode($address);

                if (!$formatted_address) return;

                $html = '
                <div class="account-address">
                    <address>'. $formatted_address .'</address>
                    <div style="display: none;">';

                ob_start();
                foreach ($shipFields as $key => $field) :
                    $val = (isset($address[$key])) ? $address[$key] : '';
                    $key .= '_'. $id;

                    woocommerce_form_field( $key, $field, $val );
                endforeach;

                do_action( 'woocommerce_after_checkout_shipping_form', $checkout);
                $html .= ob_get_clean();

                $html .= '
                        <input type="hidden" name="addresses[]" value="'. $id .'" />
                    </div>

                    <ul class="items-column" id="items_column_'. $id .'">
                        <li class="placeholder">Drag items here</li>
                    </ul>
                </div>
                ';

                $return = json_encode(array( 'ack' => 'OK', 'id' => $id, 'html' => $html));
                die($return);
                exit;
            }
        }

        function duplicate_cart() {
            global $woocommerce;
            require_once $woocommerce->plugin_path() .'/classes/class-wc-checkout.php';
            require_once $woocommerce->plugin_path() .'/classes/class-wc-cart.php';

            $checkout   = new WC_Checkout();
            $cart       = $woocommerce->cart;
            $user       = wp_get_current_user();
            //$shipFields = apply_filters( 'woocommerce_shipping_fields', array() );
            $shipFields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );
            $address    = $_POST['address'];
            $add_id     = ( isset($_POST['address_id']) && !empty($_POST['address_id']) ) ? $_POST['address_id'] : false;
            $orig_cart  = wcms_session_get( 'wcms_original_cart' );

            if ( wcms_session_isset( 'wcms_original_cart' ) && !empty($orig_cart) ) {
                $contents = wcms_session_get( 'wcms_original_cart' );
            } else {
                $contents = $cart->get_cart();
                wcms_session_set( 'wcms_original_cart', $contents );
            }

            $current_cart = $cart->get_cart();
            $add = array();
            foreach ( $contents as $cart_key => $content ) {
                $add[] = array( 'id' => $content['product_id'], 'qty' => $content['quantity'], 'key' => $cart_key);
                $add_qty = $content['quantity'];
                $current_qty = (isset($current_cart[$cart_key])) ? $current_cart[$cart_key]['quantity'] : 0;

                $cart->set_quantity( $cart_key, $current_qty + $add_qty );
            }

            $addresses  = (wcms_session_isset( 'cart_item_addresses' )) ? wcms_session_get( 'cart_item_addresses' ) : array();

            if ( $user->ID > 0 ) {
                $addresses = get_user_meta($user->ID, 'wc_other_addresses', true);
            }

            if ( $add_id !== false ) {
                $address    = $addresses[$add_id];
                $id         = $add_id;
            } else {
                $address    = $_POST['address'];
                $id         = rand(9999,99999);
            }

            foreach ( $address as $key => $value ) {
                $new_key = str_replace( 'shipping_', '', $key);
                $address[$new_key] = $value;
            }

            $formatted_address  = $woocommerce->countries->get_formatted_address( $address );
            $json_address       = json_encode($address);

            if (!$formatted_address) continue;

            if ( $user->ID > 0 ) {
                $addresses  = get_user_meta($user->ID, 'wc_other_addresses', true);

                if (! $addresses) {
                    $addresses = array();
                }

                $vals = '';
                foreach ($address as $key => $value) {
                    $vals .= $value;
                }
                $md5 = md5($vals);
                $saved = false;
                foreach ($addresses as $addr) {
                    $vals = '';
                    if( !is_array($addr) ) { continue; }
                    foreach ($addr as $key => $value) {
                        $vals .= $value;
                    }
                    $addrMd5 = md5($vals);

                    if ($md5 == $addrMd5) {
                        // duplicate address!
                        $saved = true;
                        break;
                    }
                }

                if (! $saved && ! $add_id ) {
                    // address is unique, save!
                    $id = count($addresses);
                    $addresses[] = $address;
                    update_user_meta($user->ID, 'wc_other_addresses', $addresses);
                }
            }

            $html = '
            <div class="account-address">
                <address>'. $formatted_address .'</address>
                <div style="display: none;">';

            ob_start();
            foreach ($shipFields as $key => $field) :
                $val = (isset($address[$key])) ? $address[$key] : '';
                $key .= '_'. $id;

                woocommerce_form_field( $key, $field, $val );
            endforeach;

            do_action( 'woocommerce_after_checkout_shipping_form', $checkout);
            $html .= ob_get_clean();

            $html .= '
                    <input type="hidden" name="addresses[]" value="'. $id .'" />
                </div>

                <ul class="items-column" id="items_column_'. $id .'">';

            foreach ( $add as $product ) {
                $html .= '
                <li class="address-item address-item-'. $id .' ui-draggable address-item-key-'. $product['key'] .'">
                    <span class="qty">'. $product['qty'] .'</span>
                    <h3 class="title">'. get_the_title($product['id']) .'</h3>
                    <a class="remove" href="#"><img style="width: 16px; height: 16px;" src="'. plugins_url( 'delete.png', __FILE__) .'" title="Remove"></a>';

                for ($x = 0; $x < $product['qty']; $x++) {
                    $html .= '<input type="hidden" name="items_'. $id .'[]" value="'. $product['id'] .'">';
                }
                $html .= '</li>';
            }

            $html .= '    </ul>
            </div>
            ';

            $return = json_encode(array( 'ack' => 'OK', 'id' => $id, 'html' => $html));
            die($return);
            exit;
        }

        function address_book() {
            global $woocommerce;
            $user = wp_get_current_user();

            if ($user->ID == 0) return;

            if (isset($_GET['addressbook']) && $_GET['addressbook'] == 1) {
                $addresses = get_user_meta($user->ID, 'wc_other_addresses', true);
            ?>
                <p></p>
                <h2><?php _e( 'Address Book', 'wc_shipping_multiple_address' ); ?></h2>
            <?php
                if (!empty($addresses)):
                    foreach ($addresses as $addr) {
                        if ( empty($addr) ) continue;

                        echo '<div style="float: left; width: 200px;">';
                        $address = array(
                            'first_name'    => $addr['shipping_first_name'],
                            'last_name'     => $addr['shipping_last_name'],
                            'company'       => $addr['shipping_company'],
                            'address_1'     => $addr['shipping_address_1'],
                            'address_2'     => $addr['shipping_address_2'],
                            'city'          => $addr['shipping_city'],
                            'state'         => $addr['shipping_state'],
                            'postcode'      => $addr['shipping_postcode'],
                            'country'       => $addr['shipping_country']
                        );
                        $formatted_address  = $woocommerce->countries->get_formatted_address( $address );
                        $json_address       = json_encode($address);

                        if (!$formatted_address) _e( 'You have not set up a shipping address yet.', 'woocommerce' ); else echo '<address>'.$formatted_address.'</address>';
                        echo '  <textarea style="display:none;">'. $json_address .'</textarea>';
                        echo '  <p><button type="button" class="button address-use">'. __( 'Use this address', 'wc_shipping_multiple_address' ) .'</button></p>';
                        echo '</div>';
                    }
                    echo '<div class="clear: both;"></div>';
                else:
                    echo '<h4>'. __( 'You have no shipping addresses saved.', 'wc_shipping_multiple_address' ) .'</h4>';
                endif;
                ?>
                <script type="text/javascript">
                jQuery(document).ready(function() {
                    jQuery( '.address-use' ).click(function() {
                        var address = jQuery.parseJSON(jQuery(this).parents( 'p' ).prev( 'textarea' ).val());
                        jQuery(this).prop( 'disabled', true);

                        setAddress(address, '<?php echo $_GET['sig']; ?>' );
                        tb_remove();
                    });
                });
                </script>
                <?php
                exit;
            }
        }

        function before_checkout_form() {
            global $woocommerce;

            $sess_item_address = wcms_session_get( 'cart_item_addresses' );
            $has_item_address = (!wcms_session_isset( 'cart_item_addresses' ) || empty( $sess_item_address )) ? false : true;

            if ( !$has_item_address && $woocommerce->cart->needs_shipping() )  {
                $item_allowed   = false;
                $id             = woocommerce_get_page_id( 'multiple_addresses' );

                if (count($woocommerce->cart->cart_contents) > 1) {
                    $item_allowed = true;
                } else {
                    $contents = array_values($woocommerce->cart->cart_contents);
                    if (isset($contents[0]) && $contents[0]['quantity'] > 1) {
                        $item_allowed = true;
                    }
                }

                // do not allow to set multiple addresses if only local pickup is available
                $available_methods = $woocommerce->shipping->get_available_shipping_methods();
                if ( count($available_methods) == 1 && ( isset($available_methods['local_pickup']) || isset($available_methods['local_pickup_plus']) ) ) {
                    $item_allowed = false;
                } elseif (isset($_POST['shipping_method']) && ( $_POST['shipping_method'] == 'local_pickup' || $_POST['shipping_method'] == 'local_pickup_plus' ) ) {
                    $item_allowed = false;
                }

                $css = 'style="display:none;"';

                if ($item_allowed) {
                    $css = '';
                }

                echo '<p class="woocommerce-info woocommerce_message" id="wcms_message" '. $css .'>
                        '. self::$lang['notification'] .'
                        <a class="button" href="'. get_permalink($id) .'">'. self::$lang['btn_items'] .'</a>
                      </p>';
            }
        }

        function before_shipping_form($checkout = null) {
            global $woocommerce;
            $cart = $woocommerce->cart;

            // if there is only 1 item in the cart, do not display the button
            if (count($cart->cart_contents) == 1) {
                // if quantity == 1, no need to display the button
                foreach ($cart->cart_contents as $prod) {
                    if ($prod['quantity'] == 1) {
                        return;
                    }
                }
            }

            $id = woocommerce_get_page_id( 'multiple_addresses' );

            $sess_item_address = wcms_session_get( 'cart_item_addresses' );
            $sess_cart_address = wcms_session_get( 'cart_addresses' );
            $has_item_address = (!wcms_session_isset( 'cart_item_addresses' ) || empty($sess_item_address)) ? false : true;
            $has_cart_address = (!wcms_session_isset( 'cart_addresses' ) || empty($sess_cart_address)) ? false : true;
            $inline = false;

            if ( $has_item_address ) {
                $inline = 'jQuery(function() {
                    var col = jQuery("#customer_details .col-2");

                    jQuery(col).find("#shiptobilling-checkbox")
                        .attr("checked", true)
                        .hide();

                    jQuery(col).find(".shipping_address").remove();

                    jQuery(\'<p><a href=\"'. get_permalink($id) .'\" class=\"button button-primary\">'. __( 'Modify/Add Address', 'wc_shipping_multiple_address' ) .'</a></p>\').insertAfter("#customer_details .col-2 h3:first");
                });';
                //$inline = 'jQuery(function() {jQuery("#customer_details .col-2").html("<h3>'. __( 'Shipping Address', 'woocommerce' ) .'</h3> <p><a href=\"'. get_permalink($id) .'\" class=\"button button-primary\">'. __( 'Modify/Add Address', 'wc_shipping_multiple_address' ) .'</a></p> <input id=\"shiptobilling-checkbox\" style=\"display: none;\" class=\"input-checkbox\" checked=\"checked\" type=\"checkbox\" name=\"shiptobilling\" value=\"1\">");});';
            } elseif ( $has_cart_address ) {
                $inline = 'jQuery(function() {
                    var col = jQuery("#customer_details .col-2");

                    jQuery(col).find("#shiptobilling-checkbox")
                        .attr("checked", true)
                        .hide();

                    jQuery(col).find(".shipping_address").remove();

                    jQuery(\'<p><a href=\"'. add_query_arg( 'cart', 1, get_permalink($id)) .'\" class=\"button button-primary\">'. __( 'Modify/Add Address', 'wc_shipping_multiple_address' ) .'</a></p>\').insertAfter("#customer_details .col-2 h3:first");

                });';
                //$inline = 'jQuery(function() {jQuery("#customer_details .col-2").html("<h3>'. __( 'Shipping Address', 'woocommerce' ) .'</h3> <p><a href=\"'. add_query_arg( 'cart', 1, get_permalink($id)) .'\" class=\"button button-primary\">'. __( 'Modify/Add Address', 'wc_shipping_multiple_address' ) .'</a></p> <input id=\"shiptobilling-checkbox\" style=\"display: none;\" class=\"input-checkbox\" checked=\"checked\" type=\"checkbox\" name=\"shiptobilling\" value=\"1\">");});';
            }

            if ( $inline ) {
                $woocommerce->add_inline_js($inline);
            }
        }

        function woocommerce_settings($settings) {
            $end = array_pop($settings);
            $settings[] = array(
                'name'  =>  __( 'Multiple Shipping Addresses', 'wc_shipping_multiple_address' ),
                'desc'  => __( 'Page contents: [woocommerce_select_multiple_addresses] Parent: "Checkout"', 'woocommerce' ),
                'id'    => 'woocommerce_multiple_addresses_page_id',
                'type'  => 'single_select_page',
                'std'   => true,
                'class' => 'chosen_select_nostd',
                'css'   => 'min-width:300px;',
                'desc_tip' => 1
            );
            $settings[] = $end;

            return $settings;
        }

        function checkout_process($order_id) {
            global $woocommerce;

            $packages = $woocommerce->cart->get_shipping_packages();

            if ( $packages )
                update_post_meta( $order_id, '_shipping_packages', $packages );

            $sess_item_address  = wcms_session_isset( 'cart_item_addresses' ) ? wcms_session_get( 'cart_item_addresses' ) : false;
            $sess_packages      = wcms_session_isset( 'wcms_packages' ) ? wcms_session_get( 'wcms_packages' ) : false;
            $sess_methods       = wcms_session_isset( 'shipping_methods' ) ? wcms_session_get( 'shipping_methods' ) : false;

            if ($sess_item_address !== false && !empty($sess_item_address)) {
                update_post_meta( $order_id, '_shipping_addresses', $sess_item_address );
                wcms_session_delete( 'cart_item_addresses' );

                // remove the shipping address
                update_post_meta( $order_id, '_shipping_first_name', '' );
                update_post_meta( $order_id, '_shipping_last_name', '' );
                update_post_meta( $order_id, '_shipping_company', '' );
                update_post_meta( $order_id, '_shipping_address_1', '' );
                update_post_meta( $order_id, '_shipping_address_2', '' );
                update_post_meta( $order_id, '_shipping_city', '' );
                update_post_meta( $order_id, '_shipping_postcode', '' );
                update_post_meta( $order_id, '_shipping_country', '' );
                update_post_meta( $order_id, '_shipping_state', '' );
            }

            if ( $sess_packages !== false && !empty($sess_packages) ) {
                update_post_meta( $order_id, '_wcms_packages', $sess_packages);
            }

            if ( $sess_methods !== false && !empty($sess_methods) ) {
                $methods = $sess_methods;
                update_post_meta( $order_id, '_shipping_methods', $methods );
            }
        }

        function add_item_meta( $meta, $values ) {
            global $woocommerce;

            $packages   = wcms_session_get( 'wcms_packages' );
            $methods    = wcms_session_isset( 'shipping_methods' ) ? wcms_session_get( 'shipping_methods' ) : false;

            if ( $methods !== false && !empty($methods) ) {
                if ( isset($values['package_idx']) && isset($packages[$values['package_idx']]) ) {
                    $meta->add( 'Shipping Method', $methods[$values['package_idx']]['label']);
                }
            }

            //return $item;
        }

        function checkout_validation( $post ) {
            global $woocommerce;

            if ( $post['shipping_method'] == 'multiple_shipping' ) {
                //if (! isset($_SESSION['cart_item_addresses']) || empty($_SESSION['cart_item_addresses']) ) {
                //    $woocommerce->add_error( __( 'Shipping addresses need to be defined', 'wc_followup_emails' ) );
                //} else {
                    $packages   = $woocommerce->cart->get_shipping_packages();
                    $has_empty  = false;
                    foreach ( $packages as $package ) {
                        if ( empty($package['destination']['country']) && empty($package['destination']['postcode']) ) {
                            $has_empty = true;
                        }
                    }

                    if ( $has_empty ) {
                        $woocommerce->add_error( __( 'One or more items has no shipping address.', 'wc_followup_emails' ) );
                    }
                //}
            }
        }

        function account_addresses() {
            global $woocommerce;
            require_once $woocommerce->plugin_path() .'/classes/class-wc-checkout.php';

            $checkout   = new WC_Checkout();
            $user       = wp_get_current_user();
            //$shipFields = apply_filters( 'woocommerce_shipping_fields', array() );
            $shipFields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );

            if ($user->ID == 0) return;

            $otherAddr = get_user_meta($user->ID, 'wc_other_addresses', true);
            echo '<form action="" method="post" id="address_form">';
            echo '<p><a class="button add_address" href="#">'. __( 'Add another', 'wc_shipping_multiple_address' ) .'</a></p>';
            if (! empty($otherAddr)) {
                echo '<div id="addresses">';

                foreach ($otherAddr as $idx => $address) {
                    echo '<div class="shipping_address address_block" id="shipping_address_'. $idx .'">';
                    echo '<p align="right"><a href="#" class="button delete">delete</a></p>';
                    do_action( 'woocommerce_before_checkout_shipping_form', $checkout);

                    foreach ($shipFields as $key => $field) {
                        $val = '';

                        if (isset($address[$key])) {
                            $val = $address[$key];
                        }
                        $key .= '[]';
                        woocommerce_form_field( $key, $field, $val );
                    }

                    do_action( 'woocommerce_after_checkout_shipping_form', $checkout);
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<div id="addresses">';
                foreach ($shipFields as $key => $field) :
                    $key .= '[]';
                    $val = '';

                    woocommerce_form_field( $key, $field, $val );
                endforeach;

                echo '</div>';
            }
            echo '<div class="form-row">
                    <input type="hidden" name="shipping_account_address_action" value="save" />
                    <input type="submit" name="set_addresses" value="'. __( 'Save Addresses', 'wc_shipping_multiple_address' ) .'" class="button alt" />
                </div>';
            echo '</form>';
            ?>
            <script type="text/javascript">
            var tmpl = '<div class="shipping_address address_block"><p align="right"><a href="#" class="button delete">delete</a></p>';

            tmpl += '\
            <?php
            foreach ($shipFields as $key => $field) :
                $key .= '[]';
                $val = '';
                $field['return'] = true;
                $row = woocommerce_form_field( $key, $field, $val );
                echo str_replace("\n", "\\\n", $row);
            endforeach;
            ?>
            ';

            tmpl += '</div>';
            jQuery(".add_address").click(function(e) {
                e.preventDefault();

                jQuery("#addresses").append(tmpl);
            });

            jQuery(".delete").live("click", function(e) {
                e.preventDefault();
                jQuery(this).parents("div.address_block").remove();
            });

            jQuery(document).ready(function() {
                jQuery("#address_form").submit(function() {
                    var valid = true;
                    jQuery("input[type=text],select").each(function() {
                        if (jQuery(this).prev("label").children("abbr").length == 1 && jQuery(this).val() == "") {
                            jQuery(this).focus();
                            valid = false;
                            return false;
                        }
                    });
                    return valid;
                });
            });
            </script>
            <?php
        }

        function draw_form() {
            global $woocommerce;

            if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
                require_once $woocommerce->plugin_path() .'/classes/class-wc-checkout.php';

                $user       = wp_get_current_user();
                $cart       = $woocommerce->cart;
                $checkout   = new WC_Checkout();
                $contents   = $cart->cart_contents;
                //$shipFields = apply_filters( 'woocommerce_shipping_fields', array() );
                $shipFields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );
                $tips       = array();

                if (! empty($contents)) {
                    require 'address-form.php';
                }
            } else {
                // load order and display the addresses
                $order_id = (int)$_GET['order_id'];
                $order = new WC_Order($order_id);

                if ($order_id == 0 || !$order) wp_die(__( 'Order could not be found', 'woocommerce' ) );

                $packages           = get_post_meta($order_id, '_wcms_packages', true);

                if ( !$packages ) wp_die(__( 'This order does not ship to multiple addresses', 'wc_shipping_multiple_address' ) );

                // load the address fields
                require_once $woocommerce->plugin_path() .'/classes/class-wc-checkout.php';
                require_once $woocommerce->plugin_path() .'/classes/class-wc-cart.php';
                $checkout   = new WC_Checkout();
                $cart       = new WC_Cart();
                //$shipFields = apply_filters( 'woocommerce_shipping_fields', array() );
                $shipFields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );

                echo '<table class="shop_tabe"><thead><tr><th class="product-name">'. __( 'Product', 'woocommerce' ) .'</th><th class="product-quantity">'. __( 'Qty', 'woocommerce' ) .'</th><th class="product-address">'. __( 'Address', 'woocommerce' ) .'</th></thead>';
                echo '<tbody>';

                $tr_class = '';
                foreach ( $packages as $x => $package ) {
                    $products = $package['contents'];
                    $item_meta = '';
                    foreach ( $products as $i => $product ) {
                        $tr_class = ($tr_class == '' ) ? 'alt-table-row' : '';

                        if (isset($product['data']->item_meta) && !empty($product['data']->item_meta)) {
                            $item_meta .= '<pre>';
                            foreach ($product['data']->item_meta as $meta) {
                                $item_meta .= $meta['meta_name'] .': '. $meta['meta_value'] ."\n";
                            }
                            $item_meta .= '</pre>';
                        }

                        echo '<tr class="'. $tr_class .'">';
                        echo '<td class="product-name"><a href="'. get_permalink($product['data']->id) .'">'. get_the_title($product['data']->id) .'</a><br />'. $item_meta .'</td>';
                        echo '<td class="product-quantity">'. $product['quantity'] .'</td>';
                        echo '<td class="product-address"><address>'. $woocommerce->countries->get_formatted_address( $package['full_address'] ) .'</td>';
                        echo '</tr>';
                    }
                }

                echo '</table>';
            }
        }

        function email_shipping_table($order) {
            global $woocommerce;
            $order_id           = $order->id;
            $addresses          = get_post_meta($order_id, '_shipping_addresses', true);
            $methods            = get_post_meta($order_id, '_shipping_methods', true);
            $packages           = get_post_meta($order_id, '_wcms_packages', true);
            $items              = $order->get_items();
            $available_methods  = $woocommerce->shipping->load_shipping_methods();

            //if (empty($addresses)) return;
            if ( !$packages || count($packages) == 1 ) return;

            // load the address fields
            require_once $woocommerce->plugin_path() .'/classes/class-wc-checkout.php';
            require_once $woocommerce->plugin_path() .'/classes/class-wc-cart.php';
            $checkout   = new WC_Checkout();
            $cart       = new WC_Cart();
            //$shipFields = apply_filters( 'woocommerce_shipping_fields', array() );
            $shipFields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );

            echo '<p><strong>'. __( 'This order ships to multiple addresses.', 'wc_shipping_multiple_address' ) .'</strong></p>';
            echo '<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">';
            echo '<thead><tr>';
            echo '<th scope="col" style="text-align:left; border: 1px solid #eee;">'. __( 'Product', 'woocommerce' ) .'</th>';
            echo '<th scope="col" style="text-align:left; border: 1px solid #eee;">'. __( 'Qty', 'woocommerce' ) .'</th>';
            echo '<th scope="col" style="text-align:left; border: 1px solid #eee;">'. __( '', 'woocommerce' ) .'</th>';
            echo '</thead><tbody>';

            foreach ( $packages as $x => $package ) {
                $products   = $package['contents'];
                $method     = $methods[$x]['label'];

                foreach ( $available_methods as $ship_method ) {
                    if ($ship_method->id == $method) {

                        //$method = apply_filters( 'woocommerce_order_shipping_method', ucwords( $ship_method->title ) );
                        $method = $ship_method->get_title();
                        break;
                    }
                }

                $address = ( isset($package['full_address']) && !empty($package['full_address']) ) ? $woocommerce->countries->get_formatted_address($package['full_address']) : '';

                foreach ( $products as $i => $product ) {
                    echo '<tr>';
                    echo '<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">'. get_the_title($product['data']->id) .'<br />'. $cart->get_item_data($product, true) .'</td>';
                    echo '<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">'. $product['quantity'] .'</td>';
                    echo '<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">'.  $address .'<br/><em>( '. $method .' )</em></td>';
                    echo '</tr>';
                }
            }
            echo '</table>';
        }

        function save_addresses() {
            global $woocommerce;

            require_once $woocommerce->plugin_path() .'/classes/class-wc-cart.php';
            require_once $woocommerce->plugin_path() .'/classes/class-wc-checkout.php';
            $cart       = new WC_Cart();
            $woocommerce->checkout = new WC_Checkout();
            $checkout   = $woocommerce->checkout;
            //$fields = apply_filters( 'woocommerce_shipping_fields', array() );
            $fields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );

            $cart->get_cart_from_session();
            $cart_items = $cart->get_cart();

            if (isset($_POST['shipping_address_action']) && $_POST['shipping_address_action'] == 'save' ) {
                $data   = array();
                $rel    = array();

                $address_ids = $_POST['addresses'];

                foreach ( $address_ids as $a_id ) {
                    if (! isset($_POST['items_'. $a_id]) || empty($_POST['items_'. $a_id]) ) continue;

                    $i = 1;

                    if ( isset($rel[$a_id]) && !empty($rel[$a_id])) continue;
                    ///echo '<pre>'. print_r($_POST, true); exit;
                    foreach ( $_POST['items_'. $a_id] as $x => $cart_key ) {
                        $pid            = $cart_items[$cart_key]['product_id'];
                        $rel[$a_id][]   = $cart_key;

                        $item_key   = $cart_key;
                        $sig        = $item_key .'_'. $pid .'_';

                        while ( isset($data['shipping_first_name_'. $sig . $i]) ) {
                            $i++;
                        }
                        $sig .= $i;
                        //echo '<pre>'. print_r($fields, true); exit;
                        if ( $fields ) foreach ( $fields as $key => $field ) :
                            $data[$key .'_'. $sig] = $_POST[$key .'_'. $a_id];
                        endforeach;

                        $cart_address_ids_session = wcms_session_get( 'cart_address_ids' );

                        if (!wcms_session_isset( 'cart_address_ids' ) || ! in_array($sig, $cart_address_ids_session) ) {
                            $cart_address_sigs_session = wcms_session_get( 'cart_address_sigs' );
                            $cart_address_sigs_session[$sig] = $a_id;
                            wcms_session_set( 'cart_address_sigs', $cart_address_sigs_session);
                        }
                    }

                }
                //echo '<pre>'. print_r($_POST, true) .'</pre>';
                //echo '<pre>'. print_r($data, true); exit;
                wcms_session_set( 'cart_item_addresses', $data );
                wcms_session_set( 'address_relationships', $rel );

                // redirect to the checkout page
                $checkout_url = $woocommerce->cart->get_checkout_url();
                wp_redirect($checkout_url);
                exit;
            } elseif (isset($_POST['shipping_account_address_action']) && $_POST['shipping_account_address_action'] == 'save' ) {
                unset($_POST['shipping_account_address_action'], $_POST['set_addresses']);

                $addresses = array();
                foreach ($_POST as $key => $values) {
                    foreach ($values as $idx => $val) {
                        $addresses[$idx][$key] = $val;
                    }
                }

                $user = wp_get_current_user();
                update_user_meta($user->ID, 'wc_other_addresses', $addresses);
                $woocommerce->add_message(__( 'Addresses have been saved', 'wc_shipping_multiple_address' ) );
                $page_id = woocommerce_get_page_id( 'myaccount' );
                wp_redirect(get_permalink($page_id));
                exit;
            }
        }

        function order_meta_box($type) {
            global $post;

            $addresses  = get_post_meta($post->ID, '_shipping_addresses', true);
            $methods    = get_post_meta($post->ID, '_shipping_methods', true);

            if (! empty($addresses) || $methods) {
                add_meta_box(
                    'wc_multiple_shipping',
                    __( 'Order Shipping Addresses', 'wc_shipping_multiple_address' ),
                    array( $this, 'display_meta_box' ),
                    'shop_order' ,
                    'normal',
                    'core'
                );
            }
        }

        function meta_box_css() {
            echo '
            <style type="text/css">
            .item-addresses-holder {display: block;}
            .item-address-box {float: left; width: 175px; border-right: 1px solid #ccc; padding: 0 0 20px 30px;}
            div.item-addresses-holder div.item-address-box:last-child {border-right: none !important;}
            .clear { clear: both; }
            </style>
            ';
        }

        function display_meta_box($post) {
            global $woocommerce;

            $order              = new WC_Order($post->ID);
            $addresses          = get_post_meta($post->ID, '_shipping_addresses', true);
            $packages           = get_post_meta($post->ID, '_wcms_packages', true);
            $methods            = get_post_meta($post->ID, '_shipping_methods', true);
            $available_methods  = $woocommerce->shipping->shipping_methods;

            if ( !$packages ) return;

            // load the address fields
            require_once $woocommerce->plugin_path() .'/classes/class-wc-checkout.php';
            require_once $woocommerce->plugin_path() .'/classes/class-wc-cart.php';
            $checkout   = new WC_Checkout();
            $cart       = new WC_Cart();
            //$shipFields = apply_filters( 'woocommerce_shipping_fields', array() );
            $shipFields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );

            echo '<div class="item-addresses-holder">';
            $tips = array();

            foreach ( $packages as $x => $package ) {
                $products           = $package['contents'];
                echo '<div class="item-address-box">';
                foreach ( $products as $i => $product ) {
                    $tip = $cart->get_item_data($product, true);
                    if (!empty($tip)) {
                        $tips[$x][$product['data']->id][$i] = $cart->get_item_data($product, true);
                    }

                    echo '<h2 style="margin: 0;">'. get_the_title($product['data']->id) .' <small>(qty: '. $product['quantity'] .' )</small>';

                    if ( ! empty($tip) ) {
                        echo '<img width="16" height="16" class="help_tip pkg_tip_'. $x .'_'. $product['data']->id .'_'. $i .'" src="'. $woocommerce->plugin_url() .'/assets/images/help.png" />';
                    }
                    echo '</h2>';
                }

                if ( isset($package['full_address']) && !empty($package['full_address']) ) {
                    echo '<div class="shipping_data"><div class="address">'. $woocommerce->countries->get_formatted_address( $package['full_address'] ) .'</div><br />';

                    if ( isset($package['full_address']['notes']) && !empty($package['full_address']['notes']) ) {
                        echo '<blockquote>Shipping Notes:<br /><em>&#8220;'. $package['full_address']['notes'] .'&#8221;</em></blockquote>';
                    }

                    echo '<a class="edit_shipping_address" href="#">( '. __( 'Edit', 'woocommerce' ) .' )</a><br />';

                    // Display form
                    echo '<div class="edit_shipping_address" style="display:none;">';
                    //$val = isset($_SESSION['cart_item_addresses'][$key]) ? $_SESSION['cart_item_addresses'][$key] : '';
                    if ( $shipFields ) foreach ( $shipFields as $key => $field ) :
                        $key        = str_replace( 'shipping_', '', $key);
                        $addr_key   = $key;
                        $key        = 'pkg_'. $key .'_'. $x;
                        //woocommerce_form_field( $key, $field, $package['full_address'][$addr_key] );
                        if (!isset($field['type'])) $field['type'] = 'text';
                        switch ($field['type']) {
                            case "select" :
                                woocommerce_wp_select( array( 'id' => $key, 'label' => $field['label'], 'options' => $field['options'], 'value' => $package['full_address'][$addr_key] ) );
                            break;
                            default :
                                woocommerce_wp_text_input( array( 'id' => $key, 'label' => $field['label'], 'value' => $package['full_address'][$addr_key] ) );
                            break;

                        }
                    endforeach;
                    echo '<input type="hidden" name="edit_address[]" value="'. $x .'" />';
                    echo '</div></div>';
                }

                $method         = $methods[$x]['label'];

                foreach ( $available_methods as $ship_method ) {
                    if ($ship_method->id == $method) {
                        $method = $ship_method->title;
                        break;
                    }
                }
                echo '<em>'. $method .'</em>';

                echo '</div>';
            }
            echo '</div>';
            echo '<div class="clear"></div>';

            if (!empty($tips)):
            ?>
            <script type="text/javascript">
            <?php
                foreach ( (array)$tips[$x] as $pId => $ts):
                    foreach ($ts as $i => $tip):
            ?>
            jQuery(".pkg_tip_<?php echo $x .'_'. $pId .'_'. $i; ?>").tipTip({
                content: "<?php echo str_replace( '"', '\"', $tip); ?>"
            });
                <?php
                    endforeach;
                endforeach;
            ?>

            </script>
            <?php
            endif;
            $woocommerce->add_inline_js( 'jQuery(".shipping_data a.edit_shipping_address").click(function(e) {
                    e.preventDefault();
                    jQuery(this).closest(".shipping_data").find("div.edit_shipping_address").show();
                });' );
        }

        function update_order_addresses( $post_id, $post ) {
            global $woocommerce;

            $packages = get_post_meta($post_id, '_wcms_packages', true);

            if ( $packages && isset($_POST['edit_address']) && count($_POST['edit_address']) > 0 ) {
                foreach ( $_POST['edit_address'] as $idx ) {
                    if (! isset($packages[$idx]) ) continue;

                    $address = array(
                        'first_name'        => isset($_POST['pkg_first_name_'. $idx]) ? $_POST['pkg_first_name_'. $idx] : '',
                        'last_name'         => isset($_POST['pkg_last_name_'. $idx]) ? $_POST['pkg_last_name_'. $idx] : '',
                        'company'           => isset($_POST['pkg_company_'. $idx]) ? $_POST['pkg_company_'. $idx] : '',
                        'address_1'         => isset($_POST['pkg_address_1_'. $idx]) ? $_POST['pkg_address_1_'. $idx] : '',
                        'address_2'         => isset($_POST['pkg_address_2_'. $idx]) ? $_POST['pkg_address_2_'. $idx] : '',
                        'city'              => isset($_POST['pkg_city_'. $idx]) ? $_POST['pkg_city_'. $idx] : '',
                        'state'             => isset($_POST['pkg_state_'. $idx]) ? $_POST['pkg_state_'. $idx] : '',
                        'postcode'          => isset($_POST['pkg_postcode_'. $idx]) ? $_POST['pkg_postcode_'. $idx] : '',
                        'country'           => isset($_POST['pkg_country_'. $idx]) ? $_POST['pkg_country_'. $idx] : '',
                    );

                    $packages[$idx]['full_address'] = $address;
                }
                update_post_meta( $post_id, '_wcms_packages', $packages );
            }
        }

        function view_order($order_id) {
            global $woocommerce;

            $addresses = get_post_meta($order_id, '_shipping_addresses', true);

            if (empty($addresses)) return;

            $page_id = woocommerce_get_page_id( 'multiple_addresses' );
            $url = add_query_arg( 'order_id', $order_id, get_permalink($page_id));
            echo '<div class="woocommerce_message woocommerce-message">'. __( 'This order ships to multiple addresses.', 'wc_shipping_multiple_address' ) .' <a class="button" href="'. $url .'">'. __( 'View Addresses', 'wc_shipping_multiple_address' ) .'</a></div>';
        }

        function display_shipping_methods() {
            global $woocommerce;

            //unset($_SESSION['shipping_methods']); // @todo test if this is really needed
            $packages = $woocommerce->cart->get_shipping_packages();

            if (count($packages) < 2) {
                //return;
            }

            $sess_cart_addresses = wcms_session_get( 'cart_item_addresses' );
            if ( isset($sess_cart_addresses) && !empty($sess_cart_addresses) ) {
                // always allow users to select shipping
                $this->render_shipping_row($packages, 0);
            } else {
                if ( $this->packages_have_different_origins($packages) || $this->packages_have_different_methods($packages) ) {
                    // show shipping methods available to each package
                    $this->render_shipping_row($packages, 1);
                } else {
                    if ( $this->packages_contain_methods($packages) ) {
                        // methods must be combined
                        $this->render_shipping_row($packages, 2);
                    }
                }
            }
        }

        /**
         * @param array $packages
         * @param int $type 0=multi-shipping; 1=different packages; 2=same packages
         */
        function render_shipping_row($packages, $type = 2) {
            global $woocommerce;

            $page_id            = woocommerce_get_page_id( 'multiple_addresses' );
            $available_methods  = $woocommerce->shipping->get_available_shipping_methods();
            $post               = array();

            if ( isset($_POST['post_data']) ) {
                parse_str($_POST['post_data'], $post);
            }

            if ( $type == 0 || $type == 1):

            ?>
            <tr class="multi_shipping">
                <td style="vertical-align: top;" colspan="<?php if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) echo '2'; else echo '1'; ?>">
                    <?php _e( 'Shipping Methods', 'wc_shipping_multiple_address' ); ?>

                    <div id="shipping_addresses">
                        <?php
                        $tips = array();
                        foreach ($packages as $x => $package):

                            if ( empty($package['destination']['country']) && empty($package['destination']['state']) && empty($package['destination']['postcode']) ) {
                                // no shipping needed
                                continue;
                            }

                            $shipping_methods       = array();
                            $products               = $package['contents'];
                            $sess_shipping_methods  = wcms_session_get( 'shipping_methods' );

                            if ( $type == 0 ):
                        ?>
                        <div class="ship_address">
                            <dl>
                            <?php
                                foreach ($products as $i => $product):
                                    $tip = $woocommerce->cart->get_item_data($product, true);
                                    if (!empty($tip)) {
                                        $tips[$x][$product['data']->id][$i] = $woocommerce->cart->get_item_data($product, true);
                                    }
                            ?>
                            <dd>
                                <strong><?php echo get_the_title($product['data']->id); ?> x <?php echo $product['quantity']; ?></strong>
                                    <?php if (!empty($tip)): ?>
                                <img width="16" height="16" class="help_tip pkg_tip_<?php echo $x .'_'. $product['data']->id .'_'. $i; ?>" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png" />
                                    <?php endif; ?>
                            </dd>
                                <?php endforeach; ?>
                            </dl>
                                <?php echo '<address>'. $woocommerce->countries->get_formatted_address( $package['full_address'] ) .'</address><br />'; ?>
                                <?php
                                // If at least one shipping method is available
                                $ship_package['rates'] = array();

                                foreach ( $woocommerce->shipping->load_shipping_methods( $package ) as $shipping_method ) {

                                    if ( isset($package['method']) && !in_array($shipping_method->id, $package['method']) ) continue;

                                    if ( $shipping_method->is_available( $package ) ) {

                                        // Reset Rates
                                        $shipping_method->rates = array();

                                        // Calculate Shipping for package
                                        $shipping_method->calculate_shipping( $package );

                                        // Place rates in package array
                                        if ( ! empty( $shipping_method->rates ) && is_array( $shipping_method->rates ) )
                                            foreach ( $shipping_method->rates as $rate )
                                                $ship_package['rates'][$rate->id] = $rate;
                                    }

                                }

                                foreach ( $ship_package['rates'] as $method ) {
                                    if ( $method->id == 'multiple_shipping' ) continue;

                                    $method->label = esc_html( $method->label );

                                    if ( $method->cost > 0 ) {
                                        $method->label .= ' &mdash; ';
                                        //$method->label .= ' '. $method->get_shipping_tax();
                                        $method->label .= woocommerce_price( $method->cost + $method->get_shipping_tax() );

                                        if ( $method->get_shipping_tax() > 0 && ! $woocommerce->cart->prices_include_tax ) {
                                            $method->label .= ' '.$woocommerce->countries->inc_tax_or_vat();
                                        }

                                    }
                                    $shipping_methods[] = $method;
                                }

                                // Print a single available shipping method as plain text
                                if ( 1 === count( $shipping_methods ) ) {
                                    $method = $shipping_methods[0];

                                    echo $method->label;
                                    echo '<input type="hidden" class="shipping_methods" name="shipping_methods['. $x .']" value="'.esc_attr( $method->id ).'||'. strip_tags($method->label) .'">';

                                // Show multiple shipping methods in a select list
                                } elseif ( count( $shipping_methods ) > 1 ) {
                                    echo '<select class="shipping_methods" name="shipping_methods['. $x .']">';
                                    foreach ( $shipping_methods as $method ) {
                                        if ($method->id == 'multiple_shipping' ) continue;

                                        echo '<option value="'.esc_attr( $method->id ).'||'. strip_tags($method->label) .'" '.selected( $method->id, $sess_shipping_methods[$x]['id'], false).'>';
                                        echo strip_tags( $method->label );
                                        echo '</option>';
                                    }
                                    echo '</select>';
                                } else {
                                    echo '<p>'.__( 'Sorry, it seems that there are no available shipping methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ).'</p>';
                                }

                                $sess_cart_addresses = wcms_session_get( 'cart_item_addresses' );
                                if ( $sess_cart_addresses && !empty($sess_cart_addresses) ) {
                                    echo '<p><a href="'. get_permalink($page_id) .'" class="modify-address-button">'. __( 'Modify address', 'wc_shipping_multiple_address' ) .'</a></p>';
                                }
                        ?>
                        </div>
                        <?php
                            elseif ($type == 1):
                        ?>
                        <div class="ship_address">
                            <dl>
                            <?php
                                foreach ($products as $i => $product):
                                    $tip = $woocommerce->cart->get_item_data($product, true);
                                    if (!empty($tip)) {
                                        $tips[$x][$product['data']->id][$i] = $woocommerce->cart->get_item_data($product, true);
                                    }
                            ?>
                            <dd>
                                <strong><?php echo get_the_title($product['data']->id); ?> x <?php echo $product['quantity']; ?></strong>
                                    <?php if (!empty($tip)): ?>
                                <img width="16" height="16" class="help_tip pkg_tip_<?php echo $x .'_'. $product['data']->id .'_'. $i; ?>" src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png" />
                                    <?php endif; ?>
                            </dd>
                                <?php endforeach; ?>
                            </dl>
                            <?php
                                // If at least one shipping method is available
                                // Calculate shipping method rates
                                $ship_package['rates'] = array();

                                foreach ( $woocommerce->shipping->load_shipping_methods( $package ) as $shipping_method ) {

                                    if ( isset($package['method']) && !in_array($shipping_method->id, $package['method']) ) continue;

                                    if ( $shipping_method->is_available( $package ) ) {

                                        // Reset Rates
                                        $shipping_method->rates = array();

                                        // Calculate Shipping for package
                                        $shipping_method->calculate_shipping( $package );

                                        // Place rates in package array
                                        if ( ! empty( $shipping_method->rates ) && is_array( $shipping_method->rates ) )
                                            foreach ( $shipping_method->rates as $rate )
                                                $ship_package['rates'][$rate->id] = $rate;
                                    }

                                }

                                foreach ( $ship_package['rates'] as $method ) {
                                    if ( $method->id == 'multiple_shipping' ) continue;

                                    $method->label = esc_html( $method->label );

                                    if ( $method->cost > 0 ) {
                                        $method->label .= ' &mdash; ';

                                        // Append price to label using the correct tax settings
                                        if ( $woocommerce->cart->display_totals_ex_tax || ! $woocommerce->cart->prices_include_tax ) {
                                            $method->label .= woocommerce_price( $method->cost );
                                            if ( $method->get_shipping_tax() > 0 && $woocommerce->cart->prices_include_tax ) {
                                                $method->label .= ' '.$woocommerce->countries->ex_tax_or_vat();
                                            }
                                        } else {
                                            $method->label .= woocommerce_price( $method->cost + $method->get_shipping_tax() );
                                            if ( $method->get_shipping_tax() > 0 && ! $woocommerce->cart->prices_include_tax ) {
                                                $method->label .= ' '.$woocommerce->countries->inc_tax_or_vat();
                                            }
                                        }
                                    }
                                    $shipping_methods[] = $method;
                                }

                                // Print a single available shipping method as plain text
                                if ( 1 === count( $shipping_methods ) ) {
                                    $method = $shipping_methods[0];

                                    echo $method->label;
                                    echo '<input type="hidden" class="shipping_methods" name="shipping_methods['. $x .']" value="'.esc_attr( $method->id ).'||'. strip_tags($method->label) .'">';

                                // Show multiple shipping methods in a select list
                                } elseif ( count( $shipping_methods ) > 1 ) {
                                    echo '<select class="shipping_methods" name="shipping_methods['. $x .']">';
                                    foreach ( $shipping_methods as $method ) {
                                        if ($method->id == 'multiple_shipping' ) continue;

                                        echo '<option value="'.esc_attr( $method->id ).'||'. strip_tags($method->label) .'" '.selected( $method->id, $sess_shipping_methods[$x]['id'], false).'>';
                                        echo strip_tags( $method->label );
                                        echo '</option>';
                                    }
                                    echo '</select>';
                                } else {
                                    echo '<p>'.__( 'Sorry, it seems that there are no available shipping methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ).'</p>';
                                }

                                $sess_cart_addresses = wcms_session_get( 'cart_item_addresses' );
                                if ( $sess_cart_addresses && !empty($sess_cart_addresses) ) {
                                    echo '<p><a href="'. get_permalink($page_id) .'" class="modify-address-button">'. __( 'Modify address', 'wc_shipping_multiple_address' ) .'</a></p>';
                                }
                        ?>
                        </div>
                        <?php
                            else:
                        ?>

                            <?php endif; ?>
                        <script type="text/javascript">
                        jQuery(function() {
                            <?php
                            if (!empty($tips[$x])):
                                foreach ( (array)$tips[$x] as $pId => $ts):
                                    foreach ($ts as $i => $tip):
                            ?>
                            jQuery(".pkg_tip_<?php echo $x .'_'. $pId .'_'. $i; ?>").tipTip({
                                content: "<?php echo str_replace( '"', '\"', $tip); ?>"
                            });
                            <?php
                                    endforeach;
                                endforeach;
                            endif;
                            ?>
                        });
                        </script>
                        <?php endforeach; ?>
                        <div style="clear:both;"></div>
                    </div>
                    <input type="hidden" name="shipping_method" value="multiple_shipping" />
                </td>
                <td style="vertical-align: top;"><?php echo woocommerce_price( $woocommerce->cart->shipping_total + $woocommerce->cart->shipping_tax_total ); ?></td>
                <script type="text/javascript">
                jQuery("tr.shipping").remove();
                </script>
            </tr>
            <?php
            else:
            ?>
            <tr class="multi_shipping">
                <td style="vertical-align: top;" colspan="<?php if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) echo '2'; else echo '1'; ?>">
                    <?php _e( 'Shipping Methods', 'wc_shipping_multiple_address' ); ?>

                    <?php
                    $tips = array();
                    foreach ($packages as $x => $package):
                        $shipping_methods   = array();
                        $products           = $package['contents'];

                        if ($type == 2):
                            // If at least one shipping method is available
                            // Calculate shipping method rates
                            $ship_package['rates'] = array();

                            foreach ( $woocommerce->shipping->load_shipping_methods( $package ) as $shipping_method ) {

                                if ( isset($package['method']) && !in_array($shipping_method->id, $package['method']) ) continue;

                                if ( $shipping_method->is_available( $package ) ) {

                                    // Reset Rates
                                    $shipping_method->rates = array();

                                    // Calculate Shipping for package
                                    $shipping_method->calculate_shipping( $package );

                                    // Place rates in package array
                                    if ( ! empty( $shipping_method->rates ) && is_array( $shipping_method->rates ) )
                                        foreach ( $shipping_method->rates as $rate )
                                            $ship_package['rates'][$rate->id] = $rate;
                                }

                            }

                            foreach ( $ship_package['rates'] as $method ) {
                                if ( $method->id == 'multiple_shipping' ) continue;

                                $method->label = esc_html( $method->label );

                                if ( $method->cost > 0 ) {
                                    $method->label .= ' &mdash; ';

                                    // Append price to label using the correct tax settings
                                    if ( $woocommerce->cart->display_totals_ex_tax || ! $woocommerce->cart->prices_include_tax ) {
                                        $method->label .= woocommerce_price( $method->cost );
                                        if ( $method->get_shipping_tax() > 0 && $woocommerce->cart->prices_include_tax ) {
                                            $method->label .= ' '.$woocommerce->countries->ex_tax_or_vat();
                                        }
                                    } else {
                                        $method->label .= woocommerce_price( $method->cost + $method->get_shipping_tax() );
                                        if ( $method->get_shipping_tax() > 0 && ! $woocommerce->cart->prices_include_tax ) {
                                            $method->label .= ' '.$woocommerce->countries->inc_tax_or_vat();
                                        }
                                    }
                                }
                                $shipping_methods[] = $method;
                            }

                            // Print a single available shipping method as plain text
                            if ( 1 === count( $shipping_methods ) ) {
                                $method = $shipping_methods[0];
                                echo $method->label;
                                echo '<input type="hidden" class="shipping_methods" name="shipping_method" value="'.esc_attr( $method->id ).'">';

                            // Show multiple shipping methods in a select list
                            } elseif ( count( $shipping_methods ) > 1 ) {
                                echo '<select class="shipping_methods" name="shipping_method">';
                                foreach ( $shipping_methods as $method ) {
                                    if ($method->id == 'multiple_shipping' ) continue;
                                    echo '<option value="'.esc_attr( $method->id ).'" '.selected( $method->id, (isset($post['shipping_method'])) ? $post['shipping_method'] : '', false).'>';
                                    echo strip_tags( $method->label );
                                    echo '</option>';
                                }
                                echo '</select>';
                            } else {
                                echo '<p>'.__( 'Sorry, it seems that there are no available shipping methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ).'</p>';
                            }

                            $sess_cart_addresses = wcms_session_get( 'cart_item_addresses' );
                            if ( $sess_cart_addresses && !empty($sess_cart_addresses) ) {
                                echo '<p><a href="'. get_permalink($page_id) .'" class="modify-address-button">'. __( 'Modify address', 'wc_shipping_multiple_address' ) .'</a></p>';
                            }
                        endif;
                    endforeach;
                    ?>
                </td>
                <td style="vertical-align: top;"><?php echo woocommerce_price( $woocommerce->cart->shipping_total + $woocommerce->cart->shipping_tax_total ); ?></td>
                <script type="text/javascript">
                jQuery("tr.shipping").remove();
                </script>
            </tr>
            <?php
            endif;
        }

        function available_shipping_methods($shipping_methods) {
            if ( !wcms_session_isset( 'wcms_packages' ) && isset($shipping_methods['multiple_shipping']) ) {
                unset($shipping_methods['multiple_shipping']);
            }

            return $shipping_methods;
        }

        function shipping_packages($packages) {
            global $woocommerce;
            $myPackages     = array();
            $settings       = $this->settings;
            $methods        = (wcms_session_isset( 'shipping_methods' )) ? wcms_session_get( 'shipping_methods' ) : array();

            $sess_cart_addresses = wcms_session_get( 'cart_item_addresses' );

            //echo '<pre>'. print_r($sess_cart_addresses, true) .'</pre>';
            //echo '<pre>'. print_r($_SESSION['address_relationships'], true) .'</pre>';

            if ( is_null($sess_cart_addresses) || empty($sess_cart_addresses) ) {
                // multiple shipping is not set up
                // check if items have different origins
                if (sizeof($woocommerce->cart->get_cart()) > 0) foreach ($woocommerce->cart->get_cart() as $cart_item_key => $values) {
                    $product_id     = $values['product_id'];
                    $product_cats   = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

                    // look for direct product matches
                    $matched = false;
                    foreach ( $settings as $idx => $setting ) {
                        if ( in_array($product_id, $setting['products']) ) {
                            $matched = $setting;
                            break;
                        }
                    }

                    if (! $matched ) {
                        // look for category matches
                        foreach ( $settings as $idx => $setting ) {
                            foreach ( $product_cats as $product_cat_id ) {
                                if ( in_array($product_cat_id, $setting['categories']) ) {
                                    $matched = $setting;
                                    break;
                                }
                            }
                        }
                    }

                    if ( $matched !== false ) {

                        // create or update package
                        $existing = false;
                        if ( !empty($myPackages) ) foreach ( $myPackages as $idx => $my_pkg ) {
                            if ( (isset($my_pkg['origin']) && $my_pkg['origin'] == $matched['zip']) && (isset($my_pkg['method']) && $my_pkg['method'] == $matched['method']) ) {
                                $existing = true;
                                $values['package_idx'] = $idx;
                                $myPackages[$idx]['contents'][$cart_item_key] = $values;
                                $myPackages[$idx]['contents_cost'] += $values['line_total'];

                                if ( isset($methods[$idx]) ) {
                                    $myPackages[$idx]['selected_method'] = $methods[$idx];
                                }

                                // modify the cart entry
                                $woocommerce->cart->cart_contents[$cart_item_key] = $values;
                                break;
                            }
                        }

                        if ( ! $existing ) {
                            $values['package_idx'] = count($myPackages);
                            $pkg = array(
                                'contents'          => array($cart_item_key => $values),
                                'contents_cost'     => $values['line_total'],
                                //'origin'            => $matched['zip'],
                                'method'            => $matched['method'],
                                'destination'       => $packages[0]['destination']
                            );

                            if (isset($methods[$idx])) {
                                $pkg['selected_method'] = $methods[$idx];
                            }
                            $myPackages[] = $pkg;

                            // modify the cart entry
                            $woocommerce->cart->cart_contents[$cart_item_key] = $values;
                        }
                    }
                }

                if (! empty($myPackages)) {
                    $woocommerce->add_inline_js('_multi_shipping = true;');
                    wcms_session_set( 'wcms_packages', $myPackages);
                    return $myPackages;
                }

                return $packages;
            }

            // group items into ship-to addresses
            $addresses      = wcms_session_get( 'cart_item_addresses' );
            $productsArray  = array();
            //$address_fields = apply_filters( 'woocommerce_shipping_fields', array() );
            $address_fields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );
            //echo '<pre>'. print_r($addresses, true) .'</pre>';
            if (sizeof($woocommerce->cart->get_cart())>0) foreach ($woocommerce->cart->get_cart() as $cart_item_key => $values) {
                $qty = $values['quantity'];
                for ($i = 1; $i <= $qty; $i++) {
                    if ( isset($addresses['shipping_first_name_'. $cart_item_key .'_'. $values['product_id'] .'_'. $i]) ) {
                        $address = array();

                        foreach ( $address_fields as $field_name => $field ) {
                            $addr_key = str_replace('shipping_', '', $field_name);
                            $address[$addr_key] = ( isset($addresses[ $field_name .'_'. $cart_item_key .'_'. $values['product_id'] .'_'. $i]) ) ? $addresses[$field_name .'_'. $cart_item_key .'_'. $values['product_id'] .'_'. $i] : '';
                        }

                        /*$address = array(
                            'first_name'    => $addresses['shipping_first_name_'. $cart_item_key .'_'. $values['product_id'] .'_'. $i],
                            'last_name'     => $addresses['shipping_last_name_'. $cart_item_key .'_'.$values['product_id'] .'_'. $i],
                            'company'       => $addresses['shipping_company_'. $cart_item_key .'_'.$values['product_id'] .'_'. $i],
                            'address_1'     => $addresses['shipping_address_1_'. $cart_item_key .'_'.$values['product_id'] .'_'. $i],
                            'address_2'     => $addresses['shipping_address_2_'. $cart_item_key .'_'.$values['product_id'] .'_'. $i],
                            'city'          => $addresses['shipping_city_'. $cart_item_key .'_'.$values['product_id'] .'_'. $i],
                            'state'         => $addresses['shipping_state_'. $cart_item_key .'_'.$values['product_id'] .'_'. $i],
                            'postcode'      => $addresses['shipping_postcode_'. $cart_item_key .'_'.$values['product_id'] .'_'. $i],
                            'country'       => $addresses['shipping_country_'. $cart_item_key .'_'.$values['product_id'] .'_'. $i],
                            'notes'         => $addresses['shipping_notes_'. $cart_item_key .'_'.$values['product_id'] .'_'. $i],
                        );*/
                    } else {
                        $address = array();

                        foreach ( $address_fields as $field_name => $field ) {
                            $addr_key = str_replace('shipping_', '', $field_name);
                            $address[$addr_key] = '';
                        }
                    }

                    $currentAddress = $woocommerce->countries->get_formatted_address( $address );
                    $key            = md5($currentAddress);
                    $_value         = $values;

                    $price          = round($_value['line_total'] / $qty, 2);
                    $tax            = round($_value['line_tax'] / $qty, 2);
                    $sub            = round($_value['line_subtotal'] / $qty, 2);
                    $subTax         = round($_value['line_subtotal_tax'] / $qty, 2);

                    $_value['quantity']             = 1;
                    $_value['line_total']           = $price;
                    $_value['line_tax']             = $tax;
                    $_value['line_subtotal']        = $sub;
                    $_value['line_subtotal_tax']    = $subTax;
                    $meta                           = md5($woocommerce->cart->get_item_data($_value));

                    //$origin = $this->get_product_origin( $values['product_id'] );
                    $origin = false;
                    $method = $this->get_product_shipping_method( $values['product_id'] );

                    // if origins and/or shipping method is set, group using origins and shipping methods
                    if (! $origin ) $origin = '';
                    if (! $method ) $method = '';

                    if ( !empty($origin) || !empty($method) ) $key .= $origin . $method;

                    // no origin and method selected
                    if (isset($productsArray[$key])) {
                        // if the same product exists, add to the qty and cost
                        $found = false;
                        foreach ($productsArray[$key]['products'] as $idx => $prod) {
                            if ($prod['id'] == $_value['product_id']) {
                                if ($meta == $prod['meta']) {
                                    $found = true;
                                    $productsArray[$key]['products'][$idx]['value']['quantity']++;
                                    $productsArray[$key]['products'][$idx]['value']['line_total'] += $_value['line_total'];
                                    $productsArray[$key]['products'][$idx]['value']['line_tax'] += $_value['line_tax'];
                                    $productsArray[$key]['products'][$idx]['value']['line_subtotal'] += $_value['line_subtotal'];
                                    $productsArray[$key]['products'][$idx]['value']['line_subtotal_tax'] += $_value['line_subtotal_tax'];
                                    break;
                                }
                            }
                        }

                        if (! $found) {
                            // new product
                            $productsArray[$key]['products'][] = array(
                                'id' => $_value['product_id'],
                                'meta' => $meta,
                                'value' => $_value
                            );
                        }
                    } else {
                        $productsArray[$key] = array(
                            'products'  => array(
                                array(
                                    'id' => $_value['product_id'],
                                    'meta' => $meta,
                                    'value' => $_value
                                )
                            ),
                            'country'   => $address['country'],
                            'state'     => $address['state'],
                            'postcode'  => $address['postcode'],
                            'address'   => $address
                        );
                    }

                    if ( !empty($origin) ) $productsArray[$key]['origin'] = $origin;
                    if ( !empty($method) ) $productsArray[$key]['method'] = $method;
                }
            }

            if (! empty($productsArray)) {
                $myPackages = array();
                foreach ($productsArray as $idx => $group) {
                    $pkg = array(
                        'contents'          => array(),
                        'contents_cost'     => 0,
                        'destination'       => array(
                                                    'country'   => $group['country'],
                                                    'state'     => $group['state'],
                                                    'postcode'  => $group['postcode']
                                                ),
                        'full_address'      => $group['address']
                    );

                    if ( isset($group['origin']) ) $pkg['origin'] = $group['origin'];
                    if ( isset($group['method']) ) $pkg['method'] = $group['method'];

                    if ( isset($methods[$idx]) ) {
                        $pkg['selected_method'] = $methods[$idx];
                    }

                    foreach ($group['products'] as $item) {
                        $data = (array) apply_filters( 'woocommerce_get_item_data', array(), $item['value'] );
                        $cart_item_id = $woocommerce->cart->generate_cart_id($item['value']['product_id'], $item['value']['variation_id'], $item['value']['variation'], $data);

                        $item['value']['package_idx'] = $idx;
                        $pkg['contents'][$cart_item_id] = $item['value'];
                        if ($item['value']['data']->needs_shipping()) {
                            $pkg['contents_cost'] += $item['value']['line_total'];
                        }
                    }
                    $myPackages[] = $pkg;
                }

                wcms_session_set( 'wcms_packages', $myPackages);

                $woocommerce->add_inline_js('_multi_shipping = true;');

                return $myPackages;
            }
            return $packages;
        }

        function update_order_review($post) {
            global $woocommerce;

            $ship_methods   = array();
            $data           = array();
            parse_str($post, $data);

            if (isset($data['shipping_methods']) && is_array($data['shipping_methods'])) {
                foreach ($data['shipping_methods'] as $x => $method) {
                    list($id, $label) = explode( '||', $method);

                    $ship_methods[$x] = array( 'id' => $id, 'label' => $label);
                    //$_SESSION['shipping_methods'][$x] = array( 'id' => $id, 'label' => $label);
                }
            } elseif ( isset($data['shipping_method']) && $data['shipping_method'] != 'multiple_shipping' ) {
                //$_SESSION['shipping_methods'][0] = array( 'id' => $data['shipping_method'], 'label' => $data['shipping_method']);
                $ship_methods[0] = array( 'id' => $data['shipping_method'], 'label' => $data['shipping_method']);
            }

            wcms_session_set( 'shipping_methods', $ship_methods );
        }

        function checkout_order_review() {

        }

        function clear_session() {
            wcms_session_delete( 'cart_item_addresses' );
            wcms_session_delete( 'cart_address_sigs' );
            wcms_session_delete( 'address_relationships' );
            wcms_session_delete( 'shipping_methods' );
            wcms_session_delete( 'wcms_original_cart' );
        }

        function cart_updated() {
            global $woocommerce;

            $cart = $woocommerce->cart->get_cart();

            if ( empty($cart) ) {
                wcms_session_delete( 'cart_item_addresses' );
                wcms_session_delete( 'cart_address_sigs' );
                wcms_session_delete( 'address_relationships' );
                wcms_session_delete( 'shipping_methods' );
                wcms_session_delete( 'wcms_original_cart' );
            }
        }

        function calculate_totals($cart) {
            global $woocommerce;
            $shipping_total     = 0;
            $shipping_taxes     = array();
            $shipping_tax_total = 0;

            if (! wcms_session_isset( 'shipping_methods' )) return;

            $packages   = $woocommerce->cart->get_shipping_packages();
            $available  = $woocommerce->shipping->get_available_shipping_methods();
            $chosen     = wcms_session_get( 'shipping_methods' );

            foreach ($packages as $x => $package) {
                if (isset($chosen[$x])) {
                    $ship = $chosen[$x]['id'];

                    $package = $woocommerce->shipping->calculate_shipping_for_package( $package );

                    if ( isset($package['rates'][$ship]) )
                        $shipping_total += $package['rates'][$ship]->cost;

                    if ( !empty($package['rates'][$ship]->taxes) ) {
                        foreach ( $package['rates'][$ship]->taxes as $key => $value ) {
                            if ( isset($shipping_taxes[$key]) ) {
                                $shipping_taxes[$key] += $value;
                                $shipping_tax_total += $value;
                            } else {
                                $shipping_taxes[$key] = $value;
                                $shipping_tax_total += $value;
                            }
                        }
                    }
                }
            }

            $cart->shipping_taxes       = $shipping_taxes;
            $cart->shipping_total       = $shipping_total;
            $cart->shipping_tax_total   = $shipping_tax_total;
        }

        function order_data_shipping_address() {
            global $post, $wpdb, $thepostid, $order_status, $woocommerce;

            $order  = new WC_Order( $thepostid );
            $custom = $order->order_custom_fields;

            if ( isset($custom['_shipping_addresses']) && isset($custom['_shipping_addresses'][0]) && !empty($custom['_shipping_addresses'][0]) ) {
                echo <<<EOD
<script type="text/javascript">
jQuery(jQuery("div.address")[1]).html("<p><a href=\"#wc_multiple_shipping\">Multiple Shipping Addresses</a></p>");
jQuery(jQuery("a.edit_address")[1]).remove();
jQuery(jQuery("div.edit_address")[1]).remove();
</script>
EOD;
            }
        }

        function get_product_origin( $product_id ) {
            $origin         = false;
            $settings       = $this->settings;
            $product_cats   = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

            // look for direct product matches
            $matched = false;
            foreach ( $settings as $idx => $setting ) {
                if ( in_array($product_id, $setting['products']) ) {
                    return $setting['zip'];
                }
            }

            if (! $matched ) {
                // look for category matches
                foreach ( $settings as $idx => $setting ) {
                    foreach ( $product_cats as $product_cat_id ) {
                        if ( in_array($product_cat_id, $setting['categories']) ) {
                            return $setting['zip'];
                        }
                    }
                }
            }

            //return $origin;
            return false;
        }

        function get_product_shipping_method( $product_id ) {
            $method         = false;
            $settings       = $this->settings;
            $product_cats   = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

            // look for direct product matches
            $matched = false;
            foreach ( $settings as $idx => $setting ) {
                if ( in_array($product_id, $setting['products']) ) {
                    return $setting['method'];
                }
            }

            if (! $matched ) {
                // look for category matches
                foreach ( $settings as $idx => $setting ) {
                    foreach ( $product_cats as $product_cat_id ) {
                        if ( in_array($product_cat_id, $setting['categories']) ) {
                            return $setting['method'];
                        }
                    }
                }
            }

            return $method;
        }

        function packages_have_different_methods($packages = array()) {
            $last_method = false;

            foreach ( $packages as $package ) {
                if ( isset($package['method']) ) {
                    if (! $last_method ) {
                        $last_method = $package['method'];
                    } else {
                        if ( $last_method != $package['method']) {
                            return true;
                        }
                    }
                }
            }

            return false;
        }

        function packages_have_different_origins($packages = array()) {
            $last_origin = false;

            foreach ( $packages as $package ) {
                if ( isset($package['origin']) ) {
                    if (! $last_origin ) {
                        $last_origin = $package['origin'];
                    } else {
                        if ( $last_origin != $package['origin']) {
                            return true;
                        }
                    }
                }
            }

            return false;
        }

        function packages_contain_methods( $packages = array() ) {
            $return = false;

            foreach ( $packages as $package ) {
                if ( isset($package['method'])) {
                    $return = true;
                    break;
                }
            }

            return $return;
        }

        function display_order_shipping_addresses( $order ) {
            global $woocommerce;
            $order_id           = $order->id;
            $addresses          = get_post_meta($order_id, '_shipping_addresses', true);
            $methods            = get_post_meta($order_id, '_shipping_methods', true);
            $packages           = get_post_meta($order_id, '_wcms_packages', true);
            $items              = $order->get_items();
            $available_methods  = $woocommerce->shipping->load_shipping_methods();

            //if (empty($addresses)) return;
            if ( !$packages || count($packages) == 1 ) {
                echo $formatted_shipping_address;
                return;
            }

            // load the address fields
            require_once $woocommerce->plugin_path() .'/classes/class-wc-checkout.php';
            require_once $woocommerce->plugin_path() .'/classes/class-wc-cart.php';
            $checkout   = new WC_Checkout();
            $cart       = new WC_Cart();

            $shipFields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );

            echo '<p><strong>'. __( 'This order ships to multiple addresses.', 'wc_shipping_multiple_address' ) .'</strong></p>';
            echo '<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">';
            echo '<thead><tr>';
            echo '<th scope="col" style="text-align:left; border: 1px solid #eee;">'. __( 'Product', 'woocommerce' ) .'</th>';
            echo '<th scope="col" style="text-align:left; border: 1px solid #eee;">'. __( 'Qty', 'woocommerce' ) .'</th>';
            echo '<th scope="col" style="text-align:left; border: 1px solid #eee;">'. __( '', 'woocommerce' ) .'</th>';
            echo '</thead><tbody>';

            foreach ( $packages as $x => $package ) {
                $products   = $package['contents'];
                $method     = $methods[$x]['label'];

                foreach ( $available_methods as $ship_method ) {
                    if ($ship_method->id == $method) {

                        //$method = apply_filters( 'woocommerce_order_shipping_method', ucwords( $ship_method->title ) );
                        $method = $ship_method->get_title();
                        break;
                    }
                }

                $address = ( isset($package['full_address']) && !empty($package['full_address']) ) ? $woocommerce->countries->get_formatted_address($package['full_address']) : '';

                foreach ( $products as $i => $product ) {
                    echo '<tr>';
                    echo '<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">'. get_the_title($product['data']->id) .'<br />'. $cart->get_item_data($product, true) .'</td>';
                    echo '<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">'. $product['quantity'] .'</td>';
                    echo '<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">'.  $address .'<br/><em>( '. $method .' )</em></td>';
                    echo '</tr>';
                }
            }
            echo '</table>';
        }

    }

    $ship_multi = new WC_Ship_Multiple();

    function wcms_get_product( $product_id ) {
        if ( function_exists( 'get_product' ) ) {
            return get_product( $product_id );
        } else {
            return new WC_Product( $product_id );
        }
    }

    function wcms_session_get( $name ) {
        global $woocommerce;

        if ( isset( $woocommerce->session ) ) {
            // WC 2.0
            if ( isset( $woocommerce->session->$name ) ) return $woocommerce->session->$name;
        } else {
            // old style
            if ( isset( $_SESSION[ $name ] ) ) return $_SESSION[ $name ];
        }

        return null;
    }

    function wcms_session_isset( $name ) {
        global $woocommerce;

        if ( isset($woocommerce->session) ) {
            // WC 2.0
            return (isset( $woocommerce->session->$name ));
        } else {
            return (isset( $_SESSION[$name] ));
        }
    }

    function wcms_session_set( $name, $value ) {
        global $woocommerce;

        if ( isset( $woocommerce->session ) ) {
            // WC 2.0
            $woocommerce->session->$name = $value;
        } else {
            // old style
            $_SESSION[ $name ] = $value;
        }
    }

    function wcms_session_delete( $name ) {
        global $woocommerce;

        if ( isset( $woocommerce->session ) ) {
            // WC 2.0
            unset( $woocommerce->session->$name );
        } else {
            // old style
            unset( $_SESSION[ $name ] );
        }
    }

}
