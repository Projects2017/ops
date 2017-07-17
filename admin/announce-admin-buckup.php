<?
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
include("../announce.inc.php");


// What actionmode are we in?
$actionmode = stripslashes($_REQUEST['action']);

// Gotta do this before the menu or any output
if ($actionmode == 'sendannouncementproc') {
    $target = db_user_filterlist(array(
        'team' => stripslashes($_POST['team']),
        'disabled' => 'N',
        'manager' => stripslashes($_POST['manager']),
        'division' => stripslashes($_POST['division']),
        'level' => stripslashes($_POST['level']),
        'nonPMD' => stripslashes($_POST['nonpmd']),
        'dealer_type' => stripslashes($_POST['dealer_type']),
    ));
    $announcement = announce_add($target, stripslashes($_POST['subject']), stripslashes($_POST['text']), null); // Last Arg is Expiration
    header('Location: '.$_SERVER['PHP_SELF'].'?action=viewannounce&announcement='.$announcement);
    exit();
} elseif ($actionmode == 'upannouncement') {
    $announcement = announce_update($_REQUEST['announcement'], stripslashes($_POST['subject']), stripslashes($_POST['text']), null); // Last Arg is Expiration
    header('Location: '.$_SERVER['PHP_SELF'].'?action=viewannounce&announcement='.$_REQUEST['announcement']);
    exit();
} elseif ($actionmode == 'delannouncement') {
    $announcement = stripslashes($_REQUEST['announcement']);
    announce_del($announcement);
    header('Location: '.$_SERVER['PHP_SELF']);
    exit();
}


include('menu.php');

# ====== Post Announcement
if ($actionmode == 'sendannouncement') {
    ?>
    <FONT FACE=ARIAL>
        <B>Post Announcement</B>
    </FONT>
    <BLOCKQUOTE class="article">
        <form method="post" action="<? echo $_SERVER['PHP_SELF']; ?>?action=sendannouncementproc">
            <table border="0" cellspacing="0" cellpadding="5" align="left" width="100%">
                <tr bgcolor="#CCCC99">
                    <td colspan=2 class="fat_black_12"><input type='text' name='subject' maxlength='100' size="100" value="<? echo htmlentities($announcement['subject']); ?>"></td>
                </tr>
                <tr>
                    <td colspan=2 class="text_12">
					<textarea name='text' COLS=100 ROWS=6><?
                        echo htmlentities($announcement['source']);
                        //echo nl2br(htmlentities(print_r($_SERVER,true)));
                        ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="text_12">
                        Team:&nbsp;<select id="team" name="team">
                            <option value="*" SELECTED>All</option>
                            <option value="!" >None</option>
                            <? $teams = teams_list();
                            foreach ($teams as $team) {
                                ?>	<option value="<? echo $team; ?>" ><? echo $team; ?></option>
                                <?
                            }
                            ?></select>
                        <? echo manager_name(); ?>:&nbsp;<select id="manager" name="manager">
                            <option value="*" SELECTED>All</option>
                            <option value="!" >None</option>
                            <? $managers = managers_list();
                            foreach ($managers as $manager) {
                                ?>	<option value="<? echo $manager['name']; ?>" ><? echo $manager['name']; ?></option>
                                <?
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
    <p align="center">[<a href="<? echo $_SERVER['PHP_SELF']; ?>">Back to Announcements List</a>]</p>
    <?
    /* echo '<a href=\''.$_SERVER['PHP_SELF'].'\'>Return to Announcement Listing</a><br>';
    echo '<form action=\''.$_SERVER['PHP_SELF'].'?action=sendannouncementproc\' method=\'post\'>';
    echo 'Subject: <input type=\'text\' name=\'subject\' maxlength=\'100\' /><br />';
    echo 'Message: <br /><textarea name=\'text\' COLS=100 ROWS=6></textarea><br />';
    echo '<input type=submit value=\'Send\'>';
    echo '</form>';
    echo '</body></html>'; */
} elseif ($actionmode == 'viewannounce') {
    $announcement = stripslashes($_REQUEST['announcement']);
    $announcement = announce_read($announcement);
    if (!$announcement) {
        $announcement = array(
            'subject' => "Announcement Not Found",
            'text' => "Sorry, this announcement does not exist. The most likely cause of this problem is the article has expired"
        );
    }
    ?>
    <BLOCKQUOTE class="article">
        <table border="0" cellspacing="0" cellpadding="5" align="left" width="100%">
            <tr bgcolor="#CCCC99">
                <td class="fat_black_12"><? echo htmlentities($announcement['subject']); ?></td>
            </tr>
            <tr>
                <td class="text_12">
                    <?
                    echo $announcement['text'];
                    //echo nl2br(htmlentities(print_r($_SERVER,true)));
                    ?>
                </td>
            </tr>
        </table>
        <br style="clear: both;">
    </BLOCKQUOTE>
    <BLOCKQUOTE class="article">
        <form method="post" action="<? echo $_SERVER['PHP_SELF']; ?>?action=upannouncement&announcement=<? echo $announcement['id']; ?>">
            <table border="0" cellspacing="0" cellpadding="5" align="left" width="100%">
                <tr bgcolor="#CCCC99">
                    <td class="fat_black_12"><input type='text' name='subject' maxlength='100'  size="100" value="<? echo htmlentities($announcement['subject']); ?>"></td>
                </tr>
                <tr>
                    <td class="text_12">
					<textarea name='text' COLS=100 ROWS=6><?
                        echo htmlentities($announcement['source']);
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
    <p align="center">[<a href="<? echo $_SERVER['PHP_SELF']; ?>">Back to Announcements List</a>]</p>
    <?
} else {
    $list = announce_list();
    echo '<a href=\''.$_SERVER['PHP_SELF'].'?action=sendannouncement\'>Post Announcement</a><br>';
    echo '<table border="0" cellspacing="0" cellpadding="5" align="left" width="50%">';
    echo '<tr>';
    echo '<td class="fat_black_12" bgcolor="#fcfcfc">';
    echo 'Subject';
    echo '</td>';
    echo '<td class="fat_black_12" bgcolor="#fcfcfc">';
    echo 'Expire';
    echo '</td>';
    echo '</tr>';
    foreach($list as $item) {
        echo '<tr>';
        echo '<td class="text_12">';
        echo "<A HREF='".$SERVER['PHP_SELF']."?action=delannouncement&announcement=".$item['id'].'\' ';
        ?>onClick="return confirm('Are you sure you wish to delete this announcement?');"><?
        echo "<IMG BORDER=0 ALT='X' SRC=/images/button_drop.png></A>";
        echo '<a href="'.$SERVER['PHP_SELF'].'?action=viewannounce&announcement='.$item['id'].'">'.$item['subject'].'</a></td>';
        echo '<td class="text_12">'.date('M jS, Y',$item['expire']).'</a></td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</body></html>';
}


?>
