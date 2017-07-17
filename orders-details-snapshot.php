<?php
require("database.php");
require("inc_content.php");
require("secure.php");
?>
<html>
<head>
<title>RSS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body>

<?php
require('menu.php');
$section = "dealer";

echo OrderForWeb($po,$section);

mysql_close($link);
?>

<br>
<p align="center">[<a href="javascript:history.back();">Back to Order List</a>]</p>

</body>
</html>
