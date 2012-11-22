<?php
/* Handle request "newfriend" */
  /* HttpAuth */
  // No HttpAuth necessary

  /* Get data and sanitize */
  $sender = $json->{'sender'};
  $target = $json->{'target'};
  $token = $json->{'token'};

  if (empty($sender) || !validEmail($target) || empty($token)) die('{"request":"newfriend", "error":"Wrong arguments"}');

  list ($target_local, $target_domain, $target_fullusername) = explode_username($target);

  /* Authorization */
  list ($user_local, $domain) = validateUserByToken($sender, $token);

  connectToMySQL();

  sendrequestfriend($user_local, $target);

  mysql_free_result($result);

  mysql_close();

  $response = '{"request":"newfriend", "error":"0"}';
  echo base64_encode(gzdeflate($response));
?>
