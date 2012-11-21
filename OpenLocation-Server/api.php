<?php
if (isset($DEBUG) && strcmp($DEBUG, "yesdoit") == 0) {
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
}

require_once('functions.php');

/* Sanity checks */
if ($_SERVER['REQUEST_METHOD'] !== "POST") die400('POST expected!');
if (empty($_POST["json"])) die400 ('Wrong Argument(s)');

/* Decode JSON */
$json = json_decode(gzinflate(base64_decode($_POST["json"]))) or die500('Bad JSON');
if ($json == NULL || empty($json)) {
  writetolog("Error: Bad JSON: " . gzinflate(base64_decode($_POST["json"])));
  die500('Bad JSON');
}
$request = $json->{'request'};


/* Handle request "setlocation" */
if (strcmp($request, 'setlocation') == 0) {

  /* HttpAuth */
  list ($user, $domain) = validateUser();

  /* Get data and sanitize */
  $sender = $json->{'sender'}; // Currently not used
  $data = $json->{'data'};
  //$data[] = $json->{'data'};

  if (empty($data)) die500('Missing arguments');

  connectToMySQL();

  foreach ($data as $row) {
    if (empty($row->{'time'}) || empty($row->{'latitude'}) || empty($row->{'longitude'})) die ('Wrong Argument(s) inside JSON');
    $query = "INSERT INTO `" . mysql_real_escape_string($user) . "` VALUES('" . $row->{'time'} . "', '" . $row->{'latitude'} . "', '" . $row->{'longitude'} . "', '" . $row->{'altitude'} . "', '" . $row->{'accuracy'} . "', '" . $row->{'speed'} . "', '" . $row->{'bearing'} . "', '" . $row->{'provider'} . "', '" . $_SERVER['REMOTE_ADDR'] . "');";
    $result = mysql_query($query) or writetolog("MySQL Error (INSERT): " . mysql_error());
  }
  echo 'Position updated.';

  mysql_close();
}


/* Handle request "getlocationdata" */
elseif (strcmp($request, 'getlocationdata') == 0) {

  /* HttpAuth */
  // No HttpAuth necessary

  /* Get data and sanitize */
  $sender = $json->{'sender'};
  $user = $json->{'user'};
  $auth = $json->{'auth'};
  if (!empty($json->{'starttime'})) $starttime = $json->{'starttime'};
  else $starttime = 0;
  if (!empty($json->{'endtime'})) $endtime = $json->{'endtime'};
  else $endtime = time()*1000;

  if (empty($sender) || empty($user) || empty($auth)) die500('Missing arguments');


  connectToMySQL();

  /* Check if authorized */
  $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($user) . "' AND authorized LIKE '%" . mysql_real_escape_string($sender) . ":" . mysql_real_escape_string($auth) . "%';";
  $result = mysql_query($query) or die500("MySQL Error (SELECT *): " . mysql_error());
  if (mysql_num_rows($result) != 1) {
    mysql_free_result($result);
    mysql_close();
    writetolog("Error (getlocationdata): Not Authorized: " . mysql_real_escape_string($user));
    die403('{"request":"locationdata", "error":"Not Authorized"}');
  }
  mysql_free_result($result);

  /* Get locations from MySQL */
  $query = "SELECT * FROM `" . mysql_real_escape_string($user) . "` WHERE time > " . mysql_real_escape_string($starttime) . " AND time < " . mysql_real_escape_string($endtime) . " ORDER BY time ASC;";
  $result = mysql_query($query) or die500("MySQL Error (SELECT): " . mysql_error());
  if (mysql_num_rows($result) != false && mysql_num_rows($result) > 0) {
    //      class LocationDatagram {
    //        public $time;
    //        public $latitude;
    //        public $longitude;
    //        public $altitude;
    //        public $accuracy;
    //        public $speed;
    //        public $bearing;
    //        public $provider;
    //      }
    //      $locationDatagram = new LocationDatagram();
    $data = array();

    for ($i=0; $i<mysql_num_rows($result); $i++) {
      $data[$i] = mysql_fetch_object($result);
    }

  }
  else {  // Nothing found
    $data = null;
  }
  mysql_free_result($result);

  mysql_close();

  /* Send response */
  class Response {
    public $request = "locationdata";
    public $error = 0;
    public $data;
  }

  $response = new Response();
  $response->data = $data;

  echo json_encode($response);


}


