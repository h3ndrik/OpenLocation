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

  connectToMySQL();

  $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($user) . "';";
  $result = mysql_query($query) or die500("MySQL Error (SELECT *): " . mysql_error());
  if (mysql_num_rows($result) == 1) {
    $data = array();
    $data_pending = array();
    $row = mysql_fetch_array($result);
    $friendsandtokens = explode(",", $row['friends']);
    $i = 0; $j = 0;
    foreach ($friendsandtokens as $friend) {
      if (!empty($friend) && strrpos($friend, ":")) {
        $data[$i] = substr($friend, 0, strrpos($friend, ":"));
        $i++;
      }
      elseif (!empty($friend) && strrpos($friend, "-")) {
        $data_pending[$j] = substr($friend, 0, strrpos($friend, "-"));
        $j++;
      }
    }
    $incomingandtokens = explode(",", $row['authorized']);
    $k = 0;
    foreach ($incomingandtokens as $friend) {
      if (!empty($friend) && strrpos($friend, "-")) {
        $data_incoming[$k] = substr($friend, 0, strrpos($friend, "-"));
        $k++;
      }
    }
  }
  else {
    mysql_free_result($result);
    die500("MySQL Error (SELECT): " . mysql_error());
  }
  mysql_free_result($result);

  mysql_close();

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
