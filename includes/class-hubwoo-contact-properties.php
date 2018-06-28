<?php 
/**
 * Manage all contact properties.
 *
 * @link       http://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/includes
 */

/**
 * Manage all contact properties.
 *
 * Provide a list of functions to manage all the information
 * about contacts properties and lists along with option to 
 * change/update the mapping field on hubspot.
 *
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */

class HubWooContactProperties{

	/**
	 * Contact Property Groups.
	 *
	 * @access private
	 * @since 1.0.0
	 */
	private $groups;

	/**
	 * Contact Properties.
	 *
	 * @access private
	 * @since 1.0.0
	 */
	private $properties;

	/**
	 * HubWooContactProperties Instance.
	 *
	 * @access protected
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main HubWooContactProperties Instance.
	 *
	 * Ensures only one instance of HubWooContactProperties is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return HubWooContactProperties - Main instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Define the contact prooperties related functionality.
	 *
	 * Set the contact groups and properties that we are going to use
	 * for creating/updating the contact information for our tacking purpose
	 * and providing other developers to add there field and group for tracking
	 * too by simply using our hooks.
	 *
	 * @access public
	 * @since    1.0.0
	 */
	public function __construct(){

		$this->groups = $this->_set('groups');
		$this->properties = $this->_set('properties');
	}

	/**
	 * get groups/properties.
	 * 
	 * @param  string 		  groups/properties
	 * @return array          Array of groups/properties information.
	 */
	public function _get( $option, $groupName="" ){

		if( $option == "groups" ){

			return $this->groups;

		}else if( $option == "properties" ){
			// properties of specific group.
			if( !empty( $groupName ) && isset( $this->properties[ $groupName ] )  ){

				return $this->properties[ $groupName ];
			}

			return $this->properties;
		}
	}

	/**
	 * get an array of required option.
	 * 
	 * @param  String   	$option  		the identifier.
	 * @return Array  		An array of values.	
	 * @since 1.0.0		     
	 */
	private function _set( $option ){

		$values = array();

		//if we are looking for groups, let us add our predefined groups.
		if( $option == 'groups' ){
			// name and displayName( lowercase letters, numbers, and underscores )  is required
			$values[] = array( 'name' => 'customer_group', 'displayName' => __( 'Customer', 'hubwoo' ) );
			// order details.
			$values[] = array( 'name' => 'order', 'displayName' => __( 'Order', 'hubwoo' ) );
			// RFM details.
			$values[] = array( 'name' => 'rfm_fields', 'displayName' => __( 'RFM Information', 'hubwoo' ) );
		// if we are looking for properties.
		}else if( $option == 'properties' ){
			// let's check for all active tracking groups and get there associated properties.
			$values = $this->get_all_active_groups_properties();
		}

		// add your values to the either groups or properties.
		return $values;
	}

	/**
	 * check for the active groups and get there properties.
	 * 
	 * @return Array Properties array with there associated group.
	 * @since 1.0.0
	 */
	private function get_all_active_groups_properties(){

		$active_groups_properties = array();
		//get all the active groups.
		$active_groups = $this->get_active_groups();

		//check if we get active groups in the form of array, and has groups.
		if( is_array( $active_groups ) && count( $active_groups ) ){

			foreach( $active_groups as $active_group ){

				if( !empty( $active_group ) && !is_array( $active_group ) ){

					$active_groups_properties[ $active_group ] = $this->_get_group_properties( $active_group );

				}
			}
		}
		// add your active group properties if you want.
		return apply_filters( 'hubwoo_active_groups_properties', $active_groups_properties );
	}