/* Handle request "requestfriend" */
elseif (strcmp($request, 'requestfriend') == 0) {

  /* HttpAuth */
  list ($sender, $domain) = validateUser();

  /* Get data and sanitize */
  //$sender = $json->{'sender'};
  $friend = $json->{'user'};
  $auth = newtoken();

  if (empty($sender) || empty($friend) || empty($auth)) die500('Missing arguments');

  connectToMySQL();

  /* Remove old tokens */
  // TODO: If executed, this breaks current auth!
  $query = "SELECT authorized FROM users WHERE username = '" . mysql_real_escape_string($friend) . "' AND authorized LIKE '%" . mysql_real_escape_string($sender) . "%';";
  $result = mysql_query($query) or die500("MySQL Error (SELECT): " . mysql_error());
  if (mysql_num_rows($result) == 1) {
    $sendersandtokens = explode(",", mysql_fetch_object($result), -1);
    foreach ($sendersandtokens as $singlesenderandtoken) {
      if (strpos($singlesenderandtoken, $sender) === 0) {
        // Update user
        $query = "UPDATE users WHERE username = '" . mysql_real_escape_string($friend) . "' SET authorized = REPLACE(authorized, '" . $singlesenderandtoken . "', '');";
        $result = mysql_query($query) or die500("MySQL Error (UPDATE): " . mysql_error());
        // Update sender
        $singleuserandtoken = str_replace($sender, $friend, $singlesenderandtoken);
        $query = "UPDATE users WHERE username = '" . mysql_real_escape_string($sender) . "' SET friends = REPLACE(friends, '" . $singleuserandtoken . "', '');";
        $result = mysql_query($query) or die500("MySQL Error (UPDATE): " . mysql_error());
        writetolog("Invalidate user:token (got new one): " . $singleuserandtoken);
      }
    }
  }
  mysql_free_result($result);

  /* Write user-auth to user */
  $query = "UPDATE users WHERE username = '" . mysql_real_escape_string($friend) . "' SET authorized = concat(authorized, '" . mysql_real_escape_string($sender) . "-" . $auth . "');";
  $result = mysql_query($query) or die500("MySQL Error (UPDATE): " . mysql_error());
  if (mysql_num_rows($result) != 1) {
    mysql_close();
    writetolog("Error (requestfriend): User not found: " . mysql_real_escape_string($friend));
    die400('{"request":"requestfriend", "error":"User not found"}');
  }

  /* Write user:auth to sender */
  $query = "UPDATE users WHERE username = '" . mysql_real_escape_string($sender) . "' SET friends = concat(friends, '" . mysql_real_escape_string($friend) . ":" . $auth . "');";
  $result = mysql_query($query) or die500("MySQL Error (UPDATE): " . mysql_error());
  if (mysql_num_rows($result) != 1) {
    mysql_close();
    writetolog("Error (requestfriend): Sender not found: " . mysql_real_escape_string($sender));
    die500('{"request":"requestfriend", "error":"Sender not found"}');
  }

  // Done
  writetolog("New friendship request: " . mysql_real_escape_string($friend) . "->" . mysql_real_escape_string($sender));

  mysql_close();
}


/* Handle request "grantfriend" */
elseif (strcmp($request, 'grantfriend') == 0) {

  /* HttpAuth */
  list ($sender, $domain) = validateUser();

  /* Get data and sanitize */
  //$sender = $json->{'sender'};
  $friend = $json->{'user'};
  //$auth = $json->{'auth'};

  if (empty($sender) || empty($friend)) die500('Missing arguments');


  connectToMySQL();

  mysql_close();

  die500("Not implemented");
}


/* Could not handle $request */
else {
  die500('Error or not implemented');
}

?>
