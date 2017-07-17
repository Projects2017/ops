<?php
if (strpos($_SERVER['HTTP_HOST'],"pmddealer") !== false) {
    header("Location: http://". $_SERVER['HTTP_HOST'] ."/ops/down.php");
    exit(0);
}

//header("Location: http://www.pmddealer.com/down.php");
//exit(0);
if ($_COOKIE['pmd_suuser']||$_GET['suuser']) {
	if ($_GET['suuser']) {
		$suuser = $_GET['suuser'];
	} elseif ($_COOKIE['pmd_suuser']) {
		$suuser = $_COOKIE['pmd_suuser'];
	}

	$sql = "select * from login where id='".checkSession()."' and (type='A' or type='S')";
	$query = mysql_query($sql);
	checkDBError();
	if ($result = mysql_fetch_array($query)) {
		$sql = "select * from users where ID='$suuser'";
		$query2 = mysql_query( $sql );
		checkDBError($sql);
		$result2 = mysql_fetch_array($query2);
		$userid = $result2['ID'];
		$dealerteam = $result2['team'];
		if ($result['type'] == 'S') {
			function secure_is_superadmin() { return 1; }
		} else {
			function secure_is_superadmin() { return 0; }
		}
		function secure_is_admin() { return 1; }
		function secure_is_manager() { return 1; }
		function secure_is_dealer() { return 1; }
		function secure_is_vendor() { return 0; }
		setcookie( 'pmd_suuser', $suuser, 0, "/" );
		$_COOKIE['pmd_suuser'] = $suuser;
	} else {
                setNote('Login session expired. Please login again.');
                clearSession();
		setcookie('pmd_suuser', null, time()-3600, "/");
		$_COOKIE['pmd_suuser'] = null;
		header( "Location: http://".$_SERVER['HTTP_HOST']."/ops/login.php" );
		exit;
	}
} else {
	$sql = "select * from login where id='".checkSession()."' and type != 'V'";
	$query = mysql_query( $sql );
	checkDBError($sql);

	if( !($result = mysql_fetch_array( $query )) )
	{
                clearSession();
                setNote('Login session expired. Please login again.');
		header( "Location: http://".$_SERVER['HTTP_HOST']."/ops/login.php" );
		exit;
	}
	else
	{
		$sql = "select * from users where ID = '".$result['relation_id']."'";
		$query = mysql_query($sql);
		$result2 = mysql_fetch_assoc($query);
		checkDBerror($sql);
		if( $result2['disabled'] == 'Y' )
		{
			log_this("Login attempted with disabled login ".$result['username']." dealer_id: ".$result2['ID'].".");
                        clearSession();
                        setNote("Your account has been disabled.\nPlease contact Gary Davis at 614-273-0025");
                        header( "Location: http://".$_SERVER['HTTP_HOST']."/op/login.php" );
                        exit;
		}
                if ($result['type'] == 'D' && $result2['dealer_type'] == 'L') {
                    $sql = "SELECT * FROM  `order_forms` WHERE `user` = '".$result['relation_id']."' AND `ordered` > DATE_SUB( CURDATE( ) , INTERVAL 30 DAY ) AND `total` >= 0 LIMIT 1";
                    $result = mysql_query($sql);
                    checkDBerror($sql);
                    if (!mysql_num_rows($result)) {
			log_this("Login attempted with 30 day inactive login ".$result['username']." dealer_id: ".$result2['ID'].".");
                        clearSession();
                        setNote("Your account has been inactive for 30 days,\n please contact Gary Davis at 614-273-0025 \n to reactive your account and obtain a new password.");
                        header( "Location: http://".$_SERVER['HTTP_HOST']."/ops/login.php" );
                        exit;
                    }
                }
		$userid = $result2['ID'];
		$dealerteam = $result2['team'];
		$gloginid = $result['id'];
		if ($result['type'] == 'S') {
			function secure_is_superadmin() { return 1; }
			function secure_is_admin() { return 1; }
			function secure_is_manager() { return 1; }
		} elseif ($result['type'] == 'A') {
			function secure_is_superadmin() { return 0; }
			function secure_is_admin() { return 1; }
			function secure_is_manager() { return 1; }
		} elseif ($result['type'] == 'M') {
			function secure_is_superadmin() { return 0; }
			function secure_is_admin() { return 0; }
			function secure_is_manager() { return 1; }
		} else {
			function secure_is_superadmin() { return 0; }
			function secure_is_admin() { return 0; }
			function secure_is_manager() { return 0; }
		}
		if ($result2['nonPMD'] == 'Y') {
			function secure_is_nonPMD() { return 1; }
		} else {
			function secure_is_nonPMD() { return 0; }
		}

		function secure_is_dealer() { return 1; }
		function secure_is_vendor() { return 0; }
	}
	/* Disable account if no stats for 14 days
	if (secure_is_dealer() && (!secure_is_manager()) && (!secure_is_nonPMD()) && (time() > strtotime("Jul 19th, 2007"))) {
		$sql = "SELECT stat_date FROM salestats WHERE user_id=$userid AND stat_date > SUBDATE(NOW(),14) LIMIT 1";
		$query = mysql_query($sql);
		checkdberror($sql);
		if (!mysql_num_rows($query)) {
			?>
			<html>
			<body bgcolor="#EDECDA">
			<div align="center">
			<b>Your account has been disabled due to lack of entered stats.</b><br>
			<b>Please contact your manager to with your stats, so they may enter them</b></br>
			If you have further questions contact<br>
				 Gary Davis at 614-273-0025<br>
			</div>
			</body>
			</html>
			<?php
			exit;
		}
		mysql_free($query);
	}
	*/
}

log_this_entry_now_394393();
?>
