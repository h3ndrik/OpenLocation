<?php
/* Handle request "getfriends" */
  /* HttpAuth */
  // No HttpAuth necessary

  /* Get data and sanitize */
  $sender = $json->{'sender'};
  $target = $json->{'target'};
  $token = $json->{'token'};

  if (empty($sender) || empty($token) || !validEmail($target)) die('{"request":"removefriend", "error":"Wrong arguments"}');

  /* Authorization */
  list ($user_local, $domain) = validateUserByToken($sender, $token);

  $error="0";

  /* Remove from own authorized */
  if (removeToken($user_local, $target, 'authorized', '')) {
    writetolog("Removed friendship authorization: " . $sender . "->" . $target);
  }
  else $error .= ' (Was not authorized)';

  /* Remove from own friends */
  if (removeToken($user_local, $target, 'friends', '')) {
    writetolog("Removed friend: " . $sender . "->" . $target);
  }
  else $error .= ' (Was not a friend)';

  $response = '{"request":"removefriend", "error":"' . $error . '"}';

  echo base64_encode(gzdeflate($response));
?>
