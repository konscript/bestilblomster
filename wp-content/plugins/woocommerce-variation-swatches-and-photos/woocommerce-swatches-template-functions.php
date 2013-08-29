<?php

function woocommerce_swatches_get_variation_form_args() {
    global $woocommerce, $product, $post;

    $attributes = $product->get_variation_attributes();
    $attributes_renamed = array();
    
    foreach ($attributes as $attribute => $values) {
        $attributes_renamed['attribute_' . sanitize_title($attribute)] = array_values(array_map('sanitize_title', array_map('strtolower', $values)));
    }

    $default_attributes = (array) maybe_unserialize(get_post_meta($post->ID, '_default_attributes', true));
    $selected_attributes = apply_filters('woocommerce_product_default_attributes', $default_attributes);

    // Put available variations into an array and put in a Javascript variable (JSON encoded)
    $available_variations = array();
    $available_variations_flat = array();

    foreach ($product->get_children() as $child_id) {

        $variation = $product->get_child($child_id);

        if ($variation instanceof WC_Product_Variation) {

            if (get_post_status($variation->get_variation_id()) != 'publish')
                continue; // Disabled

            if (!$variation->is_visible()) {
                continue; // Visible setting - may be hidden if out of stock
            }
            
            $variation_attributes = $variation->get_variation_attributes();
            $available_variations_flat[] = $variation_attributes;
        }
    }
    
    $av =  $product->get_available_variations();
    return array(
        'available_variations' => $av,
        'available_variations_flat' => $available_variations_flat,
        'attributes' => $attributes,
        'attributes_renamed' => $attributes_renamed,
        'selected_attributes' 	=> $product->get_variation_default_attributes(),
    );
}

?>