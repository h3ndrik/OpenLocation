<?php
require_once('functions.php');

/* HttpAuth */
list ($user, $domain) = validateUser();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html><head><title>OpenLocation - Main</title>
  <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.4/leaflet.css" />
  <link rel="stylesheet" type="text/css" href="style.css" />
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
 #friends {
  /*position*/ position:absolute; bottom:64px; right:0px; z-index: 100;
 }
 #status {
  /*position*/ position:absolute; bottom:16px; right:0px; z-index: 100;
 }
  </style>
</head>
<body>
  <div id="map"></div>
  <a href="/friends.php"><div id="friends" class="button">&#9734;</div></a>
  <a href="/status.php"><div id="status" class="button"><!--&#9998;-->&#9762;</div></a>

<script type="text/javascript">
<!--
var map = L.map('map').setView([51.513, 7.46], 9);
L.tileLayer('http://{s}.tile.cloudmade.com/3941788bcb3747e18763298b5ba22953/997/256/{z}/{x}/{y}.png', {
    //attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery &copy; <a href="http://cloudmade.com">CloudMade</a>',
    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
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
  // Show Location of friends
  // get friends
  list ($friends, $friends_pending, $friends_incoming) = getfriends($user);

  foreach($friends as $friend) {
    if (empty($friend)) continue;

    list ($friend_local, $friend_domain, $friend_fullusername) = explode_username($friend);
    $friend_auth = getfriendtoken($user, $friend);

    // Überprüfen ob localhost
    //if (strcmp($friend_domain, $_SERVER['HTTP_HOST']) == 0) {
    //  //TODO: query database directly (copy code)
    //}

    // request json from remote
    $url = "http://" . $friend_domain . "/api.php";
    $req_json = json_encode(array("request" => "getlocation", "sender" => $user . "@" . $domain, "user" => $friend_local, "auth" => $friend_auth, "starttime" => (string)(time()*1000-28800000), "endtime" => (string)(time()*1000)));
    //$req_json = base64_encode(gzdeflate(json_encode(array("request" => "getlocationdata", "sender" => mysql_real_escape_string($user . "@" . $domain), "user" => mysql_real_escape_string($friend_local), "auth" => $friend_auth))));

    $http_result = doBlockingHttpJsonRequest($url, $req_json);

    // Draw
    $result = json_decode($http_result);
    if ($result == null || empty($result) || $result->{'request'} != "location") {
      // fail silently
      echo "// Bad response for " . $friend_fullusername . "\n\n";
      //die ('Bad json: ' . $http_result);
      $rows = null;
    }

    if (isset($result->{'data'})) {
      $rows = $result->{'data'};
    }
    else {
      echo "// No data for " . $friend_fullusername . "\n\n";
      $rows = null;
    }

    if (count($rows) > 0) {

      echo 'var polyline_' . $friend_local . ' = L.polyline([';

      for ($i=0; $i<count($rows)-1; $i++) {
        $row = $rows[$i];
        echo '[' . $row->latitude . ', ' . $row->longitude . '], ';
      }
      $row = $rows[count($rows)-1];
      echo '[' . $row->latitude . ', ' . $row->longitude . ']';

      echo "], {color: 'green'}).addTo(map);\n";

      echo 'var marker_' . $friend_local . ' = L.marker([' . $row->latitude . ', ' . $row->longitude . ']).addTo(map)' . "\n";
      echo '    .bindPopup("' . $friend_local . ', ' . elapsed_time(intval($row->time / 1000)) . '");' . "\n";
      echo 'var radius_' . $friend_local . ' = L.circle([' . $row->latitude . ', ' . $row->longitude . '], ' . $row->accuracy . ', {color: \'green\', opacity: 0.2, fillOpacity: 0.1}).addTo(map);' . "\n\n";

      if(isset($_GET['friend']) && $_GET['friend'] == $friend) {
        $setview = array("name" => $friend_local, "latitude" => $row->latitude, "longitude" => $row->longitude);
      }
    }
  }

  // Show own Path and Location
  connectToMySQL();
  $starttime = (string)(time()*1000-86400000);
  $endtime = (string)(time()*1000);
  $query = "SELECT * FROM " . mysql_real_escape_string($user) . "  WHERE time > " . mysql_real_escape_string($starttime) . " AND time < " . mysql_real_escape_string($endtime) . " AND provider NOT LIKE '%jitter%' ORDER BY time ASC;";
  $result = mysql_query($query) or die500("MySQL Error (SELECT): " . mysql_error());

  if (mysql_num_rows($result) != false && mysql_num_rows($result) > 0) {
    echo 'var polyline_' . $user . ' = L.polyline([';
    for ($i=0; $i<mysql_num_rows($result)-1; $i++) {
      $row = mysql_fetch_object($result);
      echo '[' . $row->latitude . ', ' . $row->longitude . '], ';
    }
    $row = mysql_fetch_object($result);
    echo '[' . $row->latitude . ', ' . $row->longitude . ']';

    echo "], {color: 'red'}).addTo(map);\n";

    if(!isset($setview)) {
      $setview = array("name" => $user, "latitude" => $row->latitude, "longitude" => $row->longitude);
    }

    echo 'var marker_' . $user . ' = L.marker([' . $row->latitude . ', ' . $row->longitude . ']).addTo(map)' . "\n";
    echo '    .bindPopup("' . $user . ', ' . elapsed_time(intval($row->time / 1000)) . '");' . "\n";
    echo 'var radius_' . $user . ' = L.circle([' . $row->latitude . ', ' . $row->longitude . '], ' . $row->accuracy . ', {color: \'red\', opacity: 0.2, fillOpacity: 0.1}).addTo(map);' . "\n\n";

    echo "map.setView([" . $setview['latitude'] . ", " . $setview['longitude'] . "], 13);\n";

    echo "marker_" . $setview['name'] . ".openPopup();";    // setView before marker.openPopup(), otherwise it will be closed immediately

    mysql_free_result($result);
  }
  else {
    mysql_free_result($result);
    die('map.setView([51.513, 7.46], 9);</script>Nothing found.</body></html>');
  }

  mysql_close();
?>

// -->
</script>

</body></html>
