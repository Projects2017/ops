<?php
header( "Location: http://".$_SERVER['HTTP_HOST']."/ops/login.php" );
exit;

// Old Stuff
setcookie('pmd_suuser', null, time()-3600, "/");
setcookie('usercookie', null, time()-3600, "/");
setcookie('passcookie', null, time()-3600, "/");
setcookie('vendoruser', null, time()-3600, "/");
setcookie('vendorpass', null, time()-3600, "/");
setcookie('attempts', null, time()-3600, "/");
require_once("inc_database.php"); ?>
<html>
<head>
	<title>RSS Vendor Login</title>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#EDECDA">
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
  <tr>
    <td valign="middle" align="center">
	<form action="<?php if ($BoL_enabled) { echo "shipping.php"; } else { echo "vendor_select.php"; } ?>" method="post">
        <table border="0" cellspacing="0" cellpadding="0" height="179" width="378">
          <tr> 
            <td rowspan="3" width="130"> 
              <table border="1" cellspacing="0" cellpadding="0" width="100%" height="179" bordercolor="#000000">
                <tr> 
                  <td bgcolor="#FFFFFF"> 
                    <div align="center"><img src="/images/logo.gif" width="100" height="73"></div>
                  </td>
                </tr>
              </table>
            </td>
				  <td background="/images/background.gif"> 
              <table border="0" cellspacing="5" cellpadding="3" align="center">
                <tr>
                  <td colspan="2"><center><b><font face="Arial, Helvetica, sans-serif" size="2">RSS Vendor Login</font></b></center></td>
                </tr>
                <tr> 
                  <td width="59"><b><i><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF">Username</font></i></b></td>
                  <td width="140"><font face="Arial, Helvetica, sans-serif" size="2"> 
                    <input type="text" name="vendoruser" size=19>
                    </font></td>
                  
                </tr>
                <tr> 
                  <td width="59"><b><i><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF">Password</font></i></b></td>
                  <td width="140"><font face="Arial, Helvetica, sans-serif" size="2"> 
                    <input type="password" name="vendorpass" size=19>
                    </font></td>
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
