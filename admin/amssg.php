<?php
/*********************************************************************
    Licensed for Jeff Hosking (PMD Furniture) by Radium Development
        (c) 2004 Radium Development with full rights granted to
                   Jeff Hosking (PMD Furniture)
 *********************************************************************/
//   =============== INCLUDES SECTION ====================
// Secure doc
include("database.php");
include("secure.php");

// Admin form?	
$admin = 1;

if (!secure_is_admin()) {
	$admin = 0;
}

// Include functions
if ($admin) {
	include("../mssg.inc.php");
} else {
	include("mssg.inc.php");
}


// What actionmode are we in?
$actionmode = stripslashes($_REQUEST['action']);

// Gotta do this before the menu or any output
if ($actionmode == 'sendmssgproc') {
	$thread = stripslashes($_REQUEST['thread']);
	if ($thread) {
		mssg_respond($thread, stripslashes($_REQUEST['mssg']), $admin);
	} else {
		if ($admin) {
			$thread = mssg_newthread(stripslashes($_REQUEST['subject']), stripslashes($_REQUEST['mssg']), stripslashes($_REQUEST['dealer']));
		} else {
			$thread = mssg_newthread(stripslashes($_REQUEST['subject']), stripslashes($_REQUEST['mssg']));
		}
	}
	header('Location: '.$_SERVER['PHP_SELF'].'?action=sendmssg&thread='.$thread);
	exit();
} elseif ($actionmode == 'deletemssg') {
	$thread = stripslashes($_REQUEST['thread']);
	mssg_delete($admin, $thread);
	header('Location: '.$_SERVER['PHP_SELF']);
	exit();
}

if ($admin) {
	include('menu.php');
} else {
	if ($actionmode == 'sendmssg') {
		echo '<html><head><title>Message Center - ';
		if ($thread) {
			echo 'View/Reply to Thread';
		} else {
			echo 'Send Message';
		}
		echo '</title>';
		echo '<link rel="stylesheet" href="styles.css" type="text/css">';
		echo '</head><body>';
	} else {
		echo '<html><head><title>Message Center - ';
		echo 'View Threads';
		echo '</title></head>';
		echo '<link rel="stylesheet" href="styles.css" type="text/css">';
		echo '<body>';
	}
}

# ====== Non-Admin Send Message
if ($actionmode == 'sendmssg') {
	echo '<a href=\''.$_SERVER['PHP_SELF'].'\'>Return to Thread Listing</a><br>';
	echo '<form action=\''.$_SERVER['PHP_SELF'].'?action=sendmssgproc\' method=\'post\'>';
	if ($thread) {
		$mssgthread = mssg_getthread($thread, $admin);
		$mssgthread = array_reverse($mssgthread);
		if ($mssgthread) {
			echo '<table border="0" cellspacing="0" cellpadding="5" style="float: none" align="left" width="90%">';
			foreach ($mssgthread as $mssg) {
				echo '<tr>';
				echo '<td class="fat_black_12" bgcolor="#fcfcfc">';
				echo db_user_getuserinfo($mssg['from'], 'last_name');
				echo '</td>';
				echo '<td class="fat_black_12" align="right" bgcolor="#fcfcfc">';
				echo date('m/d/Y g:ia T',strtotime($mssg['date']));
				echo '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td class="text_12">';
				echo mssg_makehref($mssg['mssg']);
				echo '</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
		echo '<input type=\'hidden\' name=\'thread\' value=\''.$thread.'\' />';
	} else {
		echo 'Subject: <input type=\'text\' name=\'subject\' maxlength=\'100\' /><br />';
	}
	if ($admin && (!$thread)) {
		echo "To: <SELECT name=\"dealer\">";
		$userlist = db_user_getlist($dealerteam);
		foreach ($userlist as $value) {
			echo "<OPTION VALUE=\"".$value[id]."\"";
			echo '>'.db_user_getuserinfo($value[id], 'last_name');
			echo '</OPTION>';
		}
		echo "</SELECT>";
	}
	echo 'Message: <br /><textarea name=\'mssg\' COLS=100 ROWS=6></textarea><br />';
	echo '<input type=submit value=\'Send\'>';
	echo '</form>';
	echo '</body></html>';
} else {
	$threads = mssg_getthreads($admin);
	echo '<a href=\''.$_SERVER['PHP_SELF'].'?action=sendmssg\'>Send a new message</a><br>';
	echo '<table border="0" cellspacing="0" cellpadding="5" align="left">';
	echo '<tr>';
	  echo '<td class="fat_black_12" bgcolor="#fcfcfc">';
	    echo 'Date';
	  echo '</td>';
	  if ($admin) {
	  echo '<td class="fat_black_12" bgcolor="#fcfcfc">';
	    echo 'Dealer';
	  echo '</td>';
	  }
	  echo '<td class="fat_black_12" bgcolor="#fcfcfc">';
	    echo 'Subject';
	  echo '</td>';
	  echo '<td class="fat_black_12" bgcolor="#fcfcfc">';
	    echo '&nbsp;';
	  echo '</td>';
	echo '</tr>';
	foreach($threads as $thread) {
		$link = '<a href=\''.$_SERVER['PHP_SELF'].'?action=sendmssg&thread='.$thread['id'].'\'>';
		$extra = '';
		$extraend = '';
		if ($thread['unread']) {
			$extra = $extra.'<b>';
			$extraend = '</b>'.$extraend;
		}
		echo '<tr>';
		  echo '<td class="text_12">';
		  echo "<A HREF='".$SERVER['PHP_SELF']."?action=deletemssg&thread=".$thread['id'].'\' ';
					?>onClick="return confirm('Are you sure you wish to delete this message?');"><?php
					echo "<IMG BORDER=0 ALT='X' SRC=/images/button_drop.png></A>";
		  echo $link.$extra.$thread['date'].$extraend.'</a></td>';
		  if ($admin) {
		  	echo '<td class="text_12">'.$link.db_user_getuserinfo($thread['dealer'], 'last_name').$extraend.'</a></td>';
		  }
		  echo '<td class="text_12">'.$link.$extra.$thread['subject'].$extraend.'</a></td>';
		  echo '<td class="text_12">'.$link.$extra;
		  if ($thread['unread'] > 0) {
		  	echo $thread['unread'].'/'.$thread['total'];
		  } else {
		  	echo $thread['total'];
		  }
		  echo $extraend.'</a></td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</body></html>';
}


?>