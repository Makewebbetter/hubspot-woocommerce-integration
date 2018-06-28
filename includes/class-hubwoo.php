<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Hubwoo {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Hubwoo_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'HUBWOO_VERSION' ) ) {
			$this->version = HUBWOO_VERSION;
		} else {
			$this->version = '1.1.4';
		}
		$this->plugin_name = 'hubwoo';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Hubwoo_Loader. Orchestrates the hooks of the plugin.
	 * - Hubwoo_i18n. Defines internationalization functionality.
	 * - Hubwoo_Admin. Defines all hooks for the admin area.
	 * - Hubwoo_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoo-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoo-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-hubwoo-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-hubwoo-public.php';

		$this->loader = new Hubwoo_Loader();

		/**
		 * The class responsible for all api actions with hubspot.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoo-connection-manager.php';

		/**
		 * The class contains all the information related to customer groups and properties.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoo-contact-properties.php';

		/**
		 * The class contains are readymade contact details to send it to 
		 * hubspot.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoo-customer.php';

		/**
		 * The class responsible for property values.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoo-property-callbacks.php';

		/**
		 * The class responsible for handling ajax requests.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoo-ajax-handler.php';

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Hubwoo_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Hubwoo_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Hubwoo_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'hubwoo_set_cron_schedule_time' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'hubwoo_dashboard_setup_notice' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'hubwoo_dashboard_alert_notice' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'hubwoo_redirect_from_hubspot' );

		$plugin_enable = get_option( 'hubwoo_settings_enable', 'no' );
		
		if( $plugin_enable == 'yes' ) {
			
			$this->loader->add_action( 'hubwoo_cron_schedule', $plugin_admin, 'hubwoo_cron_schedule' );

			if( get_option( "hubwoo_abncart_added", false ) )
			{
				$this->loader->add_action( 'save_post', $plugin_admin, 'hubwoo_update_changed_skus', 10, 3 );
				$this->loader->add_action( 'created_term', $plugin_admin, 'hubwoo_updating_categories', 10, 3 );
				$this->loader->add_action( 'edit_term', $plugin_admin, 'hubwoo_updating_categories', 10, 3 );
				$this->loader->add_action( 'post_updated', $plugin_admin, 'hubwoo_update_product_names', 10, 3 );
			}
			
			$this->loader->add_action( 'admin_init', $plugin_admin, 'hubwoo_update_new_addons_groups_properties' );
			$this->loader->add_action( 'admin_init', $plugin_admin, 'hubwoo_update_new_version_groups_properties' );
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Hubwoo_Public( $this->get_plugin_name(), $this->get_version() );

		$plugin_enable = get_option( 'hubwoo_settings_enable', 'no' );

		if( $plugin_enable == 'yes' ) {
			$this->loader->add_action( 'profile_update', $plugin_public, 'hubwoo_woocommerce_save_account_details' );
			$this->loader->add_action( 'user_register', $plugin_public, 'hubwoo_woocommerce_save_account_details' );
			$this->loader->add_action( 'woocommerce_checkout_update_user_meta', $plugin_public, 'hubwoo_woocommerce_save_account_details' );
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Hubwoo_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * predefined default hubwoo tabs.
	 * @return 	Array 		An key=>value pair of hubspot tabs.
	 */
	public function hubwoo_default_tabs(){

		return array( 
				'hubwoo_connect' => __( 'Connect', 'hubwoo' ),
				);
	}

	/**
	 * Locate and load appropriate tempate.
	 *
	 * @since 	1.0.0
	 */
	public function load_template_view( $path, $params=array() ){

		$file_path = HUBWOO_ABSPATH.$path;

		if( file_exists( $file_path ) ){

			// includes the file..
			include $file_path;

		}else{

			$notice = sprintf( __( 'Unable to locate file path at location "%s" some features may not work properly in HubSpot-WooCommerce-Integration, please contact us!', 'hubwoo' ) , $file_path );

			$this->hubwoo_notice( $notice, 'error' );

		}
	}

	/**
	 * show admin notices.
	 * @param  string 	$message 	Message to display.
	 * @param  string 	$type    	notice type, accepted values - error/update/update-nag
	 * @since  1.0.0
	 */
	public static function hubwoo_notice( $message, $type='error' ) {

		$classes = "notice ";
		switch($type){

			case 'update':
				$classes .= "updated";
				break;

			case 'update-nag':
				$classes .= "update-nag";
				break;
			case 'success':
				$classes .= "notice-success is-dismissible";
				break;

			default:
				$classes .= "error";
		} 

		$notice = '<div class="'. $classes .'">';
			$notice .= '<p>'. $message .'</p>';
		$notice .= '</div>';

		echo $notice;	
	}

	/**
	 * check if access token is expired.
	 * @return boolean [description]
	 */
	public static function is_access_token_expired(){

		$get_expiry = get_option( 'hubwoo_token_expiry', false );
		
		if( $get_expiry ) {
			$current_time = time();
			if( $current_time > $get_expiry ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * check if valid hubspot client Ids is stored.
	 * @return boolean [description]
	 */
	public static function is_valid_client_ids_stored(){

		$hapikey = HUBWOO_CLIENT_ID;
		$hseckey = HUBWOO_SECRET_ID;

		if( $hapikey && $hseckey ){

			return get_option( 'hubwoo_valid_client_ids_stored' , false );
		}

		return false;
	}

	public function is_display_suggestion_popup() {
		$suggest = get_option( 'hubwoo_send_suggestions', false);
		if( $suggest ) {
			$success = get_option( 'hubwoo_suggestions_sent', false);
			if( !$success ) {
				$later = get_option( 'hubwoo_suggestions_later', false);
				if( !$later ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * verify if the hubspot setup is completed.
	 *
	 * @since 1.0.0
	 */
	public static function is_setup_completed( ){

		return get_option( 'hubwoo_setup_completed', false );
	}
}
