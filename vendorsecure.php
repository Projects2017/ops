<?php
//header("Location: http://www.pmddealer.com/down.php");
//exit(0);

$sql = "select * from login where id='".checkSession()."' and (type='V'";
if ($BoL_enabled) $sql .= " or type='A' or type='S'";
$sql .= ")";
$query = mysql_query( $sql );
checkDBError();

if( !($result = mysql_fetch_array( $query )) )
{
	if (!$duallogin) {
                setNote('Login session expired, please login again.');
                header( "Location: http://".$_SERVER['HTTP_HOST']."/ops/login.php" );
		exit;
	}
}
else
{
	$vendorid = $result['relation_id'];
	$gloginid = $result['id'];
	$dealerteam = "*";
	if ($result['type'] == 'V') {
		function secure_is_superadmin() {
			return 0;
		}

		function secure_is_admin() {
			return 0;
		}
		
		function secure_is_manager() {
			return 0;
		}

		function secure_is_dealer() {
			return 0;
		}

		function secure_is_vendor() {
			return 1;
		}
	} elseif ($result['type'] == 'S') {
		function secure_is_superadmin() {
			return 1;
		}

		function secure_is_admin() {
			return 1;
		}
		
		function secure_is_manager() {
			return 1;
		}

		function secure_is_dealer() {
			return 1;
		}

		function secure_is_vendor() {
			return 0;
		}
	} elseif ($result['type'] == 'A') {
		function secure_is_superadmin() {
			return 0;
		}

		function secure_is_admin() {
			return 1;
		}
		
		function secure_is_manager() {
			return 1;
		}

		function secure_is_dealer() {
			return 1;
		}

		function secure_is_vendor() {
			return 0;
		}
	} elseif ($result['type'] == 'M') {
		function secure_is_superadmin() {
			return 0;
		}

		function secure_is_admin() {
			return 0;
		}
		
		function secure_is_manager() {
			return 1;
		}

		function secure_is_dealer() {
			return 1;
		}

		function secure_is_vendor() {
			return 0;
		}
	} else {
		function secure_is_superadmin() {
			return 0;
		}

		function secure_is_admin() {
			return 0;
		}
		
		function secure_is_manager() {
			return 0;
		}

		function secure_is_dealer() {
			return 1;
		}

		function secure_is_vendor() {
			return 0;
		}
	}
}
?>
