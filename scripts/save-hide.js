jQuery(document).ready(function($) {
    
    $('a.save_ad_button').click(function(){
        var id = $(this).attr('id');
        var parts = id.split('_');
        
        $.ajax({url: "ajax-functions.php?action=save&post_type=" + parts[1] + "&id=" + parts[3]}).done(function(r) {
			if (r.result == "insert_success" || r.result == "update_hidden") {
				// Change the image to a green button
				$('#'+id).children('img').attr('src', 'images/button_hidden_ad.gif');
			}
            else if (r.result == "update_unsaved") {
                $('#'+id).children('img').attr('src', 'images/button_hidesave_ad.gif');
			}
            else if (r.result == "update_saved") {
                $('#'+id).children('img').attr('src', 'images/button_saved_ad.gif');
			}
            else {								
				alert("An error occurred when updating the status of your saved ad.\n We apologise for the inconvenience.\nPlease contact problems@chirstianflatshare.org.");
			}
        });
        
        return false;
    });
    
});