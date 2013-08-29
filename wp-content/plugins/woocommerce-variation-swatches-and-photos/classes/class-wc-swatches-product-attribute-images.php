<?php

class WC_Swatches_Product_Attribute_Images {

    private $taxonomy;
    private $meta_key;
    private $image_size = 'shop_thumb';
    private $image_width = 32;
    private $image_height = 32;

    /**
     * Constructor.
     *
     * Sets up a new Product Attribute image type
     *
     * @since 1.0.0
     * @access public
     *
     * @param string $attribute_image_key a meta key to store the custom image for
     * @param string $image_size a registered image size to use for this product attribute image
     * 
     * @return WC_Product_Attribute_Images
     */
    public function __construct($attribute_image_key = 'thumbnail_id', $image_size = 'shop_thumb') {
        $this->meta_key = $attribute_image_key;
        $this->image_size = $image_size;

        if (is_admin()) {
            add_action('admin_enqueue_scripts', array(&$this, 'on_admin_scripts'));
            add_action('current_screen', array(&$this, 'init_attribute_image_selector'));

            add_action('created_term', array(&$this, 'woocommerce_attribute_thumbnail_field_save'), 10, 3);
            add_action('edit_term', array(&$this, 'woocommerce_attribute_thumbnail_field_save'), 10, 3);
        }
    }

    //Enqueue the scripts if on a product attribute page
    public function on_admin_scripts() {
        global $woocommerce_swatches;
        $screen = get_current_screen();
        if (strpos($screen->id, 'pa_') !== false) :
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
            if (function_exists('wp_enqueue_media')) {
                wp_enqueue_media();
            }
        endif;
    }

    //Initalize the actions for all product attribute taxonomoies
    public function init_attribute_image_selector() {
        global $woocommerce, $_wp_additional_image_sizes;
        $screen = get_current_screen();

        if (strpos($screen->id, 'pa_') !== false) :

            $this->taxonomy = $_REQUEST['taxonomy'];

            if (taxonomy_exists($_REQUEST['taxonomy'])) {
                $term_id = term_exists(isset($_REQUEST['tag_ID']) ? $_REQUEST['tag_ID'] : 0, $_REQUEST['taxonomy']);
                $term = 0;
                if ($term_id) {
                    $term = get_term($term_id, $_REQUEST['taxonomy']);
                }

                $this->image_size = apply_filters('woocommerce_get_swatches_image_size', $this->image_size, $_REQUEST['taxonomy'], $term_id);
            }

            $the_size = isset($_wp_additional_image_sizes[$this->image_size]) ? $_wp_additional_image_sizes[$this->image_size] : $_wp_additional_image_sizes['shop_thumbnail'];

            if (isset($the_size['width']) && isset($the_size['height'])) {
                $this->image_width = $the_size['width'];
                $this->image_height = $the_size['height'];
            } else {
                $this->image_width = 32;
                $this->image_height = 32;
            }


            $attribute_taxonomies = $woocommerce->get_attribute_taxonomies();
            if ($attribute_taxonomies) {
                foreach ($attribute_taxonomies as $tax) {

                    add_action('pa_' . $tax->attribute_name . '_add_form_fields', array(&$this, 'woocommerce_add_attribute_thumbnail_field'));
                    add_action('pa_' . $tax->attribute_name . '_edit_form_fields', array(&$this, 'woocommerce_edit_attributre_thumbnail_field'), 10, 2);

                    add_filter('manage_edit-pa_' . $tax->attribute_name . '_columns', array(&$this, 'woocommerce_product_attribute_columns'));
                    add_filter('manage_pa_' . $tax->attribute_name . '_custom_column', array(&$this, 'woocommerce_product_attribute_column'), 10, 3);
                }
            }

        endif;
    }

