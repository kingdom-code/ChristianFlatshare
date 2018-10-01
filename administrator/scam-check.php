<?php 

// Autoloader
require_once __DIR__ . '/../web/global.php';
	
	// Dissallow access if user not logged in
	if (!isset($_SESSION['u_id'])) { header("Location:../index.php"); exit; }
	// Dissallow access if user is not an administrator
	if ($_SESSION['u_access'] != 'admin') { header("Location:../index.php"); exit; }	
	
	$query = "select * from cf_email_replies";
	$result = mysqli_query($GLOBALS['mysql_conn'], $query);
	
	// Creates a formatted table out of the supplied result
	function tabulate($result) {
			
		// Return a table with the results.
		$toReturn .= "\n\n";
		$toReturn = '<table cellpadding="4" cellspacing="0" border="0" class="greyTable" width="100%">'."\n";
		// First, read the table headers from the $result object
		$toReturn .= '<tr>'."\n";
		$i = 0;
		while ($i < mysqli_field_count($result)) {
			$meta = mysqli_fetch_field($result, $i);
			$toReturn .= '<th>'.$meta->name.'</th>'."\n";
			$i++;
		}
		$toReturn .= '</tr>'."\n";
		
		// Secondly, create a table row for each result row
		while($row = mysqli_fetch_row($result)) {
			$toReturn .= '<tr>'."\n";
			foreach($row as $value) {
				$toReturn .= '<td>'.$value.'</td>'."\n";
			}
			$toReturn .= '</tr>'."\n";
		}
		
		$toReturn .= '</table>'."\n\n";
		return $toReturn;
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CFS Scam check</title>
<style type="text/css">

	body {
		font-family:Verdana, Arial, Helvetica, sans-serif;
		font-size:11px;
		margin:20px;
	}
	h1 { 
		font-family:Arial, Helvetica, sans-serif;
		font-size:24px;
		font-weight:normal;
	}
	h2 { 
		font-family:Arial, Helvetica, sans-serif;
		font-size:14px;
	}
	
	.greyTable { border-collapse:collapse; border:1px solid #CCCCCC; }
	.greyTable th { border:1px solid #CCCCCC; background-color:#E5E5E5; color:#666666; font-weight:bold; text-align:center; }
	.greyTable td { border:1px solid #CCCCCC; }

</style>
</head>
<body>
<?php print tabulate($result);?>
</body>
</html>

