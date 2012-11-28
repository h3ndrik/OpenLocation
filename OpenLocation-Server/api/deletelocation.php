<?php
/* Handle request "deletelocation" */
  /* HttpAuth */
  // No HttpAuth necessary

  /* Get data and sanitize */
  $sender = $json->{'sender'};
  $token = $json->{'token'};
  $interval = $json->{'interval'};

  if (empty($sender) || empty($token) || empty($interval)) die('{"request":"deletelocation", "error":"Wrong arguments"}');

  /* Authorization */
  list ($user_local, $domain) = validateUserByToken($sender, $token);

if (strrpos($interval, "-")) {
    $starttime = substr($interval, 0, strrpos($interval, "-"));
    $endtime = substr($interval, strrpos($interval, "-")+1);
  }
    else {
    $starttime = $interval;
    $endtime = $interval;
  }

  connectToMySQL();
  $query = "DELETE FROM `" . mysql_real_escape_string($user_local) . "` WHERE time >= " . mysql_real_escape_string($starttime) . " AND time <= " . mysql_real_escape_string($endtime) . ";";
  $result = mysql_query($query) or die500("MySQL Error (DELETE): " . mysql_error());
  $numrows = mysql_affected_rows();
  if ($numrows != null && $numrows >= 0)
    $response = '{"request":"deletelocation", "error":"0", "rows":"' . $numrows . '"}';
  else
    $response = '{"request":"deletelocation", "error":"Error, 0 rows deleted"}';

  mysql_close();


  echo base64_encode(gzdeflate($response));
?>
