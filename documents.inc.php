<?php

// Who requires output of db_user_getlist($options)
// Expire requires a PHP timestamp i.e. time()
function document_add($filter = null, $subject, $source, $expire = null) {
	global $basedir; // We need the base dir var
	require($basedir."/include/inc_bbcode.php");
	if (!$expire)
		$expire = document_defaultexpire();
	if (is_null($filter))
		$who = db_user_getlist();
        else
                $who = db_user_filterlist($filter);
	$text = $bbcode->parse($source);
	$expire = date('Y-m-d G:i:s', $expire);
	$subject = mysql_escape_string($subject);
	$text = mysql_escape_string($text);
	$source = mysql_escape_string($source);
        $filterstr = mysql_escape_string(json_encode($filter));
        
	$sql = "INSERT INTO `document` (`subject`, `text`, `source`,`filter`,`expire`)VALUES('".$subject."','".$text."','".$source."', '".$filterstr."', '".$expire."')";
	mysql_query($sql);
	checkDBerror($sql);
	$document_id = mysql_insert_id();

	// Release Documents to users unread area.
	foreach ($who as $user) {
		$sql = "INSERT INTO `document_unread` (`user_id`,`document_id`) VALUES ('".$user['id']."',".$document_id.")";
		mysql_query($sql);
		checkDBerror($sql);
	}
	return $document_id;
}

function document_update($id, $subject, $source, $expire = null) {
	if (!is_numeric($id)) return false;
	global $basedir; // We need the base dir var
	require($basedir."/include/inc_bbcode.php");
	if ($expire) {
		$isexpire = true;
		$expire = ", `expire` = '".date('Y-m-d G:i:s', $expire)."'";
	} else {
		$expire = '';
	}
	$text = $bbcode->parse($source);
	$subject = mysql_escape_string($subject);
	$text = mysql_escape_string($text);
	$source = mysql_escape_string($source);
	$sql = "UPDATE `document` SET `subject` = '".$subject."', `text` = '".$text."', `source` = '".$source."'".$expire." WHERE `id` = '".$id."'";
	mysql_query($sql);
	checkDBerror($sql);
}

function document_defaultexpire() {
	return strtotime("+1 month");
}

function document_list($user_id = null, $include_read = false, $dir = 0) {
	if ($dir == 1) {
		$dir = "ASC";
	} else {
		$dir = "DESC";
	}
	if (!$include_read && $user_id) {
		$sql = "SELECT `document_unread`.`document_id` as id, `document`.`filter` as `filter`, `subject`, `expire`  FROM document_unread INNER JOIN `document` ON `document`.`id` = `document_unread`.`document_id` WHERE `user_id` = ".$user_id." ORDER BY `document_id` ".$dir;
		$result = mysql_query($sql);
		checkdberror($sql);
	} else {
		$sql = "SELECT `id`, `filter`, `subject`, `expire` FROM `document` ORDER BY `id` ".$dir;
		$result = mysql_query($sql);
		checkdberror($sql);
	}
	$return = array();
        $filtercheck = array();
	while ($row = mysql_fetch_assoc($result)) {
            if (!$user_id && secure_is_admin()) {
                $check = true;
            } elseif (isset($filtercheck[$row['filter']])) {
                $check = $filtercheck[$row['filter']];
            } else {
                $check = db_user_checkinfilter($user_id, json_decode($row['filter'], true, 2));
                // echo ("Checked... User ". $user_id ." against filter: ". json_decode($row['filter'], true, 2). " and got... ".($check?'true':'false'));
                $filtercheck[$row['filter']] = $check;
            }
            if ($check) {
                $return[] = array(
                    'id' => $row['id'],
                    'subject' => $row['subject'],
                    'expire' => strtotime($row['expire'])
                );
            }
	}
	return $return;
}

function document_read($document_id, $user_id = null) {
	if (!is_numeric($document_id)) return false;
	// Check that they have it bound...
	if ($user_id) {
		if (!is_numeric($user_id)) return false;
	}   
	$sql = "SELECT `id`, `subject`, `text`, `source`, `filter`, `expire` FROM `document` WHERE `id` = ".$document_id;
	$result = mysql_query($sql);
	checkdberror($sql);
	$return = mysql_fetch_assoc($result);
        if ($user_id) {
            if (!db_user_checkinfilter($user_id, json_decode($return['filter'], true, 2))) {
                return false;
            }
        } elseif (!secure_is_admin()) {
            return false;
        }
	if ((!$_COOKIE['pmd_suuser'])&&$user_id) {
                $sql = "DELETE FROM `document_unread` WHERE `user_id` = ".$user_id." AND `document_id` = ".$document_id;
		mysql_query($sql);
		checkdberror($sql);
	}
	return $return;
}


function document_del($document_id) {
	if (!is_numeric($document_id)) return false;
	$sql = "DELETE FROM `document_unread` WHERE `document_id` = ".$document_id;
	mysql_query($sql);
	checkdberror($sql);
	$sql = "DELETE FROM `document` WHERE `id` = ".$document_id;
	mysql_query($sql);
	checkdberror($sql);
}

// Cleanup Expired Documents
function document_cleanup() {
    return; // Just killing this off for now.. I don't want things to expire yet.
	$sql = "SELECT `id` FROM `document` WHERE `expire` < NOW()";
	$result = mysql_query($sql);
	checkdberror($sql);
	while ($row = mysql_fetch_assoc($result)) {
		document_del($row['id']);
	}
}

?>
