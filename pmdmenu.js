var bBrowserOK; bBrowserOK = 0;if(parseFloat(navigator.appVersion)>=4) if(navigator.appName == 'Microsoft Internet Explorer') bBrowserOK = 1;document.write('<STYLE>');document.write('a.MN9623927 {text-decoration:none; color:#000000; font-weight: 700; font-family: Arial; font-size: 16px; font-style:normal;} ');
document.write('a.MN9623927:hover {text-decoration:none; color:#FFFFFF; }');
document.write('a.SMN9623927 {text-decoration:none; color:#FF0000; font-weight: 400; font-family: Arial; font-size: 13px; font-style:normal;} ');
document.write('a.SMN9623927:hover {text-decoration:none; color:#7F7F7F; }');
document.write('table.MT9623927 {background:#000000; border:1 solid #000000; }');
document.write('td.MTD9623927 {padding-left:4; padding-right:4; background:#EDECDA; border:0 solid #000000; }');
document.write('.MTI9623927 {color:#000000; font-weight: 700; font-family: Arial; font-size: 16px; font-style:normal;}');
document.write('.MSI9623927 {color:#FF0000; font-weight: 400; font-family: Arial; font-size: 13px; font-style:normal;}');
document.write('</STYLE>');
function OpenMenu(MenuId){	MenuId.style.visibility = 'visible'; }
function CloseMenu(MenuId){	MenuId.style.visibility = 'hidden';}
function HLT9623927(TDId,AId){	TDId.style.background = '#EDECDA';	if(AId) AId.style.color = '#7F7F7F';}
function UHLT9623927(TDId,AId){	TDId.style.background = '#EDECDA';	if(AId) AId.style.color = '#FF0000';}
function THLT9623927(TDId,AId){	TDId.style.background = '#EDECDA';	if(AId) AId.style.color = '#FFFFFF';}
function TUHLT9623927(TDId,AId){	TDId.style.background = '#EDECDA';	if(AId) AId.style.color = '#000000';}
function WSBV9623927(MenuId){
document.write('<span id=' + MenuId + ' STYLE="position:absolute; visibility:hidden; zorder:1; ">');
document.write('<table class="MT9623927" cellspacing=0 cellpadding=2 onMouseOut="CloseMenu(' + MenuId + ');" onMouseOver="OpenMenu(' + MenuId + ');">');} 
function WSIV9623927(ItemID,ItemName,ItemURL,MenuID,ItemTitle,ItemTarget){
document.write('<tr><td NOWRAP id="X' + ItemID + '" class="MTD9623927" onMouseOver="HLT9623927(X' + ItemID + ',' + ItemID + ');" onMouseOut="UHLT9623927(X' + ItemID + ',' + ItemID + ');">');
document.write('<span NOWRAP><a href="' + ItemURL + '" title="' + ItemTitle + '" class="SMN9623927" target="' + ItemTarget + '" id="' + ItemID + '">' + ItemName + '</a></span></td></tr>');}
function WSFV9623927(ItemID,ItemFree){
document.write('<tr><td NOWRAP id="X' + ItemID + '" class="MTD9623927" onMouseOver="HLT9623927(X' + ItemID + ',' + ItemID + ');" onMouseOut="UHLT9623927(X' + ItemID + ',' + ItemID + ');">');
document.write('<span id="' + ItemID + '" class="MSI9623927" NOWRAP>' + ItemFree + '</span></td></tr>');}
function CloseSubBoxV(){document.write('</table></span>');}
function WTIV9623927(MenuID,MenuName,MenuURL,MenuTitle,MenuTarget){
document.write('<tr id="X' + MenuID + '" onMouseOver="OpenMenu(' + MenuID + ');THLT9623927(X' + MenuID + ',T' + MenuID + ');" onMouseOut="CloseMenu(' + MenuID + ');TUHLT9623927(X' + MenuID + ',T' + MenuID + ');"><td ALIGN=LEFT><span STYLE="position:relative;" class="MTI9623927" ><a id="T' + MenuID + '" href="' + MenuURL + '" title="' + MenuTitle + '" target="' + MenuTarget + '" class="MN9623927">' + MenuName + '</a></span>');
document.write('</td><td>&nbsp;');}function WTFV9623927(MenuID,MenuFree){
document.write('<tr id="X' + MenuID + '" onMouseOver="OpenMenu(' + MenuID + ');THLT9623927(X' + MenuID + ',T' + MenuID + ');" onMouseOut="CloseMenu(' + MenuID + ');TUHLT9623927(X' + MenuID + ',T' + MenuID + ');"><td ALIGN=LEFT><span id="T' + MenuID + '" STYLE="position:relative;" class="MTI9623927">' + MenuFree + '</span>');
document.write('</td><td>&nbsp;');}function CloseTopItemV(){document.write('</td></tr>');}
function WTHV9623927(MenuName,MenuURL,MenuTitle,MenuTarget){
document.write('<tr><td ALIGN=LEFT><span STYLE="position:relative;" class="MTI9623927" ><a href="' + MenuURL + '" target="' + MenuTarget + '" title="' + MenuTitle + '" class="MN9623927">' + MenuName + '</a></span>');
document.write('</td><td>&nbsp;</td></tr>');}function WTHFV9623927(MenuName,MenuFree){
document.write('<tr><td ALIGN=LEFT><span STYLE="position:relative;" class="MTI9623927">' + MenuFree + '</span>');
document.write('</td><td>&nbsp;</td></tr>');}document.write('<SPAN NOWRAP STYLE="position:relative; vertical-align:bottom; visibility:visible; background-color:#EDECDA; border:0 solid #808080; font-weight: 700; font-family: Arial; font-size:16px; height:21px">');
document.write('<TABLE cellspacing=0 cellpadding=2 border=0 bgcolor=#EDECDA><TR><TD><TABLE cellspacing=0 cellpadding=0 border=0>');if(bBrowserOK==1) {
WTIV9623927('MI9673109','SALES STATISTICS','','','_self');
WSBV9623927('MI9673109');
WSIV9623927('MI9674663','Enter Your Sales Statistics','http://www.pmddealer.com/salestats_form.php','MI9673109','','_self');
if(MI9674663) MI9674663.style.color = '#FF0000';WSIV9623927('MI9652125','View and Edit Your Stats','http://www.pmddealer.com/salestats_edit.php','MI9673109','','_self');
if(MI9652125) MI9652125.style.color = '#FF0000';WSIV9623927('MI9643073','View All Sales Statistics','http://www.pmddealer.com/salestats_query.php','MI9673109','','_self');
if(MI9643073) MI9643073.style.color = '#FF0000';CloseSubBoxV();
CloseTopItemV();
}
else
WTHV9623927('SALES STATISTICS','','','_self');
if(bBrowserOK==1) {
WTIV9623927('MI9673713','SALES REPORTS','','','_self');
WSBV9623927('MI9673713');
WSIV9623927('MI9643110','TABS & Rankings week ending 5-7-04 (RTF)','http://www.pmddealer.com/docs/tabs/TABS%20&%20Rankings%20week%20ending%205-7-04.rtf','MI9673713','','_self');
if(MI9643110) MI9643110.style.color = '#FF0000';WSIV9623927('MI9642551','President\'s Club Standings 4-30-04 (XLS) ','http://www.pmddealer.com/docs/tabs/2004%20President%20Club%204-30-04.xls','MI9673713','','_self');
if(MI9642551) MI9642551.style.color = '#FF0000';CloseSubBoxV();
CloseTopItemV();
}
else
WTHV9623927('SALES REPORTS','','','_self');
if(bBrowserOK==1) {
WTIV9623927('MI9659158','DAMAGE AND CLAIMS','','','_self');
WSBV9623927('MI9659158');
WSIV9623927('MI9680069','Damage Claim Prodedures','http://www.pmddealer.com/damage_faq.html','MI9659158','','_self');
if(MI9680069) MI9680069.style.color = '#FF0000';WSIV9623927('MI9652191','Damage Claim Request','http://www.pmddealer.com/damage_report.php','MI9659158','','_self');
if(MI9652191) MI9652191.style.color = '#FF0000';WSIV9623927('MI9675891','Parts Request Form','http://www.pmddealer.com/parts_request.php','MI9659158','','_self');
if(MI9675891) MI9675891.style.color = '#FF0000';WSIV9623927('MI9663804','Vendor Refused Submission Form','http://www.pmddealer.com/vendor_refused_report.php','MI9659158','','_self');
if(MI9663804) MI9663804.style.color = '#FF0000';WSIV9623927('MI9669658','Repair Claim Submission Form (PDF)','http://www.pmddealer.com/docs/PMD%20Repair%20Credit%20Form.pdf','MI9659158','','_self');
if(MI9669658) MI9669658.style.color = '#FF0000';CloseSubBoxV();
CloseTopItemV();
}
else
WTHV9623927('DAMAGE AND CLAIMS','','','_self');
if(bBrowserOK==1) {
WTIV9623927('MI9674558','ORDER REPORTS & STOCK STATUS','','','_self');
WSBV9623927('MI9674558');
WSIV9623927('MI9679612','PMD Open Order Report','http://www.pmddealer.com/docs/PMD%20Open%20Order%20Report%20-%20Master.xls','MI9674558','','_self');
if(MI9679612) MI9679612.style.color = '#FF0000';WSIV9623927('MI9648917','Updated Stock Report','http://www.pmddealer.com/docs/UPDATEDSTOCK%20Master.xls','MI9674558','','_self');
if(MI9648917) MI9648917.style.color = '#FF0000';WSIV9623927('MI9678859','Vendor Shortage Submission Form','http://www.pmddealer.com/vendor_shortage_report.php','MI9674558','','_self');
if(MI9678859) MI9678859.style.color = '#FF0000';CloseSubBoxV();
CloseTopItemV();
}
else
WTHV9623927('ORDER REPORTS & STOCK STATUS','','','_self');
if(bBrowserOK==1) {
WTIV9623927('MI9668855','SELECT A VENDOR','','','_self');
WSBV9623927('MI9668855');
WSIV9623927('MI9655795','View Your Order Summary ','http://www.pmddealer.com/summary.php','MI9668855','','_self');
if(MI9655795) MI9655795.style.color = '#FF0000';CloseSubBoxV();
CloseTopItemV();
}
else
WTHV9623927('SELECT A VENDOR','','','_self');
document.write('</table></td></tr>');
document.write('</table></SPAN>');
