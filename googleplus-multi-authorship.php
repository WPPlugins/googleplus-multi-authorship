<?php
/*
Plugin Name: GooglePlus Multi-Authorship
Plugin URI: http://nimrodflores.com/googleplus-multi-authorship/
Description: GooglePlus Multi-Authorship provides a straightforward solution to properly add Googleplus Authorship markup to the head of each post and page on a multi-authored site.
Version: 1.0
Author: Nimrod Flores
Author URI: http://nimrodflores.com
License: GPL2
*/

/*  Copyright 2013  Nimrod Flores, nimrodflores.com  (contact@nimrodflores.com)

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

if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");


// ------------------------------------------------------------------
//	Add Google+ contact method to user profile page:
// ------------------------------------------------------------------
if ( !function_exists( 'gpma_add_contactmethod' ) ) {
	function gpma_add_contactmethod( $contactmethods ) {
		$contactmethods['google_plus'] = __( 'Google+' );
		return $contactmethods;
	}
}
add_filter( 'user_contactmethods', 'gpma_add_contactmethod' );


// ------------------------------------------------------------------
//	Add settings submenu page into Settings:
// ------------------------------------------------------------------
function gpma_settings_page() {
	add_options_page(
		'Google+ Multi-Authorship Default Settings',
		'G+ Multi-Authorship',
		'manage_options',
		'google-plus-multi-authorship',
		'make_gpma_settings_page'
	);
}
add_action( 'admin_menu', 'gpma_settings_page' );

// ------------------------------------------------------------------
// Construct the GPMA settings page
// ------------------------------------------------------------------
function make_gpma_settings_page() { ?>
	<div class="wrap">
    <?php screen_icon('options-general'); ?>
	<h2>GooglePlus Multi-Authorship Default Settings</h2>
	<form method="post" action="options.php">
    <?php settings_fields( 'gpma-settings' );
	
	do_settings_sections('google-plus-multi-authorship');
	
	submit_button();
	?>
    </form>
    </div>
<?php	
}

// ------------------------------------------------------------------
// Add section, fields and settings during admin_init
// ------------------------------------------------------------------
function gpma_settings_init() {
	// Add the section to google-plus-multi-authorship settings so we can add our
 	// fields to it
 	add_settings_section('gpma_settings_section', // id
		'Set the default Google+ author profile and the Google+ publisher page.', // section title
		'gpma_settings_section', // callback function
		'google-plus-multi-authorship'); // page slug
 	
 	// Add the fields with the names and functions to use for our
 	// settings, put it in our section
 	add_settings_field('gpma_author', // id
		'Default Google+ Author Profile', // title
		'gpma_author_setting', // callback function
		'google-plus-multi-authorship', // page slug
		'gpma_settings_section'); // settings section
		
 	add_settings_field('gpma_publisher',
		'Google+ Publisher Page',
		'gpma_publisher_setting',
		'google-plus-multi-authorship',
		'gpma_settings_section');
 	
 	// Register our setting so that $_POST handling is done for us and
 	// our callback function just has to echo the <input>
 	register_setting('gpma-settings','gpma_author');
	register_setting('gpma-settings', 'gpma_publisher');
}// gpma_settings_init()

add_action('admin_init', 'gpma_settings_init');

// ------------------------------------------------------------------
// Settings section callback function:
// ------------------------------------------------------------------
function gpma_settings_section() {
	//echo '<p>Set the default Googleplus author profile and the publisher public page.</p>';
	echo '<p style="width:100%; max-width:800px;">Each author should add their Google+ profile URL in their WP user profile settings.</p>';
	echo '<p style="width:100%; max-width:800px;">You can provide a default Google+ author profile below. This will be used when an author has not added a Googleplus profile in his/her WP user profile settings. Leave it blank if you don\'t want to use a default.</p>';
}

// ------------------------------------------------------------------
// Callback functions for the settings fields:
// ------------------------------------------------------------------
 function gpma_author_setting() {
 	$field = '<input name="gpma_author" id="gpma_author" type="text" size="100" value="';
	$field .= get_option( 'gpma_author', '' );
	$field .= '" />';
	echo $field;
}
function gpma_publisher_setting() {
 	$field = '<input name="gpma_publisher" id="gpma_publisher" type="text" size="100" value="';
	$field .= get_option( 'gpma_publisher', '' );
	$field .= '" />';
	echo $field;
}

// ------------------------------------------------------------------
// Add the authorship link tag to wp_head:
// ------------------------------------------------------------------
function gpma_head() {
	if ( is_feed() ) return;
	
	$publisher = get_option( 'gpma_publisher', '' );
	$default_author = get_option( 'gpma_author', '' );
	global $post;
	
	if ( is_single() || is_page() && !is_front_page() && !is_home() ) {
		$author_id = $post->post_author;
		$author_googleplus = get_user_meta($author_id, 'google_plus', true);
		if ( !$author_googleplus ) $author_googleplus = $default_author;
		if ( !$author_googleplus ) {} else {
			$link_tag = '<link rel="author" href="';
			$link_tag .= $author_googleplus;
			$link_tag .= '">';
			echo $link_tag;
		}
		if ( $publisher != '' ) {
			$link_tag = '<link rel="publisher" href="';
			$link_tag .= $publisher;
			$link_tag .= '">';
			echo $link_tag;
		} // if $publisher is specified
	} // end if single post or page
	else { // if all other page type
		if ( $publisher != '' ) {
			$link_tag = '<link rel="publisher" href="';
			$link_tag .= $publisher;
			$link_tag .= '">';
			echo $link_tag;
		} // if $publisher is specified		
	} // end if all other page types
}
add_action( 'wp_head', 'gpma_head' );