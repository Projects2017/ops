<?php

// Returns thread ID of new thread
function mssg_newthread($subject, $mssg, $dealer = 0) {
  if (secure_is_admin() && is_numeric($dealer)) {
  	if (!$dealer) {
  		$dealer = $GLOBALS['userid'];
  		$admin = 1;
  	} else {
  		$admin = 1;
  	}
  } else {
  	$admin = 0;
  	$dealer = $GLOBALS['userid'];
  }
  if (strlen($subject) > 100) // That subject is too long!
    return 0;
  if ((!is_numeric($dealer))||($dealer <= 0)) // Invalid dealer ID!
  	return 0;
  
  // Insert thread  
  $sql = 'INSERT INTO `mssgthread` (`dealer`, `subject`) VALUES(';
  $sql .= "'".mysql_real_escape_string($dealer)."',";
  $sql .= "'".mysql_real_escape_string($subject)."')";
  
  mysql_query($sql);
  $thread = mysql_insert_id(); // Get thread ID
  mssg_respond($thread,$mssg,$admin); // Add message
  return $thread;
}

// Uses $admin to tell if to set the admin read bit
function mssg_respond($thread, $mssg, $admin = 0) {
  if ((!is_numeric($thread))||($thread <= 0)) // Invalid tread ID!
  	return 0;	
  if (!(($admin == 1)||($admin == 0))) // Invalid admin flag!
  	return 0;
  $dealer = $GLOBALS['userid'];
  
  $sql = 'INSERT INTO `message` (`mssgthread_id`, `from`, `mssg`, ';
  if ($admin) {
  	$sql .= '`adminread`)';
  } else {
  	$sql .= '`dealerread`)';
  }
  $sql .= ' VALUES('."'".mysql_real_escape_string($thread)."', ";
  $sql .= "'".mysql_real_escape_string($dealer)."', ";
  $sql .= "'".mysql_real_escape_string($mssg)."', ";
  $sql .= '1)';
  
  mysql_query($sql);
  $mssgid = mysql_insert_id();
  $sql = "UPDATE `mssgthread` SET admin_deleted = 'N', dealer_deleted = 'N' WHERE id = '".mysql_real_escape_string($thread)."'";
  mysql_query($sql);
  return $mssgid;
}

function mssg_delete($admin,$thread) {
	if (secure_is_admin()&&$admin) {
		$sql = "UPDATE `mssgthread` SET `admin_deleted` = 'Y' WHERE id = '".$thread."'";
	} else {
		$sql = "UPDATE `mssgthread` SET `dealer_deleted` = 'Y' WHERE id = '".$thread."'";
	}
	mysql_query($sql);
	$sql = "SELECT `id` FROM `mssgthread` WHERE `admin_deleted` = 'Y' AND `dealer_deleted` = 'Y'";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$sql = "DELETE FROM `message` WHERE `mssgthread_id` = '".$thread."'";
		mysql_query($sql);
		$sql = "DELETE FROM `mssgthread` WHERE `id` = '".$thread."'";
		mysql_query($sql);
	}
}

// Get number of messages
function mssg_numnew($admin = 0) {
  if (secure_is_admin()&&$admin) {
  	$team = $GLOBALS['dealerteam'];
  	$sql = 'SELECT count(*) FROM `mssgthread` INNER JOIN `message` ON `message`.`mssgthread_id` = `mssgthread`.`id`';
  	$sql .= ' INNER JOIN `users` ON `mssgthread`.`dealer` = `users`.`ID`';
  	$sql .= ' WHERE `message`.`dealerread` = 0 AND `mssgthread`.`admin_deleted` != \'Y\'';
  	if ($team != '*') {
  		$sql .= ' AND `users`.`team` = "'.$team.'"';
  	}
  } else {
  	$sql = 'SELECT count(*) FROM `mssgthread` INNER JOIN `message` ON `message`.`mssgthread_id` = `mssgthread`.`id` WHERE ';
  	$sql .= '`message`.`dealerread` = 0 AND `mssgthread`.`dealer` = "'.$GLOBALS['userid'].'" AND `mssgthread`.`dealer_deleted` != \'Y\'';
  }
  $result = mysql_query($sql);
  $row = mysql_fetch_row($result);
  mysql_free_result($result);
  return $row[0];
}

