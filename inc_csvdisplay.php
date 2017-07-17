<?php
checkdberror($sql);
header("Content-type: application/octet-stream"); 
header("Content-Disposition: attachment; filename=outofstock.csv"); 
header("Content-Transfer-Encoding: binary");

if (mysql_num_rows($query)) {
	$row = mysql_fetch_assoc($query);
	$line = array();
	foreach ($row as $k => $v) {
		if ($fields[$k]['type'] == 'hidden') continue;
		if ($fields[$k]['name']) {
			$line[] = strtoupper($fields[$k]['name'][0]).substr($fields[$k]['name'],1);
		} else {
			$line[] = strtoupper($k[0]).substr($k,1);
		}
	}
	echo implode(',',$line)."\n";
	$repeat = array();
	do {
		$line = array();
		foreach ($row as $k => $v) {
			if ($fields[$k]['type'] == 'hidden') continue;
			if ($fields[$k]['type'] == 'stock') {
				$stock = stock_status($v);
				$stock['day'] = '';
				if ($row['stock_day']) $stock['day'] = ' ('.$row['stock_day'].')';
				$line[] = $stock['name'].$stock['day'];
			} elseif ($fields[$k]['type'] == 'norepeat') {
				if ($repeat[$k] == $v) {
					$line[] = '';
				} else {
					$repeat[$k] = $v;
					$line[] = $v;
				}
			} else {
				$line[] = $v;
			}
		}
		echo implode(',',$line)."\n";
	} while ($row = mysql_fetch_assoc($query));
}

?>
