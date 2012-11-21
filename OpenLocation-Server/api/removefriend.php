<?php
/* Handle request "getfriends" */
  /* HttpAuth */
  // No HttpAuth necessary

  /* Get data and sanitize */
  $sender = $json->{'sender'};
  $target = $json->{'target'};
  $token = $json->{'token'};

  if (empty($sender) || empty($token) || empty($target)) die500('Missing arguments');

  /* Authorization */
  list ($user, $domain) = validateUserByToken($sender, $token);

  //die('{"request":"removefriend", "error":"0"}');

  connectToMySQL();

  /* Remove from own authorized */
  removeUserAndToken($sender, $target, "authorized");
  writetolog("Removed friendship authorization: " . $sender . "->" . $target);

  /* Remove from own friends */
  removeUserAndToken($sender, $target, "friends");
  writetolog("Removed friend: " . $sender . "->" . $target);

  mysql_close();

  $response = '{"request":"removefriend", "error":"0"}';
  echo base64_encode(gzdeflate($response));
?>
