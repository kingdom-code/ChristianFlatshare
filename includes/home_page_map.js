// JavaScript Document

	var area = "";
	var position = new Array();
	position['northern_ireland'] = 1;
	position['scotland'] = 2;
	position['north_east'] = 3;
	position['north_west'] = 4;
	position['north'] = 5;
	position['east_midlands'] = 6;
	position['west_midlands'] = 7;
	position['wales'] = 8;
	position['east'] = 9;
	position['south_west'] = 10;
	position['south_east'] = 11;
	position['london'] = 12;	
	
	function doOver(e) {
		e = (e) ? e : ((event) ? event : null);
		if (e) {
			target = (e.target)? e.target : ((e.srcElement)? e.srcElement : null);
			if (target) {
				// Safari bug:
				if (target.nodeType == 3) { target = target.parentNode; }
				area = target.id.substr(8);
				$('church_directory_map').style.backgroundPosition = "-"+position[area]*280+"px 50%";
			}
	    }
	}
	
	function doOut() {
		$('church_directory_map').style.backgroundPosition = "<?php print $defaultPos?>px 50%";
	}
