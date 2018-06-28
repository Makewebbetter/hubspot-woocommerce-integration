<?php 

/**
 * All hubspot needed general settings.
 *
 * Template for showing/managing all the hubspot settings fields.
 *
 * @since 1.0.0 
 */

// check for the settings save request with verify the 'hubwoo-settings' nonce.
if( ! empty( $_POST ) && check_admin_referer( 'hubwoo-settings' ) ){
	if( isset($_POST['hubwoo_settings_enable']) || isset($_POST['hubwoo_settings_enable']) ) {
		$message = __('Settings Saved Successfully', 'hubwoo');
		Hubwoo::hubwoo_notice( $message, 'success' );
	}
	else {
		$message = __('Please enable the plugin first', 'hubwoo');
		Hubwoo::hubwoo_notice( $message, 'error' );
	}
	// update the settings.
	woocommerce_update_options( Hubwoo_Admin::hubwoo_general_settings() );
	// lets call the api validator to update the api key validation.
	//HubWooConnectionMananager::get_instance()->hubwoo_validate_oauth_token();
}

// check if the api is entered.
$hapikey = HUBWOO_CLIENT_ID;
$hseckey = HUBWOO_SECRET_ID;
if( $hapikey && $hseckey ){

	// if its a valid, api key and all, set lets congratulate them.
	if( Hubwoo::is_valid_client_ids_stored() ){

		// add thickbox support for interactive setup.
		add_thickbox();

		?>
		<div id="hubwoo-setup-process" style="display: none;">
			<div class="popupwrap">
	          <p> <?php _e('We are setting up, Please do not navigate or reload the page before our confirmation message.', 'hubwoo')  ?></p>
		      <div class="progress">
		        <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width:0%">
		        </div>
		      </div>
		        <div class="hubwoo-message-area">
		        </div>
		    </div>
	    </div>
		<?php
		$display = "none";
		if ( Hubwoo::is_display_suggestion_popup() ) {
			$display = "block";
		}
		?>
			<div class="hub_pop_up_wrap" style="display: <?php echo $display; ?>">
				<div class="pop_up_sub_wrap">
					<p><?php _e('Support the plugin development by sending us tracking data( we just want the HubSpot id and Email id that too only once )','hubwoo'); ?>.
					</p>
					<div class="button_wrap">
						<a href="javascript:void(0);" class="hubwoo_accept"><?php _e('Yes support it','hubwoo'); ?></a>
						<a href="javascript:void(0);" class="hubwoo_later"><?php _e("I'll decide later",'hubwoo'); ?></a>
					</div>
				</div>
			</div>
		<?php

		$message = __( 'Congratulations! we have succesfully verified our hubspot keys. ', 'hubwoo' );

		// add Run Setup button at the top after key verification.
		if ( !Hubwoo::is_setup_completed() ){

			$plugin_enable = get_option('hubwoo_settings_enable', 'no');
			if( $plugin_enable == 'yes' ) {
				$message .= '<a id="hubwoo-run-setup" href="javascript:void(0)" class="button button-primary">'.__( 'Run Setup', 'hubwoo' ).'</a>';
			}
		}

		Hubwoo::hubwoo_notice( $message, 'update' );

	}
}

// render the admin settings for general page.
woocommerce_admin_fields( Hubwoo_Admin::hubwoo_general_settings() );