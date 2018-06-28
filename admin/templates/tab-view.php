<?php 
/**
 * Template tab view for loading tab section.
 *
 * Loads and handles tabs related functionality for plugin.
 *
 * @since 1.0.0
 */

// use global HubWoo class object to use throughout the page.
global $hubwoo;

// check if the tabs are there in the params.
if( is_array( $params ) && count( $params ) ){

	//check for the active tab.
	$active_tab = isset( $_GET['hubwoo_tab'] ) ? $_GET['hubwoo_tab'] : '';

	//wrap with woocommerce styling.
	?>
	<div class="row hubwoo-main-wrapper">
		<div class="wrap woocommerce hubwoo col-lg-6 col-md-6 col-sm-6 col-xs-12">
			<form method="post" action="">
				<span>
				<h1 class="hubwoo_plugin_title"><?php _e('HubSpot WooCommerce Integration', 'hubwoo'); ?></h1>
				<?php  
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
							<span class="hubwoo_oauth_span">
								<label><?php _e('Please Click this button to Authorize with HubSpot App.','hubwoo'); ?></label>
								<a href="<?php echo $hubspot_url; ?>" class="button-primary">Authorize</a>
							</span>
						<?php
					} 
				?>
				</span>
				<nav class="nav-tab-wrapper woo-nav-tab-wrapper hubwoo-nav-tab-wrapper">
				<?php

					//let's display all the tabs.
					foreach( $params as $tab_id => $tab_name ){
						$tab_classes = "nav-tab ";
						//check if this tab is current tab.
						if( !empty( $active_tab ) && $active_tab == $tab_id ){
							
							$tab_classes .= "nav-tab-active";
						//if its the direct sub menu link, make the first tab as active tab.
						}else if( empty( $active_tab ) && $tab_id == "hubwoo_connect" ){

							$tab_classes .= "nav-tab-active";
						}
						// display the tab header.
						?>
						<a class="<?php echo $tab_classes; ?>" id="<?php echo $tab_id; ?>" href="<?php echo admin_url('admin.php?page=hubwoo').'&hubwoo_tab='.$tab_id; ?>"><?php echo $tab_name; ?></a>
						<?php 
					}
				?>
				</nav>

				<?php 
				
				// if submenu is directly clicked on woocommerce.
				if( empty( $active_tab ) ){

					$active_tab = "hubwoo_connect";
				}

				// look for the path based on the tab id in the admin templates.
				$tab_content_path = 'admin/templates/'.$active_tab.'.php';
				$hubwoo->load_template_view( $tab_content_path );
				
				?>
				<!-- hubspot settings submit button with nonce fields. -->
				<p class="submit">
					<?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
						<input name="save" class="button-primary woocommerce-save-button hubwoo-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'hubwoo' ); ?>" />
					<?php endif; ?>
					<?php wp_nonce_field( 'hubwoo-settings' ); ?>
				</p>
				<!-- end of submit button section and nonce. -->
			</form>
			<!-- form end. -->
		</div>
		<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 hubwoo-promotion">
			<div class="hubwoo-side-wrap">
			  <div class="row">
			  <div class="hubwoo-main">
			  	<p class="hubwoo-pro">
			  	<?php _e("Why better to use ","hubwoo")?><a target="_new" href="https://makewebbetter.com/product/hubspot-woocommerce-integration-pro/"><?php _e("HubSpot WooCommerce Integration PRO","hubwoo")?></a></p>
			  	<div class="hubwoo-pro-now"><a target="_new" class="hubwoo-prod-link hubwoo-pro-buy" href="https://makewebbetter.com/product/hubspot-woocommerce-integration-pro/"><?php _e("Go Pro Now","hubwoo")?></a></div>
			  	<p class="hubwoo-return"><?php _e("If you don't like the pro features, you can rollback to free at any time.","hubwoo")?></p></div>
			    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 hubwoo-main-ext">
			      <h2 class="hubwoo-addon-heading"><u><?php _e("Our PRO version","hubwoo")?></u></h2>
			      <a target="_new" href="https://makewebbetter.com/product/hubspot-woocommerce-integration-pro/"><img src="<?php echo HUBWOO_URL.'admin/images/HubSpot.jpg'?>" alt="" class="img-responsive"></a>
			      <h5 class="hubwoo-prod-name"><?php _e("HubSpot WooCommerce Integration PRO","hubwoo");?></h5>
			      <p class="hubwoo-para"><?php _e("Main Features","hubwoo")?></p>
			      <ul class="hubwoo-prod-ul">
			        <li><?php _e("RFM segmentation","hubwoo")?></li>
			        <li><?php _e("Guest Customer Data Sync","hubwoo")?></li>
			        <li><?php _e("WooCommerce Subscriptions Data","hubwoo")?></li>
			        <li><?php _e("Sync old users in a single click","hubwoo")?></li>
			        <li><?php _e("Real-time user activity sync","hubwoo")?></li>
			        <li><?php _e("Real-time order details syncing","hubwoo")?></li>
			        <li><?php _e("Full history of products bought","hubwoo")?></li>
			        <li><?php _e("Full history of categories bought","hubwoo")?></li>
			        <li><?php _e("Full history of skus bought","hubwoo")?></li>
			        <li><?php _e("Shopping cart details","hubwoo")?></li>
			      </ul>
			      <div class="hubwoo-btn-wrap">
			        <a target="_new" class="hubwoo-prod-link hubwoo-main-buy" href="https://makewebbetter.com/product/hubspot-woocommerce-integration-pro/"><?php _e("Purchase Now for $199 Only","hubwoo")?></a>
			        <a class="hubwoo-prod-link hubwoo-main-trial" href="https://makewebbetter.com/product/hubspot-woocommerce-integration-pro/"><?php _e("Start $1 Trial","hubwoo")?></a>
			      </div>
			    </div>
			    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 hubwoo-addons">
			      <h2 class="hubwoo-addon-heading"><u><?php _e("Our Add-ons","hubwoo")?></u></h2>
			      <div class="hubwoo-content-wrap">
			        <a target="_new" href="https://makewebbetter.com/product/hubspot-abandoned-cart-recovery/"><img src="<?php echo HUBWOO_URL.'admin/images/abn-cart.jpg'?>" alt="" class="img-responsive"></a>
			        <h5 class="hubwoo-prod-name"><?php _e("HubSpot Abandoned Cart Recovery","hubwoo")?></h5>
			        <div class="hubwoo-btn-wrap">
			         	<a target="_new" class="hubwoo-prod-link" href="https://makewebbetter.com/product/hubspot-abandoned-cart-recovery/"><?php _e("Purchase Now","hubwoo")?></a>
			        </div>
			      </div>
			      <hr>
			      <div class="hubwoo-content-wrap">
			        <a target="_new" href="https://makewebbetter.com/product/hubspot-dynamic-coupon-code-generation/"><img src="<?php echo HUBWOO_URL.'admin/images/dyn-coupon.jpg'?>" alt="" class="img-responsive"></a>
			        <h5 class="hubwoo-prod-name"><?php _e("HubSpot Dynamic Coupon Code Generation","hubwoo")?></h5>
			        <div class="hubwoo-btn-wrap">
			          <a target="_new" class="hubwoo-prod-link" href="https://makewebbetter.com/product/hubspot-dynamic-coupon-code-generation/"><?php _e("Purchase Now","hubwoo")?></a>
			        </div>
			      </div>
			      <hr>
			      <div class="hubwoo-content-wrap">
			        <a target="_new" href="https://makewebbetter.com/product/hubspot-field-to-field-sync/"><img src="<?php echo HUBWOO_URL.'admin/images/ftf-sync.jpg'?>" alt="" class="img-responsive"></a>
			        <h5 class="hubwoo-prod-name"><?php _e("HubSpot Field to Field Sync","hubwoo")?></h5>
			        <div class="hubwoo-btn-wrap">
			          <a target="_new" class="hubwoo-prod-link" href="https://makewebbetter.com/product/hubspot-field-to-field-sync/"><?php _e("Purchase Now","hubwoo")?></a>
			        </div>
			      </div>
			      <hr>
			    </div>
			  </div>
			</div>
		</div>
	</div>
	<div style="display: none;" class="loading-style-bg" id="hubwoo_loader">
		<img src="<?php echo HUBWOO_URL;?>admin/images/loader.gif">
	</div>
	<!-- wrapper end. -->
	<?php
// if there are no tabs to show ( just checking the both sides of the road on one-way street :p )
}else{

	$notice = __( 'No tabs to display, please verify that our extensions are installed correctly, or contact us!', 'hubwoo' );

	$hubwoo->hubwoo_notice( $notice );

}