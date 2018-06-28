<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://makewebbetter.com/
 * @since             1.0.0
 * @package           hubwoo-integration
 *
 * @wordpress-plugin
 * Plugin Name:       HubSpot WooCommerce Integration
 * Plugin URI:        makewebbetter.com/hubspot-woocommerce-integration
 * Description:       A very powerful plugin to integrate your WooCommerce store with HubSpot seemlesly.
 * Version:           		1.1.4
 * Requires at least: 		4.4
 * Tested up to: 			4.9
 * WC requires at least: 	3.0
 * WC tested up to: 		3.3
 * Author:            makewebbetter
 * Author URI:        http://makewebbetter.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       hubwoo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*define( 'PLUGIN_VERSION', '1.0.0' );
*/
$activated = true;

if( function_exists('is_multisite') && is_multisite() )
{
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) )
	{
		$activated = false;
	}
}
else
{
	if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
	{
		$activated = false;
	}
}
/**
 * Checking if WooCommerce is active
 **/
if( $activated )
{
	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-hubwoo-activator.php
	 */
	function activate_hubwoo() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-hubwoo-activator.php';
		Hubwoo_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-hubwoo-deactivator.php
	 */
	function deactivate_hubwoo() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-hubwoo-deactivator.php';
		Hubwoo_Deactivator::deactivate();
	}

	register_activation_hook( __FILE__, 'activate_hubwoo' );
	register_deactivation_hook( __FILE__, 'deactivate_hubwoo' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-hubwoo.php';

	/**
	 * define HubWoo constants.
	 *
	 * @since 1.0.0
	*/
	function hubwoo_define_constants(){

		hubwoo_define( 'HUBWOO_ABSPATH', dirname( __FILE__ ) . '/' );
		hubwoo_define( 'HUBWOO_URL', plugin_dir_url( __FILE__ ) . '/' );
		hubwoo_define( 'HUBWOO_VERSION', '1.1.4' );
		hubwoo_define( 'HUBWOO_CLIENT_ID', '769fa3e6-79b1-412d-b69c-6b8242b2c62a' );
		hubwoo_define( 'HUBWOO_SECRET_ID', '2893dd41-017e-4208-962b-12f7495d16b0' );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param  string $name
	 * @param  string|bool $value
	 * @since 1.0.0
	*/
	function hubwoo_define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
	/**
	 * Setting Page Link
	 * @name hubwoo_admin_settings
	 * @since    1.0.0
	 * @author  makewebbetter
	 * @link  http://makewebbetter.com/
	 */

	function hubwoo_admin_settings($actions, $plugin_file) {

		static $plugin;
		
		if (! isset ( $plugin ) ) {
	
			$plugin = plugin_basename ( __FILE__ );
		}
		if ( $plugin == $plugin_file ) {
			$settings = array (
					'settings' => '<a href="' . admin_url ( 'admin.php' ).'?page=hubwoo'. '">' . __ ( 'Settings', 'hubwoo' ) . '</a>',
			);
			$actions = array_merge ( $settings, $actions );
		}
		
		return $actions;
	}
	
	//add link for settings
	add_filter ( 'plugin_action_links','hubwoo_admin_settings', 10, 5 );
	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_hubwoo() {

		//define contants if not defined..
		hubwoo_define_constants();

		$Hubwoo = new Hubwoo();
		$Hubwoo->run();

		$GLOBALS['hubwoo'] = $Hubwoo;

	}
	run_hubwoo();
}
else {
	/**
	 * Show warning message if woocommerce is not install
	 * @since 1.0.0
	 * @name hubwoo_plugin_error_notice()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */

	function hubwoo_plugin_error_notice()
 	{ ?>
 		 <div class="error notice is-dismissible">
 			<p><?php _e( 'Woocommerce is not activated, Please activate Woocommerce first to install HubSpot WooCommerce Integration.', 'hubwoo' ); ?></p>
   		</div>
   		<style>
   		#message{display:none;}
   		</style>
   	<?php 
 	} 
 	add_action( 'admin_init', 'hubwoo_plugin_deactivate' );  
 
 	
 	/**
 	 * Call Admin notices
 	 * 
 	 * @name hubwoo_plugin_deactivate()
 	 * @author makewebbetter<webmaster@makewebbetter.com>
 	 * @link http://www.makewebbetter.com/
 	 */ 	
  	function hubwoo_plugin_deactivate()
	{
	   deactivate_plugins( plugin_basename( __FILE__ ) );
	   add_action( 'admin_notices', 'hubwoo_plugin_error_notice' );
	}
}
?>