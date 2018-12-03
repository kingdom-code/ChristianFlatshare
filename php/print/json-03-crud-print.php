
Files from: http://www.wa4e.com/code/json-03-crud.zip

viewapi.php

<html>
<head>
</head>
<body>
<p>Howdy - Lets get a JSON list</p>
<script type="text/javascript" src="jquery.min.js">
</script>
<script type="text/javascript">
$(document).ready( function () {
  $.getJSON('getjson.php', function(data) {
      for (var i = 0; i < data.length; i++) { 
         window.console && console.log(data[i].title);
      }
    })
  }
);
</script>
</body>

<?php // getjson.php

require_once "pdo.php";
session_start();
header('Content-Type: application/json; charset=utf-8');
$stmt = $pdo->query("SELECT title, plays, rating, id FROM tracks");
$rows = array();
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
  $rows[] = $row;
}

echo json_encode($rows);

?>



<?php // index.php

require_once "pdo.php";
session_start();
?>
<html>
<head>
</head><body>
<?php
if ( isset($_SESSION['error']) ) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
if ( isset($_SESSION['success']) ) {
    echo '<p style="color:green">'.$_SESSION['success']."</p>\n";
    unset($_SESSION['success']);
}
?>
<table border="1">
  <tbody id="mytab">
  </tbody>
</table>
<a href="add.php">Add New</a>
<a href="viewapi.php" target="_blank">viewapi.php</a>
<script type="text/javascript" src="jquery.min.js">
</script>
<script type="text/javascript">
// Simple htmlentities leveraging JQuery
function htmlentities(str) {
   return $('<div/>').text(str).html();
}
</script>
<script type="text/javascript">
// Do this *after* the table tag is rendered
$.getJSON('getjson.php', function(rows) {
    $("#mytab").empty();
    found = false;
    for (var i = 0; i < rows.length; i++) {
        row = rows[i];
        found = true;
        window.console && console.log(row.title);
        $("#mytab").append("<tr><td>"+htmlentities(row.title)+'</td><td>'
            + htmlentities(row.plays)+'</td><td>'
            + htmlentities(row.rating)+"</td><td>\n"
            + '<a href="edit.php?id='+htmlentities(row.id)+'">'
            + 'Edit</a> / '
            + '<a href="delete.php?id='+htmlentities(row.id)+'">'
            + 'Delete</a>\n</td></tr>');
    }
    if ( ! found ) {
        $("#mytab").append("<tr><td>No entries found</td></tr>\n");
    }
});
</script>




