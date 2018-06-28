<?php  
/**
 * All customer details.
 *
 * @link       http://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/includes
 */

/**
 * Stores all customer data that needs to be updated on hubspot.
 *
 * Provide a list of properties and associated data for customer
 * so that at the time of updating a customer on hubspot we can 
 * simply create an instance of this class and get everything 
 * managed.
 *
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */

class HubWooCustomer{

	/**
	 * contact in the form of acceptable by hubspot.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var json
	 */
	public $contact;

	/**
	 * WooCommerce Customer ID
	 *
	 * @since 1.0.0
	 * @access public
	 * @var json
	 */
	public $_contact_id;

	/**
	 * Contact Properties.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var Array
	 */
	private $_properties = array();

	/**
	 * instance of HubWooPropertyCallbacks class
	 *
	 * @since 1.0.0
	 * @access private
	 * @var HubWooPropertyCallbacks
	 */
	private $_callback_instance = null;

	/**
	 * Load the modified customer properties.
	 *
	 * Set all the modified customer properties so that they will be
	 * ready in the form of directly acceptable by hubspot api.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $contact_id ){

		//load the contact id in the class property.
		$this->_contact_id = $contact_id;

		//store the instance of property callback.
		$this->_callback_instance = new HubWooPropertyCallbacks( $this->_contact_id );

		//prepare the modified fields data and store it in the contact
		$this->prepare_modified_fields();
	}

	/**
	 * get user email.
	 * 
	 * @since 1.0.0
	 */
	public function get_email(){

		return $this->_callback_instance->_get_mail();
	}

	/**
	 * contacts all properties.
	 * 
	 * @return array    and key value pair array of properties.
	 * @since 1.0.0
	 */
	public function get_contact_properties(){

		$this->_properties = apply_filters( 'hubwoo_contact_modified_fields', $this->_properties, $this->_contact_id );

		return $this->_properties; 
	}

	/**
	 * Format modified fields of customer.
	 *
	 * Check for all the modified fields till the last update
	 * and prepare them in the hubspot api acceptable form.
	 * 
	 * @since 1.0.0
	 * @access private
	 */
	private function prepare_modified_fields(){

		//check for the new customer.
		$hubwoo_vid = $this->get_hubwoo_vid( );
		// verify for not null and valid vid.
		if( !empty( $hubwoo_vid ) && $hubwoo_vid > 0 ){

			// pre-uploaded contact, so let's check for only modified fields.
			// store all the modified fields in the form of array.
			$modified_fields = $this->get_contact_modified_fields( );
		// its a new contact so need to update all fields.
		}else{
			// need to update all fields, so lets get all the properties that we are working with.
			$modified_fields = HubWooContactProperties::get_instance()->_get( 'properties' );
		}
		// if some data are updated after last update with hubspot.
		if( is_array( $modified_fields ) && count( $modified_fields ) ){
			// loop them all, as they are in the form of group and field.
			foreach( $modified_fields as $group_fields ){
				// check if fields are there in the group field.
				if( is_array( $group_fields ) ){
					// let's loop each field.
					foreach( $group_fields as $field ){
						// store the property value.
						$property = $this->_prepare_property( $field );
						//check if the valid array.
						if( is_array( $property ) && isset( $property[ 'value' ] ) ){

							//add it in the property list.
							$this->_properties[] = $property;
						}
					}
				}
			}
		}
	}


	/**
	 * check if the contact is not uploaded to hubspot.
	 *
	 * @return Int/null   hubspot vid if pre-uploaded either null.
	 * @since 1.0.0
	 */
	private function get_hubwoo_vid( ){

		return get_user_meta( $this->_contact_id, 'hubwoo_vid', true );
	}

	/**
	 * get modified fields since last update of the contact.
	 * 
	 * @return     Array     Array of fields modified.
	 */
	public function get_contact_modified_fields( ){

		$modified_fields = get_user_meta( $this->_contact_id, 'hubwoo_modified_fields', true );
		// if no modified fields and not an array.
		if( !is_array( $modified_fields ) ){
			// set it as array, as the filter suppose it as a array.
			$modified_fields = array();
		}
		// let others decide if they have modified fields in there integration.
		//$modified_fields = apply_filters( 'hubwoo_contact_modified_fields', $modified_fields, $this->_contact_id );
		
		return $modified_fields;
	}

	/**
	 * prepare property in the form of key value accepted by hubspot.
	 * 
	 * @param  array    $property     array of the property details to validate the value.
	 * @return array               formatted key value pair.
	 */
	public function _prepare_property( $property ){

		// property name.
		$property_name = isset( $property[ 'name' ] ) ? $property[ 'name' ] : '';
			
		//if property name is not empty.
		if( !empty( $property_name ) ){
			// get property value.
			$property_val = $this->_callback_instance->_get_property_value( $property_name, $this->_contact_id );
			// format the property name and value.
			$property = array( 'property' => $property_name, 'value' => $property_val );
			
			return $property;
		}
	}
}