<?php
require("database.php");
require("secure.php");
require("menu.php");
require("../csvtoxmlstock.php");
?>
<h1>CSV Stock Converter</h1>
<form enctype="multipart/form-data" method="POST" action="<?php= $_SERVER['PHP_SELF'] ?>">
<input type="hidden" name="submitted" value="1">
<input type="hidden" name="vendorid" value="<?php= $_REQUEST['vendorid'] ?>">
File Name: <input type="file" name="csvstock">
<input type="submit" value="Convert CSV">
</form>

<?php
if ($_POST['submitted'] && ($_FILES['csvstock']['size'] > 0)) {
//print_r($_FILES);
//print_r($_POST);
?>
<br />
Send following e-mail:<br />
Subject: XML Stock Update<br />
To: xml@retailservicesystems.com<br />
<br />
Contents:<br />
<pre>
<?php
//print file_get_contents($_FILES['csvstock']['tmp_name']); //
print htmlentities(csv2xmlstock($_POST['vendorid'], $_FILES['csvstock']['tmp_name'],true));
?>
</pre>
<?php
}
?>