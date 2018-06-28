<?php 
/**
 * All property callbacks.
 *
 * @link       http://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/includes
 */

/**
 * Manage all property callbacks.
 *
 * Provide a list of functions to manage all the information
 * about contacts properties and there callback functions to
 * get value of that property.
 *
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */

class HubWooPropertyCallbacks{

	/**
	 * contact id.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $_contact_id;

	/**
	 * WP user.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var WP_User
	 */
	protected $_user;

	/**
	 * cache values.
	 * @var array.
	 */
	protected $_cache = array();

	/**
	 * properties and there callbacks.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Associated_array
	 */
	protected $_property_callbacks = array(
		
			'customer_group' 				=> 'get_contact_group',
			'last_order_status' 			=> 'hubwoo_user_meta',

			'last_order_tracking_number' 	=> 'hubwoo_user_meta',
			'last_order_tracking_url' 		=> 'hubwoo_user_meta',
			'last_order_shipment_date' 		=> 'hubwoo_user_meta',
			'last_order_order_number' 		=> 'hubwoo_user_meta',
			'last_order_shipment_carrier' 	=> 'hubwoo_user_meta',

			'total_value_of_orders' 		=> 'hubwoo_user_meta',
			'average_order_value' 			=> 'hubwoo_user_meta',
			'total_number_of_orders' 		=> 'hubwoo_user_meta',
			'first_order_date' 				=> 'hubwoo_user_meta',
			'first_order_value' 			=> 'hubwoo_user_meta',
			'monetary_rating' 				=> 'hubwoo_user_meta',
			'order_frequency_rating' 		=> 'hubwoo_user_meta',
			'order_recency_rating' 			=> 'hubwoo_user_meta',
			'account_creation_date' 		=> 'hubwoo_user_meta',

			'last_order_date' 				=> 'hubwoo_user_meta',
			'last_order_value' 				=> 'hubwoo_user_meta',
		);
	
	/**
	 * constructor
	 * @param int    $contact_id    contact id to get property values of.
	 */
	public function __construct( $contact_id ){

		$this->_contact_id = $contact_id;

		$this->_user = get_user_by( 'id', $this->_contact_id );
	}

	/**
	 * property value.
	 *
	 * @param  string  $property_name    name of the contact property.
	 * @since 1.0.0
	 */
	public function _get_property_value( $property_name ){

		$value = "";
		if( !empty( $property_name ) ){
			// get the callback.
			$callback_function = $this->_get_property_callback( $property_name );
			
			if( !empty( $callback_function ) ){

				// get the value by calling respective callback.
				$value = $this->$callback_function( $property_name );
			}
		}

		return $value;
	}

	/**
	 * filter the property callback to get value of.
	 * 
	 * @param  strig   $property_name   name of the property.
	 * @return string/false             callback function name or false.
	 */
	private function _get_property_callback( $property_name ){
		// check if the property name exists in the array.
		if( array_key_exists( $property_name, $this->_property_callbacks ) ){
			// if exists then get the callback name.
			$callback = $this->_property_callbacks[ $property_name ];

			return $callback;
		}

		return false;
	}

	/**
	 * get contact user role.
	 *
	 * @return string    user role of the current contact.
	 * @since 1.0.0
	 */
	public function get_contact_group( ){
		// get roles from user object.
		$user_roles = isset( $this->_user->roles ) ? $this->_user->roles : '';
		// format if its an array, can have multiple roles if using any plugin.
		return $this->hubwoo_format_array( $user_roles );
	}

	/**
	 * user email
	 * @since 1.0.0
	 */
	public function _get_mail(){
		// get it from user object.
		return $this->_user->data->user_email;
	}

	/**
	 * format an array in hubspot accepted enumeration value.
	 * 
	 * @param  array   $properties  Array of values
	 * @return string       formatted string.
	 * @since 1.0.0
	 */
	protected function hubwoo_format_array( $properties ){

		if( is_array( $properties ) ){

			$properties = array_unique( $properties );

			$properties = implode( ';', $properties );
		}

		return $properties;
	}


