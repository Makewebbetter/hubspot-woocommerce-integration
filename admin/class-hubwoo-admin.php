<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/admin
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Hubwoo_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// let's modularize our codebase, all the admin actions in one function. 
		$this->admin_actions();

	}

	/**
	 * all admin actions.
	 * 
	 * @since 1.0.0
	 */
	public function admin_actions(){
		// add submenu hubspot in woocommerce top menu.
		add_action( 'admin_menu', array( &$this, 'add_hubwoo_submenu' ) );
	}

	/**
	 * add hubspot submenu in woocommerce menu..
	 *
	 * @since 1.0.0
	 */
	public function add_hubwoo_submenu(){

		add_submenu_page( 'woocommerce', __('HubSpot', 'hubwoo'), __('HubSpot', 'hubwoo'), 'manage_woocommerce', 'hubwoo', array(&$this, 'hubwoo_configurations') );

	}

	/**
	 * all the configuration related fields and settings.
	 * 
	 * @return html  all the settings and configuration options for hubspot.
	 * @since 1.0.0
	 */
	public function hubwoo_configurations(){

		global $hubwoo;

		// get all the default tabs.
		$default_tabs = $hubwoo->hubwoo_default_tabs();

		/**
		 * filter tabs with the default tab, so that developers can add
		 * there own tabs too with our integration.
		 *
		 * @param Associative array of key => value pair for tabs.
		 * @since 1.0.0
		 */
		$dev_tabs = array();

		if( is_array( $dev_tabs ) && count( $dev_tabs ) ){

			//loop newly added dev tabs and check if they are not in conflict with 
			//default added tabs.
			foreach( $dev_tabs as $dev_tab_key => $dev_tab_name ){

				if( !array_key_exists( $dev_tab_key, $default_tabs ) ){

					$default_tabs[$dev_tab_key] = $dev_tab_name;

				}
			}
		}

		//verify that $tabs is array and have some tabs in it.
		if( is_array( $default_tabs ) && count( $default_tabs ) ){

			// loading tabs template.
			$hubwoo->load_template_view( 'admin/templates/tab-view.php', $default_tabs );

		}else{

			$notice = __( 'No tabs to display, please verify that our extensions are installed correctly, or contact us!', 'hubwoo' );

			$hubwoo->hubwoo_notice( $notice );

		}

	}

	/**
	 * General setting tab fields.
	 * 
	 * @return array  woocommerce_admin_fields acceptable fields in array.
	 * @since 1.0.0
	 */
	public static function hubwoo_general_settings(){

		$basic_settings = array();

		//title 
		$basic_settings[] = array(
				'title' => __('Connect With HubSpot', 'hubwoo'),  
				'id'	=> 'hubwoo_settings_title', 
				'type'	=> 'title'	
			);

		// Enable/Disable option
		$basic_settings[] = array(
				'title' => __('Enable/Disable', 'hubwoo'),
				'id'	=> 'hubwoo_settings_enable', 
				'desc'	=> __('Turn on/off the integration', 'hubwoo'),
				'type'	=> 'checkbox'
			);

		// Enable/Disable Log
		$basic_settings[] = array(
				'title' => __('Enable/Disable', 'hubwoo'),
				'id'	=> 'hubwoo_log_enable', 
				'desc'	=> sprintf( __('Enable logging of the requests. You can view hubspot log file from <a href="%s">Here</a>', 'hubwoo'), '?page=wc-status&tab=logs'),
				'type'	=> 'checkbox'
			);

		$basic_settings[] = array(
				'type' => 'sectionend',
		        'id' => 'hubwoo_settings_end'
			);

		//return $basic_settings;
		return $basic_settings;
	}
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		
		$screen = get_current_screen();
		
        if( isset($screen->id) && $screen->id == 'woocommerce_page_hubwoo' )
        {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/hubwoo-admin.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name."-bootstrap-style", plugin_dir_url( __FILE__ ) . 'css/bootstrap.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$screen = get_current_screen();
		
        if(isset($screen->id) && $screen->id == 'woocommerce_page_hubwoo')
        {
			wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/hubwoo-admin.js', array( 'jquery' ), $this->version, false );

			wp_localize_script( $this->plugin_name, 
				'hubwooi18n', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'hubwooSecurity' => wp_create_nonce( 'hubwoo_security' ), 
					'hubwooWentWrong' => __( 'Something went wrong, please try again later!', 'hubwoo' ), 
					'hubwooSuccess' => __( 'Setup is completed successfully!', 'hubwoo' ),
					'hubwooCreatingGroup' => __( 'Created group', 'hubwoo' ),
					'hubwooCreatingProperty' => __( 'Created property', 'hubwoo' ),
					'hubwooSetupCompleted' => __('Setup completed!', 'hubwoo'),
					'hubwooMailFailure'=> __('Mail not sent', 'hubwoo')
					) );

			wp_enqueue_script( $this->plugin_name );

			wp_register_script( $this->plugin_name."-bootstap-script", plugin_dir_url( __FILE__ ) . 'js/bootstrap.js', array( 'jquery' ), $this->version, false );

			wp_enqueue_script( $this->plugin_name."-bootstap-script" );
		}
	}

	/**
	 * Update schedule data with custom time.
	 *
	 * @since    1.0.0
	 * @param      string    $schedules       Schedule data.
	 */
	public function hubwoo_set_cron_schedule_time( $schedules ) {
		if( !isset( $schedules[ "5min" ] ) ) {
	        $schedules["5min"] = array(
	            'interval' => 5*60,
	            'display' => __( 'Once every 5 minutes', 'hubwoo' )
	        );
	    }
	    return $schedules;
	}
	/**
	 * Schedule Executes when user data is update.
	 *
	 * @since    1.0.0
	 * @param      string    $schedules       Schedule data.
	 */
	public function hubwoo_cron_schedule() {

		$plugin_enable = get_option('hubwoo_settings_enable', 'no');
		
		if( $plugin_enable == 'yes' ) {

			$hubwoo_setup = get_option('hubwoo_setup_completed', false);

			if( $hubwoo_setup ) {

				$valid_token = false;
				
				if( Hubwoo::is_access_token_expired() ) {
			
					$hapikey = HUBWOO_CLIENT_ID;
					$hseckey = HUBWOO_SECRET_ID;
					$status =  HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey);
					if( $status ) {
						$valid_token = true;
					}
				}
				else {
					$valid_token = true;
				}
				
				if( $valid_token ) {

					$args['meta_query'] = array(

							array(
								'key'=>'hubwoo_user_data_change',
								'value'=>'yes',
								'compare'=>'=='
							)
					); 	

					$hubwoo_updated_user = get_users( $args );

					$hubwoo_users = array();

					$hubwoo_users = apply_filters( 'hubwoo_users', $hubwoo_updated_user );

					$hubwoo_unique_users = array();

					foreach( $hubwoo_users as $key => $value )
					{
						if( in_array( $value->ID, $hubwoo_unique_users ) )
						{
							continue;
						}
						else
						{
							$hubwoo_unique_users[]= $value->ID;
						}
					} 

					if( isset( $hubwoo_unique_users) && $hubwoo_unique_users != null  && count( $hubwoo_unique_users ) ) 
					{
						foreach ( $hubwoo_unique_users as $key => $ID )
						{
							if( $key == 50 )
							{
								break;
							}

							$hubwoo_customer = new HubWooCustomer( $ID );

							$properties = $hubwoo_customer->get_contact_properties();

							$properties = apply_filters( 'hubwoo_map_new_properties', $properties, $ID );
							
							$properties_data = array( 'email' => $hubwoo_customer->get_email(),'properties' => $properties );

							$contacts[] = $properties_data;

							update_user_meta( $ID, 'hubwoo_user_data_change', 'no' );
						}

						HubWooConnectionMananager::get_instance()->create_or_update_contacts( $contacts );			
					}
				}
			}
		}		
	}

	/**
	 * Admin notice for running hubspot setup
	 *
	 * @since    1.0.0
	 */
	public function hubwoo_dashboard_setup_notice() {
		$oauth_message = get_option('hubwoo_oauth_success', false);
		if(!isset($oauth_message) || !$oauth_message) {		

			$url = 'https://app.hubspot.com/oauth/authorize';
			$hapikey = HUBWOO_CLIENT_ID;
			$hubspot_url = add_query_arg( array(
			    'client_id'		=> $hapikey,
			    'scope' 		=> 'oauth%20contacts',
			    'redirect_uri' 	=> admin_url().'admin.php'
			), $url );
			?>
			    <div class="update-nag notice-success is-dismissible">
			        <p>
			        	<?php echo sprintf( __('You have not authorize HubSpot WooCommerce Integration Plugin with HubSpot. Click <a href="%s">Here</a> to run the authorization.', 'hubwoo'), $hubspot_url);
			        	?>
			        </p>
			    </div>
		    <?php
		}
		if( Hubwoo::is_valid_client_ids_stored()) {
			
			$hubwoo_setup = get_option('hubwoo_setup_completed', false);
			if(!isset($hubwoo_setup) || !$hubwoo_setup) {		
				?>
				    <div class="update-nag notice-success is-dismissible">
				        <p>
				        	<?php echo sprintf( __('You have successfully completed app authentication, but you have to run the setup before syncing data to HubSpot. Click <a href="%s">Here</a> to go to the run setup page.', 'hubwoo'),  admin_url()."admin.php?page=hubwoo");
				        	?>
				        </p>
				    </div>
			    <?php
			}
			$screen = get_current_screen();
			
       		if(isset($screen->id) && $screen->id == 'woocommerce_page_hubwoo')
       		{
				$suggest = get_option( 'hubwoo_send_suggestions', false);
				if( $suggest ) {
					$success = get_option( 'hubwoo_suggestions_sent', false);
					if( !$success ) {
						$later = get_option( 'hubwoo_suggestions_later', false);
						if ( $later ) {
							?>
							    <div class="update-nag notice-success is-dismissible">
							        <p>
							        	<?php echo __('Support the plugin development by sending us tracking data. It will help us to track the overall performance. Click','hubwoo' );?>
							        	<a href="javascript:void(0)" class="hubwoo_tracking"><?php _e('Here','hubwoo' )?></a>
							        </p>
							    </div>
						    <?php
						}
					}
				}
			}
		}
	}
	/**
	 * Generating access token
	 *
	 * @since    1.0.0
	 */
	public function hubwoo_redirect_from_hubspot() {
		if( isset($_GET['code'])) {
			$hapikey = HUBWOO_CLIENT_ID;
			$hseckey = HUBWOO_SECRET_ID;
			if( $hapikey && $hseckey){
				
				if( !Hubwoo::is_valid_client_ids_stored() ) {
					$response = HubWooConnectionMananager::get_instance()->hubwoo_fetch_access_token_from_code( $hapikey, $hseckey);
				}
				$oauth_message = get_option('hubwoo_oauth_success', false);
				if(!isset($oauth_message) || !$oauth_message) {
					$response = HubWooConnectionMananager::get_instance()->hubwoo_fetch_access_token_from_code( $hapikey, $hseckey);
				}
				wp_redirect(admin_url().'admin.php?page=hubwoo');
			}
		}
	}
	/**
	 * Adding more groups and properties for add-ons
	 *
	 * @since    1.1.0
	 */
	public function hubwoo_update_new_addons_groups_properties() {

		if ( Hubwoo::is_setup_completed() ){
			
			$new_grp = get_option( 'hubwoo_pro_newgroups_saved', false );
			$hubwoo_lock = get_option( 'hubwoo_lock', false );

			if( $new_grp && !$hubwoo_lock )
			{
				if( Hubwoo::is_valid_client_ids_stored() )
				{
					$flag = true;

					if( Hubwoo::is_access_token_expired() ) 
					{
						$hapikey = HUBWOO_CLIENT_ID;
						$hseckey = HUBWOO_SECRET_ID;
						$status  =  HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey);

						if( !$status ) 
						{
							$flag = false;
						}
					}

					if( $flag ) 
					{
						update_option( "hubwoo_lock", true );

						$groups = array();
						$properties = array();

						$groups = apply_filters( "hubwoo_new_contact_groups", $groups );

						foreach ( $groups as $key => $value ) 
						{
							HubWooConnectionMananager::get_instance()->create_group( $value );
							$properties = apply_filters( "hubwoo_new_active_group_properties", $properties, $value['name'] ); 

							foreach ( $properties as $key1 => $value1 ) 
							{
								$value1[ 'groupName' ] = $value['name'];
								HubWooConnectionMananager::get_instance()->create_property(  $value1 );
							}
						}
						
						update_option( 'hubwoo_pro_newgroups_saved', false );
						update_option( 'hubwoo_lock', false );
					}
				}
			}
		}
	}


	/**
	 * Adding more groups and properties for new versions
	 *
	 * @since    1.1.0
	 */
	public function hubwoo_update_new_version_groups_properties() {

		if ( Hubwoo::is_setup_completed() ){
			
			$new_version = HUBWOO_VERSION;
			$new_grp = get_option( "hubwoo_newversion_groups_saved", false );
			
			if( !$new_grp ){
				if( Hubwoo::is_valid_client_ids_stored() ) {
					$flag = true;
					if( Hubwoo::is_access_token_expired() ) {
				
						$hapikey = HUBWOO_CLIENT_ID;
						$hseckey = HUBWOO_SECRET_ID;
						$status =  HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey);

						if( !$status ) {
							$flag = false;
						}
					}
					if( $flag ) {
						$groups = HubWooContactProperties::get_instance()->_get( 'groups' );
						foreach ($groups as $key => $value) {
							HubWooConnectionMananager::get_instance()->create_group( $value );
							$properties = HubWooContactProperties::get_instance()->_get( 'properties', $value['name'] );
							foreach ( $properties as $key1 => $value1 ) {
								$value1[ 'groupName' ] = $value['name'];
								HubWooConnectionMananager::get_instance()->create_property(  $value1 );
							}
						}
						update_option( 'hubwoo_newversion_groups_saved', true );
					}
				}
			}
		}
	}

	/**
	 * Updates the updated skus on hubwoo
	 *
	 * @since    1.0.0
	 */
	
	public function hubwoo_update_changed_skus( $post_ID, $post, $post_updated ){

		$post_type = $post->post_type; 
		$post_status = $post->post_status;
		
		if( is_ajax() )
		{
			return;
		}

		$updateProperty = array();

		if( $post_status == "publish")
		{
			if( $post_type == "product" )
			{
				if( Hubwoo::is_valid_client_ids_stored() ) 
				{
					$flag = true;

					if( Hubwoo::is_access_token_expired() ) {
				
						$hapikey = HUBWOO_CLIENT_ID;
						$hseckey = HUBWOO_SECRET_ID;
						$status =  HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey);

						if( !$status ) {
							$flag = false;
						}
					}

					if( $flag )
					{
						$product_id = $post_ID;

						$currentSku = isset( $_POST['_sku'] )?$_POST['_sku']:"";

						$all_skus = $this->get_all_updated_skus( $product_id, $currentSku );

						if( get_option( "hubwoo_abncart_added", false ) )
						{
							$propertyDetails = array(
								"name" 		=> "abandoned_cart_products_skus",
								"label" 	=> __( 'Abandoned Cart Products SKUs', 'hubwoo' ),
								"type" 		=> "enumeration",
								"fieldType" => "checkbox",
								"formfield" => false,
								"options" 	=> $all_skus,
							);

							$propertyDetails[ 'groupName' ] = 'abandoned_cart';

							$updateProperty[] = $propertyDetails;
						}
						
						if( count( $updateProperty ) )
						{
							foreach( $updateProperty as $single_property )
							{
								HubWooConnectionMananager::get_instance()->update_property( $single_property );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Updates the updated skus on hubwoo
	 *
	 * @since    1.0.0
	 * @param      string    $product_id       Product ID.
	 * @param      string    $currentSku       New SKU.
	 */
	public function get_all_updated_skus( $product_id, $currentSku )
	{
		$all_skus = array();

		$args = array(
		    'post_type' => array( 'product' ),
		    'post_status' => 'any', 
		    'posts_per_page' => -1
		);

		$wcProductsArray = get_posts( $args );

		if ( count( $wcProductsArray ) ) 
		{
		    foreach ( $wcProductsArray as $productPost )
		    {
		        $productSKU = get_post_meta( $productPost->ID, '_sku', true );

		        if( $productPost->ID == $product_id )
		        {
		        	$productSKU = $currentSku;
		        }
		        //if not sku then let's use the product id instead.
		        if( empty( $productSKU ) ){

		        	$productSKU = $productPost->ID;
		        }

		        $all_skus[] = array( 'label' => $productSKU, 'value' => $productSKU );
		    }
		}

		$all_skus = apply_filters( 'hubwoo_all_skus_options', $all_skus );

		return array_values( array_unique( $all_skus, SORT_REGULAR ) );
	}

	/**
	 * updates the new categories for HubSpot Portal
	 * 
	 * @since 1.0.0
	 */

	public function hubwoo_update_category(){

		$category_options = array();

		$updateProperty = array();
		//get all product categories.
		$product_categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );

		if( is_array( $product_categories ) && count( $product_categories ) )
		{
			foreach( $product_categories as $category )
			{
				$category_slug = isset( $category->slug ) ? $category->slug : $category->term_id;

				$category_label = isset( $category->name ) ? $category->name : '';

				if( !empty( $category_label ) )
				{
					$category_options[] = array( 'label' => $category_label, 'value' => $category_slug ); 
				}
			}
		}

		$category_options = apply_filters( 'hubwoo_category_options', $category_options );

		if( count( $category_options ) )
		{
			if( get_option( "hubwoo_abncart_added", false ) )
			{
				$propertyDetails = array(
					"name" 		=> "abandoned_cart_products_categories",
					"label" 	=> __( 'Abandoned Cart Products Categories', 'hubwoo' ),
					"type" 		=> "enumeration",
					"fieldType" => "checkbox",
					"formfield" => false,
					"options" 	=> $category_options,
				);

				$propertyDetails[ 'groupName' ] = 'abandoned_cart';

				$updateProperty[] = $propertyDetails;
			}
		}

		if( Hubwoo::is_valid_client_ids_stored() )
		{
			$flag = true;
			
			if( Hubwoo::is_access_token_expired() ) 
			{
				$hapikey = HUBWOO_CLIENT_ID;
				$hseckey = HUBWOO_SECRET_ID;
				$status =  HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey);

				if( !$status ) 
				{
					$flag = false;
				}
			}

			if( $flag )
			{
				if( count( $updateProperty ) )
				{
					foreach( $updateProperty as $single_property )
					{
						HubWooConnectionMananager::get_instance()->update_property( $single_property );
					}
				}
			}
		}
	}

	public function hubwoo_updating_categories( $term_id, $tt_id = '', $taxonomy = '' )
	{
		$this->hubwoo_update_category();
	}

	/**
	 * Updates the product names on hubwoo when new product is added
	 *
	 * @since    1.0.0
	 */

	public function hubwoo_update_product_names( $post_id, $post_after, $post_before )
	{
		$post = get_post( $post_id );
		$post_type = $post->post_type;
		$post_before_status = $post_before->post_status;
		$post_after_status = $post_after->post_status;

		$updateProperty = array();

		if( $post_type == "product" && ( $post_before_status == "draft" || $post_before_status == "auto-draft" ) && $post_after_status == "publish" )
		{
			if( Hubwoo::is_valid_client_ids_stored() ) 
			{
				$flag = true;
				
				if( Hubwoo::is_access_token_expired() ) {
			
					$hapikey = HUBWOO_CLIENT_ID;
					$hseckey = HUBWOO_SECRET_ID;
					$status =  HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey);

					if( !$status ) {
						$flag = false;
					}
				}
				if( $flag )
				{
					$all_product_names = $this->hubwoo_get_updated_product_names();

					if( get_option( "hubwoo_abncart_added", false ) )
					{
						$propertyDetails = array(
							"name" 		=> "abandoned_cart_products",
							"label" 	=> __( 'Abandoned Cart Products', 'hubwoo' ),
							"type" 		=> "enumeration",
							"fieldType" => "checkbox",
							"formfield" => false,
							"options" 	=> $all_product_names,
						);

						$propertyDetails[ 'groupName' ] = 'abandoned_cart';

						$updateProperty[] = $propertyDetails;
					}

					if( count( $updateProperty ) )
					{
						foreach( $updateProperty as $single_property )
						{
							HubWooConnectionMananager::get_instance()->update_property( $single_property );
						}
					}
				}
			}
		}
	}

	/**
	 * get all product names with updates values
	 * 
	 * @since 1.0.0
	 */

	public function hubwoo_get_updated_product_names(){

		$all_names = array();

		$args = array(
		    'post_type' => array( 'product' ),
		    'post_status' => 'any', 
		    'posts_per_page' => -1
		);

		$wcProductsArray = get_posts( $args );

		if ( count( $wcProductsArray ) ) {

		    foreach ( $wcProductsArray as $productPost ) {

		        $productName = $productPost->post_name."-".$productPost->ID;
		       
		        if( !empty( $productName ) )
		        {
		 			$all_names[] = array( 'label' => $productName, 'value' => $productName );
		        }
		    }
		}

		$all_names = apply_filters( 'hubwoo_all_product_names_options', $all_names );

		return $all_names;
	}

	public function hubwoo_dashboard_alert_notice()
	{
		if( Hubwoo::is_valid_client_ids_stored() )
		{
			$hubwoo_setup = get_option( 'hubwoo_setup_completed', false );
			$hubwoo_alert = get_option( 'hubwoo_alert_param_set', false );

			if( $hubwoo_alert && $hubwoo_setup )
			{
				?>
				<div class="update-nag notice-success is-dismissible">
			        <p>
			        	<?php 
			        	_e("Real-time user activity sync over HubSpot is not working properly. Please check our ","hubwoo");?><a target="_blank" href="https://makewebbetter.com/product/hubspot-woocommerce-integration-pro/#faqs"><?php _e("FAQs","hubwoo") ?></a><?php _e(" or get our PRO version now ","hubwoo");?><a target="_blank" href="https://makewebbetter.com/product/hubspot-woocommerce-integration-pro/"><?php _e("Click Here","hubwoo");?></a>
			        </p>
				</div>
				<?php
			}
		}
	}
}