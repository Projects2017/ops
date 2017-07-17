<?php
require('database.php');

if (!isset($_POST['action'])|| !isset($_POST['user']) || !isset($_POST['pass'])) {
    // Missing arguments, let's kick out some ugly error so people pay attention.
    header("Location: http://".$_SERVER['HTTP_HOST']."/ops/login.php");
    exit(0);
}

if (!$_POST['user'] || !$_POST['pass']) {
    setNote('You must provide both a username and a password to login.');
    header("Location: http://".$_SERVER['HTTP_HOST']."/ops/login.php");
    exit(0);
}
// Check for IP Lockout
//if (ipLockedOut($_SERVER['REMOTE_ADDR'])) {
//    // Log it as an attempt, even if it was going to be successful
//    addFailedIp($_SERVER['REMOTE_ADDR']);
//    // Notify them!
//    setNote('You have failed to login to many times. You must wait at least 10 minutes before trying again.');
//    header("Location: http://".$_SERVER['HTTP_HOST']."/login.php");
//    exit(0);
//}

if (get_magic_quotes_gpc()) {
    $_POST['user'] = stripslashes($_POST['user']);
    $_POST['pass'] = stripslashes($_POST['pass']);
}


$id = checkLogin($_POST['user'], $_POST['pass']);
// Unset user and password, they should never need to be used again.
unset($_POST['user']);
unset($_POST['pass']);


if (!$id) {
    addFailedIp($_SERVER['REMOTE_ADDR']);
    setNote('Unable to match the provided login and password.');
    header("Location: http://".$_SERVER['HTTP_HOST']."/ops/login.php");
    exit(0);
} else {
    clearIpLockout($_SERVER['REMOTE_ADDR']);
}

createSession($id);

// Run the normal checks, and get on with the login process
$duallogin = 1;
require("vendorsecure.php");
if (!$vendorid) {
    require("secure.php");
}
if (secure_is_dealer()) {
    if ($system_login_redirect) {
        header("Location: ". $system_login_redirect);
        exit();
    }
    if (is_dir('wikiinstall')) {
        header('Location: wiki/');
    } else {
        if (secure_is_admin()) {
            header('Location: admin/users.php');
        } else {
            header('Location: selectvendor.php');
        }
    }
} else {
    header('Location: vloginmenu.php');
}
