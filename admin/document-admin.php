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

// Include functions
include("../documents.inc.php");


// What actionmode are we in?
$actionmode = stripslashes($_REQUEST['action']);

// Gotta do this before the menu or any output
if ($actionmode == 'senddocumentproc') {
        $target = array(
                    'team' => stripslashes($_POST['team']),
                    'disabled' => 'N',
                    'manager' => stripslashes($_POST['manager']),
                    'division' => stripslashes($_POST['division']),
                    'level' => stripslashes($_POST['level']),
                    'nonPMD' => stripslashes($_POST['nonpmd']),
                    'dealer_type' => stripslashes($_POST['dealer_type']),
                );
	$document = document_add($target, stripslashes($_POST['subject']), stripslashes($_POST['text']), null); // Last Arg is Expiration
	header('Location: '.$_SERVER['PHP_SELF'].'?action=viewdocument&document='.$document);
	exit();
} elseif ($actionmode == 'updocument') {
	$document = document_update($_REQUEST['document'], stripslashes($_POST['subject']), stripslashes($_POST['text']), null); // Last Arg is Expiration
	header('Location: '.$_SERVER['PHP_SELF'].'?action=viewdocument&document='.$_REQUEST['document']);
	exit();
} elseif ($actionmode == 'deldocument') {
	$document = stripslashes($_REQUEST['document']);
	document_del($document);
	header('Location: '.$_SERVER['PHP_SELF']);
	exit();
}


include('menu.php');

