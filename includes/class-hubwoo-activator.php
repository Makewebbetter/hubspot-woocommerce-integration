<?php

/**
 * Fired during plugin activation
 *
 * @link       http://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    hubwoo-integration
 * @subpackage hubwoo-integration/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Hubwoo_Activator {

	/**
	 * Create log file in the WC_LOG directory.
	 *
	 * Create a log file in the WooCommerce defined log directory
	 * and use the same for the logging purpose of our plugin.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		@fopen( WC_LOG_DIR.'hubwoo-logs.log', 'a' );
		if (! wp_next_scheduled ( 'hubwoo_cron_schedule' )) {
            wp_schedule_event(time(), '5min', 'hubwoo_cron_schedule');
        }
	}

}
