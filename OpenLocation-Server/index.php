<?php
if (isset($DEBUG) && strcmp($DEBUG, "yesdoit") == 0) {
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
}

require_once('functions.php');

/* HttpAuth */
list ($user, $domain) = validateUser();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html><head><title>OpenLocation - Main</title>
  <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.4/leaflet.css" />
  <!--[if lte IE 8]>
     <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.4/leaflet.ie.css" />
  <![endif]-->
  <script src="http://cdn.leafletjs.com/leaflet-0.4/leaflet.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

  <style type="text/css">
body {
    padding: 0;
    margin: 0;
}
html, body, #map {
    height: 100%;
}
  </style>
</head>
<body>
  <div id="map"></div>

<script type="text/javascript">
<!--
var map = L.map('map'); //.setView([51.513, 7.46], 10);
L.tileLayer('http://{s}.tile.cloudmade.com/3941788bcb3747e18763298b5ba22953/997/256/{z}/{x}/{y}.png', {
    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery &copy; <a href="http://cloudmade.com">CloudMade</a>',
    maxZoom: 18
}).addTo(map);

//map.locate({setView: true, maxZoom: 16});

//map.attributionControl.removeFrom(map);

function onLocationFound(e) {
}

map.on('locationfound', onLocationFound);

function onLocationError(e) {
    //alert(e.message);
}

map.on('locationerror', onLocationError);

<?php
  connectToMySQL();

  // Show Location of friends
  // get friends
  $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($user) . "';";

  $result = mysql_query($query) or die("MySQL Error (SELECT *): " . mysql_error());

  if (mysql_num_rows($result) == 1) {
    $row = mysql_fetch_object($result);
    $friends = explode(",",$row->friends);
    mysql_free_result($result);
  }
  else {
    mysql_free_result($result);
    die('MySQL Error');
  }

  foreach($friends as $friend) {
    if (empty($friend)) continue;

    if (strrpos($friend, ":")) {
      $friend_name = substr($friend, 0, strrpos($friend, ":"));
      $friend_auth = substr($friend, strrpos($friend, ":")+1);
    }
    else die("No auth");
    if (strrpos($friend_name, "@")) {
      $friend_domain = substr($friend_name, strrpos($friend_name, "@")+1);
      $friend_local = substr($friend_name, 0, strrpos($friend_name, "@"));
    }
    else die("Incorrect username");

    // Überprüfen ob localhost
    //if (strcmp($friend_domain, $_SERVER['HTTP_HOST']) == 0) {
    //  //TODO: query database directly (copy code)
    //}

    // request json from remote
    $url = "http://" . $friend_domain . "/api.php";
    $req_json = json_encode(array("request" => "getlocation", "sender" => mysql_real_escape_string($user . "@" . $domain), "user" => mysql_real_escape_string($friend_local), "auth" => $friend_auth, "starttime" => (string)(time()*1000-86400000), "endtime" => (string)time()*1000));
    //$req_json = base64_encode(gzdeflate(json_encode(array("request" => "getlocationdata", "sender" => mysql_real_escape_string($user . "@" . $domain), "user" => mysql_real_escape_string($friend_local), "auth" => $friend_auth))));

    $http_result = doBlockingHttpJsonRequest($url, $req_json);

    // Draw
    $result = json_decode($http_result);
    if ($result == null || empty($result)) die ('Bad json (empty): ' . $http_result);

    if (isset($result->{'data'})) $rows = $result->{'data'};
    else $rows = null;

    if (count($rows) > 0) {

      echo "var polyline = L.polyline([";

      for ($i=0; $i<count($rows)-1; $i++) {
        $row = $rows[$i];
        echo '[' . $row->latitude . ', ' . $row->longitude . '], ';
      }
      $row = $rows[count($rows)-1];
      echo '[' . $row->latitude . ', ' . $row->longitude . ']';

      echo "], {color: 'green'}).addTo(map);\n";

      echo 'var marker = L.marker([' . $row->latitude . ', ' . $row->longitude . ']).addTo(map)' . "\n";
      echo '    .bindPopup("' . $friend_name . ', ' . elapsed_time(intval($row->time / 1000)) . '").openPopup();' . "\n\n";
    }
  }

  // Show own Path and Location
  $starttime = (string)(time()*1000-86400000);
  $endtime = (string)(time()*1000);
  $query = "SELECT * FROM " . mysql_real_escape_string($user) . "  WHERE time > " . mysql_real_escape_string($starttime) . " AND time < " . mysql_real_escape_string($endtime) . " ORDER BY time ASC;";
  $result = mysql_query($query) or die("MySQL Error (SELECT): " . mysql_error());

  if (mysql_num_rows($result) != false && mysql_num_rows($result) > 0) {
    echo "var polyline = L.polyline([";
    for ($i=0; $i<mysql_num_rows($result)-1; $i++) {
      $row = mysql_fetch_object($result);
      echo '[' . $row->latitude . ', ' . $row->longitude . '], ';
    }
    $row = mysql_fetch_object($result);
    echo '[' . $row->latitude . ', ' . $row->longitude . ']';

    echo "], {color: 'red'}).addTo(map);\n";

    echo "map.setView([" . $row->latitude . ", " . $row->longitude . "], 15);\n";

    echo 'var marker = L.marker([' . $row->latitude . ', ' . $row->longitude . ']).addTo(map)' . "\n";
    echo '    .bindPopup("' . $user . ', ' . elapsed_time(intval($row->time / 1000)) . '").openPopup();' . "\n\n";



    mysql_free_result($result);
  }
  else {
    mysql_free_result($result);
    die('map.setView([51.513, 7.46], 10);</script>Nothing found.</body></html>');
  }

  mysql_close();
?>

// -->
</script>

</body></html>
