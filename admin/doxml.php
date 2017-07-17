<?php
require('database.php');
require('secure.php');
require('menu.php');
if(!secure_is_superadmin()) die("Unauthorized user. Permission denied.");
require('xmlmenu.php');
?>
</body>
</html>
