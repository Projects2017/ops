<?php
require("database.php");
require("secure.php");

if (!is_numeric($userid))
    die("NO SUCH USERID!");

// Post vars are
// - phone
// - cell_phone
// - cell_provider
// - fax
// - email
// - email2
// - email3
// - passwordID
// (where ID = Login ID of user)
//  there is also a old_ version of every one of those.

$update = array();
$passwords = array();
$fields = array('phone','cell_phone','cell_provider','fax','email','email2','email3');

// Grab Login Fields
$sql = "SELECT `id` FROM `login` WHERE `relation_id` = '".$user."' AND `type` != 'V'";
$query = mysql_query($sql);
checkDBerror($sql);
while ($row = mysql_fetch_assoc($query)) {
    $fields[] = "password".$row['id'];
    $passwords[] = $row['id'];
}

// Go through fields
foreach ($fields as $f) {
    if ($_POST[$f] != $_POST['old_'.$f]) {
        $update[$f] = $_POST[$f];
    }
}

foreach ($passwords as $p) {
    if (isset($update['password'.$p])) {
        $sql = "UPDATE `login` SET `password` = '".$update['password'.$p]."' WHERE `id` = '".$p."'";
        mysql_query($sql);
        checkDBerror($sql);
        unset($update['password'.$p]);
    }
}

if ($update) {
    $set = array();
    foreach($update as $k => $v) {
        $set[] = "`".$k."` = '".$v."'";
    }
    $sql = "UPDATE `users` SET ".implode(", ",$set)." WHERE `ID` = '".$userid."'";
    mysql_query($sql);
    checkDBerror($sql);
}