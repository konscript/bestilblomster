jQuery(document).ready( function($) {

    $(document).on('wc_variation_form', 'form.variations_form.swatches', function() {
        if ( ! $(this).data('calculator') ) {
            var calculator = new variation_calculator( $(this).data('product_attributes'), $(this).data('product_variations'), $(this).data('product_variations_flat'), my_all_set_callback, my_not_all_set_callback );

            $(this).find('.variations select').unbind();
            $(this).find('div.select, select').unbind();
            $(this).data('calculator', calculator);
            init_swatches();

            calculator.reset_selected();
            calculator.reset_current();
        }
    });

    $('form.variations_form.swatches').each(function(){
        var calculator = new variation_calculator( $(this).data('product_attributes'), $(this).data('product_variations'), $(this).data('product_variations_flat'), my_all_set_callback, my_not_all_set_callback );

        $(this).find('.variations select').unbind();
        $(this).find('div.select, select').unbind();
        $(this).data('calculator', calculator);
        init_swatches();

        calculator.reset_selected();
        calculator.reset_current();
    });

    function my_not_all_set_callback() {

        // Reset image
        var img = $('div.images img:eq(0)');
        var link = $('div.images a.zoom:eq(0)');
        var o_src = $(img).attr('data-o_src');
        var o_href = $(link).attr('data-o_href');

        if ( o_src && o_href ) {
            $(img).attr('src', o_src);
            $(link).attr('href', o_href);
        }

        $('form input[name=variation_id]').val('').change();
        $('.single_variation_wrap').hide();
        $('.single_variation').text('');


        if( $().uniform && $.isFunction($.uniform.update) ) {
            $.uniform.update();
        }

    }

    function my_all_set_callback(selected, product_variations) {
        var found = null;

        for (sa in selected) {
            $('#' + sa).val( selected[sa] );
        }

        for(var p = 0; p < product_variations.length; p++) {
            var result = true;
            for (attribute in product_variations[p].attributes) {
                for(selected_attribute in selected) {
                    if (selected_attribute == attribute) {
                        var v = product_variations[p].attributes[attribute];
                        if (v != selected[selected_attribute]) {
                            result = false;
                        }
                    }
                }
            }

            if (result){
                found = product_variations[p];
            }

        }

        if (!found) {
            for(var p = 0; p < product_variations.length; p++) {
                var result = true;
                for (attribute in product_variations[p].attributes) {
                    for(selected_attribute in selected) {
                        if (selected_attribute == attribute) {
                            var v = product_variations[p].attributes[attribute];
                            var vs = selected[selected_attribute];
                            if (v != '' && v != vs) {
                                result = false;
                            }
                        }
                    }
                }

                if (result){
                    found = product_variations[p];
                }
            }
        }

        if (found) {
            show_variation(found);
        }
    }

    function show_variation(variation) {
        swap_image(variation);

        $('.variations_button').show();
        $('.single_variation').html( variation.price_html + variation.availability_html );

        if (variation.sku) {
            $('.product_meta').find('.sku').text( variation.sku );
        } else {
            $('.product_meta').find('.sku').text('');
        }

        $('.single_variation_wrap').find('.quantity').show();

        if (variation.min_qty) {
            $('.single_variation_wrap').find('input[name=quantity]').attr('data-min', variation.min_qty).val(variation.min_qty);
        } else {
            $('.single_variation_wrap').find('input[name=quantity]').removeAttr('data-min');
        }

        if ( variation.max_qty ) {
            $('.single_variation_wrap').find('input[name=quantity]').attr('data-max', variation.max_qty);
        } else {
            $('.single_variation_wrap').find('input[name=quantity]').removeAttr('data-max');
        }

        if ( variation.is_sold_individually == 'yes' ) {
            $('.single_variation_wrap').find('input[name=quantity]').val('1');
            $('.single_variation_wrap').find('.quantity').hide();
        }

        $('form input[name=variation_id]').val(variation.variation_id).change();

        $('.single_variation_wrap').slideDown('200').trigger( 'show_variation', [ variation ] );
        $('form.cart').trigger( 'found_variation', [ variation ] );

    }

    function swap_image(variation) {

        var img = $('div.images img:eq(0)');
        var link = $('div.images a.zoom:eq(0)');
        var o_src = $(img).attr('data-o_src');
        var o_title = $(img).attr('data-o_title');

        var o_href = $(link).attr('data-o_href');


        var variation_image = variation.image_src;
        var variation_link = variation.image_link;
        var variation_title = variation.image_title;


        if (!o_src) {
            $(img).attr('data-o_src', $(img).attr('src'));
        }

        if (!o_title) {
            $(img).attr('data-o_title', $(img).attr('title') );
        }

        if (!o_href) {
            $(link).attr('data-o_href', $(link).attr('href'));
        }


        if (variation_image && variation_image.length > 1) {
            $(img).attr('src', variation_image);
            $(img).attr('title', variation_title);
            $(img).attr('alt', variation_title);
            $(link).attr('href', variation_link);
            $(link).attr('title', variation_title);
        } else {
            $(img).attr('src', o_src);
            $(img).attr('title', o_title);
            $(img).attr('alt', o_title);
            $(link).attr('href', o_href);
            $(link).attr('title', o_title);
        }
    }

    function init_swatches() {

        $('div.select-option').delegate('a', 'click', function(event) {
            event.preventDefault();

            var $the_option = $(this).closest('div.select-option');

            if ($the_option.hasClass('disabled')) {
                return false;
            } else if ($the_option.hasClass('selected')) {
                $the_option.removeClass('selected');

                var select = $the_option.closest('div.select');
                select.data('value', '');
                $(this).parents('div.select').trigger('change', []);

            } else {

                var select = $(this).closest('div.select');
                $(select).find('div.select-option').removeClass('selected');
                $the_option.addClass('selected');

                select.data('value', $the_option.data('value') );
                $(this).parents('div.select').trigger('change', []);
            }

            return false;

        });

        $('form.cart').on('change', 'div.select', function() {

            $variation_form = $(this).closest('form.cart');
            calculator      = $variation_form.data('calculator');

        	$variation_form.trigger( 'woocommerce_variation_select_change' );

            var $parent = $(this).closest('.variation_form_section');
            $('select', $parent).each( function(index, element) {
                var optval = $(element).val();

                optval = optval.replace("'", "&#039;");
                optval = optval.replace('"', "&quot;");


                calculator.set_selected( $(element).data('attribute-name'), optval );
            });

            $('div.select', $parent).each( function(index, element) {
                calculator.set_selected( $(element).data('attribute-name'), $(element).data('value') );
            });

            var current_options = calculator.get_current();
            $('select', $parent).each( function(index, element) {
                var attribute_name = $(element).data('attribute-name');
                var avaiable_options = current_options[attribute_name];

                $(element).find('option:gt(0)').each(function(index, option) {
                    var optval = $(option).val();

                    optval = optval.replace("'", "&#039;");
                    optval = optval.replace('"', "&quot;");

                    if (!avaiable_options[ optval ] ) {
                        $(option).attr('disabled','disabled');
                    } else {
                        $(option).removeAttr('disabled');
                    }
                });
            });

            $('div.select', $parent).each( function(index, element) {
                var attribute_name = $(element).data('attribute-name');
                var avaiable_options = current_options[attribute_name];

                $(element).find('div.select-option').each(function(index, option) {
                    if (!avaiable_options[ $(option).data('value') ] ) {
                        $(option).addClass('disabled','disabled');
                    } else {
                        $(option).removeClass('disabled');
                    }
                });
            });

            calculator.trigger_callbacks();

        });

        $('form.cart').on('change', 'select', function() {

            $variation_form = $(this).closest('form.cart');
            calculator      = $variation_form.data('calculator');

            var $parent = $(this).closest('.variation_form_section');

            $('select', $parent).each( function(index, element) {
                var optval = $(element).val();

                optval = optval.replace("'", "&#039;");
                optval = optval.replace('"', "&quot;");
                calculator.set_selected( $(element).data('attribute-name'), optval);
            });

            var current_options = calculator.get_current();
            $('select', $parent).each( function(index, element) {
                var attribute_name = $(element).data('attribute-name');
                var avaiable_options = current_options[attribute_name];

                $(element).find('option:gt(0)').each(function(index, option) {
                    var optval = $(option).val();

                    optval = optval.replace("'", "&#039;");
                    optval = optval.replace('"', "&quot;");

                    if (!avaiable_options[ optval ] ) {
                        $(option).attr('disabled','disabled');
                    } else {
                        $(option).removeAttr('disabled');
                    }
                });

            });

            $('div.select', $parent).each( function(index, element) {
                var attribute_name = $(element).data('attribute-name');
                var avaiable_options = current_options[attribute_name];

                $(element).find('div.select-option').each(function(index, option) {
                    if (!avaiable_options[ $(option).data('value') ] ) {
                        $(option).addClass('disabled','disabled');
                    } else {
                        $(option).removeClass('disabled');
                    }
                });
            });

            calculator.trigger_callbacks();

        });

        //Fire defaults
        $('div.select-option[data-default=true]').find('a').click();
        $('select', 'form.cart').trigger('change', []);

    }
});