// Get total number of messages
function mssg_numofmssg($admin = 0) {
  if (secure_is_admin()&&$admin) {
  	if ($GLOBALS['dealerteam'] == '*') {
  		$team = '%';
  	} else {
  		$team = $GLOBALS['dealerteam'];
  	}
  	$sql = 'SELECT count(*) FROM `mssgthread` INNER JOIN `message` ON `message`.`mssgthread_id` = `mssgthread`.`id`';
  	$sql .= ' INNER JOIN `users` ON `mssgthread`.`dealer` = `users`.`ID`';
  	$sql .= ' WHERE `users`.`team` = "'.$team.'" AND `mssgthread`.`admin_deleted` != \'Y\'';
  } else {
  	$sql = 'SELECT count(*) FROM `mssgthread` INNER JOIN `message` ON `message`.`mssgthread_id` = `mssgthread`.`id` WHERE ';
  	$sql .= '`mssgthread`.`dealer` = "'.$GLOBALS['userid'].'"  AND `mssgthread`.`dealer_deleted` != \'Y\'';
  }
  $result = mysql_query($sql);
  $row = mysql_fetch_row($result);
  mysql_free_result($result);
  return $row[0];
}

function mssg_threadnumnew($thread, $admin = 0) {
  $sql = 'SELECT count(*) FROM `message` INNER JOIN `mssgthread` ON `mssgthread`.`id` = `message`.`mssgthread_id` WHERE';
  if ($admin && secure_is_admin()) {
  	$sql .= ' `message`.`adminread` = 0';
  } else {
  	$sql .= ' `message`.`dealerread` = 0';
  }
  $sql .= ' AND `message`.`mssgthread_id` = "'.$thread.'"';
  if (!secure_is_admin()) {
  	$sql .= ' AND `mssgthread`.`dealer` = "'.$GLOBALS['userid'].'"';
  }
  $result = mysql_query($sql);
  $row = mysql_fetch_row($result);
  mysql_free_result($result);
  return $row[0];
}

function mssg_threadnumofmssg($thread) {
  $sql = 'SELECT count(*) FROM `message` INNER JOIN `mssgthread` ON `mssgthread`.`id` = `message`.`mssgthread_id` WHERE';
  $sql .= ' `message`.`mssgthread_id` = "'.$thread.'"';
  if (!secure_is_admin()) {
  	$sql .= ' AND `mssgthread`.`dealer` = "'.$GLOBALS['userid'].'"';
  }
  $result = mysql_query($sql);
  $row = mysql_fetch_row($result);
  mysql_free_result($result);
  return $row[0];
}


// Get the threads's that are available.
function mssg_getthreads($admin = 0) {
	$mssgs = array();
	$sql = 'SELECT `mssgthread`.* FROM `mssgthread` INNER JOIN `users` ON `mssgthread`.`dealer` = `users`.`ID`';
	if ($admin && secure_is_admin()) {
		$sql .= ' WHERE `mssgthread`.`admin_deleted` != \'Y\'';
		if ($GLOBALS['dealerteam'] != '*') {
			$sql .= ' AND `users`.`team` = \''.mysql_real_escape_string($GLOBALS['dealerteam']).'\'';
		}
	} else {
		$sql .= ' WHERE `mssgthread`.`dealer_deleted` != \'Y\'';
		$sql .= ' AND `mssgthread`.`dealer` = \''.mysql_real_escape_string($GLOBALS['userid']).'\'';
	}
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$mssgs[$row['id']] = $row;
		$mssgs[$row['id']]['unread'] = mssg_threadnumnew($row['id'],$admin);
		$mssgs[$row['id']]['total'] = mssg_threadnumofmssg($row['id']);
	}
	return $mssgs;
}

// Mark Message Read
function mssg_readmessage($message, $admin = 0) {
	$sql = 'UPDATE `message` SET ';
	if ($admin && secure_is_admin()) {
		$sql .= '`adminread` = \'1\'';
	} else {
		$sql .= '`dealerread` = \'1\'';
	}
	$sql .= ' WHERE `message`.`id` = \''.mysql_real_escape_string($message).'\'';
	mysql_query($sql);
}

// Get thread for display
function mssg_getthread($thread, $admin = 0) {
	$mssgs = array();
	$sql = 'SELECT `message`.* FROM `message` INNER JOIN `mssgthread` ON `mssgthread`.`id` = `message`.`mssgthread_id`';
	$sql .= 'WHERE `message`.`mssgthread_id` = \''.mysql_real_escape_string($thread).'\'';
	if (!secure_is_admin()) {
		$sql .= ' AND `mssgthread`.`dealer` = \''.mysql_real_escape_string($GLOBALS['userid']).'\'';
	}
	$result = mysql_query($sql);
	
	while ($row = mysql_fetch_assoc($result)) {
		mssg_readmessage($row['id'], $admin);
		$mssgs[$row['id']] = $row;
	}
	return $mssgs;
}

function mssg_makehref($string)
{
	$string = preg_replace('#(http://www\.|http://)([\w-]+\.)([a-z]+\.)+([\w\?&=\-\./%]*)?#i',"<a href=\"$0\">$0</a>",$string);
	$string = str_replace("\n","<br>\n",$string);
	return $string;
}
?>