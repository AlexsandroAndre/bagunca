<?php
/**
 * Plugin Name: Retargetengine
 * Description: An easy wordpress plugin that allows you to retarget websites you do not own such as affiliate promotions.
 * Version: 1.1.4
 * Author: Serg Kosmatinski
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'TEEOPTIMIZE_TEMPLATEPATH', WP_PLUGIN_DIR . '/' . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) . '/public/views/' );
define( 'TEEOPTIMIZE_PUBLICURL', WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) );

require_once( plugin_dir_path( __FILE__ ) . 'public/class-teeoptimize.php' );
register_activation_hook( __FILE__, array( 'Teeoptimize', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Teeoptimize', 'deactivate' ) );
add_action( 'plugins_loaded', array( 'Teeoptimize', 'get_instance' ) );

if ( is_admin() ) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-teeoptimize-admin.php' );
	add_action( 'plugins_loaded', array( 'Teeoptimize_Admin', 'get_instance' ) );
}