	/**
	 * Filter for active groups only.
	 * 
	 * @return Array active group names.
	 * @since 1.0.0
	 */
	private function get_active_groups(){

		$active_groups = array();
		$all_groups = $this->_get( 'groups' );

		if( is_array( $all_groups ) && count( $all_groups ) ){

			foreach( $all_groups as $group_details ){

				$group_name = isset( $group_details[ 'name' ] ) ? $group_details[ 'name' ] : '';

				if( !empty( $group_name ) ){

					$is_active = get_option( 'hubwoo_active_group'.$group_name, true );

					if( $is_active ){

						$active_groups[] = $group_name;
					}
				}
			}
		}
		// let's developer manage there groups seperately if they want.
		return apply_filters( 'hubwoo_active_groups', $active_groups );
	}

	/**
	 * get all the groups properties.
	 * 
	 * @param   string     $group_name     name of the existed valid hubspot contact properties group.
	 * @return  Array      Properties array.
	 * @since 1.0.0
	 */
	private function _get_group_properties( $group_name ){

		$group_properties = array();
		//if the name is not empty.
		if( !empty( $group_name ) ){

			if( $group_name == "customer_group" ){

				$group_properties[] = array(
						"name" => "customer_group",
						"label" => __( 'Customer Group/ User role', 'hubwoo' ),
						"type" => "enumeration",  
						"fieldType" => "checkbox",  
						"formField" => false,   
						"options" => $this->get_user_roles()
					);
			}else if( $group_name == "order" ){

				$group_properties[] = array(
						"name" => "last_order_status",
						"label" => __( 'Last Order Status', 'hubwoo' ),
						"type" => "enumeration",  
						"fieldType" => "select",  
						"formField" => false,   
						"options" => $this->get_order_statuses()
					);
				$group_properties[] = array(
						"name" => "last_order_tracking_number",
						"label" => __( 'Last Order Tracking Number', 'hubwoo' ),
						"type" => "string",  
						"fieldType" => "text",  
						"formField" => false,   
					);

				$group_properties[] = array(
						"name" => "last_order_tracking_url",
						"label" => __( 'Last Order Tracking URL', 'hubwoo' ),
						"type" => "string",  
						"fieldType" => "text",  
						"formField" => false,   
					);

				$group_properties[] = array(
						"name" => "last_order_shipment_date",
						"label" => __( 'Last Order Shipment Date', 'hubwoo' ),
						"type" => "date",  
						"fieldType" => "date",  
						"formField" => false,   
					);

				$group_properties[] = array(
						"name" => "last_order_order_number",
						"label" => __( 'Last Order Number', 'hubwoo' ),
						"type" => "string",  
						"fieldType" => "text",  
						"formField" => false,   
					);

				$group_properties[] = array(
						"name" => "last_order_shipment_carrier",
						"label" => __( 'Last Order Shipment Carrier', 'hubwoo' ),
						"type" => "string",  
						"fieldType" => "text",  
						"formField" => false,   
					);
			// rfm_fields
			} else if( $group_name == "rfm_fields" ){

				$group_properties[] = array(
						"name" => "total_value_of_orders",
						"label" => __( 'Total Value of Orders', 'hubwoo' ),
						"type" => "number",  
						"fieldType" => "number",  
						"formField" => false,   
					);

				$group_properties[] = array(
						"name" 	=> "average_order_value",
						"label" => __( 'Average Order Value', 'hubwoo' ),
						"type"	 => "number",  
						"fieldType" => "number",  
						"formField" => false,   
					);

				$group_properties[] = array(
						"name" => "total_number_of_orders",
						"label" => __( 'Total Number of Orders', 'hubwoo' ),
						"type" => "number",  
						"fieldType" => "number",  
						"formField" => false,   
					);

				$group_properties[] = array(
						"name" => "first_order_date",
						"label" => __( 'First Order Date', 'hubwoo' ),
						"type" => "date",  
						"fieldType" => "date",  
						"formField" => false,   
					);

				$group_properties[] = array(
						"name" => "first_order_value",
						"label" => __( 'First Order Value', 'hubwoo' ),
						"type" => "number",  
						"fieldType" => "number",  
						"formField" => false,   
					);

				$group_properties[] = array(
						"name" => "last_order_date",
						"label" => __( 'Last Order Date', 'hubwoo' ),
						"type" => "date",  
						"fieldType" => "date",  
						"formField" => false,   
					);

				$group_properties[] = array(
						"name" => "last_order_value",
						"label" => __( 'Last Order Value', 'hubwoo' ),
						"type" => "number",  
						"fieldType" => "number",  
						"formField" => false,   
					);

				$group_properties[] = array(
						"name" => "average_days_between_orders",
						"label" => __( 'Average Days Between Orders', 'hubwoo' ),
						"type" => "number",  
						"fieldType" => "number",  
						"formField" => false,   
					);

				$group_properties[] = array(
						"name" => "account_creation_date",
						"label" => __( 'Account Creation Date', 'hubwoo' ),
						"type" => "date",  
						"fieldType" => "date",  
						"formField" => false,   
					);
				$group_properties[] = array(
						"name" 		=> "monetary_rating",
						"label" 	=> __( 'Monetary Rating', 'hubwoo' ),
						"type" 		=> "enumeration",  
						"fieldType" => "select",  
						"formField" => false,
						"options" 	=> $this->get_rfm_rating(),
					);

				$group_properties[] = array(
						"name" 		=> "order_frequency_rating",
						"label" 	=> __( 'Order Frequency Rating', 'hubwoo' ),
						"type" 		=> "enumeration",  
						"fieldType" => "select",  
						"formField" => false,
						"options" 	=> $this->get_rfm_rating(),   
					);

				$group_properties[] = array(
						"name" 		=> "order_recency_rating",
						"label" 	=> __( 'Order Recency Rating', 'hubwoo' ),
						"type" 		=> "enumeration",  
						"fieldType" => "select",  
						"formField" => false,
						"options" 	=> $this->get_rfm_rating(),   
					);
			}
		}

		return apply_filters( 'hubwoo_group_properties', $group_properties, $group_name );
	}

