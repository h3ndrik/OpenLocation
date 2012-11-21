<?php
/* Handle request "setlocation" */
  /* HttpAuth */
  list ($user, $domain) = validateUser();

  /* Get data and sanitize */
  $sender = $json->{'sender'}; // Currently not used
  $data = $json->{'data'};
  //$data[] = $json->{'data'};

  if (empty($data)) die500('Missing arguments');

  connectToMySQL();

  foreach ($data as $row) {
    if (empty($row->{'time'}) || empty($row->{'latitude'}) || empty($row->{'longitude'})) die ('Wrong Argument(s) inside JSON');
    $query = "INSERT INTO `" . mysql_real_escape_string($user) . "` VALUES('" . $row->{'time'} . "', '" . $row->{'latitude'} . "', '" . $row->{'longitude'} . "', '" . $row->{'altitude'} . "', '" . $row->{'accuracy'} . "', '" . $row->{'speed'} . "', '" . $row->{'bearing'} . "', '" . $row->{'provider'} . "', '" . $_SERVER['REMOTE_ADDR'] . "');";
    $result = mysql_query($query) or writetolog("MySQL Error (INSERT): " . mysql_error());
  }
  echo 'Position updated.';

  mysql_close();

?>
