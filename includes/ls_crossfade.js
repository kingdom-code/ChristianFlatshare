	
	// LS Crossfader - version 1.1 (The "Haydon" edition)

	// ****************************************************************
	// USER-DEFINED VARIABLES
	// ****************************************************************	
	
	// The ID of the div that will contain the images
	var cf_canvas_id = 'crossfade_canvas';
	
	// The duration of the transition in milliseconds
	var cf_transition_duration = 2000;
	
	// The pause between transitions.
	// If the transition_duration is 1000 msec and delay is 3, then
	// the pause will be 3 seconds (3000 msec)
	var cf_delay_multiplier = 3;
	
	// The path of all the images
	var cf_path = 'images/rotator_home_page/';
	
	// The individual image name
	var cf_paths = [
			cf_path + '1.jpg',
			cf_path + '2.jpg',
			cf_path + '3.jpg',
			cf_path + '4.jpg',
			cf_path + '5.jpg'
	];
	
	// ****************************************************************
	// NO NEED TO EDIT ANYTHING BELOW THIS LINE
	// ****************************************************************
	
	var cf_timer = 0;
	var cf_canvas;
	var cf_counter = 0;

	window.addEvent('domready', function(){
									 
		cf_canvas = $(cf_canvas_id);
		
		if (cf_canvas) {
		
			// Start preloading all images
			new Asset.images(cf_paths,{
				
				onComplete: function(){
					
					// Clear the placeholder image of the canvas
					cf_canvas.set('html','');
					
					// Populate the canvas with all images
					cf_paths.each(function(path,i){
						
						var image = new Element('img').setProperties({'src' : path}).setStyles({
							'position':'absolute',
							'margin-top' : '-20px',
							'z-index':1000 - i
						}).injectInside(cf_canvas);
						
					});
					
					// At this stage, the canvas contains ALL images in the proper order
					// Begin the crossfade
					crossfade();				
					
				}
				
			});
		
		}
		
	});
	
	// Crossfade function
	function crossfade() {
	
		cf_canvas = $(cf_canvas_id);
		
		// Reset all opacities to 100
		cf_canvas.getElements('img').setStyles({'opacity':1});
		
		// Reset timer
		cf_timer = 0;
		
		// Add a transition to each image
		images = cf_canvas.getElements('img');
		images.each(function(image, i){
		
			// Debug
			if (typeof(console) != "undefined") { console.log(i+": "+image.src); }
			
			// Increment the crossfade_timer
			cf_timer += cf_transition_duration;
			
			fx = function() {
				if (i == images.length - 1) {
					// If we're showing the last image, we change the opacity of the FIRST
					// image back to 1 and re-call the crossfade function
					images[0].tween('opacity',0,1);
				} else {
					// Else, we simply need to fade out the current image
					image.set('tween',{ duration:cf_transition_duration });
					image.tween('opacity',1,0);
				}
			}.delay(cf_timer * cf_delay_multiplier);
			
		});
	
	}
	
	
	