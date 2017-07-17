<?php /* <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd"> */ ?>
<html>
<head>
	<title>RSS Market Order System Administration</title>
	<link rel="stylesheet" href="../styles.css" type="text/css" />
	<script type="text/javascript" src="../include/common.js"></script>
</head>

<body>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
	<td class="fat_black" colspan="2">RSS Furniture Direct - Market Order System</td>
</tr>
<tr>
	<td bgcolor="#CCCC99"><span class="fat_black_12">Menu:&nbsp;&nbsp;&nbsp;</span><a href="MoS_report_orders.php">Order Queue</a>&nbsp;&nbsp;&nbsp;<a href="MoS_edit-forms-view.php">Edit Forms</a>&nbsp;&nbsp;&nbsp;<?php if (secure_is_superadmin()) { ?><a href="MoS_maint.php">Maintaince Utilities</a>&nbsp;&nbsp;&nbsp;<?php } ?></td>
	<td bgcolor="#CCCC99" align="right" class="fat_black_12"><?php= db_user_fullname($userid) ?>&nbsp;(<a href='MoS_login.php?a=out'>Logout</a>)</td>
</tr>
</table>