# ====== Post Document
if ($actionmode == 'senddocument') {
	?>
	<FONT FACE=ARIAL>
	<B>Post Document</B>
	</FONT>
	<BLOCKQUOTE class="article">
		    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?action=senddocumentproc">
			<table border="0" cellspacing="0" cellpadding="5" align="left" width="100%">
			  <tr bgcolor="#CCCC99">
				<td colspan=2 class="fat_black_12"><input type='text' name='subject' maxlength='100' size="100" value="<?php echo htmlentities($document['subject']); ?>"></td>
			  </tr>
			  <tr>
				<td colspan=2 class="text_12">
					<textarea name='text' COLS=100 ROWS=6><?php
						echo htmlentities($document['source']); 
						//echo nl2br(htmlentities(print_r($_SERVER,true)));
					?></textarea>
				</td>
			  </tr>
			  <tr>
			    <td class="text_12">
					Team:&nbsp;<select id="team" name="team">
						<option value="*" SELECTED>All</option>
						<option value="!" >None</option>
						<?php $teams = teams_list();
						foreach ($teams as $team) {
					?>	<option value="<?php echo $team; ?>" ><?php echo $team; ?></option>
					<?php
						}
					?></select>
					<?php echo manager_name(); ?>:&nbsp;<select id="manager" name="manager">
						<option value="*" SELECTED>All</option>
						<option value="!" >None</option>
						<?php $managers = managers_list();
						foreach ($managers as $manager) {
					?>	<option value="<?php echo $manager['name']; ?>" ><?php echo $manager['name']; ?></option>
					<?php
						}
					?></select>
					Level:&nbsp;<select id="level" name="level">
						<option value="*">All</option>
						<option value="!">None</option>
						<option value="1">1</option>
						<option value="TBD">TBD</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4/5">4/5</option>
					</select>
					Division:&nbsp;<select id="division" name="division">
						<option value="*">All</option>
						<option value="!">None</option>
						<option value="1">1</option>
						<option value="2">2</option>
					</select>
                                        Dealer Type:&nbsp;<select id="dealer_type" name="dealer_type">
						<option value="B">Both</option>
						<option value="F">Franchisee</option>
						<option value="L">Licensee</option>
					</select>
					nonRSS:&nbsp;<select id="nonpmd" name="nonpmd">
						<option value="*">All</option>
						<option value="Y">Yes</option>
						<option value="N">No</option>
					</select>
				</td>
				<td class="text_12" align="right">
					<input type='submit' value='Post'>
				</td>
			  </tr>
			</table>
			</form>
			<br style="clear: both;">
		</BLOCKQUOTE>
		<p align="center">[<a href="<?php echo $_SERVER['PHP_SELF']; ?>">Back to Documents List</a>]</p>
	<?php
	/* echo '<a href=\''.$_SERVER['PHP_SELF'].'\'>Return to Document Listing</a><br>';
	echo '<form action=\''.$_SERVER['PHP_SELF'].'?action=senddocumentproc\' method=\'post\'>';
	echo 'Subject: <input type=\'text\' name=\'subject\' maxlength=\'100\' /><br />';
	echo 'Message: <br /><textarea name=\'text\' COLS=100 ROWS=6></textarea><br />';
	echo '<input type=submit value=\'Send\'>';
	echo '</form>';
	echo '</body></html>'; */
} elseif ($actionmode == 'viewdocument') {
	$document = stripslashes($_REQUEST['document']);
	$document = document_read($document);
	if (!$document) {
		$document = array(
				'subject' => "Document Not Found",
				'text' => "Sorry, this document does not exist. The most likely cause of this problem is the article has expired"
			);
	}
	?>
		<BLOCKQUOTE class="article">
			<table border="0" cellspacing="0" cellpadding="5" align="left" width="100%">
			  <tr bgcolor="#CCCC99">
				<td class="fat_black_12"><?php echo htmlentities($document['subject']); ?></td>
			  </tr>
			  <tr>
				<td class="text_12">
					<?php
						echo $document['text']; 
						//echo nl2br(htmlentities(print_r($_SERVER,true)));
					?>
				</td>
			  </tr>
			</table>
			<br style="clear: both;">
		</BLOCKQUOTE>
		<BLOCKQUOTE class="article">
		    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?action=updocument&document=<?php echo $document['id']; ?>">
			<table border="0" cellspacing="0" cellpadding="5" align="left" width="100%">
			  <tr bgcolor="#CCCC99">
				<td class="fat_black_12"><input type='text' name='subject' maxlength='100'  size="100" value="<?php echo htmlentities($document['subject']); ?>"></td>
			  </tr>
			  <tr>
				<td class="text_12">
					<textarea name='text' COLS=100 ROWS=6><?php
						echo htmlentities($document['source']); 
						//echo nl2br(htmlentities(print_r($_SERVER,true)));
					?></textarea>
				</td>
			  </tr>
			  <tr>
				<td class="text_12" align="right">
					<input type='submit' value='Update'>
				</td>
			  </tr>
			</table>
			</form>
			<br style="clear: both;">
		</BLOCKQUOTE>
		<p align="center">[<a href="<?php echo $_SERVER['PHP_SELF']; ?>">Back to Documents List</a>]</p>
	<?php
} else {
	$list = document_list();
	echo '<a href=\''.$_SERVER['PHP_SELF'].'?action=senddocument\'>Post Document</a><br>';
	echo '<table border="0" cellspacing="0" cellpadding="5" align="left" width="50%">';
	echo '<tr>';
	  echo '<td class="fat_black_12" bgcolor="#fcfcfc">';
	    echo 'Subject';
	  echo '</td>';
	  //echo '<td class="fat_black_12" bgcolor="#fcfcfc">';
	  //  echo 'Expire';
	  //echo '</td>';
	echo '</tr>';
	foreach($list as $item) {
		echo '<tr>';
		  echo '<td class="text_12">';
		  echo "<A HREF='".$SERVER['PHP_SELF']."?action=deldocument&document=".$item['id'].'\' ';
					?>onClick="return confirm('Are you sure you wish to delete this document?');"><?php
					echo "<IMG BORDER=0 ALT='X' SRC=/images/button_drop.png></A>";
		  echo '<a href="'.$SERVER['PHP_SELF'].'?action=viewdocument&document='.$item['id'].'">'.$item['subject'].'</a></td>';
		  // echo '<td class="text_12">'.date('M jS, Y',$item['expire']).'</a></td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</body></html>';
}


?>
