<?php
/* Handle request "getuserpreferences" */

  /* HttpAuth */
  list ($user, $domain) = validateUser();

  /* Get data and sanitize */
  $sender_json = $json->{'sender'};

  if ($sender_json !== $user) die500('Wrong user');

  /* Send response */
  class Response {
    public $request = "userpreferences";
    public $error = 0;
    public $token;
  }

  $response = new Response();
  $response->token = getowntoken($user);

  echo base64_encode(gzdeflate(json_encode($response)));
?>
