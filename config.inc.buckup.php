<?php

defined('__APPLICATION_ENV__')
|| define('__APPLICATION_ENV__', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'ops'));

$outmail_override = 'will@retailservicesystems.com';
$admin_pass = '7tv*A5f6'; // password required to change a user to highest security
$databasehost = 'localhost';
$databasepass = '';
$tmpdir = '/tmp/';

if (__APPLICATION_ENV__ == 'ops') {
    $databasename = 'rss';
    $databaseuser = 'root';

    $MoS_enabled = false;
    $MoS_clients['pmdmos']['IP'] = '127.0.0.1';
    $MoS_clients['pmdmos']['procurl'] = 'http://pmdmos.dev/MoS/MoS_updatepo.php?po=%s&date=%s'; // First %s is the PO #, second %s is the date processed
    $MoS_MasterFTPDBFile = 'MoS_testdump.sql';
    $MoS_MasterFTPDBFileGZ = $MoS_MasterFTPDBFile.'.gz';
    $backorder_enable = true;
    $as2_testing = true;
    $corsicana_processXML = true;
} elseif (__APPLICATION_ENV__ == 'market') {
    $databasename = 'pmdmos';
    $databaseuser = 'pmdmos';
    $MoS_enabled = true; // Is this site MoS or normal?
    $MoS_MasterPath = 'http://pmdops.dev/';
} else {
    die("This isn't configured");
}
