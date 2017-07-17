<?php
require ("../database.php");
require ("../secure.php");
require ("../form.inc.php");
require ("../announce.inc.php");
include ('../include/BigBoard.php');
include ('../include/User.php');

$id = '';
if(isset($_REQUEST['id']))
    $id = addslashes($_REQUEST['id']);

if($id == '') 
    $id = 1;

$content = mysql_query("select * from cms_pages where cms_page_id = '".$id."'") or die (mysql_error());
$content = mysql_fetch_array($content);

if($content['template_id'] == 2){
    include 'template_dashboard.php';    
} else if($content['template_id'] == 3){
    include 'template_gallery.php';    
} else if($content['template_id'] == 4){
    include 'template_grand_day.php';    
} else if($content['template_id'] == 5){
    include 'template_thanksgiving.php';    
} else if($content['template_id'] == 7){
    include 'template_resources.php';    
} else if($id == 27){
    include 'template_leaderboards.php';    
} else {
    include 'template_default.php';
}
?>



