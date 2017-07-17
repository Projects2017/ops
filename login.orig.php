<html>
<head>
	<title>RSS</title>
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
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
  <tr>
    <td valign="middle" align="center">
	<form action="selectvendor.php" method="post">
        <table border="0" cellspacing="0" cellpadding="0" height="179" width="378">
          <tr> 
            <td rowspan="3" width="130"> 
              <table border="1" cellspacing="0" cellpadding="0" width="100%" height="179" bordercolor="#000000">
                <tr> 
                  <td bgcolor="#FFFFFF"> 
                    <div align="center"><img src="images/logo.gif" width="100" height="73"></div>
                  </td>
                </tr>
              </table>
            </td>
				  <td background="images/background.gif"> 
              <table border="0" cellspacing="5" cellpadding="3" align="center">
                <tr> 
                  <td width="59"><b><i><font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF">Username</font></i></b></td>
                  <td width="140"><font face="Arial, Helvetica, sans-serif" size="2"> 
                    <input type="text" name="user" size=19>
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
