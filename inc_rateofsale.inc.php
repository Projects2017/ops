<?php
// inc_rateofsale.inc.php
// This allows us to generate rate of sale for a form

function ros_form($id, $user_id = null, $datefrom = null, $dateto = null) {
	if (is_null($datefrom)) $datefrom = strtotime("-3 month");
	if (is_null($dateto)) $dateto = time();
	
	if (!is_numeric($id)) die ("row_form: Invalid Form ID");
	if (!is_numeric($datefrom)) die("ros_form: Invalid From Date");
	if (!is_numeric($dateto)) die("ros_form: Invalid To Date");
	if ($datefrom > $dateto) die("ros_form: From Date later than To Date");
	
	$from_date = date('Y-m-d', $datefrom);
	$to_date = date('Y-m-d', $dateto);
	
	$dayperiod = 60 * 60 * 24; // Seconds * Minutes * Hours
	
	$return = array();
	$return['from'] = $datefrom;
	$return['to'] = $dateto;
        $return['user_id'] = $user_id;
	$return['days'] = round(($dateto - $datefrom) / $dayperiod, 0);
	$return['partnos'] = array();
	
	$sql = <<<EOT
		SELECT 
			snap.partno,
			snap.description,
			(
				SUM(orders.setqty) + SUM(orders.mattqty) + SUM(orders.qty)
			) AS total
		FROM
			orders 
			INNER JOIN order_forms ON orders.po_id = order_forms.ID
			INNER JOIN snapshot_items AS snap ON orders.item = snap.id
		WHERE
			orders.form = $id

EOT;
        if ($user_id && is_numeric($user_id)) {
            $sql .= <<<EOT
			AND order_forms.user = $user_id

EOT;
        }
        $sql .= <<<EOT
			AND order_forms.ordered BETWEEN '$from_date' AND '$to_date'
			AND order_forms.processed = 'Y'
			AND order_forms.deleted != 1 
			AND order_forms.type = 'o'
			AND order_forms.total > 0
		GROUP BY
			snap.partno,
			snap.description
EOT;
        // die("<pre>".$sql."</pre>");
	$query = mysql_query($sql);
	checkDBerror($sql);
	while ($result = mysql_fetch_assoc($query)) {
		if (!is_array($return['partnos'][$result['partno']])) $return['partnos'][$result['partno']] = array();
		$return['partnos'][$result['partno']][$result['description']] = $result['total'];
	}
	
	mysql_free_result($query);
	
	return $return;
}
