<?php
require("database.php");
require("secure.php");
require("menu.php");
require('csv.inc.php');
require("../csvtoxmloor.php");
require_once("../form.inc.php");
$form = 'order';
$searchcolumn = 'po';
$csvOut = new CSV;
?>
<table border="0" cellspacing="0" cellpadding="5" style="float: right">
<TR bgcolor="#CCCC99"><TH class="fat_black_12" colspan="4">Allowed CSV Headers</TH></TR>
<TR bgcolor="#FFFFFF"><TH class="fat_black_12">Field</TH><TH class="fat_black_12">CSV Header</TH><TH class="fat_black_12">Blank-able?</TH><TH class="fat_black_12">Length</TH></TR>
<?php
$fields = forminfo($form);
?>
<TR bgcolor="#FFFFCC">
	<TD class="text_12"><?php=$fields[$searchcolumn]['nicename'];?></TD>
	<TD class="text_12"><?php=$fields[$searchcolumn]['id'];?></TD>
	<TD class="text_12" colspan="2" align="center">Update Filtered By</TD>
</TR>
<?php
foreach ($fields as $field) {
	if ($field['id'] == $searchcolumn) continue; // We've already seen it... moving on!
	if (!$field['edit']||!$field['visible']||($field['id'] == 'vendor_id')) continue; // We're not interested!
	echo "<TR><TD class='text_12'>".$field['nicename']."</TD><TD class='text_12'>".$field['id']."</TD><TD align='center' class='text_12'>";
	if ($field['required']) {
		echo "No";
	} else {
		echo "Yes";
	}
	echo "</TD><TD class='text_12' align='right'>";
	if ($field['limit'] == -1) {
		echo "-";
	} else {
		echo $field['limit'];
	}
	echo "</TD></TR>";
}
?>
<TR bgcolor="#CCCC99"><TH class="fat_black_12" colspan="4" align="center">Non-present columns will not be updated.<BR />Present columns with no value will store blank value.</TH></TR>
</table>
<h1>CSV Open Order Report Converter</h1>
<form enctype="multipart/form-data" method="POST" action="<?php= $_SERVER['PHP_SELF'] ?>">
<input type="hidden" name="submitted" value="1">
<input type="hidden" name="vendorid" value="<?php= $_REQUEST['vendorid'] ?>">
File Name: <input type="file" name="csvoor">
<input type="submit" value="Convert CSV">
</form>

<?php
if ($_POST['submitted'] && ($_FILES['csvoor']['size'] > 0)) {
	//print_r($_FILES);
	//print_r($_POST);
	?>
	<br />
	<?php
	//$to = "garydavis@pmdfurniture.com";
	//$subject = "XML Open Order Report Update";
	//$body = csv2xmloor($_FILES['csvoor']['tmp_name'],true);
	//if (mail($to, $subject, $body)) {
	//  echo("The following information was inserted to the Open Orders database. <br /><a href='../form.php?form=order&action=display'>Click here to see the update</a><br />");
	// } else {
	//  echo("<p>Message delivery failed. Please contact your administrator</p>");
	// }
	?>
	<pre>
	<?php
	$results = formcsvimport($form,$searchcolumn,$_FILES['csvoor']['tmp_name']);
	?>
	</pre>
	<table border="0" cellspacing="0" cellpadding="5">
	<TR bgcolor="#CCCC99"><TH class="fat_black_12" colspan="4">CSV Results</TH></TR>
	<TR bgcolor="#FFFFFF"><TH class="fat_black_12">Line #</TH><TH class="fat_black_12">Type</TH><TH class="fat_black_12">Description</TH></TR>
	<?php
	if ($results['succ']) {
		$result = $results['succ'];
		unset($results['succ']); // Remove success message from loops so it doesn't show up with the rest of the errors
		?>
		<TR bgcolor="#66CC99"><TD class="text_12">-</TD><TD class="text_12">Success</TD><TD class="text_12">Successfully Updated <?php= $result['rows'] ?> row(s) with values from <?php= $result['lines'] ?> line(s).</TD></TR>
		<?php
	}
	foreach ($results as $line => $errs) {
		foreach ($errs as $error) {
			?>
			<TR bgcolor="<?php
			switch ($error['type']) {
				case "warn":
					echo "#FFFF99";
					break;
				case "error":
					echo "#CC9999";
					break; 
				case "fatal":
					echo "#CC6666";
					break;
			}
			?>"><TD class="text_12"><?php= $line ?></TD><TD class="text_12"><?php
			switch ($error['type']) {
				case "warn": 
					echo "Warning";
					break;
				case "error":
					echo "Error";
					break;
				case "fatal":
					echo "Fatal";
					break;
			}
			?></TD><TD class="text_12"><?php=$error['desc']?></TD></TR>
			<?php
		}
	}
	?>
	</table>
<?php
}
?>