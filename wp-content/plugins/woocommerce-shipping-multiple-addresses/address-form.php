<?php
global $woocommerce;

$addresses  = (wcms_session_isset('cart_item_addresses')) ? wcms_session_get('cart_item_addresses') : array();
$relations  = (wcms_session_isset('address_relationships')) ? wcms_session_get('address_relationships') : array();
$placed     = array();

if ($user->ID != 0) {
    $addresses = get_user_meta($user->ID, 'wc_other_addresses', true);

    if ($addresses) foreach ($addresses as $x => $addr) {
        foreach ( $contents as $key => $value ) {
            if ( isset($relations[$x]) && !empty($relations[$x]) ):
                $qty = array_count_values($relations[$x]);

                if ( in_array($key, $relations[$x]) ) {
                    if ( isset($placed[$key]) ) {
                        $placed[$key] += $qty[$key];
                    } else {
                        $placed[$key] = $qty[$key];
                    }
                }

            endif;
        }
    }
} else {
    $sigs = array();

    if ( isset($addresses) && !empty($addresses) ) {
        $sigs = wcms_session_get('cart_address_sigs');
    }

    foreach ( $sigs as $sig => $addr_id ) {
        if ( isset($relations[$addr_id]) && !empty($relations[$addr_id]) ):
            $qty = array_count_values($relations[$addr_id]);
            foreach ( $contents as $key => $value ) {
                if ( in_array($key, $relations[$addr_id]) ) {
                    if ( isset($placed[$key]) ) {
                        $placed[$key] += $qty[$key];
                    } else {
                        $placed[$key] = $qty[$key];
                    }
                }
            }
        endif;
    }
}
?>
<form method="post" action="" id="address_form">
    <div id="address_wrapper">

        <div id="cart_items">
            <h2><?php _e('Cart Items', 'wc_shipping_multiple_address'); ?></h2>
            <ul class="cart-items">
                <?php
                foreach ($contents as $key => $value):
                    $_product   = $value['data'];
                    $pid        = $value['product_id'];

                    if (! $_product->needs_shipping() ) continue;

                    if ( isset($placed[$key]) ) {
                        if ( $placed[$key] >= $value['quantity'] ) {
                            continue;
                        } else {
                            $value['quantity'] -= $placed[$key];
                        }
                    }
                ?>
                <li data-product-id="<?php echo $value['product_id']; ?>" data-quantity="<?php echo $value['quantity']; ?>" class="cart-item cart-item-<?php echo $value['product_id']; ?>" id="<?php echo $key; ?>">
                    <span class="qty"><?php echo $value['quantity']; ?></span>

                    <h3 class="title">
                        <?php
                        echo get_the_title($value['product_id']);
                        ?>
                    </h3>

                    <?php echo $woocommerce->cart->get_item_data( $value );

                    $data_min = apply_filters( 'woocommerce_cart_item_data_min', '', $_product );
                    $data_max = ( $_product->backorders_allowed() ) ? '' : $_product->get_stock_quantity();
                    $data_max = apply_filters( 'woocommerce_cart_item_data_max', $data_max, $_product );
                    //printf( '<div class="quantity"><input name="cart[%s][qty]" data-min="%s" data-max="%s" value="%s" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div>', $key, $data_min, $data_max, esc_attr( $value['quantity'] ) );
                    ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php
            $settings = get_option( 'woocommerce_multiple_shipping_settings', array() );

            if ( isset($settings['cart_duplication'])  && $settings['cart_duplication'] != 'no' ):
            ?>
            <a class="duplicate-cart-button user-duplicate-cart" href="#"><?php _e('Duplicate Cart', 'wc_shipping_multiple_address'); ?></a>
            <img class="help_tip" title="Duplicating your cart will allow you to ship the exact same cart contents to multiple locations. This will also increase the price of your purchase." src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png">
            <?php endif; ?>
        </div>

        <div id="cart_addresses">
            <?php
            if ($user->ID != 0):
                $addresses = get_user_meta($user->ID, 'wc_other_addresses', true);

            ?>
            <h2>
                <?php _e('Saved Addresses', 'wc_shipping_multiple_address'); ?>
                <a class="h2-link user-add-address" href="#"><?php _e('Add New', 'wc_shipping_multiple_address'); ?></a>
            </h2>

            <div id="addresses_container">
                <?php
                    $addresses_url = get_permalink( woocommerce_get_page_id( 'account_addresses' ) );
                    if ($addresses) foreach ($addresses as $x => $addr) {

		                if ( empty( $addr ) )
		                	continue;

                        $address_fields = $woocommerce->countries->get_address_fields( $addr['shipping_country'], 'shipping_' );
                        //$address_fields = apply_filters( 'woocommerce_shipping_fields', array() );

                        $address = array();
                        $formatted_address = false;

                        foreach ( $address_fields as $field_name => $field ) {
                            $addr_key = str_replace('shipping_', '', $field_name);
                            $address[$addr_key] = ( isset($addr[$field_name]) ) ? $addr[$field_name] : '';
                        }

                        if (! empty($address) ) {
                            $formatted_address  = $woocommerce->countries->get_formatted_address( $address );
                            $json_address       = json_encode($address);
                        }

                        if ( ! $formatted_address )
                        	continue;
		                ?>
		                <div class="account-address">
                            <a class="edit" href="<?php echo $addresses_url .'#shipping_address_'. $x; ?>">edit</a>
		                    <address><?php echo $formatted_address; ?></address>

		                    <div style="display: none;">
		                    <?php
		                    foreach ($shipFields as $key => $field) :
		                        $val = (isset($addr[$key])) ? $addr[$key] : '';
		                        $key .= '_'. $x;

		                        woocommerce_form_field( $key, $field, $val );
		                    endforeach;

		                    do_action('woocommerce_after_checkout_shipping_form', $checkout);
		                    ?>
		                        <input type="hidden" name="addresses[]" value="<?php echo $x; ?>" />
		                        <textarea style="display:none;"><?php echo $json_address; ?></textarea>
		                    </div>

		                    <ul class="items-column" id="items_column_<?php echo $x; ?>">
		                        <?php
		                        if ( isset($relations[$x]) && !empty($relations[$x]) ):
		                            $qty = array_count_values($relations[$x]);
		                            foreach ( $contents as $key => $value ) {
		                                if ( in_array($key, $relations[$x]) ) {
		                                    if ( isset($placed[$key]) ) {
		                                        $placed[$key] += $qty[$key];
		                                    } else {
		                                        $placed[$key] = $qty[$key];
		                                    }
		                        ?>
		                        <li data-product-id="<?php echo $value['product_id']; ?>" data-key="<?php echo $key; ?>" class="address-item address-item-<?php echo $value['product_id']; ?> address-item-key-<?php echo $key; ?>">
		                            <span class="qty"><?php echo $qty[$key]; ?></span>
		                            <h3 class="title"><?php echo get_the_title($value['product_id']); ?></h3>
		                            <?php echo $woocommerce->cart->get_item_data( $value ); ?>

		                            <?php for ($item_qty = 0; $item_qty < $qty[$key]; $item_qty++): ?>
		                            <input type="hidden" name="items_<?php echo $x; ?>[]" value="<?php echo $key; ?>">
		                            <?php endfor; ?>
		                            <a class="remove" href="#"><img style="width: 16px; height: 16px;" src="<?php echo plugins_url('delete.png', __FILE__); ?>" class="remove" title="Remove"></a>
		                        </li>
		                        <?php
		                                }
		                            }
		                        ?>

		                        <?php else: ?>
		                        <li class="placeholder">Drag items here</li>
		                        <?php endif; ?>
		                    </ul>
		                </div>
		                <?php
                    }
                echo '</div>';
            else:
                ?>
            <h2>
                <?php _e('Shipping Addresses', 'wc_shipping_multiple_address'); ?>
            <a class="button add-address" href="#"><?php _e('Add New', 'wc_shipping_multiple_address'); ?></a>
            </h2>
            <div id="addresses_container" style="overflow: hidden; width:100%">
                <?php
                    $sigs = array();
                    $displayed_addresses = array();
                    if ( isset($addresses) && !empty($addresses) ) {
                        $sigs = wcms_session_get('cart_address_sigs');
                    }

                    foreach ( $sigs as $sig => $addr_id ) {
                        if (! isset($addresses['shipping_first_name_'. $sig]) ) continue;

                        //$address_fields = apply_filters( 'woocommerce_shipping_fields', $address );
                        $address = array(
                            'first_name'    => $addresses['shipping_first_name_'. $sig],
                            'last_name'     => $addresses['shipping_last_name_'. $sig],
                            'company'       => $addresses['shipping_company_'. $sig],
                            'address_1'     => $addresses['shipping_address_1_'. $sig],
                            'address_2'     => $addresses['shipping_address_2_'. $sig],
                            'city'          => $addresses['shipping_city_'. $sig],
                            'state'         => $addresses['shipping_state_'. $sig],
                            'postcode'      => $addresses['shipping_postcode_'. $sig],
                            'country'       => $addresses['shipping_country_'. $sig]
                        );

                        $formatted_address  = $woocommerce->countries->get_formatted_address( $address );
                        $json_address       = json_encode($address);

                        if (!$formatted_address) continue;
                        if ( in_array($json_address, $displayed_addresses) ) continue;
                        $displayed_addresses[] = $json_address;

                        ?>
                        <div class="account-address">
                            <address><?php echo $formatted_address; ?></address>

                            <div style="display: none;">
                            <?php
                            foreach ($shipFields as $key => $field) :
                                $val = (isset($addresses[$key .'_'. $sig])) ? $addresses[$key .'_'. $sig] : '';
                                $key .= '_'.$addr_id;
                                //$key .= '_'. $x;

                                woocommerce_form_field( $key, $field, $val );
                            endforeach;

                            do_action('woocommerce_after_checkout_shipping_form', $checkout);
                            ?>
                                <input type="hidden" name="addresses[]" value="<?php echo $addr_id; ?>" />
                                <textarea style="display:none;"><?php echo $json_address; ?></textarea>
                            </div>

                            <ul class="items-column" id="items_column_<?php echo $addr_id; ?>">
                                <?php
                                if ( isset($relations[$addr_id]) && !empty($relations[$addr_id]) ):
                                    $qty = array_count_values($relations[$addr_id]);

                                    foreach ( $contents as $key => $value ) {
                                        if ( in_array($key, $relations[$addr_id]) ) {
                                            if ( isset($placed[$key]) ) {
                                                $placed[$key] += $qty[$key];
                                            } else {
                                                $placed[$key] = $qty[$key];
                                            }
                                ?>
                                <li data-product-id="<?php echo $value['product_id']; ?>" data-key="<?php echo $key; ?>" class="address-item address-item-<?php echo $value['product_id']; ?> address-item-key-<?php echo $key; ?>">
                                    <span class="qty"><?php echo $qty[$key]; ?></span>
                                    <h3 class="title"><?php echo get_the_title($value['product_id']); ?></h3>
                                    <?php echo $woocommerce->cart->get_item_data( $value ); ?>
                                    <input type="hidden" name="items_<?php echo $addr_id; ?>[]" value="<?php echo $key; ?>">
                                    <a class="remove" href="#"><img style="width: 16px; height: 16px;" src="<?php echo plugins_url('delete.png', __FILE__); ?>" class="remove" title="Remove"></a>
                                </li>
                                <?php
                                        }
                                    }
                                ?>

                                <?php else: ?>
                                <li class="placeholder">Drag items here</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <?php
                    } // endforeach
                    ?>
            </div>
                <?php
            endif;
                ?>
        </div>

    </div>

    <br clear="both"/>
    <div class="form-row">
        <input type="hidden" name="shipping_type" value="item" />
        <input type="hidden" name="shipping_address_action" value="save" />
        <input type="submit" name="set_addresses" value="<?php echo __('Save Addresses and Continue', 'wc_shipping_multiple_address'); ?>" class="button alt" />
    </div>
