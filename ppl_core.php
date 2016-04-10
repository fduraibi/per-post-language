<?php
/*
* Plugin Name: Per Post Language
* Plugin URI: http://www.fadvisor.net/blog/xxxx
* Description: This plugin allow you to set the blog language per post while having a default blog language.
* Domain Path: /languages
* Text Domain: perpostlanguage
* License:     GPLv3
* Version: 1.0
* Author: Fahad Alduraibi
* Author URI: http://www.fadvisor.net/blog/

Copyright (C) 2016 Fahad Alduraibi

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

require 'ppl_settings_page.php';

// Add settings link on plugin page
function ppl_add_settings_links($links) { 
	$settingsLink = '<a href="options-general.php?page=ppl_settings_page">' . esc_html__('Settings') . '</a>'; 
	array_unshift($links, $settingsLink); 
	return $links; 
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ppl_add_settings_links' );

function ppl_add_settings_page(){
    add_option( "ppl_selected_languages");
}
register_activation_hook( __FILE__, "ppl_add_settings_page" );

function ppl_set_post_language() {
	$postID = url_to_postid( $_SERVER["REQUEST_URI"] );
	if ($postID > 0) {
		$postLanguage = esc_attr( get_post_meta( $postID, '_ppl_post_language', true ) );
		if ( ! empty( $postLanguage ) ) {
			global $locale;
			$locale = $postLanguage;
		}
	}
}
// Any call to 'url_to_postid' earlier then 'setup_theme' will generate a fatal error.
add_action('setup_theme', 'ppl_set_post_language');

// Update the post language if the user has selected one when saving the post
function ppl_save_post_meta( $post_id ) {
	global $post;
	if( $post->post_type == "post" ) {
		if (isset( $_POST ) && isset($_POST['pplPostLang']) ) {
			update_post_meta( $post_id, '_ppl_post_language', strip_tags( $_POST['pplPostLang'] ) );
		}
	}
}
add_action( 'save_post', 'ppl_save_post_meta' );

function ppl_get_language_list( $post ) {
	$postID = $post->ID;
	if ($postID > 0) {
		$postLanguage = esc_attr( get_post_meta( $postID, '_ppl_post_language', true) );
		$pplLanguages = get_option("ppl_options");
		if ( $pplLanguages == false ) {
			esc_html_e('You need to add languages from the plugin settings page.', 'perpostlanguage');
			?> <a href="options-general.php?page=ppl_settings_page"><?php esc_html_e('Go to settings', 'perpostlanguage');?></a><?php
		} else {
			foreach ($pplLanguages as $key => $value) {
				?>
				<input type="radio" name="pplPostLang" value="<?php echo $key; ?>" <?php if ($postLanguage == $key){ echo "checked=\"checked\"";} ?>>
					<?php echo $value; ?>
				</input><br />
				<?php
			}
		}
	}
}

function ppl_register_meta_boxes() {
	if(current_user_can( 'edit_posts' ) ){
		add_meta_box( 'ppl_meta_box', esc_html__( 'Post Language', 'perpostlanguage' ), 'ppl_get_language_list', 'post', 'side', 'high', null );
	}
}
add_action( 'add_meta_boxes', 'ppl_register_meta_boxes' );

// Load plugin textdomain (translation file).
function ppl_load_textdomain() {
	load_plugin_textdomain( 'perpostlanguage', false, dirname(plugin_basename(__FILE__)) . '/languages' ); 
}
add_action( 'init', 'ppl_load_textdomain' );

?>