
	// We'll initialise the mouseover handlers only after all page content has loaded
	window.addEvent('load',function(){
									
		// Apply a opacity tween to all 4 <div class="iconOver"> and set their visibility to "visible"
		$$('#iconCanvas .iconOver').each(function(div){
			
			div.set('tween',{ duration:300 });
			div.set('opacity',0);
			div.setStyle('visibility','visible');			
			
		});
			
		// Get all 4 links that reside inside the #iconCanvas div
		$$('#iconCanvas a').each(function(a){
										  
			// the each function will iterate through all 4 links with
			// a becoming each link element
			a.addEvent('mouseenter',function(){
			
				// Get the nested <div class="iconOver"> inside this link and tween it's opacity
				a.getElement('.iconOver').tween('opacity',1);
									 
			});
			
			// Opposite of the above, tween opacity down to 0
			a.addEvent('mouseleave',function(){
			
				// Get the nested <div class="iconOver"> inside this link and tween it's opacity
				a.getElement('.iconOver').tween('opacity',0);
									 
			});
			
										  
		});									
									
	});