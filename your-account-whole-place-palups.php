<?php
session_start();

// Autoloader
require_once 'web/global.php';



connectToDB();

	$remove_msg = "";
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }
	
	// Redirect if user is an advertiser
	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }
	
        if (isset($_GET['action'])) { $action = $_GET['action']; } else { $action = NULL; }
        if (isset($_GET['id'])) { $id = $_GET['id']; } else { $id = NULL; }

	
	// If we're removing the palup choice
	switch($action) {
			case "remove":
		//	$query = "delete from cf_palups where offered_id = '".$id."' and wanted_id = '".$wid."' and user_id = '".$_SESSION['u_id']."'";
	 	  $query = "delete from cf_palups where offered_id = '".$id."' and user_id = '".$_SESSION['u_id']."'";			
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result) {
				$remove_msg = '<p class="green"><strong>Your pal-up listing has been removed from the offered advert.</strong></p>';
			}
			break;
	
	}
	
	
	// Load all the "Saved ads" for this user
	$s = "";
	
	// 1. Offered ads
	$query = "
		select distinct
			o.offered_id,
			o.bedrooms_available,
			o.bedrooms_total,
			o.accommodation_type,
			o.room_share,			
			o.building_type,
			o.street_name,
			o.town_chosen,
			o.suspended,
                       (CASE WHEN LENGTH(town_chosen)>0
                                 THEN o.town_chosen
                                  ELSE (SELECT j2.town
                                        FROM   cf_jibble_postcodes j2
                                        WHERE  SUBSTRING_INDEX(o.postcode,' ',1) = j2.postcode )
                        END) as town,
			o.postcode,
			DATE_FORMAT(p.palup_date,'%d %b, %Y') as `palup_date`,
			'LIVE_AD' as `status`,
			p.offered_id as `ad_id`
		from cf_palups as `p`, cf_offered as `o`
		where o.offered_id = p.offered_id
		and p.user_id = '".$_SESSION['u_id']."'
		UNION 
		select distinct 
			o.offered_id,
			o.bedrooms_available,
			o.bedrooms_total,
			o.accommodation_type,
			o.room_share,			
			o.building_type,
			o.street_name,
			o.town_chosen,
			o.suspended,
                        (CASE WHEN LENGTH(town_chosen)>0
                               THEN o.town_chosen
                               ELSE (SELECT j2.town
                                     FROM   cf_jibble_postcodes j2
                                     WHERE  SUBSTRING_INDEX(o.postcode,' ',1) = j2.postcode )
                        END) as town,
			o.postcode,
			DATE_FORMAT(p.palup_date,'%d %b, %Y') as `palup_date`,
			'DELETED_AD' as `status`,
			p.offered_id as `ad_id`
		from cf_palups as `p`, cf_offered_archive as `o`
		where o.offered_id = p.offered_id
		and p.user_id = '".$_SESSION['u_id']."'
	";
    $class = "odd";		
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$debug .= $query;
	if (mysqli_num_rows($result)) {
		while($ad = mysqli_fetch_assoc($result)) {
			$s .= '<tr class="'.$class.'">'."\n";
			$s .= '<td>';
			$s .= '<strong>'.getAdTitle($ad,"offered").'</strong><br />';
			
			if ($ad['status'] == 'DELETED_AD') {
				$s .= '<span class="red">(this advert has been deleted)</span><br />';
			} else if ($ad['suspended']) {
				$s .= '<span style="color:#FF9900;">(this ad is suspended)</span><br />';
			}			
			
			$s .= '<span class="grey f10">Palup date '.$ad['palup_date'].'</span>';
						
            $reply_query = "SELECT DATE_FORMAT(r.reply_date,'%d %b, %Y at %H:%i') as `reply_date` 
			         FROM cf_email_replies r
					 WHERE r.from_user_id = ".$_SESSION['u_id']."
					 AND   r.to_ad_id = ".$ad['offered_id']."
					 AND   r.to_post_type = 'offered'
					 ";
            $reply_result = mysqli_query($GLOBALS['mysql_conn'], $reply_query);					 
			if (mysqli_num_rows($result)) {
  		      while($reply = mysqli_fetch_assoc($reply_result)) {
			  $s .= '<br /><span class="grey f10">You replied on '.$reply['reply_date'].'</span>';
			  }
			}
			
						
			$s .= '</td>';
//			$s .= '<td align="right" valign="top"><a href="'.$_SERVER['PHP_SELF'].'?action=remove&id='.$ad['offered_id'].'&wid='.$ad['wanted_id'].'">Remove</a></td>';				
			$s .= '<td align="right" valign="top"><a href="'.$_SERVER['PHP_SELF'].'?action=remove&id='.$ad['offered_id'].'">Remove</a></td>';				
			$s .= '</tr>';
			$class = ($class == "even")? "odd":"even";				
						
		}
	}
	
	
	// Set the flag for the weclome message to TRUE
	if (!$s) {
	    $welcome_msg = TRUE;		
		$s .= '<p>You have no adverts marked for Whole Place Pal-Up.</p>';
	} else {
		$welcome_msg = FALSE;	
		$temp .= '<table cellpadding="5" cellspacing="0"  width="100%">';
		$temp .= $s;
		$temp .= '</table>';
		$s = $temp;
		unset($temp);
    // Unliked other pages, we always display this "welcome" message 
     $s .= '<div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;">
				<h2 class="mt0 mb5">Your Whole Place Pal-Ups </h2>
	      <p class="mt0 mb5" align="justify">This feature helps accommodation seekers to connect with others willing to share the accommodation offered in a particular Whole Place advert. Whole Place adverts are adverts for accommodation which in currently unoccupied. </p>
				<p class="mt0 mb5" align="justify">While looking for accommodation you can choose to mark Whole Place ads of interest to you using the "<strong>Pal-Up</strong>" link shown in the top-right corner of ads. Clicking this link will add your name and the details of your and and current Wanted Accommodation advert(s) to the bottom of the Whole Place advert. You must place a Wanted Accommodation advert first to use this feature.</p>
				<p class="mt0 mb0" align="justify">This page shows you the adverts you have marked for Pal-Up and allows to remove your pal-up choice.</p>
		  </div>';		
	}
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Your saved ads - Christian Flatshare</title>
<!-- InstanceEndEditable -->
<link href="styles/web.css" rel="stylesheet" type="text/css" />
<link href="styles/headers.css" rel="stylesheet" type="text/css" />
<link href="css/global.css" rel="stylesheet" type="text/css" />
<link href="favicon.ico" rel="shortcut icon"  type="image/x-icon" />
	<!-- jQUERY -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
    //no conflict jquery
    jQuery.noConflict();
