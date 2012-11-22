<?php
/* Handle request "sendrequestfriend" */
  /* HttpAuth */
  // No HttpAuth necessary

  /* Get data and sanitize */
  $sender = $json->{'sender'};
  $target = $json->{'target'};
  $token = $json->{'token'};

  if (empty($sender) || !validEmail($target) || empty($token)) die('{"request":"sendrequestfriend", "error":"Wrong arguments"}');

  list ($target_local, $target_domain, $target_fullusername) = explode_username($target);

  /* Authorization */
  list ($user_local, $domain) = validateUserByToken($sender, $token);

  sendrequestfriend($user_local, $target);

  $response = '{"request":"sendrequestfriend", "error":"0"}';
  echo base64_encode(gzdeflate($response));
?>
