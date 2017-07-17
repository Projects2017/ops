<?php
// array_combine replacement by msfx@myrealbox.com
// url: http://us4.php.net/manual/en/function.array-combine.php
function array_combine($array_keys, $array_values) {
   $t      = array();
   $size  = count($array_keys);
   if (($size != count($array_values)) || ($size == 0)) return false;
   for ($x = 0,    $key    = array_values($array_keys),
                   $value  = array_values($array_values); $x < $size; $x++) {
           $t[$key[$x]] = $value[$x];
   }
   return $t;
}

function matrixSort($matrix,$sortKey, $datesort = 0) {
   if (!$datesort) {
      foreach ($matrix as $key => $subMatrix)
         $tmpArray[$key] = strtolower($subMatrix[$sortKey]);
   } else {
      foreach ($matrix as $key => $subMatrix)
         $tmpArray[$key] = strtotime($subMatrix[$sortKey]);
   }

   asort($tmpArray); // Sort
 
   foreach ($tmpArray as $key => $subMatrix)
       $newMatrix[] = $matrix[$key];
   return $newMatrix;
}


// Will not return line if csv has differing keys per line.
// Returns multi-demensional array for display
function loadcsv($keys, $filename) {
    $lines = file($filename);
    foreach ($lines as $line)
       $records[] = array_combine($keys, explode('|', $line)); // line returns fals if number of keys doesn't match number of columns in csv
    return $records;  
}

?>
