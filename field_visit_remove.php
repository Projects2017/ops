<?php
// field_visit_remove.php
// script to remove duplicate field visit record
// parameter via GET: ID = visit_id in fieldvisit of the visit to remove
// note: at this point, this is a destructive action; i.e. no going back
require("database.php");
require("secure.php");
// here we go
$sql = "DELETE FROM fieldvisit WHERE visit_id = ".$_GET['id'];
$que = mysql_query($sql);
checkDBerror($sql);
// things have gone well, so now we go to the field visit viewer
header('Location: field_visit_view.php');
// that's it.
?>