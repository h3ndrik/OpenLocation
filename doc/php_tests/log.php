<?php
$myFile = "log.txt";
$fh = fopen($myFile, 'a') or die("FAILED");
$timestamp = time();
$stringData = "TIME:$timestamp IP:$REMOTE_ADDR\n";
fwrite($fh, $stringData);
fclose($fh);
//echo "<html><body>DONE (at $timestamp)</body></html>";
?>
