<?php
require_once('MoS_database.php');
/* vim:set tabstop=4: */
//header("Location: http://www.pmddealer.com/down.php");
//exit(0);

if ($_GET['a'] == "out") {
	if ($_COOKIE['pmd_session_id']) {
		if (!is_numeric($_COOKIE['pmd_session_id'])) die("Non-Numeric Session ID");
		$sql = "SELECT `id`, `key` FROM `MoS_session` WHERE `id` = '".$_COOKIE['pmd_session_id']."' AND `key` = '".$_COOKIE['pmd_session_key']."'";
		$query = mysql_query($sql);
		checkDBerror($sql);
		if (mysql_num_rows($query)) {
			$sql = "DELETE FROM `MoS_session` WHERE `id` = '".$_COOKIE['pmd_session_id']."'";
			mysql_query($sql);
			checkDBerror($sql);
		}
	}
	foreach($_COOKIE as $key => $value) {
		setcookie($key, null, time()-3600, "/");
	}

}
?><html>
<head>
	<title>RSS - Market Order System Login</title>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#EDECDA">
<?php
if( $attempts >= 3 )
{
?>
	Login Failed After 3 Attempts
<?php
	exit;
}


//-- In the normal RSS system the login page goes right to the desired page and secure.php handles the rest. This is BAD when
//-- people are not going to be closing the browser. See MoS_do_login.php for an explanation.
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
  <tr>
    <td valign="middle" align="center">
	<form action="MoS_do_login.php" method="post">
        <table border="0" cellspacing="0" cellpadding="0" height="179" width="378">
		  <TR><TD colspan=2 align=center><H3>Market Order System</H3></TD></TR>
          <tr> 
            <td rowspan="3" width="130"> 
              <table border="1" cellspacing="0" cellpadding="0" width="100%" height="179" bordercolor="#000000">
                <tr> 
                  <td bgcolor="#FFFFFF"> 
                    <div align="center"><img src="../images/logo.gif" width="100" height="73"></div>
                  </td>
                </tr>
              </table>
            </td>
				  <td background="../images/background.gif"> 
              <table border="0" cellspacing="5" cellpadding="3" align="center">
                <tr> 
                  <td width="59"><b><i><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF">Username</font></i></b></td>
                  <td width="140"><font face="Arial, Helvetica, sans-serif" size="2"> 
                    <input type="text" name="user" size=19 <?php if ($_POST['user']) echo "value=\"".$_POST['user']."\"";?>>
                    </font></td>
                  
                </tr>
                <tr> 
                  <td width="59"><b><i><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF">Password</font></i></b></td>
                  <td width="140"><font face="Arial, Helvetica, sans-serif" size="2"> 
                    <input type="password" name="pass" size=19>
                    </font></td>
                </tr>
                <tr> 
                  <td colspan=2 align="right"> 
                    <div align="center"><B><font face="Arial, Helvetica, sans-serif" size="2" color="white"> 
                      <input type="checkbox" id="admin_check" name="admin_check"><label for="admin_check">Log in as administrator
                      </label></B></font></div>
                  </td>
                </tr>
                <tr> 
                  <td colspan=2 align="right"> 
                    <div align="center"><font face="Arial, Helvetica, sans-serif" size="2"> 
                      <input type="submit" value="Login" style="background-color:#CA0000;color:white" name="submit">
                      </font></div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </form>
</td>
  </tr>
</table>
</body>
</html>
