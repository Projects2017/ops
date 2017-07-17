<?php
require("database.php");
require("secure.php");
require("../inc_backorder.php");
if (!is_numeric($_REQUEST['bo'])) die("BO# required to be numeric");
if (isset($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'push') {
        completebackorder($_REQUEST['bo'], true);
    } elseif ($_REQUEST['action'] == 'pushpart') {
        $items = array();
        foreach ($_POST as $item => $qty) {
            if (substr($item,0,4) == 'item') {
                $item = substr($item,4);
                if (!is_numeric($item)) continue;
                $items[$item] = $qty;
                echo $item." - ".$qty."\n<br />";
            }
        }
        if ($items) {
            completebackorderpart($_REQUEST['bo'], $items, true);
        }
    } elseif ($REQUEST['action'] == 'cancel') {
        if (!secure_is_superadmin()) {
            die("Access Denied.");
        }
        cancelbackorder($_REQUEST['bo']);
    } elseif ($_REQUEST['action'] == 'cancelpart') {
        if (!secure_is_superadmin()) {
            die("Access Denied.");
        }
        $items = array();
        foreach ($_POST as $item => $qty) {
            if (substr($item,0,4) == 'item') {
                $item = substr($item,4);
                if (!is_numeric($item)) continue;
                $items[] = $item;
                echo $item." - ".$qty."\n<br />";
            }
        }
        if ($items) {
            cancelbackorderpart($_REQUEST['bo'], $items);
        }
    }
}
// Send the browser to view the newly canceled order
header("Location: ../backorder_view.php?bo=".$_REQUEST['bo']."&return=".urlencode($_REQUEST['return']));
exit();
?>