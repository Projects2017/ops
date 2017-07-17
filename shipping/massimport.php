<?php
// massimport.php
// script to send over a chunk of orders by date to the shipping system
// user selects date range, then after being told the # of records, verifies the CURL push
function dateSelect($prefix) {
  $months = array(1 => "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
  echo "<select name=\"$prefix".'month">';
  for($i=1; $i<=12; $i++) {
    echo "<option value='$i'";
    if(date('n')==$i) echo ' selected';
    echo ">{$months[$i]}</option>\n";
  }
  echo '</select>&nbsp;<select name="'.$prefix.'day">';
  for($i=1; $i<=31; $i++) {
    echo "<option value='$i'";
    if(date('j')==$i) echo ' selected';
    echo ">$i</option>\n";
  }
  echo '</select>&nbsp;<select name="'.$prefix.'year">';
  for($i=2003; $i<=date('Y'); $i++) {
    echo "<option value='$i'";
    if(date('Y')==$i) echo ' selected';
    echo ">$i</option>\n";
  }
  echo '</select>';
}

function showHeader() {
  $head = <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Mass Order Import to Shipping</title>
	<meta http-equiv="content-Type" content="text/html; charset=iso-8859-1">
	<meta name="generator" content="WebDesign">
	<link type="text/css" href="css/styles.css" rel="stylesheet">
</head>
<body>
EOT;
echo $head;
}
require('../database.php');
if(!$_GET) {
showHeader();
?><form name="massimport" action="massimport.php" method="get">
<p align="center" style="font-size: 24px; font-weight: bold;">Mass Order Import to Shipping</p>
<p>Select Date Range of Orders to Import</p>
  <?php
  dateSelect('from_');
  echo ' through ';
  dateSelect('thru_');
?></p><input type="submit" name="go" value="Submit">
</form>
</body>
</html>
<?php } else if(!$_GET['verify']) {
showHeader();
?><form name="massimport" action="massimport.php" method="get">
<?php
foreach($_GET as $k => $v) {
  if($k!="go") echo "<input type=\"hidden\" name=\"$k\" value=\"$v\">";
}
if(strlen($_GET['from_month'])<2) { $from_month = "0".$_GET['from_month']; } else { $from_month = $_GET['from_month']; }
if(strlen($_GET['thru_month'])<2) { $thru_month = "0".$_GET['thru_month']; } else { $thru_month = $_GET['thru_month']; }
if(strlen($_GET['from_day'])<2) { $from_day = "0".$_GET['from_day']; } else { $from_day = $_GET['from_day']; }
if(strlen($_GET['thru_day'])<2) { $thru_day = "0".$_GET['thru_day']; } else { $thru_day = $_GET['thru_day']; }
$sql = "SELECT COUNT(id) FROM order_forms WHERE ordered BETWEEN '{$_GET['from_year']}-$from_month-$from_day 00:00:00' AND '{$_GET['thru_year']}-$thru_month-$thru_day 23:59:59'";
$que = mysql_query($sql);
$res = mysql_fetch_row($que);
?><form name="massimport" action="massimport.php" method="get">
<p align="center" style="font-size: 24px; font-weight: bold;">Mass Order Import to Shipping</p>
<?php
echo "<p>There are {$res[0]} orders to import. Are you sure you want to proceed?</p>";
echo '<input type="submit" name="verify" value="Yes">&nbsp;&nbsp;<input type="submit" name="verify" value="No">';
?></form>
</body>
</html>
<?php } else if($_GET['verify']=="No") {
  header('Location: massimport.php');
  exit();
} else if($_GET['verify']=="Yes") {
  require('bolxml.php');
  showHeader();
  $main_sql = "SELECT id FROM order_forms WHERE ordered BETWEEN '{$_GET['from_year']}-$from_month-$from_day 00:00:00' AND '{$_GET['thru_year']}-$thru_month-$thru_day 23:59:59'";
  $main_que = mysql_query($main_sql);
  while($po_id = mysql_fetch_row($main_que)) {
    $sql = "SELECT form, user, ordered, snapshot_form FROM order_forms WHERE ID = ".$po_id[0];
    $query = mysql_query($sql);
    checkDBError();
    if ($result = mysql_fetch_array($query)) {
    	$user2 = $result['user'];
	    $form = $result['form'];
	    $date = $result['ordered'];
	    $snapshot_form = $result['snapshot_form'];
    } else {
	   // Can't find the Order, so let's make sure we don't get some stupid values.
	    $user2 = 0;
	    $form = 0;
	    $date = 0;
	    $snapshot_form = 0;
    }

    $sql = "SELECT email, email2, email3 FROM users WHERE ID=$user2";
    $query = mysql_query($sql);
    checkDBError();
    if ($result = mysql_fetch_array($query)) {
	    $email = $result[0];
	    $email2 = $result[1];
	    $email3 = $result[2];
    } else {
	    unset($email);
	    unset($email2);
	    unset($email3);
    }

    $sql = "SELECT name FROM snapshot_forms WHERE id='$snapshot_form'";
    $query = mysql_query($sql);
    checkDBError();
    if ($result = mysql_fetch_Array($query))
	    $vendor = $result['name'];

    $sql = "SELECT email, email2, proc_email, proc_email2, proc_url FROM vendors INNER JOIN forms ON `forms`.`vendor` = `vendors`.`ID` WHERE `forms`.`ID` = '".$form."'";
    $query = mysql_query($sql);
    checkDBError($sql);
    if ($result = mysql_fetch_Array($query))
    	$vemail = $result;
    if ($vemail['proc_url'])
    {
    	$ch = curl_init();
	    curl_setopt($ch, CURLOPT_HEADER, false);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_URL, $vemail['proc_url']."BoL/add.php");
	    $xmlstring = OrderToXML($po_id[0], true, false);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, "xml=".urlencode($xmlstring));
	    $res = curl_exec($ch);
	    if (curl_errno($ch)) {
        echo curl_error($ch);
      } else {
      curl_close($ch);
    }
    if ($res != 1) {
      $badorders[] = $po_id[0];
      ini_set(sendmail_from, 'Shipping Queue Daemon <noreply@retailservicesystems.com>');
      sendmail('Shipping DBA <will@retailservicesystems.com>', 'Shipping Queue Daemon Error - Adding Order from Production (report-orders-process.php line 53)', $res);
      die('There was a problem adding your order to the shipping database. The system administrator has been notified. We are sorry for the inconvenience.<br />Please <a href="selectvendor.php">click here</a> to return.');
      }
    }
   }
   if($badorders) {
    echo "<p>There were problems with ".count($badorders)." order transfers. The system administrator has been notified.</p>";
  } else {
    echo "<p>Import successful.</p>";
  }
}
?>
