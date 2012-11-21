<?php
/* Handle request "getlocation" */
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
  $result = mysql_query($query) or die500("MySQL Error: (SELECT *): " . mysql_error());
  if (mysql_num_rows($result) != 1) {
    mysql_free_result($result);
    mysql_close();
    writetolog("Error (getlocationdata): Not Authorized: " . mysql_real_escape_string($user));
    die('{"request":"location", "error":"Not Authorized"}');
  }
  mysql_free_result($result);

  /* Get locations from MySQL */
  $query = "SELECT * FROM `" . mysql_real_escape_string($user) . "` WHERE time > " . mysql_real_escape_string($starttime) . " AND time < " . mysql_real_escape_string($endtime) . " ORDER BY time ASC;";
  $result = mysql_query($query) or die500("MySQL Error: (SELECT *): " . mysql_error());
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
    public $request = "location";
    public $error = 0;
    public $data;
  }

  $response = new Response();
  $response->data = $data;

  echo base64_encode(gzdeflate(json_encode($response)));

?>
