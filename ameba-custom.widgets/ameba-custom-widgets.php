<?php
 /**
 * Plugin Name: Ameba - Custom Widgets
 * Plugin URI: http://www.mercurycreative.net
 * Description: This plugin adds custom Widgets
 * Version: 1.0
 * Author: Mercury Creative
 */

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

define( 'ADD_CUSTOM_WIDGETS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ADD_CUSTOM_WIDGETS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
/**
 *	Registration hook
 */
register_activation_hook( __FILE__, 'ameba_widgets_activate' );
function ameba_widgets_activate(){
    // Require cmb2 plugin
    if( ! is_plugin_active( 'cmb2/init.php' ) and current_user_can( 'activate_plugins' ) ) {
        wp_die('Sorry, but this plugin requires the <a href="' . admin_url( 'plugin-install.php?tab=search&s=cmb2' ) . '">CMB2</a> Plugin to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
    }
}
/**
 *	Require widgets classes
 */
spl_autoload_register(function($class){
	$segments = array_filter( explode("\\", $class) );
	if( array_shift($segments) === "AmebaWidget" ){
		$path = __DIR__ . "/" . implode("/", $segments) . ".php";
		if(file_exists($path)){
			include $path;
		}
	}
});
/**
 *	Register Widgets
 */
add_action( 'widgets_init', 'regAmebaCustomWidgets' );
function regAmebaCustomWidgets() {
	//example of widget register
    //register_widget( 'AmebaWidget\genericWidgets\genericWidgetsWidget' ); 
}
/**
 *	widget metaboxes functions
 */
require_once ADD_CUSTOM_WIDGETS_PLUGIN_DIR."amebaCWMetaboxfunc.php";

?>
