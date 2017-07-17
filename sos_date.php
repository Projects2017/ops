<?php
require('database.php');
require('secure.php');

if (secure_is_manager()&&$_REQUEST['f_userid']) {
	$f_userid = $_REQUEST['f_userid'];
	if ($f_userid == $userid) {
		unset($_REQUEST['f_userid']);
	}
} else {
	$f_userid = $userid;
}

function formatdate($date) {
	return date('m/d/Y',$date);
}

function endweek($date) {
	return strtotime("+6 days",$date);
}

function weekrange($date) {
	$output = "";
	$ndate = endweek($date);
	$output .= formatdate($date);
	$output .= " - ";
	$output .= formatdate($ndate);
	return $output;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
	<title>Financial Reports</title>
	<link type="text/css" href="styles.css" rel="stylesheet" />
</head>
<body>
<?php require('menu.php'); ?>
	<table style="text-align: left; width: 80%;" cellpadding="2" cellspacing="2">
		<tbody>
			<tr>
				<th colspan="2">
					<h2 style="text-align: center">Financial Reports</h2>
				</th>
			</tr>
			<tr>
				<td colspan="2" style="color: darkred; text-align: center;">
					<?php if ($_REQUEST['note']) {
						echo $_REQUEST['note'];
					} else { ?>
					When filing reports, be sure to start with the oldest report first.
					<?php } ?>
				</td>
			</tr>
<?php
			if (secure_is_manager()) {
?>
			<tr>
				<th>Select Dealer</th>
				<td>
					<form method="post" action="sos_date.php">
						<select name="f_userid" onchange="this.form.submit();">
							<?php $user = db_user_getuserinfo($userid); ?>
							<option value="<?php= $userid; ?>"><?php= $user['last_name'].' '.$user['first_name']; ?></option>
<?php
							$users = db_user_getlist();
							foreach ($users as $user) {
?>							<option value="<?php= $user['id']; ?>"<?php= ($f_userid == $user['id']) ? ' SELECTED' : ''; ?>><?php= $user['last_name'].' '.$user['first_name']; ?></option>
<?php							} ?>
						</select>
					</form>
				</td>
			</tr>
<?php
			}
?>
			<tr>
				<th>File a Weekly Report:</th>
				<td>
					<form method="post" action="sos_insert.php">
<?php
							if ($_REQUEST['f_userid']) {
?>						<input type="hidden" name="f_userid" value="<?php= $f_userid ?>">
<?php							}
							$sql = "SELECT MAX(`date`) as `lastdate` FROM `sos` WHERE `user_id` = '".$f_userid."'";
							$query = mysql_query($sql);
							checkdberror($sql);
							if (($lastdate = mysql_fetch_assoc($query)) && $lastdate['lastdate']) {
								$lastdate = strtotime($lastdate['lastdate']);
							} else {
								$lastdate = strtotime('last sunday',strtotime('-2 week'));
							}
						?>
						<select id="rep_week" name="rep_week">
<?php
							$lastdate = strtotime('+7 days', $lastdate);
							$i = 0;
							while (endweek($lastdate) < time()) {
								++$i;
								echo "\t\t\t\t\t\t\t<option value=\"".$lastdate."\">".weekrange($lastdate)."</option>\n";
								$lastdate = strtotime('+7 days', $lastdate);
							}
						?>
						</select>
						<input type="submit" value="Report Week">
					</form>
				</td>
			</tr>
			<tr>
				<th>Review Filed Reports:</th>
				<td>
					<form method="post" action="sos_report.php">
<?php					if ($_REQUEST['f_userid']) {
?>						<input type="hidden" name="f_userid" value="<?php= $f_userid ?>">
<?php							}
?>						<select id="rep_month" name="rep_month">
<?php

							$sql = 'SELECT EXTRACT( YEAR_MONTH FROM `date` ) as `month` FROM `sos` WHERE `user_id` = "'.$f_userid.'" GROUP BY `month` ORDER BY `month` DESC';
							$query = mysql_query($sql);
							checkdberror($sql);
							while ($month = mysql_fetch_assoc($query)) {
								$ym = $month['month'];
								$year = substr($ym,0,4);
								$month = substr($ym,4);
								$time = mktime(1,1,1,$month,1,$year);
								echo "\t\t\t\t\t\t\t<option value=\"".$ym."\">".date('F Y',$time)."</option>\n";
							}
?>
						</select>
						<input type="submit" value="View Month">
					</form>
				</td>
			</tr>
		</tbody>
	</table>
</body>
</html>
