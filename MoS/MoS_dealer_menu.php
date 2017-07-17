<?php if ($_SERVER['PHP_SELF'] != '/MoS/MoS_form-view.php') { ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<?php } ?>
<html>
<head>
	<title>RSS Market Order System Administration</title>
	<link rel="stylesheet" href="../styles.css" type="text/css" />
	<script type="text/javascript" src="../include/common.js"></script>
</head>

<body>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
	<td class="fat_black" colspan="2">RSS Market Order System</td>
</tr>
<tr>
	<td bgcolor="#CCCC99"><span class="fat_black_12">Menu:&nbsp;&nbsp;&nbsp;</span><a href="MoS_dealer_main.php">Order Forms</a>&nbsp;&nbsp;&nbsp;<a href="MoS_report_orders.php?u=<?php echo $userid; ?>">View Your Orders</a>&nbsp;&nbsp;&nbsp;</td>
	<td bgcolor="#CCCC99" align="right" class="fat_black_12" nowrap><?php= db_user_fullname($userid) ?>&nbsp;(<a href="print" onclick="window.print();return false">Print</a>)&nbsp;(<a href='MoS_login.php?a=out'>Logout</a>)<br>
	Recently Ordered: <?php $sql = "SELECT SUM(total) AS total FROM MoS_order_forms WHERE DATE_SUB(CURDATE(),INTERVAL 1 MONTH) <= ordered AND deleted = 0 AND processed != 'D' AND `user` = '".$userid."'"; $query = mysql_query($sql); checkdberror($sql); $result = mysql_fetch_assoc($query); echo makeThisLookLikeMoney($result['total']); ?></td>
</tr>
</table>
<br>