    //The field used when adding a new term to an attribute taxonomy
    public function woocommerce_add_attribute_thumbnail_field() {
        global $woocommerce;
        ?>
        <div class="form-field ">
            <label for="product_attribute_swatchtype_<?php echo $this->meta_key; ?>">Swatch Type</label>
            <select name="product_attribute_meta[<?php echo $this->meta_key; ?>][type]" id="product_attribute_swatchtype_<?php echo $this->meta_key; ?>" class="postform">
                <option value="-1">None</option>
                <option value="color">Color Swatch</option>
                <option value="photo">Image</option>
            </select>

            <script type="text/javascript">
                jQuery(document).ready(function($) {
                            
                    $('#product_attribute_swatchtype_<?php echo $this->meta_key; ?>').change(function() {
                        $('.swatch-field-active').hide().removeClass('swatch-field-active');
                        $('.swatch-field-' + $(this).val()).slideDown().addClass('swatch-field-active');
                    });
                                                                                                                                                                                
                });
                                                                                                                                                                                                                                                        
            </script>
        </div>

        <div class="form-field swatch-field swatch-field-color section-color-swatch" style="overflow:visible;display:none;">
            <div id="swatch-color" class="<?php echo sanitize_title($this->meta_key); ?>-color">
                <label><?php _e('Color', 'wc_swatches_and_photos'); ?></label>
                <div id="product_attribute_color_<?php echo $this->meta_key; ?>_picker" class="colorSelector"><div></div></div>
                <input class="woo-color" 
                       id="product_attribute_color_<?php echo $this->meta_key; ?>" 
                       type="text" class="text" 
                       name="product_attribute_meta[<?php echo $this->meta_key; ?>][color]"
                       value="#FFFFFF" />
            </div>
        </div>

        <div class="form-field swatch-field swatch-field-photo" style="overflow:visible;display:none;">
            <div id="swatch-photo" class="<?php echo sanitize_title($this->meta_key); ?>-photo">
                <label><?php _e('Thumbnail', 'woocommerce'); ?></label>
                <div id="product_attribute_thumbnail_<?php echo $this->meta_key; ?>" style="float:left;margin-right:10px;">
                    <img src="<?php echo $woocommerce->plugin_url() . '/assets/images/placeholder.png' ?>" width="<?php echo $this->image_width; ?>px" height="<?php echo $this->image_height; ?>px" />
                </div>
                <div style="line-height:60px;">
                    <input type="hidden" id="product_attribute_<?php echo $this->meta_key; ?>" name="product_attribute_meta[<?php echo $this->meta_key; ?>][photo]" />
                    <button type="submit" class="upload_image_button button"><?php _e('Upload/Add image', 'woocommerce'); ?></button>
                    <button type="submit" class="remove_image_button button"><?php _e('Remove image', 'woocommerce'); ?></button>
                </div>
                <script type="text/javascript">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                			
                    window.send_to_termmeta = function(html) {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                				
                        jQuery('body').append('<div id="temp_image">' + html + '</div>');
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                				
                        var img = jQuery('#temp_image').find('img');
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                				
                        imgurl 		= img.attr('src');
                        imgclass 	= img.attr('class');
                        imgid		= parseInt(imgclass.replace(/\D/g, ''), 10);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                				
                        jQuery('#product_attribute_<?php echo $this->meta_key; ?>').val(imgid);
                        jQuery('#product_attribute_thumbnail_<?php echo $this->meta_key; ?> img').attr('src', imgurl);
                        jQuery('#temp_image').remove();
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                				
                        tb_remove();
                    }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                			
                    jQuery('.upload_image_button').live('click', function(){
                        var post_id = 0;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                				
                        window.send_to_editor = window.send_to_termmeta;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                				
                        tb_show('', 'media-upload.php?post_id=' + post_id + '&amp;type=image&amp;TB_iframe=true');
                        return false;
                    });
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                			
                    jQuery('.remove_image_button').live('click', function(){
                        jQuery('#product_attribute_thumbnail_<?php echo $this->meta_key; ?> img').attr('src', '<?php echo $woocommerce->plugin_url() . '/assets/images/placeholder.png'; ?>');
                        jQuery('#product_attribute_<?php echo $this->meta_key; ?>').val('');
                        return false;
                    });
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                			
                </script>
                <div class="clear"></div>
            </div>
        </div>
        <?php
    }

