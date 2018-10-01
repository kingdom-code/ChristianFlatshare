// JavaScript Document

	
	var opacities = [];
	
	window.addEvent("load",function(){ 
									
		var i = 1;
	
		// Get all links
		$$('.ps_thumbs a').each(function(a){
		
			 a.id = 'opacity_control_'+i;
			 i++;
			 
			 var img = a.getElement('img');
			 opacities[a.id] = new Fx.Tween(img,{property:'opacity',duration:200,wait:false});
			 opacities[a.id].set(0.30);
			 
			 a.addEvent('mouseover',function(){
				opacities[this.id].start(0.30,1);
			 });
			 a.addEvent('mouseout',function(){
				opacities[this.id].start(1,0.30);
			 });
			 
			 a.setStyle('display','');
		
		});
		
		show_tips();
	
	});
	
	function show_tips() {
		var tips = new Tips($$('.image_tooltip'));	
	}
			
