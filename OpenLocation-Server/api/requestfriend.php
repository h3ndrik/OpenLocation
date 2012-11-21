<?php
/* Handle request "requestfriend" */
  /* HttpAuth */
  // No HttpAuth necessary

  /* Get data and sanitize */
  $sender = $json->{'sender'};
  $target = $json->{'target'};
  $newtoken = newtoken();

  if (empty($sender) || empty($target)) die500('Missing arguments');

  if (strrpos($target, "@")) {
    $local = substr($target, 0, strrpos($target, "@"));
    $domain = substr($target, strrpos($target, "@")+1);
  }
    else {
    $local = $target;
    $domain = $_SERVER['HTTP_HOST'];
  }

  if ($domain != $_SERVER['HTTP_HOST']) die400('Wrong host');

  /* Check if authorized */
  // not necessary

  connectToMySQL();

  /* Remove old tokens */
  // TODO: If executed, this breaks current auth!
  //removeUserAndToken($target, $sender, "authorized");
  //writetolog("Invalidate user:token (got new one): " . $target . "->" . $sender);

  /* Write user-auth to user */
  $query = "UPDATE users SET authorized = CONCAT(authorized, '" . mysql_real_escape_string($sender) . "-" . $newtoken . ",') WHERE username = '" . mysql_real_escape_string($local) . "';";
  $result = mysql_query($query) or die500("MySQL Error (UPDATE): " . mysql_error());
  if (mysql_affected_rows() != 1) {
    mysql_close();
    writetolog("Error (requestfriend): User not found: " . mysql_real_escape_string($target));
    die('{"request":"requestfriend", "error":"User not found"}');
  }

  // Done
  writetolog("New friendship request: " . mysql_real_escape_string($target) . "->" . mysql_real_escape_string($sender));

  mysql_close();

  echo base64_encode(gzdeflate('{"request":"requestfriend", "error":"0", "token":"' . $newtoken . '"}'));
?>