    //The field used when editing an existing proeuct attribute taxonomy term
    public function woocommerce_edit_attributre_thumbnail_field($term, $taxonomy) {
        global $woocommerce;

        $swatch_term = new WC_Swatch_Term($this->meta_key, $term->term_id, $taxonomy, false, $this->image_size);
        $image = '';
        ?>

        <tr class="form-field ">
            <th scope="row" valign="top"><label><?php _e('Type', 'wc_swatches_and_photos'); ?></label></th>
            <td>
                <label for="product_attribute_swatchtype_<?php echo $this->meta_key; ?>">Swatch Type</label>
                <select name="product_attribute_meta[<?php echo $this->meta_key; ?>][type]" id="product_attribute_swatchtype_<?php echo $this->meta_key; ?>" class="postform">
                    <option <?php selected('none', $swatch_term->get_type()); ?> value="-1"><?php _e('None', 'wc_swatches_and_photos'); ?></option>
                    <option <?php selected('color', $swatch_term->get_type()); ?> value="color"><?php _e('Color Swatch', 'wc_swatches_and_photos'); ?></option>
                    <option <?php selected('photo', $swatch_term->get_type()); ?> value="photo"><?php _e('Photo', 'wc_swatches_and_photos'); ?></option>
                </select>


                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                                        
                        $('#product_attribute_swatchtype_<?php echo $this->meta_key; ?>').change(function() {
                            $('.swatch-field-active').hide().removeClass('swatch-field-active');
                            $('.swatch-field-' + $(this).val() ).show().addClass('swatch-field-active');
                        });
                                                                                                                                                                                
                    });                                                                                                                                                                                
                </script>
            </td>
        </tr>

        <?php $style = $swatch_term->get_type() != 'color' ? 'display:none;' : ''; ?>
        <tr class="form-field swatch-field swatch-field-color section-color-swatch" style="overflow:visible;<?php echo $style; ?>">
            <th scope="row" valign="top"><label><?php _e('Color', 'wc_swatches_and_photos'); ?></label></th>
            <td>
                <div id="swatch-color" class="<?php echo sanitize_title($this->meta_key); ?>-color">

                    <div id="product_attribute_color_<?php echo $this->meta_key; ?>_picker" class="colorSelector"><div></div></div>
                    <input class="woo-color" 
                           id="product_attribute_color_<?php echo $this->meta_key; ?>"
                           type="text" class="text" 
                           name="product_attribute_meta[<?php echo $this->meta_key; ?>][color]"
                           value="<?php echo $swatch_term->get_color(); ?>" />
                </div>

            </td>
        </tr>

        <?php $style = $swatch_term->get_type() != 'photo' ? 'display:none;' : ''; ?>
        <tr class="form-field swatch-field swatch-field-photo" style="overflow:visible;<?php echo $style; ?>">
            <th scope="row" valign="top"><label><?php _e('Photo', 'wc_swatches_and_photos'); ?></label></th>
            <td>
                <div id="product_attribute_thumbnail_<?php echo $this->meta_key; ?>" style="float:left;margin-right:10px;">
                    <img src="<?php echo $swatch_term->get_image_src(); ?>"  width="<?php echo $swatch_term->get_width(); ?>px" height="<?php echo $swatch_term->get_height(); ?>px" />
                </div>
                <div style="line-height:60px;">
                    <input type="hidden" id="product_attribute_<?php echo $this->meta_key; ?>" name="product_attribute_meta[<?php echo $this->meta_key; ?>][photo]" value="<?php echo $swatch_term->get_image_id(); ?>" />
                    <button type="submit" class="upload_image_button button"><?php _e('Upload/Add image', 'woocommerce'); ?></button>
                    <button type="submit" class="remove_image_button button"><?php _e('Remove image', 'woocommerce'); ?></button>
                </div>
                <script type="text/javascript">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        				
                    window.send_to_termmeta = function(html) {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        					
                        jQuery('body').append('<div id="temp_image">' + html + '</div>');
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        					
                        var img = jQuery('#temp_image').find('img');
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        					
                        imgurl 		= img.attr('src');
                        imgclass 	= img.attr('class');
                        imgid		= parseInt(imgclass.replace(/\D/g, ''), 10);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        					
                        jQuery('#product_attribute_<?php echo $this->meta_key; ?>').val(imgid);
                        jQuery('#product_attribute_thumbnail_<?php echo $this->meta_key; ?> img').attr('src', imgurl);
                        jQuery('#temp_image').remove();
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        					
                        tb_remove();
                    }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        				
                    jQuery('.upload_image_button').live('click', function(){
                        var post_id = 0;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        					
                        window.send_to_editor = window.send_to_termmeta;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        					
                        tb_show('', 'media-upload.php?post_id=' + post_id + '&amp;type=image&amp;TB_iframe=true');
                        return false;
                    });
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        				
                    jQuery('.remove_image_button').live('click', function(){
                        jQuery('#product_attribute_thumbnail_<?php echo $this->meta_key; ?> img').attr('src', '<?php echo $woocommerce->plugin_url() . '/assets/images/placeholder.png'; ?>');
                        jQuery('#product_attribute_<?php echo $this->meta_key; ?>').val('');
                        return false;
                    });
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        				
                </script>
                <div class="clear"></div>
            </td>
        </tr>
        <?php
    }

    //Saves the product attribute taxonomy term data
    public function woocommerce_attribute_thumbnail_field_save($term_id, $tt_id, $taxonomy) {
        if (isset($_POST['product_attribute_meta'])) {

            $metas = $_POST['product_attribute_meta'];
            if (isset($metas[$this->meta_key])) {
                $data = $metas[$this->meta_key];

                $photo = isset($data['photo']) ? $data['photo'] : '';
                $color = isset($data['color']) ? $data['color'] : '';
                $type = isset($data['type']) ? $data['type'] : '';


                update_woocommerce_term_meta($term_id, $taxonomy . '_' . $this->meta_key . '_type', $type);
                update_woocommerce_term_meta($term_id, $taxonomy . '_' . $this->meta_key . '_photo', $photo);
                update_woocommerce_term_meta($term_id, $taxonomy . '_' . $this->meta_key . '_color', $color);
            }
        }
    }

    //Registers a column for this attribute taxonomy for this image
    public function woocommerce_product_attribute_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns[$this->meta_key] = __('Thumbnail', 'wc_swatches_and_photos');
        unset($columns['cb']);
        $columns = array_merge($new_columns, $columns);
        return $columns;
    }

    //Renders the custom column as defined in woocommerce_product_attribute_columns
    public function woocommerce_product_attribute_column($columns, $column, $id) {
        if ($column == $this->meta_key) :
            $swatch_term = new WC_Swatch_Term($this->meta_key, $id, $this->taxonomy, false, $this->image_size);
            $columns .= $swatch_term->get_output();
        endif;
        return $columns;
    }

}
?>