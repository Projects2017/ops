<?php
 include('../database.php');
 include('../secure.php');
 $userinfo = db_user_getuserinfo($_REQUEST['dealer_id']);
 if ($userinfo['disabled'] == 'Y') die('Access Denied');
 if ($userinfo['nonPMD'] == 'Y') die('Access Denied');
 if (!secure_is_manager() && $userinfo['admin'] != 'M') die('Access Denied');
?>
<!DOCTYPE html PUBLIC "-//w3c//dtd html 4.0 transitional//en">
<html>
<head>
  <meta http-equiv="Content-Type"
 content="text/html; charset=iso-8859-1">
  <meta name="Author" content="Gary Davis">
  <meta name="GENERATOR"
 content="Mozilla/4.79 [en] (Windows NT 5.0; U) [Netscape]">
  <title>RSS - <?php echo $userinfo['last_name'].' - '.$userinfo['city'].', '.$userinfo['state']; ?></title>
</head>
<body bgcolor="#edecda">
<?php /* require('../menu.php'); */ ?>
&nbsp;
<center>
<table cellspacing="0" cellpadding="5">
  <tbody>
    <tr align="center" valign="CENTER" bgcolor="#cccc99">
      <td align="center" valign="CENTER">
      <center><b><font face="Arial,Helvetica"><font size="+2"><?php echo $userinfo['last_name']; ?></font></font></b></center>
      </td>
      <td>
      <center><b><font face="Arial,Helvetica"><font size="+2"><?php echo $userinfo['first_name']; ?></font></font></b></center>
      </td>
    </tr>
    <tr align="center" valign="CENTER" bgcolor="#ffffff">
      <td>
      <center><img
<?php if (file_exists('photos/'.$userinfo['ID'].'.jpg')) { ?>
 src="<?php echo 'photos/'.$userinfo['ID'].'.jpg'; ?>"
<?php } else { ?>
 src="blank.jpg"
<?php } ?>
 nosave=""
 title="" alt=""></center>
      </td>
      <td>
      <center><b><font face="Arial,Helvetica"><font color="#cc0000"><font
	   size="+1"><?php echo $userinfo['address']; ?></font></font></font></b><br>
	   <b><font face="Arial,Helvetica"><font color="#cc0000"><font
	   size="+1"><?php echo $userinfo['city']; ?>, <?php echo $userinfo['state']; ?>  <?php echo $userinfo['zip']; ?></font></font></font></b> <br>
      </font></font></font></b><br>
	        <b><font face="Arial,Helvetica"><font color="#cc0000"><font
 size="+1"><?php echo $userinfo['phone']; ?><br>
      <table>
        <tbody>
          <tr>
            <td><a href="mailto:<?php echo $userinfo['email']; ?>">
            <img src="aniemail19.gif"border="0">
          </tr>
        </tbody>
      </table>
      <br>
      </center>
      </td>
    </tr>
  </tbody><caption align="bottom">&nbsp;<br>
  </caption>
</table>
</center>
<br>
&nbsp;
<br>
&nbsp;
</body>
</html>
