<html>
<head>
<title>RSS<?php if ($title) { echo " - ".$title; } ?></title>
<link rel="stylesheet" href="<?php if ($adminside) { ?>../<?php } ?>styles.css" type="text/css">
</head>
<body>
<?php
require(($adminside?'../':'').'menu.php');
if ($title) { ?><h1 align="center"><?php echo $title; ?></h1><?php } ?>
<?php
if ($filter) {
  echo "<div class=\"noprint\">\n".$filter."</div>\n";
} 
checkdberror($sql);
?>
<?php
echo '<table border="0" cellspacing="0" cellpadding="5" ';
if (isset($width)) echo 'width="'.htmlentities($width).'" ';
echo ">\n";
if (mysql_num_rows($query)) {
	echo "<thead>\n";
	echo "\t<tr>\n";
	$row = mysql_fetch_assoc($query);
	foreach ($row as $k => $v) {
		if ($fields[$k]['type'] == 'hidden') continue;
		if ($fields[$k]['name']) {
			echo "\t\t<th bgcolor='#CCCC99' class=\"fat_black_12\" align=\"left\">".htmlentities(strtoupper($fields[$k]['name'][0]).substr($fields[$k]['name'],1))."</th>\n";
		} else {
			echo "\t\t<th bgcolor='#CCCC99' class=\"fat_black_12\" align=\"left\">".htmlentities(strtoupper($k[0]).substr($k,1))."</th>\n";
		}
	}
	echo "\t</tr>";
	echo "</thead>\n";
	echo "<tbody>\n";
	$repeat = array();
	do {
		echo "\t<tr>\n";
		foreach ($row as $k => $v) {
			if ($fields[$k]['type'] == 'hidden') continue;
			if ($fields[$k]['type'] == 'norepeat') {
				if ($repeat[$k] == $v) {
					$v = '';
				} else {
					$repeat[$k] = $v;
				}
			}
			if ($fields[$k]['type'] == 'stock') {
				$line = stock_status($v);
				$style = stock_buildstyle($line);
				echo "\t\t<td bgcolor='#FFFFFF' class=\"".($fields[$k]['class']?$fields[$k]['class']:"text_12")."\" style=\"".htmlentities($style)."\">".htmlentities($line['name']);
				if ($row['stock_day']) echo " (".$row['stock_day'].")";
				echo "</td>\n";
			} else {
				echo "\t\t<td bgcolor='#FFFFFF' class=\"".($fields[$k]['class']?$fields[$k]['class']:"text_12")."\">";
				if ($v == '') {
					echo "&nbsp;";
				} else {
					echo htmlentities($v);
				}
				echo "</td>\n";
			}
		}
		echo "\t</tr>\n";
	} while ($row = mysql_fetch_assoc($query));
	echo "</tbody>\n";
}

echo "</table>\n";
?>
</body>
</html>
