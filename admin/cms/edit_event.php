<?php
require("../database.php");
require("../secure.php");

if ($_POST){
	# save the page

	$strPhotoSQL = "";

	$startDate = explode("/",$_POST['start_date']);
	$startDate = $startDate[2]."-".$startDate[0]."-".$startDate[1];
	$endDate = explode("/",$_POST['end_date']);
	$endDate = $endDate[2]."-".$endDate[0]."-".$endDate[1];
	
	$r = rand(1,1000000000);
	if(isset($_FILES['filename']) && !empty($_FILES['filename']['name'])) {
		$uploadfile = $_SERVER['DOCUMENT_ROOT']."/uploads/events/".$r."_".basename($_FILES['filename']['name']);
		if (move_uploaded_file($_FILES['filename']['tmp_name'], $uploadfile)) {
		   $strPhotoSQL = ",filename='".$r."_".basename($_FILES['filename']['name'])."'";
		} 
	}

	if (!empty($_POST['cms_event_id'])){
		# update
		$sql = "UPDATE cms_events set title='".$_POST['title']."',description='".$_POST['description']."',start_date='".$startDate."',end_date='".$endDate."'".$strPhotoSQL." WHERE cms_event_id=".$_POST['cms_event_id'].";";
	} else {
		$sql = "INSERT INTO cms_events set  title='".$_POST['title']."',description='".$_POST['description']."',start_date='".$startDate."',end_date='".$endDate."'".$strPhotoSQL.";";
	}
	mysql_query($sql);

	Header("location: events.php");
} else {

?>
<?php require("menu.php");  ?>
<script src="//cdn.ckeditor.com/4.5.10/standard/ckeditor.js"></script>

<h1>Edit Event</h1>

<?php

$startDate = "";

if (!empty($_REQUEST['cms_event_id'])){
	$sql = "select * from cms_events WHERE cms_event_id=".$_REQUEST['cms_event_id'];
	$query = mysql_query($sql);
	checkDBError();
	$row = mysql_fetch_array($query);
	$startDate = date("m/d/Y",strtotime($row['start_date']));
	$endDate = date("m/d/Y",strtotime($row['end_date']));
}

?>

	<form action="" method="POST" id="frmMain" enctype="multipart/form-data">
		<input type="hidden" name="cms_event_id" value="<?php=$row['cms_event_id']?>"/>

	<table border="0" cellspacing="5" cellpadding="0" width="90%">

	<tr valign="top">
		<td class="fat_black_12">Event Name</td>
		<td class="text_12"><input style="width:100%;" type="text" name="title" id="title" value="<?php=$row['title']?>"><br></td>
	</tr>

	<tr valign="top">
		<td class="fat_black_12">Event Description (optional)</td>
		<td class="text_12"><input style="width:100%;" type="text" name="description" id="description" value="<?php=$row['description']?>"><br></td>
	</tr>

	<tr valign="top">
		<td class="fat_black_12">Event Start Date</td>
		<td class="text_12"><input class="datepicker" style="width:100%;" type="text" name="start_date" id="start_date" value="<?php=$startDate?>"><br></td>
	</tr>

	<tr valign="top">
		<td class="fat_black_12">Event End Date</td>
		<td class="text_12"><input class="datepicker" style="width:100%;" type="text" name="end_date" id="end_date" value="<?php=$endDate?>"><br></td>
	</tr>

	<tr valign="top">
		<td class="fat_black_12">Event Image (Select to Replace)</td>
		<td class="text_12"><input style="width:100%;" type="file" name="filename" id="filename"/><br></td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td> <input type="submit" style="background-color:#CA0000;color:white" value="Save Event"> <input type="button" style="background-color:#444;color:white" value="Cancel" onClick="javascript:history.back();"></td>
	</tr>
	</table>

	</form>
	
<?php
}