<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/public
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Hubwoo_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}
	/**
	 * Update key as soon as user data is updated.
	 *
	 * @since    1.0.0
	 * @param      string    $user_id       User Id.
	 */

	public function hubwoo_woocommerce_save_account_details( $user_id ) {

		update_user_meta( $user_id, 'hubwoo_user_data_change', 'yes' );

	}
	
}