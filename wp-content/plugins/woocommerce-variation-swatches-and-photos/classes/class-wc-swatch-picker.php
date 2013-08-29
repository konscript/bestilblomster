<?php

class WC_Swatch_Picker {

    private $size;
    private $attributes;
    private $selected_attributes;
    private $swatch_type_options;

    public function __construct($product_id, $attributes, $selected_attributes) {
        $this->swatch_type_options = get_post_meta($product_id, '_swatch_type_options', true);

        if (!$this->swatch_type_options) {
            $this->swatch_type_options = array();
        }

        $product_configured_size = get_post_meta($product_id, '_swatch_size', true);
        if (!$product_configured_size) {
            $this->size = 'swatches_image_size';
        } else {
            $this->size = $product_configured_size;
        }

        $this->attributes = $attributes;
        $this->selected_attributes = $selected_attributes;
    }

    public function picker() {
        global $woocommerce;
        ?>
        
        <table class="variations-table" cellspacing="0">
            <tbody>
                <?php
                $loop = 0;
                foreach ($this->attributes as $name => $options) : $loop++;
                    ?>
                    <tr>
                        <td><label for="<?php echo sanitize_title($name); ?>"><?php echo $woocommerce->attribute_label($name); ?></label></td>
                        <td>
                            <?php
                            if (isset($this->swatch_type_options[sanitize_title($name)])) {
                                $picker_type = $this->swatch_type_options[sanitize_title($name)]['type'];
                                if ($picker_type == 'default') {
                                    $this->render_default(sanitize_title($name), $options);
                                } else {
                                    $this->render_picker(sanitize_title($name), $options);
                                }
                            } else {
                                $this->render_default(sanitize_title($name), $options);
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    public function render_picker($name, $options) {
        global $woocommerce;
        $selected_value = (isset($this->selected_attributes[sanitize_title($name)])) ? $this->selected_attributes[sanitize_title($name)] : '';
        ?>
        <div 
            data-attribute-name="<?php echo 'attribute_' . sanitize_title($name); ?>"
            data-value="<?php echo $selected_value; ?>"
            id="<?php echo esc_attr(sanitize_title($name)); ?>" 
            class="select attribute_<?php echo sanitize_title($name); ?>_picker">

            <input type="hidden" name="<?php echo 'attribute_' . sanitize_title($name); ?>" id="<?php echo 'attribute_' . sanitize_title($name); ?>" value="<?php echo $selected_value; ?>" />

            <?php if (is_array($options)) : ?>
                <?php
                // Get terms if this is a taxonomy - ordered
                if (taxonomy_exists(sanitize_title($name))) :
                    $args = array('menu_order' => 'ASC');
                    $terms = get_terms(sanitize_title($name), $args);

                    foreach ($terms as $term) :

                        if (!in_array($term->slug, $options)) {
                            continue;
                        }


                        if ($this->swatch_type_options[$name]['type'] == 'term_options') {
                            $size = apply_filters('woocommerce_swatches_size_for_product', $this->size, get_the_ID(), sanitize_title($name));
                            $swatch_term = new WC_Swatch_Term('swatches_id', $term->term_id, sanitize_title($name), $selected_value == $term->slug, $size);
                        } elseif ($this->swatch_type_options[$name]['type'] == 'product_custom') {
                            $size = apply_filters('woocommerce_swatches_size_for_product', $this->swatch_type_options[sanitize_title($name)]['size'], get_the_ID(), sanitize_title($name));
                            $swatch_term = new WC_Product_Swatch_Term($this->swatch_type_options[$name], $term->term_id, sanitize_title($name), $selected_value == $term->slug, $size);
                        }


                        do_action('woocommerce_swatches_before_picker_item', $swatch_term);
                        echo $swatch_term->get_output();
                        do_action('woocommerce_swatches_after_picker_item', $swatch_term);
                        
                    endforeach;
                else :
                    foreach ($options as $option) :
                        $size = apply_filters('woocommerce_swatches_size_for_product', $this->swatch_type_options[sanitize_title($name)]['size'], get_the_ID(), sanitize_title($name));
                        $swatch_term = new WC_Product_Swatch_Term($this->swatch_type_options[sanitize_title($name)], $option, $name, $selected_value == sanitize_title($option), $size);
                        
                        do_action('woocommerce_swatches_before_picker_item', $swatch_term);
                        echo $swatch_term->get_output();
                        do_action('woocommerce_swatches_after_picker_item', $swatch_term);
                    endforeach;
                endif;
                ?>
            <?php endif; ?>
        </div>
        <?php
    }

    public function render_default($name, $options) {
        global $woocommerce;
        ?>
        <select 
            data-attribute-name="<?php echo 'attribute_' . sanitize_title($name); ?>"
            id="<?php echo esc_attr(sanitize_title($name)); ?>" 
            name="attribute_<?php echo sanitize_title($name); ?>">
            <option value=""><?php echo __('Choose an option', 'woocommerce') ?>&hellip;</option>
            <?php if (is_array($options)) : ?>
                <?php
                $selected_value = (isset($this->selected_attributes[sanitize_title($name)])) ? $this->selected_attributes[sanitize_title($name)] : '';
                // Get terms if this is a taxonomy - ordered
                if (taxonomy_exists(sanitize_title($name))) :
                    $args = array('menu_order' => 'ASC');
                    $terms = get_terms(sanitize_title($name), $args);

                    foreach ($terms as $term) :
                        if (!in_array($term->slug, $options))
                            continue;
                        echo '<option value="' . esc_attr($term->slug) . '" ' . selected($selected_value, $term->slug) . '>' . $term->name . '</option>';
                    endforeach;
                else :
                    foreach ($options as $option) :
                        echo '<option value="' . esc_attr(sanitize_title($option)) . '" ' . selected($selected_value, sanitize_title($option)) . '>' . $option . '</option>';
                    endforeach;
                endif;
                ?>
            <?php endif; ?>
        </select>
        <?php
    }

}
?>