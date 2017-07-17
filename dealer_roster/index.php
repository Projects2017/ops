<?php
require('../database.php');
require('../secure.php');
$team = $_REQUEST['team'];
$state = $_REQUEST['state'];
$manager = $_REQUEST['manager'];
if (secure_is_manager()) {
	$where_sql = '';
	if ($team&&strlen($team) == 1) {
		$where_sql .= ' AND team = \''.$team.'\'';
	}
	if ($state&&strlen($state) == 2) {
		$where_sql .= ' AND state = \''.$state.'\'';
	}
	if ($manager) {
		$where_sql .= ' AND manager = \''.$manager.'\'';
	}
} else {
	$where_sql = ' AND admin = \'M\'';
}
$sql = "SELECT * FROM `users` WHERE nonPMD != 'Y' AND `disabled` != 'Y'".$where_sql." ORDER BY last_name";
$query = mysql_query($sql);
checkDBerror($sql);

$dealers = array();
while ($row = mysql_fetch_assoc($query)) {
	$dealers[] = $row;
}

$states = array('AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN',
	'IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH',
	'NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT',
	'VT','VA','WA','WV','WI','WY');

$columns = 0;
$lines = 0;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>Dealers</title>
  <meta http-equiv="Content-Type"
 content="text/html; charset=iso-8859-1">
  <link rel="stylesheet" href="../styles.css" type="text/css">
</head>
<body alink="#999999" bgcolor="#edecda" link="#cc3300" text="#000000"
 vlink="#cc0000">
<?php require('../menu.php'); ?>
<div style="text-align: center;">
<center style="font-family: arial;">
<div style="text-align: center;"></div>
<!--
<table style="text-align: left; margin-left: auto; margin-right: auto;"
 bgcolor="#cccc99" border="4" width="298">
  <tbody>
    <tr>
      <td width="282" align="center">
        <h1>PMD Dealers</h1>
      </td>
    </tr>
  </tbody>
</table>
-->
<br />
<?php if (secure_is_manager()) { ?>
<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<div style="text-align: center;">
	Team<select name="team">
	<option value="">All</option>
	<?php $teams = teams_list();
	foreach ($teams as $curteam) {
	  ?><option value="<?php echo $curteam; ?>" <?php
	  if ($curteam == $team) echo "SELECTED";
	  ?>><?php echo $curteam; ?></option>
	  <?php
	}
	?></select>
	State&nbsp;<select name="state">
	<option value="">All</option><?php
	foreach ($states as $curstate) {
	   ?><option value="<?php echo $curstate; ?>" <?php if ($curstate == $state) echo "SELECTED"; ?>><?php echo $curstate; ?></option>
	   <?php
	}?>
	</select>
	<?php echo manager_name(); ?>&nbsp;<select name="manager">
	<option value="">All</option><?php
	$manager_list = managers_list();
	foreach ($manager_list as $curmanager) { $curmanager = $curmanager['name'];
	   ?><option value="<?php echo $curmanager; ?>" <?php if ($curmanager == $manager) echo "SELECTED"; ?>><?php echo $curmanager; ?></option>
	   <?php
	}?></select>
	<input type="submit" value="Filter">
</div>
</form>
<br />
<?php } // end if manager or above ?
foreach ($dealers as $dealer) { ?>
<?php   if ($lines == 0) {
     ++$lines; ?>
<!-- Start Page -->
<table style="text-align: left; margin-left: auto; margin-right: auto;"
 bgcolor="#ffffff" border="4" cellspacing="10">
  <tbody>
<?php   }
     if ($columns == 0) { 
       ++$columns; ?>
<!-- Line <?php echo $lines ?> -->
    <tr valign="bottom">
<?php   } ?>
      <td align="center" bgcolor="#ffffff"><a
 href="dealer.php?dealer_id=<?php echo $dealer['ID'] ?>"
 onclick="window.open('dealer.php?dealer_id=<?php echo $dealer['ID'] ?>','windowname','width=530,height=320');return(false);"
 alt="<?php $dealer['last_name']; ?>" shape="rect" coords="343,482,363,494"><img alt=""
<?php if (file_exists('photos/'.$dealer['ID'].'.jpg')) { ?>
 src="<?php echo 'photos/'.$dealer['ID'].'.jpg'; ?>"
<?php } else { ?>
 src="blank.jpg"
<?php } ?>
 style="border: 4px solid ; width: 150px; height: 135px;"></a><br>
      <strong><?php echo $dealer['last_name']; ?></strong><br>
<?php echo $dealer['city'].', '.$dealer['state']; ?><strong></strong><br>
      </td>
<?php   if ($columns >= 5) { ?>
    </tr>
<?php     ++$lines;
       $columns = 0;
     } else {
       ++$columns;
     }

     if ($lines > 3) {
       $lines = 0;
?>
  </tbody>
</table>
<div style="text-align: center;"><br>
<br>
<br>
<br>
</div>
<div style="text-align: center;"></div>
<!-- End Page Block -->
<?php    }
}
// Clean up and close what's necisarry
if ($columns <= 5) {
  echo "</tr>";
}
if ($lines <= 4) {
  echo "</tbody></table>";
}

?>
<div style="text-align: center;">
<br>
<br>
</div>
<div style="text-align: center;"></div>
<div style="text-align: center;"><br>
<br>
<br>
<br>
</div>
</center>
</div>
</body>
</html>
