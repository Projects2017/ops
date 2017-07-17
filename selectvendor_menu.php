<?php
require("database.php");
require("secure.php");
?>
<html>
<head>
<title>RSS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body bgcolor="#EDECDA">
<?php require('menu.php'); ?>
<TABLE><TR><TD WIDTH=150 VALIGN=TOP>
<FONT FACE=ARIAL><B>SALES STATISTICS</B><BR>
<a href="salestats_form.php">Enter Your Sales Statistics</a><BR>
<a href="salestats_edit.php">View and Edit Your Stats</a><BR>
<a href="salestats_query.php">View All Sales Statistics</a><BR>
&nbsp;<BR>

<B>SALES REPORTS</B><BR>
<a href="/docs/tabs/TABS%20%20Rankings%20week%20ending%204-16-04.rtf">
TABS & Rankings week ending 4-16-04 (RTF)</A><BR>
<!-- <A HREF="./">President's Club Standings</A> --><BR>
&nbsp;<BR>

<B>DAMAGE AND CLAIMS</B><BR>
<a href="/damage_report.php">Damage Claim Submission Form</A><BR>
&nbsp;<BR>

<B>ORDER REPORTS</B><BR>
<a href="/docs/PMD%20Open%20Order%20Report%20-%20Master.xls">RSS Open Order Report</A><BR>
<a href="/docs/UPDATEDSTOCK%20Master.xls">Updated Stock Report</A><BR>
&nbsp;<BR>

<?php
$query = mysql_query("select * from users where ID=$userid");
checkDBError();

$sql = "SELECT vendor_access.*, vendors.name FROM vendor_access
 LEFT JOIN vendors ON vendors.ID=vendor_access.vendor WHERE vendor_access.user=$userid ORDER BY vendors.name";
$query = mysql_query($sql);
checkDBError();
if (mysql_num_rows($query) == 0)
	echo "<p align=\"center\">You do not have access to any vendors!</p>";
else {
?>
<B>SELECT A VENDOR</B><BR>
<a href="summary.php">View Your Order Summary</a> <BR>&nbsp;<BR>

</TD><TD WIDTH=5>&nbsp;</TD><TD VALIGN=TOP>


<table border="0" cellspacing="0" cellpadding="5" align="left" width="760">
  <tr bgcolor="#CCCC99">
    <td class="fat_black_12">Vendor</td>
    <td class="fat_black_12">Form</td>
    <td class="fat_black_12">View Previous Orders</td>
  </tr>
  <?php
	while($result = mysql_fetch_array($query)) {
		$sql = "select * from forms where vendor=".$result['vendor'];
		$query2 = mysql_query($sql);
		checkDBError();
		while ($result2 = mysql_fetch_array($query2)) {
	?>
  <tr bgcolor="#FFFFFF">
    <td class="text_12"><?php echo $result['name'] ?></td>
    <td><a href="form-view.php?ID=<?php echo $result2['ID'] ?>"><?php echo $result2['name'] ?></a></td>
    <td><a href="orders.php?ID=<?php echo $result2['ID'] ?>">view orders</a></td>
  </tr>
  <?php
		}
	}
}
mysql_close($link);
?>
<tr>
    <td><img src="images/furniture1.jpg" width="238" height="150"></td>
    <td align="center"><img src="images/furniture2.jpg" width="238" height="150"></td>
    <td><img src="images/furniture3.jpg" width="238" height="150"></td>
  </tr>
</table>
<//TABLE>
</body>
</html>
