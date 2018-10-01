<?php

session_start();
    
// Autoloader
require_once 'web/global.php';

require('includes/class.randompass.php');	// Random password generator class


connectToDB();

	// In order for this page to work, the id & type must be provided
	if (isset($_REQUEST['post_type'])) { $post_type = $_REQUEST['post_type']; } 
	if (isset($_REQUEST['ad_type'])) { $post_type = $_REQUEST['ad_type']; } 	// backward compatibility; change of ad_type to post_Type
	if (isset($_REQUEST['id'])) { $id = $_REQUEST['id']; } //else { header("Location:index.php"); exit; }

	
	
	if (isset($_POST['cancel'])) { header("Location:your-account-manage-posts.php"); exit; }	
	if (isset($_POST['accommodation_description'])) { $accommodation_description = $_POST['accommodation_description']; } else { $accommodation_description = NULL; }	
	if (isset($_POST['household_description'])) { $household_description = $_POST['household_description']; } else { $household_description = NULL; }		
	if (isset($_POST['accommodation_situation'])) { $accommodation_situation = $_POST['accommodation_situation']; } else { $accommodation_situation = NULL; }			
	
	if (isset($_POST['id'])) { $id = $_POST['id']; } 
	if (isset($_POST['post_type'])) { $post_type = $_POST['post_type']; } 
	
	$open_photo_after_load = NULL;
						

/*			
	if ($_POST)	{
	 	if ($post_type == "wanted") {
			$query = "UPDATE cf_wanted SET accommodation_situation = '".$accommodation_situation."'
				 				WHERE wanted_id = '".$id."'
								AND   user_id = '".$_SESSION['u_id']."'
								";
		} else {
			$query = "UPDATE cf_offered 
								SET accommodation_description = '".$accommodation_description."',
										household_description = '".$household_description."'
						 		WHERE offered_id = '".$id."'
								AND   user_id = '".$_SESSION['u_id']."'
								";
		}			
		
		$debug .= debugEvent("UPDATE query",$query);		
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if ($result) {
				header("Location: your-account-manage-posts.php?report=updateSuccess");
			} else {
				header("Location: your-account-manage-posts.php?report=updateFailure");
			}			
	} // if $_POST
*/	
	
