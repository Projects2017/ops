<?php
require("database.php");
require("secure.php");
require("documents.inc.php");
require("include/inc_bbcode.php");
$id = $_REQUEST['id'];

if ($id == 'recent' || !$id) {
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
		<B>Documents</B>
		<ul>
	<?php
	$documents = document_list($userid, true);
	foreach($documents as $document) {
		?>
			<li><a href="documents.php?id=<?php echo $document['id']; ?>"><?php echo $document['subject']; ?></a></li>
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

$document = document_read($id, $userid);
if (!$document) {
	$document = array(
			'subject' => "Document Not Found",
			'text' => "Sorry, this document does not exist. The most likely cause of this problem is the article has expired"
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
<p align="center">[<a href="documents.php?id=recent">Back to Documents</a>]<br>
	[<a href="selectvendor.php">Back to Vendor List</a>]</p>
</body>
</html>
