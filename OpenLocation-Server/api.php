<?php
if (isset($DEBUG) && strcmp($DEBUG, "yesdoit") == 0) {
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
}

require_once('functions.php');

/* Sanity checks */
if ($_SERVER['REQUEST_METHOD'] !== "POST") die400('POST expected!');
if (empty($_POST["json"])) die('{"request":"", "error":"API: No JSON found"}');

/* Decode JSON */
$json = json_decode(gzinflate(base64_decode($_POST["json"]))) or die('{"request":"", "error":"API: Error decoding JSON"}');
if ($json == NULL || empty($json)) {
  writetolog("Error: API: got Bad JSON: " . gzinflate(base64_decode($_POST["json"])));
  die('{"request":"", "error":"API: Bad JSON received"}');
}
$request = $json->{'request'};


/* Handle request "setlocation" */
if (strcmp($request, 'setlocation') == 0) {

  require("api/setlocation.php");

}


/* Handle request "getlocation" */
elseif (strcmp($request, 'getlocation') == 0) {

  require("api/getlocation.php");

}


/* Handle request "getfriends" */
elseif (strcmp($request, 'getfriends') == 0) {

  require("api/getfriends.php");

}


/* Handle request "requestfriend" */
elseif (strcmp($request, 'requestfriend') == 0) {

  require("api/requestfriend.php");

}


/* Handle request "sendrequestfriend" */
elseif (strcmp($request, 'sendrequestfriend') == 0) {

  require("api/sendrequestfriend.php");

}


/* Handle request "removefriend" */
elseif (strcmp($request, 'removefriend') == 0) {

  require("api/removefriend.php");

}


/* Handle request "deletelocation" */
elseif (strcmp($request, 'deletelocation') == 0) {

  require("api/deletelocation.php");

}


/* Could not handle $request */
else {
  die('{"request":"", "error":"API: ' . $request . ' not implemented"}');
}

?>