/*	// First of all, check for ownership of the ad
	if ($edit_mode == "edit") {
		$query = "select count(*) from cf_".$post_type." where user_id = '".$_SESSION['u_id']."' and ".$post_type."_id = '".$id."';";
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$check = cfs_mysqli_result($result,0,0);
		if (!$check) {
			header("Location: your-account-manage-posts.php"); exit;
		}					
  } // ownership check	
*/
	
	if ($post_type == "offered") {
		$query  = "
			select o.*,
			 (CASE IFNULL(o.town_chosen,'')
				WHEN '' THEN j.town 
				ELSE o.town_chosen
				END) as town,			 
			(CASE available_date > SYSDATE()
			   WHEN true THEN DATE_FORMAT(o.available_date,'%d %M %Y')
			   ELSE 'Today'
			 END) as `available_date_formatted`,
			DATEDIFF(curdate(),created_date) as `ad_age`, 
			(SELECT DATEDIFF(curdate(),last_login)  from cf_users 
			where cf_users.user_id = o.user_id) as `last_login_days` 
		";
		if (isset($_SESSION['u_id'])) { $query .= ", s.active as `active` "; } // Saved ad status
		$query .= "
			from cf_offered as `o` 
			left join cf_jibble_postcodes as `j` on SUBSTRING_INDEX(o.postcode,' ',1) = j.postcode 
		";
		if (isset($_SESSION['u_id'])) {
			$query .= "
				left join cf_saved_ads as `s` 
				on s.ad_id = o.offered_id and 
				s.post_type = 'offered' and 
				s.user_id = '".$_SESSION['u_id']."' 
			";
		}
		$query .= "
			where o.offered_id = '".$id."'
		";
		$debug = debugEvent("Details for offered ad query:",$query);
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$ad = mysqli_fetch_assoc($result);
		$propertyDescription = getPropertyDescription($ad['accommodation_type'],$ad['building_type']);
	}
	
	if ($post_type == "wanted") {
		$query = "
			select *, 
			DATE_FORMAT(available_date,'%d %b %Y') as `available_date_formatted`, 
			(CASE available_date > SYSDATE()
			   WHEN true THEN DATE_FORMAT(available_date,'%d %M %Y')
			   ELSE 'Today'
			 END) as `available_date_formatted`,
			DATEDIFF(curdate(),created_date) as `ad_age`,
			(select DATEDIFF(curdate(),last_login)  from cf_users where cf_users.user_id = cf_wanted.user_id) as `last_login_days`			
		";
		if (isset($_SESSION['u_id'])) { $query .= ", s.active as `active` "; } // Saved ad status
		$query .= "		
			from cf_wanted  
		";
		if (isset($_SESSION['u_id'])) {
			$query .= "
				left join cf_saved_ads as `s` 
				on s.ad_id = wanted_id and 
				s.post_type = 'wanted' and 
				s.user_id = '".$_SESSION['u_id']."' 
			";
		}
		$query .= "where wanted_id = '".$id."'";
		$debug = debugEvent("Details for wanted ad query:",$query);
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		$ad = mysqli_fetch_assoc($result);
	}
	
	$query = "select * from cf_photos where ad_id = '".$id."' and post_type = '".$post_type."' order by photo_sort asc;";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$num_photos = mysqli_num_rows($result); // This is used to determine the height of the Google ad strip
	if (!mysqli_num_rows($result)) {
		$photos .= '<p align="center" class="mt10 mb0"><img src="images/icon-polaroids.gif" alt="Photos for this ad" /></p>'."\n";
		$photos .= '<p class="grey" align="center">'.stripslashes($ad['contact_name']).' has not uploaded any photos yet</p>'; 
	} else {
		//$photos = '<p>Click on any thumbnail for a larger image:</p>';
		$photos = '';
		while($row = mysqli_fetch_assoc($result)) {
			// The image must have a max height of 90px and must fit on a 120 * 90 area
			list($w,$h) = getImgRatio("images/photos/".$row['photo_filename'],"",90,120,90);
			$photos .= '<div class="photoContainer mb10">';
			// AUto-open a lightbox photo if user clicked on a small thumbnail on display.php
			if ($_GET['photo_id'] == $row['photo_id']) {
				$open_photo_after_load = '	Slimbox.open("images/photos/'.$row['photo_filename'].'","'.($row['caption']? $row['caption']:' ').'");';
			}
			$photos .= '<a href="/images/photos/'.$row['photo_filename'].'" rel="lightbox[gallery]" title="'.($row['caption']? $row['caption']:' ').'">';
			$photos .= '<img src="thumbnailer.php?img=images/photos/'.$row['photo_filename'].'&w='.$w.'&h='.$h.'" border="0"/>';
			$photos .= '</a>';
			// The caption
			if (trim($row['caption'])) {
				$photos .= '<div>'.$row['caption'].'</div>';
			}
			$photos .= '</div>'."\n";
		}
	}	

   	function createDisplayEmails($ad,$post_type) {	
    // Create ad reply summaries	
	$query = "
			select DATE_FORMAT(e.reply_date,'%d %b, %Y at %H:%i') as `reply_date`,
			       e.message,
				   reply_id,
				   first_name,
				   from_user_id
			from cf_email_replies as `e`, 
				 cf_users as `u_from`,
				 cf_".$post_type."_all as `ad`
			where ((e.from_user_id = '".$_SESSION['u_id']."'
					    and e.from_user_id != ad.user_id						
						 )
						or
						 (e.from_user_id = '".$_SESSION['u_id']."'
					 	  and e.to_user_id = '".$_SESSION['u_id']."'
						  and e.from_user_id = ad.user_id
						))
			and   ad.".$post_type."_id = e.to_ad_id
			and   e.to_post_type = '".$post_type."'
			and   e.to_ad_id   = '".$ad[$post_type.'_id']."'
			and   u_from.user_id = e.from_user_id
			and   e.sender_deleted = 0			
			order by e.reply_date desc;	
		";
		
		$debug .= debugEvent("Email replies query",$query);
		$result = mysqli_query($GLOBALS['mysql_conn'], $query);
		if (mysqli_num_rows($result)) {
 		  $o .= 'Your replies are visible only to you and are shown to help you track the adverts you reply to.'.'<br /><br />'."\n";										

  		  while($reply = mysqli_fetch_assoc($result)) {
  		    $o .= '<tr class="even">'."\n";
	        $o .= '<td style="padding-left:10px;padding-right:10px;padding-top:10px;padding-bottom:10px;">'."\n";
					$o .= '<strong>Sent: </strong>'.$reply['reply_date'].'<br />'."\n";					
          $o .= '<strong>Your message to '.stripslashes(trim($ad['contact_name'])).':</strong>'.'<br />'."\n";		
       //     $o .= '<p class="mt5">'.clickable_link(nl2br(stripslashes($reply['message']))).'</p>'."\n";		
          $o .= '<p class="mt5">'.makeClickableLinks(nl2br(stripslashes($reply['message']))).'</p>'."\n";								
          $o .= '<strong>Adverts by '.trim($reply['first_name']).' currently showing on Christian Flatshare:</strong><br />'."\n";	
			$adsSummary = createSummaryForAllAds($reply['from_user_id'], FALSE);
			if (!$adsSummary) { 
			  $o .= 'No adverts showing.'.'<br />'."\n";
		    } else {
              $o .= '<strong>'.$adsSummary.'</strong>'; 
			}		
	        $o .= '</td>'."\n";
  		    $o .= '<tr class="odd" style="height:10px;">'."\n";									
	        $o .= '<td></td>'."\n";			
		    $o .= '</tr>'."\n";
		} // WHILE email loop end
	} else { 
	      if (!$_SESSION['u_id']) {
		  // Not logged in 
		  $class = "odd";
  		    $o .= '<tr class="'.$class.'">'."\n";
	        $o .= '<td style="padding-left:0px;padding-right:5px;padding-top:0px;padding-bottom:5px;">'."\n";
			$o .= 'Login to see your replies to this ad shown here.<br />'."\n";					
 		    $o .= 'Your replies are visible only to you and are shown here to help you track the adverts you respond to.'.'<br />'."\n";			
		    $o .= '</td>'."\n";
		    $o .= '</tr>'."\n";
		    $class = ($class == "even")? "odd":"even";		  
		  
		  
		  } else {
		  $class = "odd";
  		    $o .= '<tr class="'.$class.'">'."\n";
	        $o .= '<td style="padding-left:0px;padding-right:5px;padding-top:0px;padding-bottom:5px;">'."\n";
			$o .= 'You have not replied to this ad.'."\n";					
		    $o .= '</td>'."\n";
		    $o .= '</tr>'."\n";
		    $class = ($class == "even")? "odd":"even";
		  }
	} // IF results
	
	return $o;
    } // createDisplayEmails
	
	
	// Update the time_viewed field
	$query = "update cf_".$post_type." set times_viewed = (times_viewed + 1) where ".$post_type."_id = '".$id."';";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	
	$terms = getTermsArray("minimum");
		
?>