</script>
<!-- MooTools -->
<script language="javascript" type="text/javascript" src="includes/mootools-1.2-core.js"></script>
	<script language="javascript" type="text/javascript" src="includes/mootools-1.2-more.js"></script>
	<script language="javascript" type="text/javascript" src="includes/icons.js"></script>
<!-- InstanceBeginEditable name="head" -->
<style type="text/css">
<!--
.style1 {color: #000000}
.style5 {
	font-size: 14px;
	font-weight: bold;
}
-->
</style>
<!-- InstanceEndEditable -->
<!-- InstanceParam name="mainContentClass" type="text" value="" -->
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
		<div class="cl" id="cl">
			<h1 class="mt0">Your Whole Place Pal-Ups </h1>
			<div class="clear"><!----></div>
			<?php print $msg?>
			<?php print $remove_msg?>
			<?php print $s?>			
			<form name="updateForm" method="post" action="<?php print $_SERVER['PHP_SELF']?>">
			</form>
		<!--	<?php if ($welcome_msg) { ?> -->
		  <div class="mt10" style="background-color:#FFFFCC;padding:10px;border:1px solid #FFCC00;">
				<h2 class="mt0 mb5">Your Whole Place Pal-Ups </h2>
	      <p class="mt0 mb5" align="justify">Whole Place adverts are adverts for accommodation which is  unoccupied. This feature helps accommodation seekers to connect with others willing to explore sharing the accommodation offered in a particular Whole Place advert.  </p>
				<p class="mt0 mb5" align="justify">While looking for accommodation you can choose to mark Whole Place advers of interest to you using the "<strong>Pal-Up</strong>" link shown in the top-right corner those adverts. Clicking this link will add your name and the details of your active Wanted Accommodation advert(s) to the bottom of the Whole Place advert. To use this feature you must place a Wanted Accommodation advert.</p>
				<p class="mt0 mb0" align="justify">This page shows you the adverts you have marked for Pal-Up and allows to remove your Whole Place pal-up choices.</p>
				<p class="mt0 mb0" align="justify">&nbsp;</p>
				</div>
		   <!-- <?php } ?>		-->
		  </p>
		</div>
		<div class="cs" style="width:20px; height:270px;"><!----></div>
		<div class="cr">
			<?php print $theme['side']; ?>
			<?php print sharingCFS()?>					
		</div>
		<div class="cc0"><!----></div>
		<!-- InstanceEndEditable -->
		</div>
		<div class="redMenu">
			<ul>
				<!--<li><a href="../flat-finding-tips.php">flat finding tips</a></li>-->
				<li><a href="advertising.php">advertising</a></li>			
				<li><a href="where-does-all-the-money-go.php">where does the money go?</a></li>
				<li><a href="glossary.php">glossary</a></li>
				<li><a href="terms-and-conditions.php">terms &amp; conditions</a></li>
				<li><a href="privacy-policy.php">privacy policy</a></li>
				<li><a href="contact-us.php">contact us</a></li>
				<li class="noSeparator"><a href="resources.php">links &amp; resources</a></li>
			</ul>
		</div>
		<div id="footerText">
			<p class="m0"><strong>Christian Flatshare... helping accommodation seekers connect with the local church community<br />
			Finding homes, growing churches and building communities </strong>&copy; ChristianFlatShare.org 2007-<?php print date("Y")?></p>
	  </div>
	</div>
	<div id="footer"><img src="images/spacer.gif" alt="*" width="1" height="12"/></div>
</div>
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); }?>
<?php print getTrackingCode();?>
</body>
<!-- InstanceEnd --></html>
