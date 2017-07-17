<?php
//header("Location: http://www.pmddealer.com/down.php");
//exit(0);

if (strpos($_SERVER['HTTP_HOST'],"pmddealer") !== false) {
    header("Location: http://". $_SERVER['HTTP_HOST'] ."/ops/down.php");
    exit(0);
}

$sql = "select * from login where id='".checkSession()."' and (type ='A' OR type = 'S')";
$query = mysql_query( $sql );
checkDBError($sql);

if( !($result = mysql_fetch_array( $query )) )
{
        setNote('Login session expired. Please login again.');
	if ($MoS_enabled) header( "Location: MoS_login.php" );
	else header( "Location: /login.php" );
	exit;
}
else
{
	$sql = "select * from users where ID = '".$result['relation_id']."'";
	$query = mysql_query($sql);
	$result2 = mysql_fetch_array($query);
	checkDBerror($sql);
	if( $result2['disabled'] == 'Y' )
	{
		log_this("Login attempted with disabled login ".$result['username'].".");
                setNote('Your account has been disabled.\nPlease contact Gary Davis at 614-273-0025');
                header( "Location: /login.php" );
		exit;
	}

	$userid = $result2['ID'];
	$dealerteam = $result2['team'];
	$gloginid = $result['id'];
	if ($result['type'] == 'S') {
		function secure_is_superadmin() { return 1; }
		function secure_is_admin() { return 1; }
		function secure_is_manager() { return 1; }
		$security = 'S';
	} elseif ($result['type'] == 'A') {
		function secure_is_superadmin() { return 0; }
		function secure_is_admin() { return 1; }
		function secure_is_manager() { return 1; }
		$security = 'Y';
	} elseif ($result['type'] == 'M') {
		function secure_is_superadmin() { return 0; }
		function secure_is_admin() { return 0; }
		function secure_is_manager() { return 1; }
		$security = 'N';
	} else {
		function secure_is_superadmin() { return 0; }
		function secure_is_admin() { return 0; }
		function secure_is_manager() { return 0; }
		$security = 'N';
	}
	function secure_is_dealer() { return 1; }
	function secure_is_vendor() { return 0; }
}
/* Old!
if ($user != "") {
	$usercookie = $user;
	$passcookie = $pass;
	setcookie('usercookie', $usercookie,0,"/");
	setcookie('passcookie', $passcookie,0,"/");
}

$sql = "select * from users where username='$usercookie' and password='$passcookie' and disabled!='Y' and (admin='Y' or admin='S')";
$query = mysql_query($sql);
checkDBError();

if (!($result = mysql_fetch_array($query))) {
	setcookie('usercookie', "",0,"/");
	setcookie('passcookie', "",0,"/");
	setcookie('securitycookie', "",0,"/");

	header("Location: login.php");
	exit;
}
else {
	$userid = $result['ID'];
	$security = $result['admin'];
	$dealerteam = $result['team'];
	setcookie('usercookie', $usercookie,0,"/");
	setcookie('passcookie', $passcookie,0,"/");
	setcookie('securitycookie', $security,0,"/");
}

function secure_is_superadmin() {
	$sql = "select * from users where id='".$GLOBALS['userid']."' and (admin='S')";
	$query = mysql_query($sql);
	checkDBError();
	$valid = mysql_num_rows($query);
	@mysql_free_result($query);
	return $valid;
}

function secure_is_admin() {
	$sql = "select * from users where id='".$GLOBALS['userid']."' and (admin='Y' or admin='S')";
	$query = mysql_query($sql);
	checkDBError();
	$valid = mysql_num_rows($query);
	@mysql_free_result($query);
	return $valid;
}

function secure_is_manager() {
	$sql = "select * from users where id='".$GLOBALS['userid']."' and (admin='M' or admin='Y' or admin='S')";
	$query = mysql_query($sql);
	checkDBError();
	$valid = mysql_num_rows($query);
	@mysql_free_result($query);
	return $valid;
}

function secure_is_dealer() {
	return 1;
}

function secure_is_vendor() {
	return 0;
}
*/
log_this_entry_now_394393();
?>