</form>
<?php if ( $user->ID == 0 ): ?>
<div id="address_form_template" style="display: none;">
    <form id="add_address_form">
    <div class="shipping_address address_block" id="shipping_address">
    <?php do_action('woocommerce_before_checkout_shipping_form', $checkout); ?>

        <div class="address-column">
    <?php
        foreach ($shipFields as $key => $field) :
            $key = 'address['. $key .']';
            $val = '';

            woocommerce_form_field( $key, $field, $val );
        endforeach;

        do_action('woocommerce_after_checkout_shipping_form', $checkout);
    ?>
            <input type="hidden" name="id" id="address_id" value="" />
        </div>

    </div>

    <input type="submit" class="button" id="use_address" value="<?php _e('Use this address', 'wc_shipping_multiple_address'); ?>" />
    </form>
</div>
<?php else: ?>
<div id="address_form_template" style="display: none;">
    <form id="add_address_form">
    <div class="shipping_address address_block" id="shipping_address">
    <?php do_action('woocommerce_before_checkout_shipping_form', $checkout); ?>

        <div class="address-column">
    <?php
        foreach ($shipFields as $key => $field) :
            $val = '';
            $key = 'address['. $key .']';

            woocommerce_form_field( $key, $field, $val );
        endforeach;

        do_action('woocommerce_after_checkout_shipping_form', $checkout);
    ?>
        </div>
    </div>

    <input type="submit" id="save_address" class="button" value="<?php _e('Save Address', 'wc_shipping_multiple_address'); ?>" />
    </form>
