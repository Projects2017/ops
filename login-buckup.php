<?
require('inc_database.php');
if (strpos($_SERVER['HTTP_HOST'],"pmddealer") !== false) {
    header("Location: http://". $_SERVER['HTTP_HOST'] ."/down.php");
    exit(0);
}
$notes = getNotes();
// if (substr($_SERVER['REMOTE_ADDR'],0,5) == '64.12') {
//    $notes[] = "AOL currently causes issues with our login system.\nYou may experience unexpected session timeouts.";
// }

?><html>
<head>
    <title>RSS</title>
    <style type="text/css">
        body {
            margin: 0 0 0 0;
            background-color: #EDECDA;
        }

        .login {
            width: 378px;
        }

        .login p {
            font-weight: bold;
            font-style: italic;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 0.8em;
            color: rgb(255,255,255);
            display: block;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .login p.error {
            margin: 0 auto;
            text-align: center;
            display: block;
            font-style: normal;
            color: white;
            background-color: red;
        }

        .login tbody tr {
            background-color: #cccc99;
        }

        .login input.submit {
            background-color:#CA0000;
            color: rgb(255,255,255);
            margin: 0 auto;
            display: block;
        }

        .login input.text {
            width: 250px;
        }
        }
    </style>
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
    <tr>
        <td valign="middle" align="center">
            <form action="distributer.php" method="post">
                <table border="0" cellspacing="0" cellpadding="0" height="179" width="378">
                    <tr>
                        <td rowspan="3" width="130">
                            <table class="login" border="0" cellspacing="0" cellpadding="0" >
                                <thead>
                                <!-- Above Login Fields -->
                                <tr>
                                    <td width="19px"><img src="images/bg_tl.gif" border="0"></td>
                                    <td colspan="2" style="background: url(images/bg_top.gif) repeat-x top left; background-color: #cccc99;">&nbsp;</td>
                                    <td width="19px"><img src="images/bg_tr.gif" border="0"></td>
                                </tr>
                                </thead>
                                <tbody>
                                <? foreach ($notes as $note): ?>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td colspan="2"><p class="error"><? echo nl2br(htmlentities($note)); ?></p></td>
                                        <td>&nbsp;</td>
                                    </tr>
                                <? endforeach; ?>
                                <? if (isset($_GET['note'])&&$_GET['note']): ?>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td colspan="2"><p class="error"><? echo htmlentities($_GET['note']); ?></p></td>
                                        <td>&nbsp;</td>
                                    </tr>
                                <? endif; ?>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td><p>Username</p></td>
                                    <td><input class="text" type="text" name="user"></td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td><p>Password</p></td>
                                    <td><input class="text" type="password" name="pass"></td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td colspan="2" align="center"><input class="submit" type="submit" value="Login" name="submit"></td>
                                    <td>&nbsp;</td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td width="19px"><img src="images/bg_bl.gif" border="0"></td>
                                    <td colspan="2" style="background: url(images/bg_bottom.gif) repeat-x top left; background-color: cccc99;">&nbsp;</td>
                                    <td width="19px"><img src="images/bg_br.gif" border="0"></td>
                                </tr>
                                </tfoot>
                            </table>
                            <input type="hidden" name="action" value="login">
            </form>
        </td>
    </tr>
</table>
</body>
</html>
