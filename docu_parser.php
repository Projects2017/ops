<?php
// 
require_once('database.php');
$duallogin = 1;
include("vendorsecure.php");
if (!$vendorid)
   include("secure.php");
require_once('inc_urlparse.php');
$q = cleanpath($_REQUEST['q']);
$real = realpath($basedir.'doc/'.$q);
$parts = explode('/',$q);
$allowed = false;
switch ($parts[0]) {
	case 'dealer':
		if (secure_is_dealer()) $allowed = true;
		break;
	case 'vendor':
		if (secure_is_vendor()||secure_is_admin()) $allowed = true;
		break;
        case 'franchisee':
                if (secure_is_franchisee()) $allowed = true;
                break;
        case 'licensee':
                if (secure_is_licensee()) $allowed = true;
                break;
	case 'manager':
		if (secure_is_manager()) $allowed = true;
		break;
	case 'home':
		if (secure_is_admin()) $allowed = true;
		break;
	case 'exec':
		if (secure_is_superadmin()) $allowed = true;
		break;
	case 'xml':
		if (secure_is_superadmin()) $allowed = true;
		break;
        case 'wiki-img':
                if (secure_is_dealer()) $allowed = true;
                break;
	default:
		die('Access Denied');
}

if (!$allowed) die('Access Denied');

if (is_dir($real)) {
	$q .= '/index.html';
	$real .= '/index.html';
}

if (file_exists($real)) {
	fileoutput($real);
} else {
	echo "Can't find the file you requested (".$q."), sorry.";
}
