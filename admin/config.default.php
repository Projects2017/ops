<?php
// These values MUST be set in config.inc.php
// They will not be read here, this is just an example

$basedir = dirname(__FILE__).'/'; // Note trailing slash
$docdir = $basedir.'doc/'; // Document directory
$vdocdir = $basedir.'d/'; // Document directory visible from superusers
$xmldir = $docdir.'xml/'; // XML Documents directory
$tmpdir = dirname(dirname(__FILE__)).'/tmp/'; // Path to temporary files directory
$databasename = "dbname";
$databasehost = 'localhost';
$databaseuser = 'dbuser';
$databasepass = '';
//$link = mysql_pconnect('localhost', 'dbuser', 'dbpass');
//mysql_select_db($databasename);
$admin_pass = "adminpass"; // password required to change a user to highest security
$thumbconverter = 'imagemagick'; // 'gd1','gd2','imagemagick'

// Market Order System
$MoS_enabled = false; // Is this site MoS or normal?
$MoS_IP = ''; // MoS IP to allow orders from

// Market Order System Master..
$MoS_MasterPath = 'http://ext.pmddealer.com/';
$MoS_MasterFTPHost = 'ext.pmddealer.com';
$MoS_MasterFTPUser = 'pmdextdev';
$MoS_MasterFTPPass = 'xGdf783TT';
$MoS_MasterFTPDBFile  = "MoS_dump.sql";
$MoS_MasterFTPDBFileGZ = $MoS_MasterFTPDBFile.".gz";
?>