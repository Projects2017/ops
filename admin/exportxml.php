<?php
require('database.php');
require('secure.php');
require('menu.php');
require('xmlmenu.php');
if(!secure_is_superadmin()) die("Unauthorized user. Permission denied.");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>RSS XML - Export Multiple Purchase Orders</title>
</head>

<body>
<form id="POchoice" name="POchoice" method="post" action="makexml.php">
  <p>Choose the PO(s) you wish to export: </p>
    <table width="80%" border="0" cellspacing="3" cellpadding="3">
  <tr>
    <td><label>
      <input name="potype" type="radio" checked value="num" />
      By number range </label></td>
    <td><input type="text" name="from_number" size="8" value="<?php
	$sql = "SELECT MIN(ID) FROM order_forms";
	$query = mysql_query($sql);
	$result = mysql_fetch_array($query);
	echo $result['MIN(ID)'] + 1000;
	?>" /> to <input name="thru_number" type="text" size="8" value="<?php
	$sql = "SELECT MAX(ID) FROM order_forms";
	$query = mysql_query($sql);
	$result = mysql_fetch_array($query);
	echo $result['MAX(ID)'] + 1000;
	?>" /></td>
  </tr>
  <tr>
    <td><label>
      <input name="potype" type="radio" value="date" />
      By date range </label></td>
    <td><select name="from_month">
      <option value="01">January</option>
      <option value="02">February</option>
      <option value="03" selected="selected">March</option>
      <option value="04">April</option>
      <option value="05">May</option>
      <option value="06">June</option>
      <option value="07">July</option>
      <option value="08">August</option>
      <option value="09">September</option>
      <option value="10">October</option>
      <option value="11">November</option>
      <option value="12">December</option>
    </select> 
      <input type="text" name="from_day" size="3" value="01" /> <select name="from_year">
	   <option selected="selected">2003</option>
	   <?php
	   for($y=2003; $y<=date('Y'); $y++)
	   {
	   	echo "<option>$y</option>\n";
	   }
        ?>
      </select> 
      to <select name="thru_month">
	  <?php
	  $montharray = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	  for($i=1; $i<=12; $i++) {
	  echo '<option value="'.$i.'"';
	  if($i==date('n')) echo ' selected="selected"';
	  echo '>'.$montharray[$i].'</option>'."\n";
	  }
	?>
    </select> 
      <input type="text" name="thru_day" size="3" value="<?php echo date('j'); ?>" /> <select name="thru_year">
	  <?php
	  $yeararray = array(2003 => 2003, 2004, 2005, 2006, 2007);
	  for($i=1; $i<=5; $i++) {
	  echo '<option value="'.($i+2002).'"';
	  if($yeararray[$i+2002]==date('Y')) echo ' selected="selected"';
	  echo '>'.$yeararray[$i+2002].'</option>'."\n";
	  }
	  ?>
      </select>
      </td>
  </tr>
  <tr>
  	<td><label><input name="potype" type="radio" value="deal" />
	By dealer</label></td>
	<td><select name="dealer">
	<?php
	$bigresult = db_user_getlist();
	foreach($bigresult as $row) {
		echo '<option value="'.$row['last_name'].'">'.$row['last_name'].'</option>'."\n";
		}
	?></select></td></tr>
  <tr>
  	  <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
  	  <td><label>
      <input name="createtype" type="radio" checked value="ind" />
      Create file for each PO</label></td>
	  <td><label>
	  <input name="dload" type="checkbox" checked />
	  Download ZIP'd file when complete</label></td>
  </tr>
  <tr>
  	  <td colspan="2"><label>
	  <input name="createtype" type="radio" value="one" />
	  Create one file for all POs</label></td>
  </tr>	  
</table>
<p><input type="submit" value="Generate XML File(s)"/>&nbsp;&nbsp;&nbsp;&nbsp;<input type="reset" value="Reset Form"/>
</form>
</body>
</html>