<div id="canvas">
	<div id="content">
		<div class="" id="mainContent">
		
		
	  <?php if ($ad['suspended']) { ?>
		<h1>This advert is suspended</h1>
		<p>This advert has been suspended by its owner.<br />Please try visiting it again at a later date.</p>
		<br />
		<br />
		
		<?php } else if (!$ad || $ad['published'] == 2) { ?>
		<div style="padding:60px;">
		  <h1 align="center">Thank you for visiting Christian Flatshare</h1>
		  <h2 align="center">Unfortunately this advert is no longer available... </h2>
		  <p align="center"><a href="index.php">Return to the home page</a></p>
		  </div>
		<?php } else { ?>		
			<div style="padding:30px; background-color:#F4FEEB; border:1px solid #009900;" class="mb20">
			<h1 align="center" class="green mt0">Your Christian Flatshare advert has been posted successfully!</h1>
			<h2 align="center">Please review your advert to check it is correct.</h2>
			</div>
 
			<?php if ($post_type == "offered") { ?>

				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="mb10">
					<tr>
						<td><h1 class="m0"><a name="detail"></a>Offered ad details</h1></td>
						<td align="right">
							<?php 			
							// Next and previous functionality
							$debug .= debugEvent("$_SESSION variable 'result_set':",print_r($_SESSION['result_set'],true));
							echo nextPreviousAd($post_type, $id);		
							$debug .= debugEvent("Referer:",print_r($_SERVER,true)); 
							?>						</td>
					</tr>
				</table>
				<?php print createSummaryV2($ad,"offered","odd mb10",FALSE,FALSE,TRUE)?>
				
				<!-- Contact name,phone number, member login and ad age-->
				<?php	
					if ($ad['last_login_days']==0) {
					$last_logged_in = 'logged in today';
					} else if ($ad['last_login_days']==1) {
					$last_logged_in = 'logged in yesterday';
					} else {
					$last_logged_in = 'last logged in '.$ad['last_login_days'].' days ago';	
					}	?>
								
				<div style="float:left; width:700px;">				
  <!--		<p class="mt0"><span class="style1">Contact: <a href="reply.php?offered_id=<?php print $id?>"><?php print trim(stripslashes($ad['contact_name']))?></a></span><span class="style1"><?php print ($ad['contact_phone']?", ".$ad['contact_phone']:" ")?></span> -->
				  
				  <?php print $res?>
				  
				  <p class="mt0"><span class="style1">Contact: <a href="reply.php?offered_id=<?php print $id?>"><?php print trim(stripslashes($ad['contact_name']))?></a></span><span class="style1"><?php if ($ad['contact_phone']&&isset($_SESSION['u_id']))
				        { echo ", ".$ad['contact_phone']."</span>"; }
								else if ($ad['contact_phone']) 
								{ echo '</span><span class="grey">&nbsp;&nbsp;- please <a href="login.php">login</a> to see contact details</span>'; } 
								else 
								{ echo '</span>'; }
				?>
				    
				    
			    <br /><span class="grey">(<?php print stripslashes($ad['contact_name'])?> <?php print $last_logged_in?>)</span></p>
				  
				<!--
				<div class="fieldSet">
					<div class="fieldSetTitle">THE ACCOMMODATION</div>
				-->
				  <div class="box_light_grey mb10">
				    <div class="tr"><span class="l"></span><span class="r"></span></div>
				  <div class="mr">
				    
				    <h2 class="mt0">The Accommodation</h2>
					  <p><strong><?php print stripslashes($ad['street_name'])?>, <?php print $ad['town']?> (<?php print trim(substr($ad['postcode'],0,-3))?>)</strong></p>
					  <table cellpadding="0" cellspacing="0" border="0" class="mb10">
					    <tr>
					      <td width="140">Bedrooms available:</td>
							  <td width="180"><strong><?php print createBedroomSummary($ad)?></strong></td>
							  <td width="140">Price:</td>
							  <td>
							    <?php
								if ($ad['accommodation_type'] == "whole place" && $ad['room_letting'] == 1) {
									echo '<strong>&pound;'.$ad['price_pcm'].' Whole '.ucwords($ad['building_type']).' </strong>'."\n";
								} elseif ($ad['accommodation_type'] == "whole place" && $ad['room_letting'] == 0) {									
									echo '<strong>&pound;'.$ad['price_pcm'].' Whole '.ucwords($ad['building_type']).'</strong>'."\n";
								} else {
									echo '<strong>&pound;'.$ad['price_pcm'].' per bedroom</strong>'."\n";
								}	
							?>						    </td>
						  </tr>
					    <tr>
					      <td width="140">Total number of bedrooms:</td>
							  <td width="180"><strong><?php print $ad['bedrooms_total']?></strong></td>
							  <td width="140">Deposit required:</td>
							  <td><strong><?php print ($ad['deposit_required']? '&pound;'.$ad['deposit_required'] : 'None')?></strong></td>
						  </tr>
					    <tr>
					      <td width="140">Building type:</td>
							  <td width="180"><strong>
							    <?php print ucwords($ad['building_type'])?>
						    </strong></td>
							  <td width="140">Price includes:</td>
							  <td>
							    
							    <table border="0" cellpadding="0" cellspacing="5">
							      <tr>
							        <td><?php print ($ad['incl_council_tax']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
										  <td <?php print ($ad['incl_council_tax']? '':' class="grey"')?>>Council Tax</td>
										  <td><?php print ($ad['incl_utilities']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
										  <td <?php print ($ad['incl_utilities']? '':' class="grey"')?>>Bills</td>
									  </tr>
						      </table>									
							  <!--<strong>
								<?php
	
								if (!$ad['incl_utilities'] && !$ad['incl_council_tax']) {
									echo "-";
								} else {
									if ($ad['incl_utilities']) { $t .= "utilities, "; }
									if ($ad['incl_council_tax']) { $t .= "council tax, "; }
									if ($t) { $t = substr($t,0,-2); } // Snip last space and comma
									echo $t;
								}								
								?>
								</strong>-->						</td>
						  </tr>
					    
					    <tr>
					      <td width="140">Furnishings:</td>
							  <td width="180"><strong><?php print ($ad['furnished']? 'Furnished' : 'Unfurnished')?></strong></td>
			  <!--				<td width="140"><?php print ($ad['accommodation_type'] == "whole place"? 'Indicative monthly bills:' : 'Indicative share of bills:')?></td> 	Bills are no longer included for Whole Places -->
					      <td width="140">
					        <?php 
							if ($ad['offered_id'] > 5416) {	
									// from ad 5416 ave bills is CT for whole place ads
							 		if ($ad['accommodation_type'] == "whole place") { 
											echo 'Council tax:';
									} elseif ($ad['incl_council_tax'] == 1 && $ad['incl_utilities'] == 1) {
										echo 'Bills and council tax share:';
									} elseif ($ad['incl_council_tax'] == 1 && $ad['incl_utilities'] == 0) {
										echo 'Indicative share of bills:';									
									} elseif ($ad['incl_council_tax'] == 0 && $ad['incl_utilities'] == 1) {
										echo 'Share of council tax:';																	
									} else {
										echo 'Bills and council tax share:';																								
									}
							} else {
								if ($ad['accommodation_type'] == "whole place") { 
									echo 'Indicative monthly bills:';
									} elseif ($ad['incl_council_tax'] == 1 && $ad['incl_utilities'] == 1) {
										echo 'Bills and council tax share:';
									} elseif ($ad['incl_council_tax'] == 1 && $ad['incl_utilities'] == 0) {
										echo 'Indicative share of bills:';									
									} elseif ($ad['incl_council_tax'] == 0 && $ad['incl_utilities'] == 1) {
										echo 'Share of council tax:';																	
									} else {
										echo 'Bills and council tax share:';																								
									}
							 } ?>				        </td>		
							  <td>
							    <?php
								if ($ad['accommodation_type'] != "whole place") {
									if ($ad['incl_utilities'] && $ad['incl_council_tax'] ) {
										echo '<strong>Bills and CT included</strong>';								
									}	elseif ($ad['average_bills'] > 0) {
										if ($ad['bedrooms_available'] > 1) { 
											echo '<strong>&pound;'.$ad['average_bills'].' a month, per bedroom</strong>';										
										} else {
											echo '<strong>&pound;'.$ad['average_bills'].' a month</strong>';										
										}
									}	else {
									  echo '<span class="grey">No amount given for bills</span>';
									}
								} elseif ($ad['accommodation_type'] == "whole place") {
									// figure represents CT
									if ($ad['offered_id'] > 5416) {	
										if ($ad['average_bills'] == 0 && !$ad['incl_council_tax']) {
											echo '<span class="grey">No amount given for council tax</span>';
										} elseif ($ad['incl_council_tax'] && $ad['incl_utilities']) {
											echo '<strong>CT and bills included</strong>';
										} elseif ($ad['incl_council_tax']) {										
											echo '<strong>Council tax included</strong>';
										} else {
											echo '<strong>&pound;'.$ad['average_bills'].'</strong>';
										}
									} else { // figure represnts bills
										if ($ad['average_bills'] == 0) {
											echo '<span class="grey">No amount given for bills</span>';
										} else {
											echo '<strong>&pound;'.$ad['average_bills'].'</strong>';										
										}
									}
								}
								?>						    </td>
						  </tr>
				    </table>
					  <table cellpadding="0" cellspacing="0" border="0" class="mb10">
					    <tr>
					      <td width="140">Date available:</td>
							  <td width="180">
							    <strong>
						      <?php print $ad['available_date_formatted']?><br />
						      </strong>						    </td>
							  <td width="140">Parking available:</td>
							  <td><strong>
							    <?php print $ad['parking']?>
						    </strong></td>
						  </tr>
					    <tr>
					      <td width="140">Minimum term::</td>
							  <td width="180">
							    <?php
								if ($ad['min_term']) {
									echo '<strong>'.$terms[$ad['min_term']].'</strong>';
								} else {
									echo '<span class="grey">None</span>';
								}						
							?>						    </td>
							  <td width="140">
							    <?php if ($ad['accommodation_type'] == "whole place") { ?>
							    Room letting:
							    <?php } ?>						    </td>
							  <td>
							    <?php if ($ad['accommodation_type'] == "whole place") { ?>							
							    <?php if ($ad['room_letting'] == 1) { ?>
							    <strong>Yes.</strong> Bedrooms may be let
							    <?php } else { ?>
							    <span class="grey">No, not for individual bedroom letting</span>
							    <?php } ?>		
							    <?php } ?>						    </td>
						  </tr>
					    <tr>
					      <td width="140">Maximum term:</td>
							  <td width="180">
							    <?php
								if ($ad['max_term'] == "999") {
									echo '<span class="grey">None</span>';
								} else {
									echo '<strong>'.$terms[$ad['max_term']].'</strong>';
									if ($ad['max_term'] <= 12) {
									echo ' <strong>(short-term)</strong>';
									}					
								}												
							?>						    </td>
							  <td width="140">&nbsp;</td>
							  <td>
							    <?php if ($ad['room_letting'] == 1) { ?>
							    individually, please enquire.
							    <?php } ?>						    </td>
						  </tr>
				    </table>
					  <table border="0" cellpadding="0" cellspacing="5" class="mb10">
					    <tr>
					      <td><?php print ($ad['shared_lounge_area']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td width="125" <?php print ($ad['shared_lounge_area']? '':' class="grey"')?>>Shared lounge area</td>
							  
							<td><?php print ($ad['bicycle_store']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td width="125" <?php print ($ad['bicycle_store']? '':' class="grey"')?>>Bicycle store </td>
							  
							
							  <td><?php print ($ad['washing_machine']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td width="125" <?php print ($ad['washing_machine']? '':' class="grey"')?>>Washing machine </td>
						  </tr>
					    <tr>
					      <td><?php print ($ad['garden_or_terrace']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td width="125" <?php print ($ad['garden_or_terrace']? '':' class="grey"')?>>A garden / roof terrace </td>
  
							<td><?php print ($ad['shared_broadband']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td width="125" <?php print ($ad['shared_broadband']? '':' class="grey"')?>>Shared broadband </td>													
							  
							<td><?php print ($ad['dish_washer']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td width="125" <?php print ($ad['dish_washer']? '':' class="grey"')?>>Dish washer </td>
						  </tr>
					    <tr>
					      <td><?php print ($ad['ensuite_bathroom']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td width="125" <?php print ($ad['ensuite_bathroom']? '':' class="grey"')?>>Ensuite bathroom </td>
							  
							<td><?php print ($ad['cleaner']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td width="125" <?php print ($ad['cleaner']? '':' class="grey"')?>>A cleaner that visits </td>	
							  
							<td><?php print ($ad['tumble_dryer']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td width="125" <?php print ($ad['tumble_dryer']? '':' class="grey"')?>>Tumble dryer </td>
						  </tr>
					    <!-- <tr>
							<td><?php print ($ad['central_heating']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="125"><?php print ($ad['central_heating']? '':' class="grey"')?>Central heating </td>

							<td>&nbsp;</td>
							<td width="125">&nbsp;</td>
							<td>&nbsp;</td>
							<td width="125">&nbsp;</td>
						</tr> -->
				    </table>
  
					<?php if ($ad['accommodation_description']) { ?>
				    <div id="displayAccommodationDetails">
				      <p class="mt0"><strong>More about the accommodation:</strong></p>
					  <!--	<p class="mb0"><?php print nl2br(clickable_link(stripslashes($ad['accommodation_description'])))?></p> -->
				      <p class="mb0"><?php print nl2br(makeClickableLinks(stripslashes(trim($ad['accommodation_description']))))?></p>
					  </div>	
					  <?php } ?>
				    </div>
				  <div class="br"><span class="l"></span><span class="r"></span></div>
				  </div>
				  
				<?php if (strpos($propertyDescription,"Whole") === FALSE) {	// Only show the CURRENT HOUSEHOLD tab if the ad is NOT about a whole flat or whole house ?>
				  <div class="box_light_grey mb10">
				    <div class="tr"><span class="l"></span><span class="r"></span></div>
				  <div class="mr">
				    <h2 class="mt0">The Household</h2>
					  <table cellpadding="0" cellspacing="0" border="0" class="mb10">
					    <tr>
					      <td width="160">Members:</td>
							  <td><strong><?php
							$t = "";
							if ($ad['current_num_males']) {
								$t = $ad['current_num_males'].' male';
								if ($ad['current_num_males'] > 1) { $t .= 's'; }
								$t .= ' and ';
							}
							if ($ad['current_num_females']) {
								$t .= $ad['current_num_females'].' female';
								if ($ad['current_num_females'] > 1) { $t .= 's'; }
							} else {
								$t = substr($t,0,-5); // Snip the ' and ';
							}
							echo $t;
							?></strong></td>
						  </tr>
					    <tr>
					      <td>Age:</td>
							  <td><strong><?php
							if (!$ad['current_min_age'] && !$ad['current_max_age']) {
								echo '-';
							} else if (!$ad['current_min_age']) {
								echo "Up to ".$ad['current_max_age']." years old";
							} else if (!$ad['current_max_age']) {
								echo "Not younger than ".$ad['current_min_age']." years old";
							} else {
								if ($ad['current_min_age'] == $ad['current_max_age']) {
									echo $ad['current_min_age']." years old";
								} else {
								echo $ad['current_min_age']." to ".$ad['current_max_age']." years old";
							}
							}
							?></strong></td>
						  </tr>
					    <tr>
					      <td>Occupation:</td>
							  <td><strong>
							    <?php if ($ad['current_num_males'] + $ad['current_num_females'] > 1) {
											$current_occupation = $ad['current_occupation'];
										} else {
											// Remove the trailing S, for the singular
										  $current_occupation = substr($ad['current_occupation'], 0, -1);
											if ($ad['current_occupation'] == "Students (<22yrs)") { $current_occupation = "Student (<22yrs)"; }											
										}
								?>
							    <?php print ucwords($current_occupation)?>
						    </strong></td>
						  </tr>
				    </table>
					  
					<table border="0" cellpadding="0" cellspacing="5" class="mb10">
					  <tr>
					    <td><?php print ($ad['owner_lives_in']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td<?php print ($ad['owner_lives_in']? '':' class="grey"')?>>The owner is a household member</td>
						  </tr>
					  <tr>
					    <td><?php print ($ad['current_is_couple']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td<?php print ($ad['current_is_couple']? '':' class="grey"')?>>The household has a married couple</td>
						  </tr>
					  <tr>
					    <td><?php print ($ad['current_is_family']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td<?php print ($ad['current_is_family']? '':' class="grey"')?>>The household is a family with children</td>						
						  </tr>
					  </table>
					  
					<table border="0" cellpadding="0" cellspacing="0" class="mb10">
					  <tr>
					    <td width="160">Church attended:</td>
							  <td><strong><?php print stripslashes($ad['church_attended'])?></strong></td>
						  </tr>
					  <?php if ($ad['church_url']) { ?>
					  <tr>
					    <td width="160">Our church website:</td>
					  <!--		<td><strong><?php print nl2br(clickable_link((" ".stripslashes($ad['church_url']))))?></strong></td> -->
					    <td><strong><?php print nl2br(makeClickableLinks((" ".stripslashes($ad['church_url']))))?></strong></td>							
						  </tr>
					  <?php } ?>
					  </table>
					  
			
					  <?php if ($ad['household_description']) { ?>
				    <div id="displayHouseholdDetails">
				      <p class="mt0"><strong>More about the household:</strong></p>
						  <p class="mb0"><?php print nl2br(makeClickableLinks(stripslashes(trim($ad['household_description']))))?></p>
					  <!--	<p class="mb0"><?php print nl2br(clickable_link(stripslashes($ad['household_description'])))?></p>		-->
			      </div>
					  <?php } ?>
				    </div>
				  <div class="br"><span class="l"></span><span class="r"></span></div>
				  </div>
				  <?php } ?>
				  
				  <div class="box_light_grey mb10">
				    <div class="tr"><span class="l"></span><span class="r"></span></div>
				  <div class="mr">
				    
				    <h2 class="mt0">The Accommodation Would Suit</h2>
					  <table cellpadding="0" cellspacing="0" border="0" class="mb10">
					    <tr>
					      <td width="160">Sex:</td>
							  <td><strong><?php
							$isPlural = ($ad['bedrooms_available'] > 1)? TRUE:FALSE;
							switch($ad['suit_gender']) {
								case "Male(s)": $toEcho .= ($isPlural)? "Males":"Male"; break;
								case "Female(s)": $toEcho .= ($isPlural)? "Females":"Female"; break;
								case "Mixed": $toEcho .= ($isPlural)? "Males or females":"Male or female"; break;
							}
							echo $toEcho;						
							?></strong></td>
						  </tr>
					    <tr>
					      <td>Age:</td>
							  <td><strong><?php
							if ($ad['suit_min_age'] && $ad['suit_max_age']) { // If we have minimum or maximum ages defined
								echo "Approx. ".$ad['suit_min_age']."-".$ad['suit_max_age']." years";
							} else if ($ad['suit_min_age']) { // only minimum age defined
								echo "Preferably over ".$ad['suit_min_age']." years";
							} else if ($ad['suit_max_age']) { // only maximum age defined
								echo "Preferably under ".$ad['suit_max_age']." years";
							} else {
								echo "Any age";
							}						
							?></strong></td>
						  </tr>
					    <tr>
					      <td>Occupation:</td>
							  <td><strong><?php
							$t = "";
							$flag = FALSE;
							if ($ad['suit_student']) { // If ad suits students
								$t .= "Student";
								$t .= ($isPlural)? "s, ":", ";
								$flag = TRUE;
							}
							if ($ad['suit_mature_student']) { // If ad suits mature students
								$t .= "Mature student";
								$t .= ($isPlural)? "s, ":", ";
								$flag = TRUE;
							}
							if ($ad['suit_professional']) { // If ad suits professionals
								$t .= "Professional";
								$t .= ($isPlural)? "s, ":", ";
								$flag = TRUE;			
							}
							// If flag is still false then no occupation was selected
							if (!$flag) { $t .= "Any occupation"; }
							if (substr($t,-2,2) == ", ") { $t = substr($t,0,-2); } // Snip last comma and space (if there) 
							echo $t;						
							?></strong></td>
						  </tr>
				    </table>
					  
					<table border="0" cellpadding="0" cellspacing="5" class="mb10">
					  <tr>
					    <td><?php print ($ad['suit_married_couple']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td<?php print ($ad['suit_married_couple']? '':' class="grey"')?>>Would suit a married couple</td>
						  </tr>
					  <tr>
					    <td><?php print ($ad['suit_family']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td<?php print ($ad['suit_family']? '':' class="grey"')?>>Would suit a family with children</td>
						  </tr>
					  <?php if($ad['church_reference'] > 0){?>
					  <tr>
					    <td><?php print ($ad['church_reference']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							  <td<?php print ($ad['church_reference']? '':' class="grey"')?>>Would suit someone who, if asked, could provide a recommendation from a current or previous church</td>
						  </tr>
					  <?php } ?>						
					  </table>
				</div>
				  <div class="br"><span class="l"></span><span class="r"></span></div>
				  </div>
				  
				<table cellpadding="0" cellspacing="0" width="100%">
				  <tr><td>						
				    <!-- Advert responses -->
				    <div class="box_light_grey mb10" style="float:left; width:550px;">
				      <div class="tr"><span class="l"></span><span class="r"></span></div>
				    <div class="mr">
				      <h2 class="mt0 mb5">Your replies to this ad</h2>
				    <table border="0" cellpadding="0" cellspacing="0" width="100%">
				      <tr><td>
				        <?php
					   echo createDisplayEmails($ad,$post_type);					
					?>
				        </td></tr>
				      </table>
				  </div>
				  <div class="br"><span class="l"></span><span class="r"></span></div>
				  </div>					
				  
								
				  <td align="right" valign="top">
				    <!-- AddThis Bookmark Button BEGIN -->
				    <script type="text/javascript">
					addthis_url    = location.href;   
					addthis_title  = document.title;  
					addthis_pub    = 'Christian Flatshare';     
					</script><script type="text/javascript" src="http://s7.addthis.com/js/addthis_widget.php?v=12" ></script>
			    <!-- AddThis Bookmark Button END -->				    </td></tr>
				  
				  <tr><td style="padding-top:5px;">
				    <!-- Advert age -->
				    <span class="grey mt10">
				      <?php
					if ($ad['ad_age']==0) {
						$ad_age .= '(advert: created today, ';
					} else if ($ad['ad_age']==1) {
						$ad_age .= '(advert: created yesteday, ';
					} elseif ($ad['ad_age']>15) {
					      $ad_age .= '(advert: more than 15 days old, ';
						} else {
						  $ad_age .= '(advert: '.$ad['ad_age'].' days old, ';	
					} 
					echo $ad_age;
				?>
				      <!-- Times views  -->
				      <?php 
					if ($ad['times_viewed']==0) {
						$times_viewed  .= 'viewed once)';
					} else if ($ad['times_viewed']==1) {
						$times_viewed  .= ' viewed once)';
					} else {
						$times_viewed .= ' viewed '.$ad['times_viewed'].' times)';	
					}
					echo $times_viewed;
				?>	
				      <br /><br />
				      Offered adverts are suspended when owners do not login for 30 days, or when <br />
				      adverts are 10 days older than their  available date. Owners are notified prior. <br />
				      <br />   
				      </span>						
				    </td>
				  </tr>
				  </table>
				</div>
				<div class="cs" style="width:21px; height: 600px;"><!---->
				</div>
				<div style="float:left; width:129px;">
					<h2 class="mt0">Photos:</h2>
					<?php print $photos?>
					
					<div class="clear"><!----></div>
					<?php print loadBanner("120",$ad['postcode'],FALSE, $num_photos)?>
				</div>
				<div class="clear" style="height:0px;"><!---->
				</div>
			<?php } else if ($post_type == "wanted") { ?> 
			
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="mb10">
					<tr>
						<td><h1 class="m0"><a name="detail"></a>Wanted ad details</h1></td>
						<td align="right">
							<?php 			
							// Next and previous functionality
							$debug .= debugEvent("$_SESSION variable 'result_set':",print_r($_SESSION['result_set'],true));
							echo nextPreviousAd($post_type, $id);		
							$debug .= debugEvent("REferer:",print_r($_SERVER,true)); 
							?>						</td>
					</tr>
				</table>
				<?php print createSummaryV2($ad,"wanted","odd mb10",FALSE,FALSE,TRUE)?>
				
				<!-- Contact name,phone number and member login -->
				<?php	
					if ($ad['last_login_days']==0) {
					$last_logged_in = 'logged in today';
					} else if ($ad['last_login_days']==1) {
					$last_logged_in = 'logged in yesterday';
					} else {
					$last_logged_in =  'last logged in '.$ad['last_login_days'].' days ago';	
					}	?>	
				
				<div class="cl" style="width:700px;">
				<?php print $res?>
				<p class="mt0"><span class="style1">Contact: <a href="reply.php?wanted_id=<?php print $id?>"><?php print trim(stripslashes($ad['contact_name']))?></a></span><span class="style1"><?php if ($ad['contact_phone']&&isset($_SESSION['u_id']))
				        { echo ", ".$ad['contact_phone']."</span>"; }
								else if ($ad['contact_phone']) 
								{ echo '</span><span class="grey">&nbsp;&nbsp;- please <a href="login.php">login</a> to see contact details</span>'; }
								else 
								{ echo '</span>'; }								
				?>
				<br /><span class="grey">(<?php print stripslashes($ad['contact_name'])?> <?php print $last_logged_in?>)</span></p>
				<div class="box_light_grey mb10">
				<div class="tr"><span class="l"></span><span class="r"></span></div>
				<div class="mr">

					<h2 class="mt0">The Accommodation Wanted</h2>
 				  <table cellpadding="0" cellspacing="0" border="0" class="mb10">
						<tr>
							<td width="160">Location</td>
							<td><strong><?php
							echo 'Within '.$ad['distance_from_postcode'].' mile';
							if ($ad['distance_from_postcode'] > 1) { echo 's'; }
							echo ' of '.stripslashes($ad['location']).' ('.$ad['postcode'].')';
							?></strong></td>
						</tr>
						<tr>
							<td>Bedrooms required:</td>
							<td><strong><?php print $ad['bedrooms_required']?></strong></td>
						</tr>
						<tr>
							<td>Price (approx monthly max):</td>
							<td><strong>&pound;<?php print $ad['price_pcm']?> per bedroom</strong></td>
						</tr>
					</table>
					
					<table cellpadding="0" cellspacing="0" border="0" class="mb10">
						<tr>
							<td width="160">Required from:</td>
							<td><strong>
							<?php print $ad['available_date_formatted'];?>
							</strong></td>
						</tr>
						<tr>
							<td>Minimum term:</td>
							<td><?php
							if ($ad['min_term']) {
								echo '<strong>';
								echo $terms[$ad['min_term']];
								echo '</strong>';
							} else {
								echo '<span class="grey">None</span>';
							}
							?></td>
						</tr>
						<tr>
							<td>Maximum term:</td>
							<td><?php
							if ($ad['max_term'] == "999") {
								echo '<span class="grey">None</span>';
							} else {
								echo '<strong>';
								echo $terms[$ad['max_term']];
								echo '</strong>';
								if ($ad['max_term'] <= 12) {
									echo ' <strong>(short-term)</strong>';
								}
							}						
							?></td>
						</tr>
						<tr>
							<td>Accommodation type:</td>
							<td><strong><?php
							  $t = '';
								if ($ad['accommodation_type_flat_share']) { $t = 'flatshare, '; }
								if ($ad['accommodation_type_room_share']) { $t .= 'room share, '; }								
								if ($ad['accommodation_type_family_share']) { $t .= 'family share, '; }
								if ($ad['accommodation_type_whole_place']) { $t .= 'whole place, '; }
								if ($ad['palup']) { $t .= 'pal-up, '; }
								$t = substr($t,0,-2); // Snip last space & comma
								$t = ucfirst($t);
								echo $t;
							?></strong></td>
						</tr>
						<tr>
							<td>Building type:</td>
							<td><strong><?php
							if ($ad['building_type_flat'] && $ad['building_type_house']) {
								echo "House or flat";
							} else if ($ad['building_type_flat']) {
								echo "Flat";
							} else if ($ad['building_type_house']) {
								echo "House";
							}
							?></strong></td>
						</tr>
					</table>
					
					<p>The accommodation must have:</p>
					<table border="0" cellpadding="0" cellspacing="5">
						<tr>
							<td><?php print ($ad['shared_lounge_area']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['shared_lounge_area']? '':' class="grey"')?>>Shared lounge area</td>
							
							<td><?php print ($ad['washing_machine']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['washing_machine']? '':' class="grey"')?>>Washing machine </td>
							
							<td><?php print ($ad['garden_or_terrace']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['garden_or_terrace']? '':' class="grey"')?>>A garden / roof terrace </td>
						</tr>
						<tr>
							<td><?php print ($ad['bicycle_store']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['bicycle_store']? '':' class="grey"')?>>Bicycle store </td>
							
							<td><?php print ($ad['dish_washer']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['dish_washer']? '':' class="grey"')?>>Dish washer </td>
							
							<td><?php print ($ad['parking']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['parking']? '':' class="grey"')?>>Somewhere to park a car</td>
						</tr>
						<tr>
							<td><?php print ($ad['ensuite_bathroom']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['ensuite_bathroom']? '':' class="grey"')?>>Ensuite bathroom </td>
							
							<td><?php print ($ad['tumble_dryer']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['tumble_dryer']? '':' class="grey"')?>>Tumble dryer </td>
							
							<td></td>
							<!--
							<td><?php print ($ad['central_heating']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td width="165"<?php print ($ad['central_heating']? '':' class="grey"')?>>Central heating </td> 
							-->									
						</tr>
					</table>								
				</div>
				<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>
				
		

				
				<div class="box_light_grey mb10">
				<div class="tr"><span class="l"></span><span class="r"></span></div>
				<div class="mr">
				
					<?php if ($ad['current_num_females'] + $ad['current_num_males'] > 1) { $plural = "s"; } ?>
					<h2 class="mt0">The Accommodation Seeker<?php echo $plural; ?></h2>
					<table cellpadding="0" cellspacing="0" border="0" class="mb10">
						<tr>
							<td width="160">Sex:</td>
							<td><strong><?php
							$t = "";
							if ($ad['current_num_males']) {
								$t = $ad['current_num_males'].' male';
								if ($ad['current_num_males'] > 1) { $t .= 's'; }
								$t .= ' and ';
							}
							if ($ad['current_num_females']) {
								$t .= $ad['current_num_females'].' female';
								if ($ad['current_num_females'] > 1) { $t .= 's'; }
							} else {
								$t = substr($t,0,-5); // Snip the ' and ';
							}
							echo $t;
							?></strong></td>
						</tr>
						<tr>
							<td>Age:</td>
							<td><strong><?php
							if (!$ad['current_min_age'] && !$ad['current_max_age']) {
								echo '-';
							} else if (!$ad['current_min_age']) {
								echo "Under ".$ad['current_max_age']." years old";
							} else if (!$ad['current_max_age']) {
								echo "Over ".$ad['current_min_age']." years old";
							} else {
								if ($ad['current_min_age'] == $ad['current_max_age']) {
									echo $ad['current_min_age']." years old";
								} else {
								echo $ad['current_min_age']." to ".$ad['current_max_age']." years old";
							}
							}
							?></strong></td>
						</tr>
						<tr>
							<td>Occupation:</td>
							<?php if ($ad['current_num_males'] + $ad['current_num_females'] > 1) {
											$current_occupation = $ad['current_occupation'];
										} else {
											// Remove the trailing S, for the singular
										  $current_occupation = substr($ad['current_occupation'], 0, -1);
											if ($ad['current_occupation'] == "Students (<22yrs)") { $current_occupation = "Student (<22yrs)"; }
										}
							?>
							<td><strong><?php print ucwords($current_occupation)?></strong></td>
						</tr>
					</table>
					
					<table border="0" cellpadding="0" cellspacing="5" class="mb10">
						<tr>
							<td><?php print ($ad['current_is_couple']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['current_is_couple']? '':' class="grey"')?>>Are a married couple</td>
						</tr>
						<tr>
							<td><?php print ($ad['current_is_family']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['current_is_family']? '':' class="grey"')?>>Are a family with children</td>						
						</tr>
						<?php if($ad['church_reference'] > 0){?>
						<tr>
							<td><?php print ($ad['church_reference']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['church_reference']? '':' class="grey"')?>>Could provide a recommendation from a previous church if asked to</td>
						</tr>					
						<?php } ?>
					</table>
					
					<?php if ($ad['church_attended']) { ?>
					<table border="0" cellpadding="0" cellspacing="0" class="mb10">
						<tr>
							<td width="160">Church attended:</td>
							<td><strong><?php print stripslashes($ad['church_attended'])?></strong></td>
						</tr>
						<?php if ($ad['church_url']) { ?>
						<tr>
							<td width="160">Our church website:</td>
						<!-- <td><strong><?php print nl2br(clickable_link((" ".stripslashes($ad['church_url']))))?></strong></td> -->
							<td><strong><?php print nl2br(makeClickableLinks((" ".stripslashes($ad['church_url']))))?></strong></td>							
						</tr>
						<?php } ?>
					</table>
					<?php } ?>
				
					<?php if ($ad['accommodation_situation']) { ?>
						<div id="displayHouseholdDetails">
						<p class="mt0"><strong>More about the accommodation seeker<?php echo $plural; ?>:</strong></p>
				<!--		<p class="mb0"><?php print nl2br(clickable_link(stripslashes($ad['accommodation_situation'])))?></p> -->
						<p class="mb0"><?php print nl2br(makeClickableLinks(stripslashes(trim($ad['accommodation_situation']))))?></p>						
					</div>
					<?php } ?>
				</div>
				<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>
				
						<?php if ($ad['accommodation_type_flat_share'] || $ad['accommodation_type_family_share'] || $ad['accommodation_type_room_share']) { ?>
				
				<div class="box_light_grey mb10">
				<div class="tr"><span class="l"></span><span class="r"></span></div>
				<div class="mr">

					<h2 class="mt0">The Preferred Household</h2>
					<table cellpadding="0" cellspacing="0" border="0" class="mb10">
						<tr>
							<td width="160">Max number of members:</td>
							<td><strong><?php print ($ad['shared_adult_members']==4? "Any number":$ad['shared_adult_members'])?></strong></td>
						</tr>
						<tr>
							<td>Sex:</td>
							<td><strong><?php
							$toEcho = "";
							if ($ad['shared_males']) { $toEcho .= "Males, "; }
							if ($ad['shared_females']) { $toEcho .= "Females, "; }
							if ($ad['shared_mixed']) { $toEcho .= "Mixed household, "; }
							// Snip last comma and space
							$toEcho = substr($toEcho,0,-2);
							echo $toEcho;
							?></strong></td>
						</tr>
						<tr>
							<td>Age:</td>
							<td><?php
								if (!$ad['shared_min_age'] && !$ad['shared_max_age']) {
									echo '<strong>';
									echo "Any age";
									echo '</strong>';
								} else {
									echo '<strong>';
									if (!$ad['shared_min_age']) {
										echo "Under ".$ad['shared_max_age']." years old";
									} else if (!$ad['shared_max_age']) {
										echo "Over ".$ad['shared_min_age']." years old";
									} else {
										echo $ad['shared_min_age']." to ".$ad['shared_max_age']." years old";
									}
									echo '</strong>';
								}
							?></td>
						</tr>
						<tr>
							<td>Occupation:</td>
							<td><?php
								$isPlural = ($ad['shared_adult_members'] > 1)? TRUE : FALSE;
								if ((!$ad['shared_student'] && !$ad['shared_mature_student'] && !$ad['shared_professional']) ||
								    ($ad['shared_student'] && $ad['shared_mature_student'] && $ad['shared_professional'])) {
									echo '<strong>Any occupation</strong>';
								} else {
									echo '<strong>';
									$t = null;
									if ($ad['shared_student']) {
										$t .= 'Student';
										if ($isPlural) { $t .= 's'; }
										$t .= ' (<22yrs), ';
									}
									if ($ad['shared_mature_student']) {
										$t .= 'Mature student';
										if ($isPlural) { $t .= 's'; }
										$t .= ', ';
									}
									if ($ad['shared_professional']) {
										$t .= 'Professional';
										if ($isPlural) { $t .= 's'; }
										$t .= ', ';
									}
									$t = substr($t,0,-2);
									echo $t;
									echo '</strong>';
								}
							?></td>
					</table>
					
					<table border="0" cellpadding="0" cellspacing="5">
						<tr>
							<td><?php print ($ad['shared_owner_lives_in']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['shared_owner_lives_in']? '':' class="grey"')?>>The owner could be a member</td>
						</tr>
						<tr>
							<td><?php print ($ad['shared_married_couple']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['shared_married_couple']? '':' class="grey"')?>>It could have a married couple</td>
						</tr>
						<tr>
							<td><?php print ($ad['shared_family']? '<img src="images/icon-feature-check.gif" alt="Available" width="11" height="12" />':'<img src="images/icon-feature-cross.gif" alt="Not available" width="10" height="12" />')?></td>
							<td<?php print ($ad['shared_family']? '':' class="grey"')?>>It could be a family with children</td>
						</tr>
					</table>
				</div>
				<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>
				
				<?php } ?>
				
				<table cellpadding="0" cellspacing="0" width="100%">
				<tr><td>						
				  <!-- Advert responses -->
				  <div class="box_light_grey mb10" style="float:left; width:550px;">
				  <div class="tr"><span class="l"></span><span class="r"></span></div>
				  <div class="mr">
				  <h2 class="mt0 mb5">Your replies to this ad</h2>
				   <table border="0" cellpadding="0" cellspacing="0" width="100%">
				  <tr><td>
					<?php
					   echo createDisplayEmails($ad,$post_type);					
					?>
				  </td></tr>
				  </table>
				</div>
				<div class="br"><span class="l"></span><span class="r"></span></div>
				</div>					
				
								
				<td align="right" valign="top">
					<!-- AddThis Bookmark Button BEGIN -->
					<script type="text/javascript">
					addthis_url    = location.href;   
					addthis_title  = document.title;  
					addthis_pub    = 'Christian Flatshare';     
					</script><script type="text/javascript" src="http://s7.addthis.com/js/addthis_widget.php?v=12" ></script>
					<!-- AddThis Bookmark Button END -->				</td></tr>

				<tr><td style="padding-top:5px;">
				<!-- Advert age -->
				<span class="grey mt10">
				<?php
					if ($ad['ad_age']==0) {
						$ad_age .= '(advert: created today, ';
					} else if ($ad['ad_age']==1) {
						$ad_age .= '(advert: created yesteday, ';
					} elseif ($ad['ad_age']>15) {
					      $ad_age .= '(advert: more than 15 days old, ';
						} else {
						  $ad_age .= '(advert: '.$ad['ad_age'].' days old, ';	
					} 
					echo $ad_age;
				?>
				<!-- Times views  -->
				<?php 
					if ($ad['times_viewed']==0) {
						$times_viewed  .= 'viewed once)';
					} else if ($ad['times_viewed']==1) {
						$times_viewed  .= ' viewed once)';
					} else {
						$times_viewed .= ' viewed '.$ad['times_viewed'].' times)';	
					}
					echo $times_viewed;
				?>	
				<br /><br />
				Wanted adverts are suspended when owners do not login for 30 days, or when <br />
				adverts are 10 days older than their &quot;wanted from&quot; date. Owners are notified prior. <br /><br />   
				</span>						
				</td></tr>
				</table>		
				</div>
				<div class="cs" style="width:21px; height: 600px;"><!---->
				</div>
				<div class="cr" style="width:129px;">
				<h2 class="mt0">Photos:</h2>
					<?php print $photos?>
					<div class="clear"><!----></div>
					<?php print loadBanner("120",$ad['postcode'],FALSE, $num_photos)?>
				</div>
				<div class="clear" style="height:0px;"><!---->
				</div>
			<?php } ?>
	
			<?php } ?>
		</div>
	</div>
</div>