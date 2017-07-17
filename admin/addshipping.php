<?php
// addshipping.php
// script to add the shipping fees to the production dbs
// requires info sent via ... GET

if(!$_GET) {
  die("Information must be passed to this script via GET.");
}
require('database.php');
require('../inc_content.php');
$params = array();
foreach($_GET as $k => $v) {
  if($k=="comment") {
    $params[$k] = urldecode($v);
  } else {
    $params[$k] = $v;
  }
}
if(submitCreditFee($params['user'], $params['type'], $params['comment'], $params['total'])) {
  echo "OK";
} else {
  $output = "Parameters sent via CURL:\n";
  foreach($params as $k => $v) {
    $output .= "$k => $v\n";
  }
  ini_set(sendmail_from, 'Shipping Queue Daemon <noreply@retailservicesystems.com>');
  sendmail('Web Administration <will@retailservicesystems.com>', 'Shipping Queue Production DB Error', $output);
  echo "ERROR|".$output; 
}
?>