	/**
	 * formatted options for user role enumaration.
	 * 
	 * @return JSON    formatted json encoded array of user role options.
	 * @since 1.0.0
	 */
	private function get_user_roles(){

		$exiting_user_roles = array();
		//get all editable roles
		if ( ! function_exists( 'get_editable_roles' ) ) {
		    require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		$editable_roles = get_editable_roles();

		if( is_array( $editable_roles ) && count( $editable_roles ) ){

			foreach ( $editable_roles as $role => $role_info){

				$role_label = isset( $role_info[ 'name' ] ) ? $role_info[ 'name' ] : $role;

				$exiting_user_roles[] = array( 'label' => $role_label, 'value' => $role ); 
			}
		}
		return $exiting_user_roles;
		
	}

	/**
	 * get all available woocommerce order formts.
	 * 
	 * @return JSON Order statuses in the form of enumaration options.
	 * @since 1.0.0
	 */
	private function get_order_statuses(){

		$all_wc_statuses = array();

		//get all statuses
		$all_status = wc_get_order_statuses();
		
		//if status available
		if( is_array( $all_status ) && count( $all_status ) ){

			foreach( $all_status as $status_id => $status_label ){

				$all_wc_statuses[] = array( 'label' => $status_label, 'value' => $status_id );
			}
		}
		return $all_wc_statuses;
	}

	/**
	 * get ratings for RFM analysis
	 * 
	 * @return ratings for RFM analysis
	 * @since 1.0.0
	 */
	public function get_rfm_rating(){

		$rating = array();

		$rating[] = array( 'label' =>__('5', 'hubwoo' ), 'value'=> 5 );
		$rating[] = array( 'label' =>__('4', 'hubwoo' ), 'value'=> 4 );
		$rating[] = array( 'label' =>__('3', 'hubwoo' ), 'value'=> 3 );
		$rating[] = array( 'label' =>__('2', 'hubwoo' ), 'value'=> 2 );
		$rating[] = array( 'label' =>__('1', 'hubwoo' ), 'value'=> 1 );

		$rating = apply_filters( 'hubwoo_rfm_ratings', $rating );

		return $rating;
	}
}