</div>
<?php endif; ?>
<div id="duplicate_address_form_template" style="display:none;">
    <form id="duplicate_cart_form">

        <p>
            <select name="address_id">
                <option value=""><?php _e('Select an existing address', 'wc_shipping_multiple_address'); ?></option>
                <?php
                foreach ($addresses as $x => $addr) {
                    if ( empty($addr) ) continue;

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

                    if (!$formatted_address) continue;
                ?>
                    <option value="<?php echo $x; ?>">
                        <?php echo $address['first_name'] .' '. $address['last_name'] .' - '. $address['address_1'] .' '. $address['address_2'] .', '. $address['city'] .', '. $address['state'] .' '. $address['country'] .' '. $address['postcode']; ?>
                    </option>
                <?php
                }
                ?>
            </select>
        </p>

    <div class="shipping_address address_block" id="shipping_address">
    <?php do_action('woocommerce_before_checkout_shipping_form', $checkout); ?>

        <div class="address-column">
    <?php
        foreach ($shipFields as $key => $field) :
            $val = '';
            $key = 'address['. $key .']';

            woocommerce_form_field( $key, $field, $val );
        endforeach;

        do_action('woocommerce_after_checkout_shipping_form', $checkout);
    ?>
        </div>
    </div>

    <input type="submit" id="duplicate_cart" class="button" value="<?php _e('Duplicate Cart Items', 'wc_shipping_multiple_address'); ?>" />
    </form>
</div>
