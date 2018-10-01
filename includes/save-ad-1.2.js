// JavaScript Document



	window.addEvent('domready', function(){

	
		// Regular expression to parse the id of each "save_ad_Button" link, e.g.: save_offered_ad_4149
		var saveExp = /^save\_(.+)\_ad\_(\d+)$/;

		// Get all links on the page with the "save_ad_button" class
		$$('a.save_ad_button').each(function(a) {

			var m = a.id.match(saveExp);

			if (m != null) {

				// Add an onclick event handler to the link
				a.addEvent('click',function(e) {

					e = new Event(e);

					// Make an ajax call to ajax-save-ad.php with action == "save" and post_type, ad_id
					new Request({
								
						url: 'ajax-functions.php',
						method: 'get',
						
						onComplete: function(r) {
							
							r = JSON.decode(r);


							if (r.result == "insert_success" || r.result == "update_hidden") {

								// Change the image to a green button

								$(a.id).getElement('img').src = 'images/button_hidden_ad.gif';

								//alert("This ad was saved succesfully");

							} else if (r.result == "update_unsaved") {

								$(a.id).getElement('img').src = 'images/button_hidesave_ad.gif';

								//alert("Ad was removed from your Saved list");

							} else if (r.result == "update_saved") {

								$(a.id).getElement('img').src = 'images/button_saved_ad.gif';

								//alert("Ad was removed from your Saved list");

							} else {								

								alert("An error occured when updating the status of your saved ad.\n We apologise for the inconvenience.\nPlease contact problems@chirstianflatshare.org.");

							}

						}

					}).send('action=save&post_type='+m[1]+'&id='+m[2]);

					//alert("Save "+m[1]+" ad with id "+m[2]);

					e.stop();							

				});

			}

		});
									 

	});