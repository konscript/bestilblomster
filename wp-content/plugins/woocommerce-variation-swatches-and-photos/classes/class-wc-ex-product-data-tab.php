<?php
if (!class_exists('WC_EX_Product_Data_Tab_Swatches')) {

    class WC_EX_Product_Data_Tab_Swatches {

        public $tab_class = '';
        public $tab_additional_class = '';
        public $tab_id = '';
        public $tab_title = '';
        public $tab_icon = '';
        public $tab_script_src = '';

        public function __construct($tab_class, $tab_id, $tab_title, $tab_icon = '', $script = false) {

            if (is_array($tab_class)) {
                $this->tab_class = $tab_class[0];
                for ($x = 1; $x < count($tab_class); $x++) {
                    $this->tab_additional_class .= ' ' . $tab_class[$x];
                }
            } else {
                $this->tab_class = $tab_class;
            }
            $this->tab_id = $tab_id;
            $this->tab_title = $tab_title;
            $this->tab_icon = $tab_icon;

            $this->tab_script_src;

            add_action('woocommerce_init', array(&$this, 'on_woocommerce_init'));
            add_action('admin_head', array(&$this, 'on_admin_head'));

            add_action('woocommerce_product_write_panel_tabs', array(&$this, 'product_write_panel_tabs'), 99);
            add_action('woocommerce_product_write_panels', array(&$this, 'product_data_panel_wrap'), 99);
            add_action('woocommerce_process_product_meta', array(&$this, 'process_meta_box'), 1, 2);
        }

        public function on_woocommerce_init() {
            global $woocommerce;
            if (empty($this->tab_icon)) {
                $wc_default_icons = $woocommerce->plugin_url() . '/assets/images/icons/wc-tab-icons.png';
                $this->tab_icon = $wc_default_icons;
            }
        }

        public function on_admin_head() {
            if (!function_exists('get_product')) {
                echo '<style type="text/css">';
                echo '#woocommerce-product-data ul.product_data_tabs li.' . $this->tab_class . ' a {padding:9px 9px 9px 34px;line-height:16px;border-bottom:1px solid #d5d5d5;text-shadow:0 1px 1px #fff;color:#555;background:#ececec url(' . $this->tab_icon . ') no-repeat 9px 9px;}';
                echo '#woocommerce-product-data ul.product_data_tabs li.' . $this->tab_class . '.active a{background-color:#f8f8f8;border-bottom:1px solid #f8f8f8;}';
                echo '#' . $this->tab_id . ' { padding:10px; }';
                echo '</style>';
            } else {

                echo '<style type="text/css">';
                echo '#woocommerce-product-data ul.product_data_tabs li.' . $this->tab_class . ' a {padding: 5px 5px 5px 28px;background:#F1F1F1 url(' . $this->tab_icon . ') no-repeat 5px 5px;}';
                echo '#woocommerce-product-data ul.product_data_tabs li.' . $this->tab_class . '.active a{ border-color: #DFDFDF;position: relative;background-color: #F8F8F8;color: #555;margin: 0 -1px 0 0;width: 113px;}';
                echo '#' . $this->tab_id . ' { padding:10px; }';
                echo '#swatches {width:99% !important;};';
                echo '</style>';
            }
        }

        public function product_write_panel_tabs() {
            ?>
            <li class="<?php echo $this->tab_class; ?><?php echo $this->tab_additional_class; ?>"><a href="#<?php echo $this->tab_id; ?>"><?php echo $this->tab_title; ?></a></li>
            <?php
        }

        public function product_data_panel_wrap() {
            ?>
            <div id="<?php echo $this->tab_id; ?>" class="panel <?php echo $this->tab_class; ?> woocommerce_options_panel">
                <?php $this->render_product_tab_content(); ?>
            </div>
            <?php
        }

        public function render_product_tab_content() {
            
        }

        public function process_meta_box($post_id, $post) {
            
        }

    }

}
?>