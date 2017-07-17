<?php
require ("../database.php");
require ("../secure.php");
require ("../form.inc.php");
require ("../announce.inc.php");
include ('../include/BigBoard.php');

?>

<?php include 'includes/header.php';?>
<style> 
.s15bg {background-color:rgba(255, 255, 255, 1);border-radius:5px;  box-shadow:0 1px 4px rgba(0, 0, 0, 0.6);;  border:1px solid rgba(255, 214, 170, 1);background-image:url(//static.parastorage.com/services/skins/2.1212.0/images/wysiwyg/core/themes/base/apple_box.png);background-repeat:repeat-x;background-position:0 0;}
.s15[data-state~="mobileView"] .s15bg {left:10px;right:10px;}
.s15inlineContent { position:relative;}
.s7 {word-wrap:break-word;}
.s7 li {font-style:inherit;font-weight:inherit;line-height:inherit;letter-spacing:normal;}
.s7 ol,.s7 ul {padding-left:1.3em;padding-right:0;margin-left:0.5em;margin-right:0;line-height:normal;letter-spacing:normal;}
.s7 ol[class~="wix-list"] li,.s7 ul[class~="wix-list"] li {margin-left:1em;margin-right:0;}
.s7 ol[class~="wix-list"][dir="rtl"] li,.s7 ul[class~="wix-list"][dir="rtl"] li {margin-left:0;margin-right:1em;}
.s7 ul {list-style-type:disc;}
.s7 ol {list-style-type:decimal;}
.s7 ul ul,.s7 ol ul {list-style-type:circle;}
.s7 ul ul ul,.s7 ol ul ul {list-style-type:square;}
.s7 ul ol ul,.s7 ol ol ul {list-style-type:square;}
.s7 ul[dir="rtl"],.s7 ol[dir="rtl"] {padding-left:0;padding-right:1.3em;margin-left:0;margin-right:0.5em;}
.s7 ul[dir="rtl"] ul,.s7 ul[dir="rtl"] ol,.s7 ol[dir="rtl"] ul,.s7 ol[dir="rtl"] ol {padding-left:0;padding-right:1.3em;margin-left:0;margin-right:0.5em;}
.s7 p {margin:0;line-height:normal;letter-spacing:normal;}
.s7 h1 {margin:0;line-height:normal;letter-spacing:normal;}
.s7 h2 {margin:0;line-height:normal;letter-spacing:normal;}
.s7 h3 {margin:0;line-height:normal;letter-spacing:normal;}
.s7 h4 {margin:0;line-height:normal;letter-spacing:normal;}
.s7 h5 {margin:0;line-height:normal;letter-spacing:normal;}
.s7 h6 {margin:0;line-height:normal;letter-spacing:normal;}
.s7 a {color:inherit;}
.s5link {border-radius:5px;  transition: border-color 0.4s ease 0s, background-color 0.4s ease 0s;  box-shadow:0 1px 4px rgba(0, 0, 0, 0.6);;  background:rgba(48, 48, 48, 1) url(//static.parastorage.com/services/skins/2.1212.0/images/wysiwyg/core/themes/base/shiny1button_bg.png) 50% 50% repeat-x;border:solid rgba(48, 48, 48, 1) 0px;}
.s5label {font:normal normal normal 12px/1.1em Arial,'ｍｓ ｐゴシック','ms pgothic','돋움',dotum,helvetica,sans-serif ;  transition: color 0.4s ease 0s;  color:#FFFFFF;display:inline-block;margin-top:calc(-1 * 0px);position:relative;white-space:nowrap;}
.s5[data-state~="sv_buttonUsesFlex"] .s5label {margin-top:0;}
.s5[data-disabled="false"] {cursor:pointer !important;}
.s5[data-disabled="false"]:active[data-state~="mobile"] .s5link,.s5[data-disabled="false"]:hover[data-state~="desktop"] .s5link {transition: border-color 0.4s ease 0s, background-color 0.4s ease 0s;    background-color:rgba(255, 132, 0, 1);border-color:rgba(48, 48, 48, 1);}
.s5[data-disabled="false"]:active[data-state~="mobile"] .s5label,.s5[data-disabled="false"]:hover[data-state~="desktop"] .s5label {transition: color 0.4s ease 0s;    color:#000000;}
.s5[data-disabled="true"] .s5link {background-color:rgba(204, 204, 204, 1);border-color:rgba(204, 204, 204, 1);}
.s5[data-disabled="true"] .s5label {color:#FFFFFF;}
.s5link {border-radius:5px;  transition: border-color 0.4s ease 0s, background-color 0.4s ease 0s;  box-shadow:0 1px 4px rgba(0, 0, 0, 0.6);;  background:rgba(48, 48, 48, 1) url(//static.parastorage.com/services/skins/2.1212.0/images/wysiwyg/core/themes/base/shiny1button_bg.png) 50% 50% repeat-x;border:solid rgba(48, 48, 48, 1) 0px;}
.s5label {font:normal normal normal 12px/1.1em Arial,'ｍｓ ｐゴシック','ms pgothic','돋움',dotum,helvetica,sans-serif ;  transition: color 0.4s ease 0s;  color:#FFFFFF;display:inline-block;margin-top:calc(-1 * 0px);position:relative;white-space:nowrap;}
.s5[data-state~="sv_buttonUsesFlex"] .s5label {margin-top:0;}
.s5[data-disabled="false"] {cursor:pointer !important;}
.s5[data-disabled="false"]:active[data-state~="mobile"] .s5link,.s5[data-disabled="false"]:hover[data-state~="desktop"] .s5link {transition: border-color 0.4s ease 0s, background-color 0.4s ease 0s;    background-color:rgba(255, 132, 0, 1);border-color:rgba(48, 48, 48, 1);}
.s5[data-disabled="false"]:active[data-state~="mobile"] .s5label,.s5[data-disabled="false"]:hover[data-state~="desktop"] .s5label {transition: color 0.4s ease 0s;    color:#000000;}
.s5[data-disabled="true"] .s5link {background-color:rgba(204, 204, 204, 1);border-color:rgba(204, 204, 204, 1);}
.s5[data-disabled="true"] .s5label {color:#FFFFFF;} 
</style>

<?php BigBoard::printContentBlock(18,'main_content'); ?>

<?php include 'includes/footer.php';?>
