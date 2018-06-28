(function( $ ) {
	'use strict';

	// i18n variables
	var ajaxUrl = hubwooi18n.ajaxUrl;
	var hubwooWentWrong = hubwooi18n.hubwooWentWrong;
	var hubwooSuccess = hubwooi18n.hubwooSuccess;
	var hubwooCreatingGroup = hubwooi18n.hubwooCreatingGroup;
	var hubwooCreatingProperty = hubwooi18n.hubwooCreatingProperty;
	var hubwooSetupCompleted = hubwooi18n.hubwooSetupCompleted;
	var hubwooMailFailure = hubwooi18n.hubwooMailFailure;
	var hubwooSecurity = hubwooi18n.hubwooSecurity;

	jQuery(document).ready(function(){
		jQuery('.hubwoo_tracking').on('click',function(){
			jQuery('.hub_pop_up_wrap').show();
		});
		jQuery('.hubwoo_later').on('click',function(){
			jQuery('#hubwoo_loader').show();
			jQuery.post( ajaxUrl, {'action' : 'hubwoo_suggest_later', 'hubwooSecurity' : hubwooSecurity }, function(response){
				jQuery('#hubwoo_loader').hide();
				jQuery('.hub_pop_up_wrap').hide();
			});
			
		});
		jQuery('.hubwoo_accept').on('click',function(){
			jQuery('#hubwoo_loader').show();
			
			jQuery.post( ajaxUrl, {'action' : 'hubwoo_check_oauth_access_token', 'hubwooSecurity' : hubwooSecurity }, function(response){
				
				var oauth_response = jQuery.parseJSON( response );
				var oauth_status = oauth_response.status;
				var oauthMessage = oauth_response.message;
				
				if( oauth_status ){
					
					jQuery.post( ajaxUrl, {'action' : 'hubwoo_suggest_accept', 'hubwooSecurity' : hubwooSecurity }, function(response){
						if( response != null ){
							var data_status = response;
							if( data_status == 'failure') {
								alert(hubwooMailFailure);
							}
							jQuery('.hub_pop_up_wrap').hide();
						}
						else {
							// close the popup and show the error.
							alert( hubwooWentWrong );

							return false;
						}
						jQuery('#hubwoo_loader').hide();
					});
				}
				else {
					// close the popup and show the error.
					alert( hubwooWentWrong );
					jQuery('#hubwoo_loader').hide();
					return false;
				}
			});
		});
		// run the setup.
		jQuery("#hubwoo-run-setup").on( 'click', function() {
			jQuery('#hubwoo_loader').show();
			
			jQuery.post( ajaxUrl, {'action' : 'hubwoo_check_oauth_access_token', 'hubwooSecurity' : hubwooSecurity }, function(response){
				
				var oauth_response = jQuery.parseJSON( response );
				var oauth_status = oauth_response.status;
				var oauthMessage = oauth_response.message;
				
				if( oauth_status ){
					
					jQuery('#hubwoo_loader').hide();
					jQuery('#hubwoo-setup-process').show();
					// show the loader and current processing state.
					jQuery.post( ajaxUrl, {'action' : 'hubwoo_get_groups', 'hubwooSecurity' : hubwooSecurity }, function(response){
						if( response != null ){
							// get all groups
							var groups = jQuery.parseJSON(response);
							var group_count = groups.length;

							var group_progress = parseFloat(100/group_count);
							var current_progress = 0;
							// loop all groups
							jQuery.each(groups, function(index,group_details){
								// group name
								var displayName = group_details.displayName;
								// post data to create groups.
								var groupData = {
									'action' : 'hubwoo_create_group_and_property',
									'createNow': 'group',
									'groupDetails': group_details,
									'hubwooSecurity' : hubwooSecurity
								};
								// call the function for each request.
								//var groupResponse = createGroup( groupData );

								jQuery.ajax({ url: ajaxUrl, type: 'POST', data : groupData, async: false }).done(function(groupResponse){
									
									var response = jQuery.parseJSON( groupResponse );
									var errors = response.errors;
									var hubwooMessage = "";

									if( !errors ){

										var responseCode = response.status_code;
										if( responseCode == 200 ){

											hubwooMessage = "<div class='notice updated'><p> "+ hubwooCreatingGroup + " <strong>" + displayName +"</strong></p></div>";

										}else{

											var hubwooResponse = response.response;
											if( hubwooResponse != null && hubwooResponse != "" ){

												hubwooResponse = jQuery.parseJSON( hubwooResponse );

												hubwooMessage = "<div class='notice error'><p> "+ hubwooResponse.message +"</p></div>";
											}else{

												hubwooMessage = "<div class='notice error'><p> "+ responseCode +"</p></div>";
											}
										}
									}else{

										hubwooMessage = "<div class='notice error'><p> "+ errors +"</p></div>";
									}
									
									/*var groupCreateMessage = hubwooCreatingGroup + " <strong>" + displayName + "</strong>";*/ 

									jQuery(".hubwoo-message-area").append( hubwooMessage );

									//var groupFields = createGroupProperties ( group_details.name );
									
									//let's create the group property.
									var getProperties = { action : 'hubwoo_get_group_properties', groupName: group_details.name, 'hubwooSecurity' : hubwooSecurity };
									jQuery.ajax({ url: ajaxUrl, type: 'POST', data : getProperties, async: false }).done(function( propResponse ){

										if( propResponse != null ){
											// parse all properties.
											var allProperties = jQuery.parseJSON( propResponse );
											var allProperties_count = allProperties.length;
											
											var allProperties_progress = parseFloat(group_progress/allProperties_count);
											
											jQuery.each( allProperties, function( i, propertyDetails ) {
												current_progress+= allProperties_progress;
												jQuery('.progress-bar').css('width',current_progress+'%');
												var createProperties = { action : 'hubwoo_create_group_property', groupName: group_details.name, propertyDetails: propertyDetails, 'hubwooSecurity' : hubwooSecurity };
												jQuery.ajax({ url: ajaxUrl, type: 'POST', data : createProperties, async: false }).done(function( propertyResponse ){

													var proresponse = jQuery.parseJSON( propertyResponse );
													var proerrors = proresponse.errors;
													var prohubwooMessage = "";

													if( !proerrors ){

														var proresponseCode = proresponse.status_code;
														if( proresponseCode == 200 ){

															prohubwooMessage = "<div class='notice updated'><p> "+ hubwooCreatingProperty + " <strong>" + propertyDetails.name +"</strong></p></div>";

														}else{

															var prohubwooResponse = proresponse.response;
															if( prohubwooResponse != null && prohubwooResponse != "" ){

																prohubwooResponse = jQuery.parseJSON( prohubwooResponse );

																prohubwooMessage = "<div class='notice error'><p> "+ prohubwooResponse.message +"</p></div>";
															}else{

																prohubwooMessage = "<div class='notice error'><p> "+ proresponseCode +"</p></div>";
															}
														}
													}else{

														prohubwooMessage = "<div class='notice error'><p> "+ proerrors +"</p></div>";
													}
													
													/*var groupCreateMessage = hubwooCreatingGroup + " <strong>" + displayName + "</strong>";*/ 

													jQuery(".hubwoo-message-area").append( prohubwooMessage );
												});
												//createProperty( groupName, propertyDetails );
											});
										}
									});	
								});
							});
						}else{
							// close the popup and show the error.
							alert( hubwooWentWrong );

							return false;
						}

						// mark the process as completed.
						jQuery.post(ajaxUrl, {'action': 'hubwoo_setup_completed', 'hubwooSecurity' : hubwooSecurity}, function( response ){

							alert( hubwooSetupCompleted );

							location.reload();
						});
					}); 
				}
				else {
					// close the popup and show the error.
					alert( hubwooWentWrong );
					jQuery('#hubwoo_loader').hide();
					return false;
				}
			});
			
		});
	});

	// create group.
	/*function createGroup( groupData ){

		jQuery.ajax({ url: ajaxUrl, type: 'POST', data : groupData, async: false }).done(function(response){
			return response;
		});
	}

	// get all properties of group.
	function createGroupProperties( groupName ){

		var data = { action : 'hubwoo_get_group_properties', groupName: groupName }
		jQuery.ajax({ url: ajaxUrl, type: 'POST', data : data, async: false }).done(function( response ){

			if( response != null ){
				// parse all properties.
				var allProperties = jQuery.parseJSON( response );

				jQuery.each( allProperties, function( i, propertyDetails ) {

					var propertyCreateMessage = hubwooCreatingProperty + " <strong>" + propertyDetails.name + "</strong>"; 

					jQuery(".hubwoo-message-area").append("<p>"+ propertyCreateMessage +"</p>");
					//createProperty( groupName, propertyDetails );
				})
			}
		});
	}


	function createProperty( groupName, propertyDetails ){

		var data = { action : 'hubwoo_create_group_property', groupName: groupName, propertyDetails: propertyDetails }
		jQuery.ajax({ url: ajaxUrl, type: 'POST', data : data, async: false });
	}*/

})( jQuery );
