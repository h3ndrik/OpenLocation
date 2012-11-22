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

  $error = sendrequestfriend($user_local, $target);
  switch ($error) {
    case 0:
      $response = '{"request":"sendrequestfriend", "error":"0"}';
      break;
    case -1:
      $response = '{"request":"sendrequestfriend", "error":"User does not exist"}';
      break;
    case -2:
      $response = '{"request":"sendrequestfriend", "error":"Is already a friend"}';
      break;
    default:
      $response = '{"request":"sendrequestfriend", "error":"Unknown error"}';
      break;
  }

  echo base64_encode(gzdeflate($response));
?>
