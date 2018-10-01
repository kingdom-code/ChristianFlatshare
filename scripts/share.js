jQuery(document).ready(function($) {
    $('a.fb-share').click(function(){
        var width = $(window).width();
        window.open( $(this).attr('href'), "Facebook Share", "status=1, height=300, width=660, resizable=0, scrollbars = no, top=200, left=" + (width/2));
        return false;
    });
});