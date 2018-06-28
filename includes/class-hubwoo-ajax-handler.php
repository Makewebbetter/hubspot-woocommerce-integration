<?php

/**
 * Handles all admin ajax requests.
 *
 * @link       http://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/includes
 */

/**
 * Handles all admin ajax requests.
 *
 * All the functions required for handling admin ajax requests
 * required by the plugin.
 *
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class HubWooAjaxHandler {

	/**
	 * construct.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		//check oauth access token
		add_action( 'wp_ajax_hubwoo_check_oauth_access_token', array( &$this, 'hubwoo_check_oauth_access_token' ) );
		// get all groups request handler.
		add_action( 'wp_ajax_hubwoo_get_groups', array( &$this, 'hubwoo_get_groups' ) );
		//create a group request handler.
		add_action( 'wp_ajax_hubwoo_create_group_and_property', array( &$this, 'hubwoo_create_group' ) );
		//get group properties.
		add_action( 'wp_ajax_hubwoo_get_group_properties', array( &$this, 'hubwoo_get_group_properties' ) );
		// create property.
		add_action( 'wp_ajax_hubwoo_create_group_property', array( &$this, 'hubwoo_create_group_property' ) );
		//mark setup as completed.
		add_action( 'wp_ajax_hubwoo_setup_completed', array( &$this, 'hubwoo_setup_completed' ) );
		//send mail later.
		add_action( 'wp_ajax_hubwoo_suggest_later', array( &$this, 'hubwoo_suggest_later' ) );
		//send mail later.
		add_action( 'wp_ajax_hubwoo_suggest_accept', array( &$this, 'hubwoo_suggest_accept' ) );
	}
	public function hubwoo_suggest_later() {
		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
		update_option( 'hubwoo_suggestions_later', true);
		die;
	}
	public function hubwoo_suggest_accept() {

		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
		$status =  HubWooConnectionMananager::get_instance()->send_clients_details();
		
		if( $status ) {
			update_option( 'hubwoo_suggestions_sent', true );
			echo "success";
		}
		else {
			update_option( 'hubwoo_suggestions_later', true);
			echo "failure";
		}
		wp_die();
	}
	public function hubwoo_check_oauth_access_token() {

		$response = array('status'=>true, 'message'=>__('Success', 'hubwoo') );
		// check the nonce sercurity.
		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
		//checking if access token is expired
		
		if( Hubwoo::is_access_token_expired() ) {
			
			$hapikey = HUBWOO_CLIENT_ID;
			$hseckey = HUBWOO_SECRET_ID;
			$status =  HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey);
			
			if( !$status ) {
				$response['status'] = false;
				$response['message'] = __('Something went wrong, please check your API Keys');
			}
		}
		echo json_encode($response);
		wp_die();
	}
	/**
	 * get all groups.
	 * 
	 * @return [type] [description]
	 */
	public function hubwoo_get_groups(){

		// check the nonce sercurity.
		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
		$groups = HubWooContactProperties::get_instance()->_get( 'groups' );
		echo json_encode($groups);
		wp_die();
	}

	/**
	 * create a group on ajax request.
	 */
	public function hubwoo_create_group(){
		// check the nonce sercurity.
		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
		// check if request has complete details.
		if( isset( $_POST[ 'createNow' ] ) && isset( $_POST[ 'groupDetails' ] ) ){
			// what we have to create
			$createNow = $_POST[ 'createNow' ];
			// if we have to create a group.
			if( $createNow == "group" ){
				// collect the group details.
				$groupDetails = $_POST[ 'groupDetails' ];
				// let's create the group.
				echo json_encode( HubWooConnectionMananager::get_instance()->create_group( $groupDetails ) );

				wp_die();
			}
		}
	}

	/**
	 * create an group property on ajax request.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_create_group_property(){
		// check the nonce sercurity.
		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
		if ( isset( $_POST[ 'groupName' ] ) && isset( $_POST[ 'propertyDetails' ] ) ){

			$propertyDetails = $_POST[ 'propertyDetails' ];

			$propertyDetails[ 'groupName' ] = $_POST[ 'groupName' ];

			echo json_encode( HubWooConnectionMananager::get_instance()->create_property(  $propertyDetails ) );
			wp_die();
		}
	}

	/**
	 * get hubwoo group properties by group name.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_get_group_properties(){
		// check the nonce sercurity.
		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
		if( isset( $_POST[ 'groupName' ] ) ){

			$groupName = $_POST[ 'groupName' ];
			echo json_encode( HubWooContactProperties::get_instance()->_get( 'properties', $groupName ) );
			wp_die();
		}
	}


	/**
	 * mark setup is completed.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_setup_completed(){
		// check the nonce sercurity.
		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
		update_option( 'hubwoo_setup_completed', true );
		update_option( 'hubwoo_newversion_groups_saved', true );
		return true;
		wp_die();
	}
}

new HubWooAjaxHandler();