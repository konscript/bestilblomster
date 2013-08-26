<?php
/*
Plugin Name: WooDojo
Plugin URI: http://woothemes.com/woodojo/
Description: WooDojo is a powerful collection of WooThemes features to enhance your website.
Version: 1.5.2
Author: WooThemes
Author URI: http://woothemes.com/
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
/*  Copyright 2012  WooThemes  (email : info@woothemes.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	if ( ! defined( 'ABSPATH' ) ) exit;

	require_once( 'classes/woodojo.class.php' );

	$GLOBALS['woodojo'] = new WooDojo( __FILE__ );
	$GLOBALS['woodojo']->version = '1.5.2';
?>