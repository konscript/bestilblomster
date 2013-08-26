<?php
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php

/*-----------------------------------------------------------------------------------

TABLE OF CONTENTS

- woo_custom_navigation_deprecation_notice()
- Woothemes Custom Navigation Setup
-- Woothemes Custom Navigation Setup
-- Woothemes Custom Navigation Menu Item
-- Woothemes Custom Navigation Scripts
- Woothemes Custom Navigation Interface
- Woothemes Custom Navigation Functions
-- woo_custom_navigation_output()
-- woo_custom_navigation_sub_items()
-- woo_child_is_current()
-- woo_get_pages()
-- woo_get_categories()
-- woo_custom_navigation_default_sub_items()
- Recursive Get Child Items Function

-----------------------------------------------------------------------------------*/

function woo_custom_navigation_deprecation_notice( $function ) {
	trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s of the WooFramework! Use %3$s instead.', 'woothemes' ), $function, '5.4', __( 'WordPress Menu Management', 'woothemes' ) ) );
}

/*-----------------------------------------------------------------------------------*/
/* Woothemes Custom Navigation Menu Setup
/* Setup of the Menu
/* Add Menu Item to the theme
/* Scripts - JS and CSS
/*-----------------------------------------------------------------------------------*/

function woo_custom_navigation_setup() { woo_custom_navigation_deprecation_notice( __FUNCTION__ ); }

function woo_custom_nav_reset() { woo_custom_navigation_deprecation_notice( __FUNCTION__ ); }

function woo_custom_navigation_menu() { woo_custom_navigation_deprecation_notice( __FUNCTION__ ); }

function woo_custom_nav_scripts() { woo_custom_navigation_deprecation_notice( __FUNCTION__ ); }


/*-----------------------------------------------------------------------------------*/
/* Woothemes Custom Navigation Menu Interface
/* woo_custom_navigation() is the main function for the Custom Navigation
/* See functions in admin-functions.php
/*-----------------------------------------------------------------------------------*/

function woo_custom_navigation() { woo_custom_navigation_deprecation_notice( __FUNCTION__ ); }

/*-----------------------------------------------------------------------------------*/
/* WooThemes Custom Navigation Functions */
/* woo_custom_navigation_output() displays the menu in the back/frontend
/* woo_custom_navigation_sub_items() is a recursive sub menu item function
/* woo_get_pages()
/* woo_get_categories()
/* woo_custom_navigation_default_sub_items() is a recursive sub menu item function
/*-----------------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------------*/
/* Main Output Function
/* args list
/* type - frontend or backend
/* name - name of your menu
/* id - id of menu in db
/* desc - 1 = show descriptions, 2 = dont show descriptions
/* before_title - html before title is outputted in <a> tag
/* after_title - html after title is outputted in <a> tag
/*-----------------------------------------------------------------------------------*/

function woo_custom_navigation_output($args = array()) { woo_custom_navigation_deprecation_notice( __FUNCTION__ ); wp_list_pages( 'sort_column=menu_order&depth=6&title_li=&exclude=' ); }

//RECURSIVE Sub Menu Items
function woo_custom_navigation_sub_items($post_id,$type,$table_name,$output_type,$menu_id = 0,$depth = 0,$depth_counter = 0) { woo_custom_navigation_deprecation_notice( __FUNCTION__ ); }

//Checks if any of parent menu items children are the current page
function woo_child_is_current($parent_id, $menu_id, $table_name, $queried_id, $type_settings, $full_web_address) { woo_custom_navigation_deprecation_notice( __FUNCTION__ ); }

//Outputs All Pages and Sub Items
function woo_get_pages($counter,$type) { woo_custom_navigation_deprecation_notice( __FUNCTION__ ); }

//Outputs All Categories and Sub Items
function woo_get_categories($counter, $type) { woo_custom_navigation_deprecation_notice( __FUNCTION__ ); }

//RECURSIVE Sub Menu Items of default categories and pages
function woo_custom_navigation_default_sub_items($childof, $intCounter, $parentli, $type, $output_type) { woo_custom_navigation_deprecation_notice( __FUNCTION__ ); }

/*-----------------------------------------------------------------------------------*/
/* Recursive get children */
/*-----------------------------------------------------------------------------------*/

function get_children_menu_elements($childof, $intCounter, $parentli, $type, $menu_id, $table_name) { woo_custom_navigation_deprecation_notice( __FUNCTION__ ); }

?>