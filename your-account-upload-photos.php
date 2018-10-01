<?php
session_start();

// Autoloader
require_once 'web/global.php';


require('includes/class.upload.php');		// PEAR upload class

connectToDB();
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:index.php"); exit; }
	
	// Redirect if user is an advertiser
	if ($_SESSION['u_access'] == "advertiser") { header("Location: advertisers.php"); exit; }	
	
	$now = new Date(); // Store current date / time into $now
	
	// Initialise needed variables
	$error = NULL;
	$photos = NULL;
	if (isset($_REQUEST['action'])) { $action = $_REQUEST['action']; } else { $action = NULL; }
	if (isset($_REQUEST['id'])) { $id = $_REQUEST['id']; } else { header("Location: your-account-manage-posts.php"); exit; }
	if (isset($_REQUEST['post_type'])) { $post_type = $_REQUEST['post_type']; } else { header("Location: your-account-manage-posts.php"); exit; }
	
	if ($_POST['deletePhotos']) {
		$action = "delete";
	} else if ($_POST['updateCaptions']) {
		$action = "update";
	}
	
	switch($action) {
		case "upload":
		
			$upload = new http_upload();
			// Set which extensions to allow through
			$file = $upload->getFiles('userfile');
			$file->setValidExtensions(array("jpg","jpeg","gif"),"allow");
			if ($file->isError()) {
				$error['upload'] = $file->getMessage();
			} elseif ($file->isValid()) {
				// First make sure file is not larger than a certain size
				if ($photo_size > 10485760 ) {
					$error['upload'] = "File size must not exceed 10MB";
				} elseif ($file->isMissing()) {
					$error['upload'] = "No file was selected to add";
				} else {
					// Format the filename (replace spaces and special chars)
					$file->setName("uniq","image-");
					// Move the uploaded file
					$dest_name = $file->moveTo("images/photos/");
					// Check if operation was succesfully completed
					if (PEAR::isError($dest_name)) {
						$error['upload'] = ($dest_name->getMessage());
					} else {
						// File was uploaded successfully
						$msg = '<p class="success">Photo was successfully uploaded</p>';
						
						// Reduce the image size to fit to a 640 by 480 box
						ini_set("memory_limit","20M");
						ini_set("max_execution_time",60);
						list($orig_width, $orig_height) = getimagesize("images/photos/".$dest_name);
						list($w,$h) = getImgRatio("images/photos/".$dest_name,"",480,640,480);
						$image_p = imagecreatetruecolor($w, $h);
						$image = imagecreatefromjpeg("images/photos/".$dest_name);
						imagecopyresampled($image_p,$image,0,0,0,0,$w,$h,$orig_width,$orig_height);
						imagejpeg($image_p,"images/photos/".$dest_name,90);
						imagedestroy($image);
						imagedestroy($image_p);
						
						// Associate the uploaded picture with the current image
						$query  = "insert into cf_photos (ad_id,post_type,photo_filename) values (";
						$query .= "'".$id."','".$post_type."','".$dest_name."'";
						$query .= ");";
						$result = mysqli_query($GLOBALS['mysql_conn'], $query);
						
						// Change the permissions of the image to 744
						chmod("images/photos/".$dest_name,0744);
						
					}
				}
			}
			break;
			
		case "delete":
			
			// First, find out all details for the selected images
			$sqlWhere = "";
			foreach($_POST['ad'] as $ad_id) { $sqlWhere .= $ad_id.","; }
			$sqlWhere = substr($sqlWhere,0,-1);
			$query = "select * from cf_photos where photo_id in (".$sqlWhere.");";
			$debug .= debugEvent("Delete query #1",$query);
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			// Now delete all the image files
			while ($row = mysqli_fetch_assoc($result)) {
				@unlink("images/photos/".$row['photo_filename']);
			}
			$query = "delete from cf_photos where photo_id in (".$sqlWhere.");";
			$result = mysqli_query($GLOBALS['mysql_conn'], $query);
			if ($result) {
				$msg = '<p class="success">Photo(s) deleted</p>';
			} else {
				$msg = '<p class="error">There was an error deleting photo(s). Please contact '.TECH_EMAIL.'</p>';
			}
			break;	
			
		case "update":
		
			/*
				The post data will contain something along the lines of:
				[id] => 1622
				[post_type] => offered
				[ad_caption_1204] => (enter caption)
				[ad_caption_1198] => (enter caption)
				[ad_caption_1205] => (enter caption)
			*/
			foreach($_POST as $key => $value) {
				if (preg_match('/^ad_caption_(\d+)$/',$key,$matches) && trim($value) != "(enter caption)") {
					$query = "update cf_photos set caption = '".trim($value)."' where photo_id = '".$matches[1]."'";
					$result = mysqli_query($GLOBALS['mysql_conn'], $query);
				}
			}			
			break;
	
	}
		
	// First, find out if we're dealing with a valid id.
	$query = "select ".$post_type."_id from cf_".$post_type." where ".$post_type."_id = '".$id."';";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	if (!mysqli_num_rows($result)) { header("Location: your-account-manage-posts.php"); exit; }	
		
	// If offered, get accommodation type: house/flat
	if ($post_type == "offered") {
		$query = "select building_type, accommodation_type 
		          from cf_offered where offered_id = '".$id."';";
		$row = mysqli_fetch_row(mysqli_query($GLOBALS['mysql_conn'], $query));
		$building_type = $row[0];
		$accommodation_type = $row[1];		
	}
		
	// Get all photos for this ad
	$query = "select * from cf_photos where ad_id = '".$id."' and post_type = '".$post_type."' order by photo_sort asc;";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	$photoCount = mysqli_num_rows($result);
	if (!$photoCount) {
		$photos = '<p>You have not added any photographs yet</p>';
	} else {
		$photos  = "\n\n";
		$photos .= '<p>You have added '.mysqli_num_rows($result);
		if (mysqli_num_rows($result)!=1) {
		  $photos .= ' photos';
		} else {
		  $photos .= ' photo';
		}
		$photos .= ' to this advert. Use the buttons below to update captions and rotate or delete photos.<br />Click on a photo below to see it enlarged with your caption.</p> ';
		$photos .= '<form name="deletionForm" method="post" action="'.$_SERVER['PHP_SELF'].'">';
		$photos .= '<input type="hidden" name="id" value="'.$id.'" />'."\n";
		$photos .= '<input type="hidden" name="post_type" value="'.$post_type.'" />'."\n";
		while($row = mysqli_fetch_assoc($result)) {
			// The image must have a max height of 90px and must fit on a 120 * 90 area
			list($w,$h) = getImgRatio("images/photos/".$row['photo_filename'],"",90,120,90);
			$photos .= '<div class="uploadPhotoContainer" id="photo_'.$row['photo_id'].'">'."\n";
			$photos .= '<a href="images/photos/'.$row['photo_filename'].'" rel="lightbox[photos]" '.($row['caption']? 'title="'.$row['caption'].'"':'').'>'."\n";
			$photos .= '<img src="thumbnailer.php?img=images/photos/'.$row['photo_filename'].'&w='.$w.'&h='.$h.'&rnd='.rand(1,100000).'" border="0"/>';
			$photos .= '</a>';
			// The caption box
			if ($row['caption']) { $tempValue = $row['caption']; } else { $tempValue = "(enter caption)"; }
			$photos .= '<div class="uploadPhotoCaption"><input type="text" name="ad_caption_'.$row['photo_id'].'" id="ad_caption_'.$row['photo_id'].'" value="'.$tempValue.'" maxlength="120" onfocus="fieldFocus(this.id);" onblur="fieldBlur(this.id);" /></div>'."\n";
			// The selection checkbox
			$photos .= '<div><input type="checkbox" name="ad[]" value="'.$row['photo_id'].'" onclick="return toggle(this);"/></div>'."\n";
			$photos .= '</div>'."\n";	
		}
		$photos .= '<div class="clear" style="height:0px;"><!----></div>'."\n";
		$photos .= '<p class="m0">';
		$photos .= '<input type="submit" name="updateCaptions" value="Save all captions" />&nbsp;';
		$photos .= '<br/></p><br/>Select a photograph from above and use the buttons below to rotate it 90o or delete it:<br/><br/>';
		$photos .= '<input type="submit" name="deletePhotos" value="Delete photo(s)" onclick="return validateDeletion();" />';
		$photos .= '</p>'."\n";
		$photos .= '</form>'."\n\n";
	}
	
	if ($error['upload']) {
		$error['upload'] = '<span class="error">'.$error['upload'].'</span>';
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
	<title>Your ads - Add photos - Christian Flatshare</title>
	<link href="styles/lightbox.css" rel="stylesheet" type="text/css" />
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
	<script language="javascript" type="text/javascript">
		
		function toggle(obj) {
			var id = obj.value;
			if (obj.checked) {
				$('photo_'+id).className = "uploadPhotoContainer selected";
			} else {
				$('photo_'+id).className = "uploadPhotoContainer";
			}
			return true;
		}
		
		function validateDeletion() {
			// Iterate through all the checkboxes of the deletionForm
			var x = document.deletionForm.getElementsByTagName("input");
			var proceed = false;
			for (var i=0;i<x.length;i++) {
				// If at least one checkbox is checked, proceed
				if (x[i].type == "checkbox" && x[i].checked) {
					proceed = true;
				}
			}
			if (!proceed) {
				alert("Please select at least one photo to delete");
				return false;
			} else {
				return confirm("Proceed with deletion?");
			}
		}
		
		function doRotate(direction) {
			if (!direction) { return false; }
			// Iterate through all the checkboxes, ensure only one is checked
			var x = document.deletionForm.getElementsByTagName("input");
			var selectedNumber = 0;
			var selectedCheckbox = null;
			for (var i=0;i<x.length;i++) {
				// If at least one checkbox is checked, proceed
				if (x[i].type == "checkbox" && x[i].checked) {
					selectedNumber++;
					selectedCheckbox = x[i];
				}
			}
			if (!selectedNumber) {
				alert("Please select one photograph to rotate");
			} else if (selectedNumber > 1) {
				alert("Please ensure you have selected only one photograph to rotate");
			} else {
				window.location = 'your-account-rotate-photo.php?photo_id='+selectedCheckbox.value+'&direction='+direction;
			}
		}
		
		function fieldFocus(id) {
			if ($(id).value == "(enter caption)") {
				$(id).value = "";
			}
		}
		
		function fieldBlur(id) {
			$(id).value = trim($(id).value);
			if ($(id).value == "") {
				$(id).value = "(enter caption)";
			}
		}
		function trim(toTrim) {
			while(''+toTrim.charAt(0) == " ") { toTrim = toTrim.substring(1,toTrim.length); }
			while(''+toTrim.charAt(toTrim.length-1) == " ") { toTrim = toTrim.substring(0,toTrim.length-1); }
			return toTrim;
		}
		
	</script>
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
			<h1 class="mt0">Add photos<br />
			</h1>
			<p>
			<?php print $msg?>
			<?php if ($photoCount <= 7) { ?>
		  </p>
 		 
		 <?php if ($post_type == "wanted") { ?>
	      <p class="mb0">Adding photos can help you to get the best response from your advert!<br /><br />A Photo or two of those looking for accommodation can be a good way to help introduce the accommodation seeker(s), and can be fun too... maybe your holiday snap??<br /><br /></p>
		  <?php } else { ?>
	      <p class="mb0">Adding photos will help you to get the best response from your advert.<br /><br />Photos both of the bedroom(s) and of the living areas inside the <?php echo $building_type; ?> and of the outside of the <?php echo $building_type; ?> are recommended. <?php if ($accommodation_type != "whole place") { ?><br />People photos which introduce the household can be especially helpful, and fun too... maybe your holiday snaps??<?php } ?><br /><br /></p>		  		  
		  <?php } ?>  
		  
<!--	      <p class="mb0">Adding photos will help you to get the best response from your advert. They can help others to see what offered  accommodation is like, and they can help to introduce the household or the  accommodation seeker.<br />
	        <br /> 
        People photos are especially helpful, both for offered and wanted ads, and they can be fun too... maybe your holiday snaps?? <br />
			  <br />
	      </p>
-->		  
			<div id="uploadBack">
			  <p class="mt0">Use the form below to add photos to this ad. 
			    You may add up to 8 photos. <br />
			    (Max size 10MB, file type: JPEG)</p>
					<?php print $error['upload']?>
					
					<script language="javascript" type="text/javascript">
							function showLoader() {
								$('photo_loader').setStyle('display','');
								return true;
							}
						
					</script>					
				<form name="uploadPicture" method="post" action="<?php print $_SERVER['PHP_SELF']?>" enctype="multipart/form-data">
					<input type="hidden" name="id" value="<?php print $id?>" />
					<input type="hidden" name="post_type" value="<?php print $post_type?>" />
					<input type="hidden" name="action" value="upload" />
					<table border="0" cellspacing="0" cellpadding="0" class="prTD5">
					<tr>
						<td><input name="userfile" type="file" size="60" /></td>
						<td><input type="submit" name="uploadPictureSubmit" value="Upload photo" onclick="return showLoader();" /></td>
						<td><img src="images/photo-loader.gif" width="16" height="16" id="photo_loader" style="display:none;" /></td>
					</tr>
				</table>
				</form>
			</div>
			<?php } else { ?>
			<p>You have added the maximum number of photos allowed.</p>
			<?php } ?>
			<h2>Your advert photos</h2>
			<?php print $photos?>
			<?php if ($photoCount) { ?>
			<p><input type="button" value="Rotate clockwise" onclick="doRotate('anticlockwise');"/>&nbsp;<input type="button" value="Rotate anti-clockwise" onclick="doRotate('clockwise');"/></p>
			</p>
			<?php } ?>
		</div>
		<div class="cs" style="width:20px; height:500px;"><!----></div>
		<div class="cr">
			<?php print $theme['side']; ?>
		</div>
		<div class="clear"><!----></div>
		<p class="mb0"><a href="your-account-manage-posts.php">Back to Your Ads page</a></p>
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