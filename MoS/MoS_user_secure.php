<?php
//header("Location: http://www.pmddealer.com/down.php");
//exit(0);

$sql = "DELETE FROM MoS_session WHERE `lastaccess` >= DATE_SUB(CURDATE(), INTERVAL 10 MINUTE)";

if ($_POST['user'] && $_POST['pass']) {
	if ($_COOKIE['pmd_session_id']) {
		if (!is_numeric($_COOKIE['pmd_session_id'])) die("Non-Numeric Session ID");
		$sql = "SELECT id, `key` FROM MoS_session WHERE id = '".$_COOKIE['pmd_session_id']."' AND `key` = '".$_COOKIE['pmd_session_key']."'";
		$query = mysql_query($sql);
		checkDBerror($sql);
		if (mysql_num_rows($query)) {
			$sql = "DELETE FROM MoS_session WHERE `id` = '".$_COOKIE['pmd_session_id']."'";
			mysql_query($sql);
			checkDBerror($sql);
		}
	}

	$sql = "select * from login where `username`='".$_POST['user']."' and `password`='".$_POST['pass']."' and `type` != 'V'";
	$query = mysql_query( $sql );
	checkDBError($sql);
	if( !($result = mysql_fetch_array( $query )) )
	{
		setcookie('pmd_session_id', null, time()-3600, "/");
		setcookie('pmd_session_key', null, time()-3600, "/");
		header( "Location: MoS_login.php" );
		exit;
	} else {
		$sql = "SELECT * FROM users WHERE ID = '".$result['relation_id']."'";
		$query = mysql_query( $sql );
		checkDBError($sql);
		$result = mysql_fetch_assoc($query);
		if( $result['disabled'] == 'Y' ) {
			?>
			<html>
			<body bgcolor="#EDECDA">
			<div align="center">
			<b>Your account has been disabled.</b><br>
			Please contact Gary Davis at 614-273-0025
			</div>
			</body>
			</html>
			<?php
			setcookie('pmd_session_id', null, time()-3600, "/");
			setcookie('pmd_session_key', null, time()-3600, "/");
			exit;
		}
		$userid = $result['ID'];
		$dealerteam = $result['team'];
		$_COOKIE['pmd_session_key'] = uniqid('');

		$sql = "INSERT INTO MoS_session (`key`,`user_id`,`admin`) VALUES ('".$_COOKIE['pmd_session_key']."',".$userid.",'".$result['admin']."')";
		$query = mysql_query($sql);
		checkDBerror($sql);
		$_COOKIE['pmd_session_id'] = mysql_insert_id();
		setcookie( 'pmd_session_id', $_COOKIE['pmd_session_id'], 0, "/" );
		setcookie( 'pmd_session_key', $_COOKIE['pmd_session_key'], 0, "/" );
	}
} elseif ($_COOKIE['pmd_session_id']) {
	$sql = "SELECT `user_id`, `admin` FROM MoS_session WHERE `id` = '".$_COOKIE['pmd_session_id']."' AND `key` = '".$_COOKIE['pmd_session_key']."' AND `lastaccess` >= DATE_SUB(CURDATE(), INTERVAL 5 MINUTE)";
	$query = mysql_query($sql);
	checkDBerror($sql);
	if( !($result = mysql_fetch_array( $query )) ) { // Failed
		setcookie('pmd_session_id', null, time()-3600, "/");
		setcookie('pmd_session_key', null, time()-3600, "/");
		header( "Location: MoS_login.php" );
		exit;
	} else { // Success
		$sql = "select * from users where `ID` ='".$result['user_id']."'";
		$query = mysql_query( $sql );
		checkDBError($sql);
		if (!($result = mysql_fetch_array($query))) {
			// User was deleted... kill off session and let them login
			$sql = "DELETE FROM MoS_session WHERE `id` = '".$_COOKIE['pmd_session_id']."'";
			mysql_query($sql);
			checkDBerror($sql);
			setcookie('pmd_session_id', null, time()-3600, "/");
			setcookie('pmd_session_key', null, time()-3600, "/");
			header( "Location: MoS_login.php" );
			exit;
		}
		$userid = $result['ID'];
		$dealerteam = $result['team'];
		$sql = "UPDATE MoS_session SET `lastaccess` = NOW() WHERE id = '".$_COOKIE['pmd_session_id']."'";
		mysql_query($sql);
		checkDBerror($sql);
	}
} else {
	header( "Location: MoS_login.php" );
	exit;
}


/*
if ($suuser) {
	$sql = "select * from users where username='$adminusercookie' and password='$adminpasscookie' and disabled!='Y' and (admin='Y' or admin='S')";
	$query = mysql_query($sql);
	checkDBError();
	if ($result = mysql_fetch_array($query)) {
		$sql = "select * from users where ID='$suuser' and (admin!='Y' or admin!='S')";
		$query = mysql_query( $sql );
		checkDBError();
		if ($result = mysql_fetch_array($query)) {
			setcookie('usercookie',$result[username],0,"/");
			setcookie('passcookie',$result[password],0,"/");
			$user = $result[username];
			$pass = $result[password];
		}
	}
}
*/

// Added by Radium Development (dev: Will Robertson)
// will.robertson@radiumdev.net
// Used for apps used both by admin and regular user.
function secure_is_superadmin() {
	$sql = "select * from users where id='".$GLOBALS['userid']."' and (admin='S')";
	$query = mysql_query($sql);
	checkDBError($sql);
	$valid = mysql_num_rows($query);
	@mysql_free_result($query);
	return $valid;
}

function secure_is_admin() {
	$sql = "select * from users where id='".$GLOBALS['userid']."' and (admin='Y' or admin='S')";
	$query = mysql_query($sql);
	checkDBError($sql);
	$valid = mysql_num_rows($query);
	@mysql_free_result($query);
	return $valid;
}

function secure_is_manager() {
	$sql = "select * from users where id='".$GLOBALS['userid']."' and (admin='M' or admin='Y' or admin='S')";
	$query = mysql_query($sql);
	checkDBError($sql);
	$valid = mysql_num_rows($query);
	@mysql_free_result($query);
	return $valid;
}

function secure_is_vendor() {
	return 0;
}

function secure_is_dealer() {
	return 1;
}

?>
