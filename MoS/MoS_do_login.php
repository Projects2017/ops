<?php

/*

	So, the login page goes here, MoS_user_secure will handle whether they are valid or not, if they aren't, back to the login screen, if they are
	then they are forwarded to the dealer screen.

	This way MUST be done when users will not be closing the browser. 

	When a person logs out using the logout button the cookie values are reset and so if a person hits login with nothing in the boxes it keeps them logged out.
	The normal RSS dealer and admin sections do not do this, the cookies are still set and so a person can log in like that, however the browser will most likely be
	closed.

	Since the browser is not closed at the Market, the values are reset to prevent that. HOWEVER, if a user were to hit back enough they could hit the dealer main
	page from when the user was logging in, hit refresh and all the cookies would be set as if the user had just logged in. This is very bad as people can make
	fake purchases with that user.

	By having this page as an intermediary where they are forwarded explicitly with the header command and not say, javascript it's like the person was never on
	this page since the server never returns this page to the user's computer so the person using the computer cannot get back to this page simply by hitting
	back to gain control of the person last logged in.

*/

require("MoS_database.php");

if ($_POST['admin_check'] == "on") {
	if (get_magic_quotes_gpc()) {
            $_POST['user'] = stripslashes($_POST['user']);
            $_POST['pass'] = stripslashes($_POST['pass']);
        }
        $id = checkLogin($_POST['user'], $_POST['pass']);
        // Unset user and password, they should never need to be used again.
        unset($_POST['user']);
        unset($_POST['pass']);
        
        if ($id) {
            createSession($id);
            header("Location: MoS_report_orders.php");
        } else {
            header('Location: MoS_login.php');
        }
}
else {
	require("MoS_user_secure.php");

	//-- Since they'll go to the login page if they're not secure, add the header now
	header("Location: MoS_dealer_main.php");
}
exit;


?>