	/**
	 * user details with hubwoo_ prefix.
	 * 
	 * @since 1.0.0
	 */
	public function hubwoo_user_meta( $key ){
		
		// check if the property value is already in cache.
		if( array_key_exists( $key, $this->_cache ) ){
			// return the cache value.
			return $this->_cache[ $key ];
		}
		$this->_cache[ 'total_value_of_orders' ] = 0;
		// if the first call of users order related points, let's calculate them all.
		$customer_orders = get_posts( array(
	        'numberposts' => -1,
	        'meta_key'    => '_customer_user',
	        'meta_value'  => $this->_contact_id,
	        'post_type'   => wc_get_order_types(),
	        'post_status' => array_keys( wc_get_order_statuses() ),
	        'order'	  => 'DESC', // last order first
	    ) );

	    $customer = new WP_User( $this->_contact_id );

	    $order_frequency = 0; 

		$account_creation = isset( $customer->data->user_registered )?$customer->data->user_registered:"";

		$account_creation = strtotime( $account_creation );
		$this->_cache[ 'account_creation_date' ] = $this->hubwoo_set_utc_midnight( $account_creation );
		
		// if customer have orders
		if( is_array( $customer_orders ) && count( $customer_orders ) ){
			// total number of customer orders.
			$this->_cache[ 'total_number_of_orders' ] = count( $customer_orders );

			$order_frequency = $this->_cache[ 'total_number_of_orders' ];

			$counter = 0;

			foreach( $customer_orders as $order_details ){

				// get the order id.
				$order_id = isset( $order_details->ID ) ? intval( $order_details->ID ) : 0;

				// if order id not found let's check for another order.
				if( !$order_id )
					continue;

				// get order.
				$order = new WC_Order( $order_id );
				
				// check for WP_Error object
				if( empty( $order ) || is_wp_error( $order ) )
					continue;

				// get all order items first.
				$order_items = $order->get_items();

				$order_total = $order->get_total();

				$this->_cache[ 'total_value_of_orders' ] += floatval( $order_total );
				// check for last order and finish all last order calculations.
				if( !$counter ){
					// last order calculations over here.
					$this->_cache[ 'last_order_date' ] = $this->hubwoo_set_utc_midnight( get_post_time( 'U', true, $order_id ) );

					$last_order_date = get_post_time( 'U', true, $order_id );

					$this->_cache [ 'last_order_value' ] = $order_total;
					// last order calculations over here.
					$this->_cache [ 'last_order_shipment_carrier' ] = apply_filters( 'hubwoo_order_shipment_carrier', '', $order_id );

					$this->_cache[ 'last_order_order_number' ] = $order_id;

					$this->_cache[ 'last_order_shipment_date' ] = apply_filters( 'hubwoo_order_shipment_date', '', $order_id );

					$this->_cache[ 'last_order_tracking_number' ] = apply_filters( 'hubwoo_order_tracking_number', '', $order_id );

					$this->_cache[ 'last_order_tracking_url' ] = apply_filters( 'hubwoo_order_tracking_url', '', $order_id );

					$this->_cache[ 'last_order_status' ] = "wc-".$order->get_status();
				}
				// check for first order.
				if( $counter == count( $customer_orders ) - 1 ) {
					// first order based calculation here..
					$this->_cache[ 'first_order_date' ] = $this->hubwoo_set_utc_midnight( get_post_time( 'U', true, $order_id ) );
					$this->_cache[ 'first_order_value' ] = $order_total;
				}
				$counter++;
			}

			$hubwoo_rfm_at_5    = array( 0 => 30, 1 => 20, 2 => 1000 );

	        $hubwoo_from_rfm_4  = array( 0 => 31, 1 => 10, 2 => 750 );

	        $hubwoo_to_rfm_4    = array( 0 => 90, 1 => 20, 2 => 1000 );

	        $hubwoo_from_rfm_3  = array( 0 => 91, 1 => 5, 2 => 500 );

	        $hubwoo_to_rfm_3    = array( 0 => 180, 1 => 10, 2 => 750 );

	        $hubwoo_from_rfm_2  = array( 0 => 181, 1 => 2, 2 => 250 );

	        $hubwoo_to_rfm_2    = array( 0 => 365, 1 => 5, 2 => 500 );

	        $hubwoo_rfm_at_1    = array( 0 => 365, 1 => 2, 2 => 250 );

	        $order_monetary = $this->_cache[ 'total_value_of_orders' ];

			$current_date 		= gmdate("Y-m-d H:i:s", time() );
			$current_date 		= new DateTime($current_date);
			$last_order_date 	= gmdate("Y-m-d H:i:s", $last_order_date);
			$last_order_date 	= new DateTime($last_order_date);
			$order_recency 		= date_diff( $current_date, $last_order_date, true );
			
			$order_recency = $order_recency->days;

			if( $order_recency <= $hubwoo_rfm_at_5[0] )
			{
				$this->_cache[ 'order_recency_rating' ] = 5;
			}
			elseif( $order_recency >= $hubwoo_from_rfm_4[0] && $order_recency <= $hubwoo_to_rfm_4[0] )
			{
				$this->_cache[ 'order_recency_rating' ] = 4;
			}
			elseif($order_recency >= $hubwoo_from_rfm_3[0] && $order_recency <= $hubwoo_to_rfm_3[0] )
			{
				$this->_cache[ 'order_recency_rating' ] = 3;
			}
			elseif( $order_recency >= $hubwoo_from_rfm_2[0] && $order_recency <= $hubwoo_to_rfm_2[0] )
			{
				$this->_cache[ 'order_recency_rating' ] = 2;
			}
			elseif( $order_recency > $hubwoo_rfm_at_1[0] )
			{
				$this->_cache[ 'order_recency_rating' ] = 1;
			}

			if( $order_frequency >= $hubwoo_rfm_at_5[1] )
			{
				$this->_cache[ 'order_frequency_rating' ] = 5;
			}
			elseif( $order_frequency >= $hubwoo_from_rfm_4[1] && $order_frequency < $hubwoo_to_rfm_4[1] )
			{
				$this->_cache[ 'order_frequency_rating' ] = 4;
			}
			elseif( $order_frequency >= $hubwoo_from_rfm_3[1] && $order_frequency < $hubwoo_to_rfm_3[1] )
			{
				$this->_cache[ 'order_frequency_rating' ] = 3;
			}
			elseif( $order_frequency >= $hubwoo_from_rfm_2[1] && $order_frequency < $hubwoo_to_rfm_2[1] )
			{
				$this->_cache[ 'order_frequency_rating' ] = 2;
			}
			elseif( $order_frequency < $hubwoo_rfm_at_1[1] )
			{
				$this->_cache[ 'order_frequency_rating' ] = 1;
			}

			if( $order_monetary >= $hubwoo_rfm_at_5[2] )
			{
				$this->_cache[ 'monetary_rating' ] = 5;
			}
			elseif( $order_monetary >= $hubwoo_from_rfm_4[2] && $order_monetary < $hubwoo_to_rfm_4[2] )
			{
				$this->_cache[ 'monetary_rating' ] = 4;
			}
			elseif( $order_monetary >= $hubwoo_from_rfm_3[2] && $order_monetary < $hubwoo_to_rfm_3[2] )
			{
				$this->_cache[ 'monetary_rating' ] = 3;
			}
			elseif( $order_monetary >= $hubwoo_from_rfm_2[2] && $order_monetary < $hubwoo_to_rfm_2[2] )
			{
				$this->_cache[ 'monetary_rating' ] = 2;
			}
			elseif( $order_monetary < $hubwoo_rfm_at_1[2] )
			{
				$this->_cache[ 'monetary_rating' ] = 1;
			}
			// rest calculations here.
			$this->_cache[ 'average_order_value' ] = floatval( $this->_cache[ 'total_value_of_orders' ] / $this->_cache[ 'total_number_of_orders' ] );
			
			return $this->_cache[ $key ];

		// no orders
		} else {
			// set all the order related fields to null or empty.
			$this->_cache[ 'total_number_of_orders' ] = 0;
		}
	}
	/**
	 * convert unix timestamp to hubwoo formatted midnight time.
	 * 
	 * @param  Unix timestamp    $unix_timestamp
	 * @return Unix midnight timestamp
	 * @since 1.0.0
	 */
	protected function hubwoo_set_utc_midnight( $unix_timestamp ){

		$string = gmdate("Y-m-d H:i:s", $unix_timestamp );
		$date = new DateTime( $string );
		$date->modify( 'midnight' );
		return $date->getTimestamp() * 1000; // in miliseconds
	}

}