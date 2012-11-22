<?php
/* Handle request "getfriends" */
  /* HttpAuth */
  // No HttpAuth necessary

  /* Get data and sanitize */
  $sender_json = $json->{'sender'};
  $token = $json->{'token'};

  if (empty($sender_json) || empty($token)) die500('Missing arguments');

  /* Authorization */
  list ($user, $domain) = validateUserByToken($sender_json, $token);

  list ($data, $data_pending, $data_incoming) = getfriends($user);

  /* Send response */
  class Response {
    public $request = "friends";
    public $error = 0;
    public $data;
    public $pending;
    public $incoming;
  }

  $response = new Response();
  $response->data = $data;
  $response->pending = $data_pending;
  $response->incoming = $data_incoming;

  echo base64_encode(gzdeflate(json_encode($response)));
?>
