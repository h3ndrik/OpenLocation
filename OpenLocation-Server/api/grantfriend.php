<?php
/* Handle request "grantfriend" */
  /* HttpAuth */
  // No HttpAuth necessary

  /* Get data and sanitize */
  $sender = $json->{'sender'};
  $target = $json->{'target'};
  $token = $json->{'token'};

  if (empty($sender) || empty($token) || empty($target)) die500('Missing arguments');

  /* Authorization */
  list ($user, $domain) = validateUserByToken($sender, $token);

  connectToMySQL();

  $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($sender) . "' AND authorized LIKE '%" . mysql_real_escape_string($target) . "%';";
  $result = mysql_query($query) or die500("MySQL Error (SELECT *): " . mysql_error());
  if (mysql_num_rows($result) == 1) {
    $row = mysql_fetch_array($result);
    $usersandtokens = explode(",", $row['authorized']);
    foreach ($usersandtokens as $singleuserandtoken) {
      if (strpos($singleuserandtoken, $target) === 0) {
        $newuserandtoken = str_replace("-", ":", $singleuserandtoken);
        $query = "UPDATE users SET authorized = REPLACE(authorized, '" . $singleuserandtoken . "', '" . $newuserandtoken . "') WHERE username = '" . mysql_real_escape_string($sender) . "';";
        $result = mysql_query($query) or die500("MySQL Error (UPDATE): " . mysql_error());
        // TODO: store target:targettoken in column friends
        writetolog("Granted friendship: " . $sender . "->" . $target);
      }
    }
  }
  mysql_free_result($result);

  mysql_close();

  $response = '{"request":"grantfriend", "error":"0"}';
  echo base64_encode(gzdeflate($response));
?>
