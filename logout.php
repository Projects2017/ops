<?php
require('database.php');
if ($_COOKIE['pmd_suuser']) {
    setcookie('pmd_suuser','',0,"/");
    header('Location: admin/users.php');
    exit(0);
} else {
    clearSession();
    header('Location: /login.php');
    exit(0);
}
?>
