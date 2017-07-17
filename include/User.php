<?php

class User {
   
    public static function getUserFromLogin($login_id){
        $user_login_info    = mysql_fetch_array(mysql_query("select * from login_session where id = '".$login_id."'"));
        $user_login         = mysql_fetch_array(mysql_query("select * from login where id = '".$user_login_info['login_id']."' "));
        $user               = mysql_fetch_array(mysql_query("select * from users where ID = '".$user_login['relation_id']."' "));
        return $user;
    }
}

?>