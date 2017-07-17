<?php

// Who requires output of db_user_getlist($options)
// Expire requires a PHP timestamp i.e. time()
function announce_add($who = null, $subject, $source, $expire = null) {
	global $basedir; // We need the base dir var
	require($basedir."/include/inc_bbcode.php");
	if (!$expire)
		$expire = announce_defaultexpire();
	if (is_null($who))
		$who = db_user_getlist();
	$text = $bbcode->parse($source);
	$expire = date('Y-m-d G:i:s', $expire);
	$subject = mysql_escape_string($subject);
	$text = mysql_escape_string($text);
	$source = mysql_escape_string($source);
	$sql = "INSERT INTO `announcement` (`subject`, `text`, `source`, `expire`)VALUES('".$subject."','".$text."','".$source."', '".$expire."')";
	mysql_query($sql);
	checkDBerror($sql);
	$announcement_id = mysql_insert_id();

	// Release Announcements to users
	foreach ($who as $user) {
		$sql = "INSERT INTO `announcement_bound` (`user_id`,`announcement_id`, `read`) VALUES ('".$user['id']."',".$announcement_id.", 0)";
		mysql_query($sql);
		checkDBerror($sql);
	}
	return $announcement_id;
}

function announce_update($id, $subject, $source, $expire = null) {
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
	$sql = "UPDATE `announcement` SET `subject` = '".$subject."', `text` = '".$text."', `source` = '".$source."'".$expire." WHERE `id` = '".$id."'";
	mysql_query($sql);
	checkDBerror($sql);
}

function announce_defaultexpire() {
	return strtotime("+1 month");
}

function announce_list($user_id = null, $include_read = false, $dir = 0) {
	if ($dir == 1) {
		$dir = "ASC";
	} else {
		$dir = "DESC";
	}
	if ($user_id) {
		if ($include_read) {
			$sql = "SELECT `announcement_id` as id, `read` FROM announcement_bound WHERE `user_id` = ".$user_id." ORDER BY `announcement_id` ".$dir;
		} else {
			$sql = "SELECT `announcement_id` as id, `read` FROM announcement_bound WHERE `user_id` = ".$user_id." AND `read` = '0' ORDER BY `announcement_id` ".$dir;
		}
		$result = mysql_query($sql);
		checkdberror($sql);
	} else {
		$sql = "SELECT `id`, 0 as `read` FROM `announcement` ORDER BY `id` ".$dir;
		$result = mysql_query($sql);
		checkdberror($sql);
	}
	$return = array();
	while ($row = mysql_fetch_assoc($result)) {
		$sql = "SELECT `subject`, `text`, `source`, `expire` FROM `announcement` WHERE `id` = ".$row['id'];
		$result2 = mysql_query($sql);
		checkdberror($sql);
		$result2 = mysql_fetch_assoc($result2);
		$return[] = array(
				'id' => $row['id'],
				'read' => $row['read'],
				'subject' => $result2['subject'],
				'text' => $result2['text'],
				'source' => $result2['source'],
				'expire' => strtotime($result2['expire'])
			);
	}
	return $return;
}

function announce_read($announce_id, $user_id = null) {
	if (!is_numeric($announce_id)) return false;
	// Check that they have it bound...
	if ($user_id) {
		if (!is_numeric($user_id)) return $return;
		$sql = "SELECT `announcement_id` FROM `announcement_bound` WHERE `announcement_id` = '".$announce_id."' AND `user_id` = '".$user_id."'";
		$result = mysql_query($sql);
		if (!mysql_num_rows($result)) {
			return false;
		}
	}
	$sql = "SELECT `id`, `subject`, `text`, `source`, `expire` FROM `announcement` WHERE `id` = ".$announce_id;
	$result = mysql_query($sql);
	checkdberror($sql);
	$return = mysql_fetch_assoc($result);
	if ((!$_COOKIE['pmd_suuser'])&&$user_id) {
		$sql = "UPDATE `announcement_bound` SET `read` = '1' WHERE `user_id` = ".$user_id." AND `announcement_id` = ".$announce_id;
		mysql_query($sql);
		checkdberror($sql);
	}
	return $return;
}


function announce_del($announce_id) {
	if (!is_numeric($announce_id)) return false;
	$sql = "DELETE FROM `announcement_bound` WHERE `announcement_id` = ".$announce_id;
	mysql_query($sql);
	checkdberror($sql);
	$sql = "DELETE FROM `announcement` WHERE `id` = ".$announce_id;
	mysql_query($sql);
	checkdberror($sql);
}

// Cleanup Expired Announcements
function announce_cleanup() {
	$sql = "SELECT `id` FROM `announcement` WHERE `expire` < NOW()";
	$result = mysql_query($sql);
	checkdberror($sql);
	while ($row = mysql_fetch_assoc($result)) {
		announce_del($row['id']);
	}
}

?>
