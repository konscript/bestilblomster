jQuery(document).ready(function($) {
    jQuery(".address_book").live("click", function(e) {
        e.preventDefault();
        var sig = jQuery(this).data('sig');
        tb_show('Address Book', wc_ship_url + "&sig="+ sig);
    });
    
    jQuery(".address_save").live("click", function(e) {
        e.preventDefault();
        
        var btn     = this;
        var sig     = jQuery(this).data("sig");
        var block      = jQuery(this).parents("div.address_block");
        var addr    = {};
        var valid   = true;
        var post    = true;
        
        jQuery(block).find("input,select").each(function() {
            // make sure the address is valid
            if (jQuery(this).prev("label").children("abbr").length == 1 && jQuery(this).val() == "") {
                jQuery(this).focus();
                valid   = false;
                post    = false;
            }
            
            if (! valid) {
                return false;
            }
            
            name    = jQuery(this).attr("id").replace("_"+ sig, "");
            val     = jQuery(this).val();
            addr[name] = val;
        });
        
        if (! post) return false;
        
        jQuery.post(
            WC_Shipping.ajaxurl, {
                action : 'wc_save_to_address_book',
                address: addr
            },
            function( response ) {
                if (response == "OK") {
                    jQuery(btn).remove();
                    alert("Address saved");
                } else {
                    alert(response);
                }
            }
        );
    });
});
function setAddress(addr, sig) {
    address = addr;
    for (field in address) {
        jQuery("#shipping_"+ field +"_"+ sig).val(address[field]);
    }
}