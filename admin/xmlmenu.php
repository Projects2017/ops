<?php
// xmlmenu.php

// displays the XML order options

echo '<p><span onclick="window.location=\'exportxml.php\'" onmouseover="this.style.cursor=\'pointer\'" onmouseout="this.style.cursor=\'default\'">Export Orders to XML</span>&nbsp;&nbsp;&nbsp;&nbsp;<span onclick="window.location=\'importxml.php\'" onmouseover="this.style.cursor=\'pointer\'" onmouseout="this.style.cursor=\'default\'">Import Orders from XML</span>&nbsp;&nbsp;&nbsp;&nbsp;<span onclick="window.location=\'downloadxml.php\'" onmouseover="this.style.cursor=\'pointer\'" onmouseout="this.style.cursor=\'default\'">Download XML Order</span>&nbsp;&nbsp;&nbsp;&nbsp;<span onclick="window.location=\'removexml.php\'" onmouseover="this.style.cursor=\'pointer\'" onmouseout="this.style.cursor=\'default\'">Delete XML Files</span></p>';

?>