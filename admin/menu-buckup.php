<?php
if (!$brokenhtml) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<?php
} // if ! $brokenhtml
?>
<html>
<head>
    <title>RSS Administration</title>
    <link rel="stylesheet" href="../styles.css" type="text/css">
    <link rel="shortcut icon" href="/images/favicon.ico" />
    <script src="../include/common.js" type="text/javascript"></script>
    <script src="../include/printing.js" type="text/javascript"></script>
    <?php if ($_SERVER['PHP_SELF'] != '/admin/form-edit.php') {
        // if Page is form-edit... there is too much HTML, these slow it down
        ?>
        <link href="../include/CalendarControl.css" rel="stylesheet" type="text/css">
        <script src="../include/CalendarControl.js" type="text/javascript"></script>
        <script src="../include/sorttable.js" type="text/javascript"></script>
    <?php } ?>
    <script language="javascript" type="text/javascript">
        <?php echo $gExtraJS; ?>
        function verifyEmailVendor() {
          msg = "You are about to e-mail this order to the vendor. \n Are you sure you want to e-mail this order?";
          return confirm(msg);
        }
        function vendorcopy(id, current){
          response=prompt("Enter the new name for copied vendor:",current + " (Copy)");
          if (response == null)
            return false;
          document.forms['copy'].id.value=id;
          document.forms['copy'].newname.value=response;
          document.forms['copy'].submit();
        }

        function recordcopy(what, id, current){
          response=prompt("Enter the new name for copied " + what + ":",current + " (Copy)");
          if (response == null)
            return false;
          document.forms['copy'].id.value=id;
          document.forms['copy'].newname.value=response;
          document.forms['copy'].submit();
        }

        function accessfreight(vendor, vendoracc) {
          freight = document.getElementById('div'+vendor);
          if (vendoracc.checked) {
            freight.style.visibility = 'visible';
          } else {

            freightacc = document.getElementById('VD'+vendor);
            freightd = document.getElementById('VDH'+vendor);
            freightper = document.getElementById('VF'+vendor);
            freightacc.checked = false;
            freightper.value = freightd.value;
            freightper.disabled = true;
            // Fix for IE, which for some reason grabs the text input rather than the div
            if (freight.tagName == 'INPUT') {
              freight = freight.parentNode;
            }
            freight.style.visibility = 'hidden';
          }
        }

        function updatefreight(vendor, freightacc) {
          freight = document.getElementById('VF'+vendor);
          if (freightacc.checked) {
            freightf = document.getElementById('VFH'+vendor);
            freight.value = freightf.value;
            freight.disabled = false;
          } else {
            freightd = document.getElementById('VDH'+vendor);
            freight.value = freightd.value;
            freight.disabled = true;
          }
        }

        function updatefdefault(freightd) {
          vform = freightd.form;
          x = 0;
          for (var i in vform.elements) {
            d = vform.elements[i];
            if (d.disabled && (d.type == 'text'))
            {
              d.value = freightd.value;
            }
          }
        }
        <?php if ($extra_javascript && !$_REQUEST['extra_javascript']) {
            // We only want to run this if the variable diddn't come through alternative means
            if (is_array($extra_javascript)) {
                foreach ($extra_javascript as $java) {
                    echo $java;
                }
            } else {
                echo $extra_javascript;
            }
        } ?>
    </script>
</head>

<body OnLoad="<?php
if ($extra_onload && !$_REQUEST['extra_onload']) {
    echo $extra_onload;
} ?>">
<?php
require('../menu.php');
function footer($link)
{
    mysql_close($link);
    ?>
<?php } ?>