function variation_manager(variations) {
    this.variations = variations;
    this.find_matching_variation = function(selected) {

        for (var v = 0;v<this.variations.length;v++) {
            var variation = this.variations[v];
            var matched = true;

            //Find any with an exact match.
            for( var attribute in variation.attributes ) {
                matched = matched & selected[attribute] != undefined && selected[attribute] == variation.attributes[attribute];
            }

            if (matched) {
                return variation;
            }
        }

        //An exact match was not found.   Find any with a wildcard match
        for (var v = 0;v<this.variations.length;v++) {
            var variation = this.variations[v];
            var matched = true;

            //Find any with an exact match.
            for( var attribute in variation.attributes ) {
                matched = matched & selected[attribute] != undefined && (selected[attribute] == variation.attributes[attribute] || variation.attributes[attribute] == '');
            }

            if (matched) {
                return variation;
            }
        }

        return false;
    }
}

function variation_calculator(keys, possibile, possibile_flat, all_set_callback, not_all_set_callback) {
    this.recalc_needed = true;

    this.all_set_callback = all_set_callback;
    this.not_all_set_callback = not_all_set_callback;

    //The varioius variation key values available as configured in woocommerce.
    this.variation_keys = keys;

    //The actual variations that are configured in woocommerce.
    this.variations_available = possibile_flat;

    //Stores the attribute + values that are currently available
    this.variations_current = {};

    //Stores the selected attributes + values
    this.variations_selected = {};

    this.reset_current = function( ) {
        for(var key in this.variation_keys) {
            this.variations_current[ key ] = {};
            for( var av = 0; av < this.variation_keys[key].length; av++ ) {
                this.variations_current[ key ][ this.variation_keys[key][av] ] = 0;
            }
        }
    };

    this.update_current = function( ) {
        this.reset_current();

        for( var i = 0; i < this.variations_available.length; i++ ) {
            for(var attribute in this.variations_available[ i ]) {

                var available_value = this.variations_available[ i ][attribute];
                var selected_value = this.variations_selected[attribute];

                if (selected_value && selected_value == available_value ) {
                    this.variations_current[ attribute ][ available_value ] = 1;//this is a currently selected attribute value
                } else {

                    var result = true;
                    //Loop though any other item that is selected, checking to see if any DO NOT match.
                    for(var other_selected_attribute in this.variations_selected) {

                        if (other_selected_attribute == attribute) {
                            //We are looking to see if any attribute that is selected will cause this to fail.
                            continue;
                        }

                        var other_selected_attribute_value = this.variations_selected[other_selected_attribute];
                        var other_available_attribute_value = this.variations_available[i][other_selected_attribute];
                        if ( other_selected_attribute_value ) {
                            if ( other_available_attribute_value ) {
                                if (other_selected_attribute_value != other_available_attribute_value) {
                                    result = false;
                                }
                            }
                        }
                    }

                    if (result) {
                        if (available_value) {
                            this.variations_current[ attribute ][ available_value ] = 1;
                        } else {
                            for (var av in this.variations_current[ attribute ]) {
                                this.variations_current[ attribute ][ av ] = 1;
                            }
                        }
                    }

                }
            }
        }

        this.recalc_needed = false;
    };

    this.get_current = function() {
        if (this.recalc_needed) {
            this.update_current();
        }

        return this.variations_current;
    };

    this.get_value_is_current = function( key, value ) {
        if (this.recalc_needed) {
            this.update_current();
        }

        return this.variations_current[ key ][ value ] === true;
    };

    this.reset_selected = function() {
        this.recalc_needed = true;
        this.variations_selected = [];
    }

    this.set_selected = function(key, value) {
        this.recalc_needed = true;
        this.variations_selected[ key ] = value;
    };

    this.get_selected = function() {
        return this.variations_selected;
    }

    this.trigger_callbacks = function(){
        var all_set = true;

        for (var key in this.variation_keys) {
            all_set = all_set & this.variations_selected[key] != undefined && this.variations_selected[key] != '';
        }

        if (all_set) {
            this.all_set_callback( this.variations_selected, possibile );
        } else {
            this.not_all_set_callback();
        }
    }
};