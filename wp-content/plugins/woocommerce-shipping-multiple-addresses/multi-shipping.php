<?php
/**
 * woocommerce_multi_shipping_init function.
 *
 * @access public
 * @return void
 */
function woocommerce_multi_shipping_init() {

    if ( ! class_exists( 'WC_Shipping_Method' ) ) return;

    /**
     * WC_Multiple_Shipping class.
     *
     * @extends WC_Shipping_Method
     */
    class WC_Multiple_Shipping extends WC_Shipping_Method {
        function __construct() {
            $this->id           = 'multiple_shipping';
            $this->method_title = __('Multiple Shipping', 'wc_shipping_multiple_address');
            $this->method_description = __('Multiple Shipping is used automatically by the WooCommerce Ship to Multiple Addresses.', 'wc_shipping_multiple_address');
            $this->init();
        }

        function init() {
            // Load the form fields.
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();

            // Define user set variables
            $this->enabled              = 'yes';
            $this->title                = $this->settings['title'];
            $this->cart_duplication     = $this->settings['cart_duplication'];
            $this->lang_notification    = $this->settings['lang_notification'];
            $this->lang_btn_items       = $this->settings['lang_btn_items'];

            add_action('woocommerce_update_options_shipping_multiple_shipping', array( $this, 'process_admin_options' ) );
        }

        function calculate_shipping( $package = array() ) {
            $rate = array(
                'id'        => $this->id,
                'label'     => $this->title,
            );
            $this->add_rate($rate);
        }

        function init_form_fields() {
            global $woocommerce;
            $this->form_fields = array(
                'title' => array(
                    'title'         => __( 'Title', 'wc_shipping_multiple_address' ),
                    'type'          => 'text',
                    'description'   => __( 'This controls the title which the user sees during checkout.', 'wc_shipping_multiple_address' ),
                    'default'       => __( 'Multiple Shipping', 'wc_shipping_multiple_address' )
                ),
                'cart_duplication_section' => array(
                    'type'          => 'title',
                    'title'         => __('Cart Duplication', 'wc_shipping_multiple_address'),
                    'description'   => __('This functionality will allow your customers to duplicate the contents of their cart in order to be able to ship the same cart to multiple addresses in addition to individual products.', 'wc_shipping_multiple_address')
                ),
                'cart_duplication' => array(
                    'title'         => __('Enable Cart Duplication', 'wc_shipping_multiple_address'),
                    'type'          => 'checkbox',
                    'label'         => 'Enable'
                ),
                'language_section' => array(
                    'type'          => 'title',
                    'title'         => __('Text your shoppers see when Multiple Shipping is enabled at checkout', 'wc_shipping_multiple_address')
                ),
                'lang_notification' => array(
                    'type'          => 'text',
                    'title'         => __('Checkout Notification', 'wc_shipping_multiple_address'),
                    'css'           => 'width: 350px;',
                    'default'       => 'You may use multiple shipping addresses on this cart'
                ),
                'lang_btn_items' => array(
                    'type'          => 'text',
                    'title'         => __('Button: Item Addresses', 'wc_shipping_multiple_address'),
                    'css'           => 'width: 350px;',
                    'default'       => 'Set Addresses'
                )
            );
        }

        /**
         * is_available function.
         *
         * @access public
         * @param mixed $package
         * @return void
         */
        function is_available( $package ) {
            global $woocommerce;

            $methods = wcms_session_get('shipping_methods');

            if ( ! $methods || empty( $methods ) ) {
                // multiple shipping is not set up
                return false;
            }
            return true;
        }
    }

    function add_multiple_shipping_method($methods) {
        $methods[] = 'WC_Multiple_Shipping'; return $methods;
    }

    add_filter( 'woocommerce_shipping_methods','add_multiple_shipping_method' );
}

add_action('plugins_loaded', 'woocommerce_multi_shipping_init', 100);