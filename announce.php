<?php
require("database.php");
require("secure.php");
require("announce.inc.php");
require("include/inc_bbcode.php");
$id = $_REQUEST['id'];

if ($id == 'recent') {
	?>
		<html>
		<head>
		<title>RSS</title>
		<link rel="stylesheet" href="styles.css" type="text/css">
		</head>
		<body bgcolor="#EDECDA">
		<?php require('menu.php'); ?>
		<br>
		<FONT FACE=ARIAL>
		<B>Announcements</B>
		<ul>
	<?php
	$announcements = announce_list($userid, true);
	foreach($announcements as $announce) {
		?>
			<li><a href="announce.php?id=<?php echo $announce['id']; ?>"><?php echo $announce['subject']; ?></a></li>
		<?php
	}
	?>
		</ul>
		<p align="center">[<a href="selectvendor.php">Back to Vendor List</a>]</p>
		</body>
		</html>
	<?php
	exit();
} // else everything else....

$announcement = announce_read($id, $userid);
if (!$announcement) {
	$announcement = array(
			'subject' => "Announcement Not Found",
			'text' => "Sorry, this announcement does not exist. The most likely cause of this problem is the article has expired"
		);
}
?>
<html>
<head>
<title>RSS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body bgcolor="#EDECDA">
<?php require('menu.php'); ?>
<BLOCKQUOTE class="article">
<table border="0" cellspacing="0" cellpadding="5" align="left" width="100%">
  <tr bgcolor="#CCCC99">
    <td class="fat_black_12"><?php echo htmlentities($announcement['subject']); ?></td>
  </tr>
  <tr>
	<td class="text_12">
		<?php
			echo $announcement['text']; 
			//echo nl2br(htmlentities(print_r($_SERVER,true)));
		?>
	</td>
  </tr>
</table>
<br style="clear: both;">
</BLOCKQUOTE>
<p align="center">[<a href="announce.php?id=recent">Back to Announcements</a>]<br>
	[<a href="selectvendor.php">Back to Vendor List</a>]</p>
</body>
</html>
