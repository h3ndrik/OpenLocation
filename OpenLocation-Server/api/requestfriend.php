<?php
/* Handle request "requestfriend" */
  /* HttpAuth */
  // No HttpAuth necessary

  /* Get data and sanitize */
  $sender = $json->{'sender'};
  $target = $json->{'target'};  // we are the target
  $token = $json->{'token'};

  if (!validEmail($sender) || empty($target) || empty($token)) die('{"request":"requestfriend", "error":"Wrong arguments"}');

  /* Check if authorized */
  // not necessary  // TODO: really?

  list ($target_local, $target_domain, $target_fullusername) = explode_username($target);

  if ($target_domain != $_SERVER['HTTP_HOST']) die('{"request":"requestfriend", "error":"Wrong host"}');

  /* Check if user exists */
  if (!isUser($target_local)) die('{"request":"requestfriend", "error":"User does not exist"}');

  /* Cancel if $sender is known as 'friends' */
  if (isKnown($target_local, $sender, 'friends', ':')) die('{"request":"requestfriend", "error":"Is already a friend"}');

  /* Temp store $token in 'friends', it authorizes me for $sender */
  storeToken($target_local, $sender, $token, 'friends');

  /* Mark tokens valid, if $sender is known 'authorized' */
  if (isKnown($target_local, $sender, 'authorized', '-')) {
    markTokenValid($target_local, $sender, 'authorized');
    markTokenValid($target_local, $sender, 'friends');
  }

  /* Done */
  writetolog("New friendship request: " . mysql_real_escape_string($sender) . "->" . mysql_real_escape_string($target));
//die('{"request":"requestfriend", "error":"test"}');
  echo base64_encode(gzdeflate('{"request":"requestfriend", "error":"0", "token":"' . $newtoken . '"}'));
?>
