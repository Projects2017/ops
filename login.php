<?php
require ('header.php');
require('inc_database.php');
if (strpos($_SERVER['HTTP_HOST'],"pmddealer") !== false) {
    header("Location: http://". $_SERVER['HTTP_HOST'] ."/ops/down.php");
    exit(0);
}
$notes = getNotes();
// if (substr($_SERVER['REMOTE_ADDR'],0,5) == '64.12') {
//    $notes[] = "AOL currently causes issues with our login system.\nYou may experience unexpected session timeouts.";
// }
?>


<html>
<head>
	<title>RSS ADMINISTRATION</title>


    </head>
<body class="login-rss">
    <div class="container-fluid login-rss">
        <div class="row main-wrapper-login">
            <div class="container wrapper-login-rss">
                <div class="col-lg-5  col-md-5 login-rss-second-mobile">
                    <div class="col-lg-12 col-md-12 rss-logo-login-mobile">
                        <object data="new_css/img/logo.svg"  class="rss-logo-login-mobile" type="image/svg+xml"></object>
                    </div>
                </div>
                <div class="col-lg-7 col-md-7 login-rss-first">
                     <h1 class="title-rss-s2">Welcome to RSS</h1>
                    <form>
                        <div class="form-group">
                            <div class="username"><object data="new_css/img/user1.svg"  class="rss-logo-login-username" type="image/svg+xml"></object></div>
                           <input type="text" class="form-control username-login" id="username" placeholder="User Name">
                        </div>
                        <div class="form-group">
                            <div class="pass-icon"><object data="new_css/img/password.svg"  class="rss-logo-login-password" type="image/svg+xml"></object></div>
                           <input type="password" class="form-control username-password" id="password" placeholder="Password">
                        </div>
                        <div class="row">
                                <div class="col-lg-6">
                                <h4 class="footer-login">Forgot Password?</h4>
                            </div>
                            <div class="col-lg-6">
                                <button type="submit" class="btn btn-lg btn-login-rss">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-5  col-md-5 login-rss-second-desktop">
                    <div class="col-lg-12 col-md-12 rss-logo-login-desktop">
                        <object data="new_css/img/logo.svg"  class="rss-logo-desktop-login" type="image/svg+xml"></object>
                   </div>
                </div>
            </div>
        </div>
        <?php require ('footer-login.php');?>
    </div>
</body>
</html>
