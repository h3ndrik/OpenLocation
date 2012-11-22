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
  writetolog("Error: API: got Bad JSON: " . gzinflate(base64_decode($_POST["json"])));
  die500('API: got Bad JSON');
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


/* Handle request "newfriend" */
elseif (strcmp($request, 'newfriend') == 0) {

  require("api/newfriend.php");

}


/* Handle request "removefriend" */
elseif (strcmp($request, 'removefriend') == 0) {

  require("api/removefriend.php");

}


/* Could not handle $request */
else {
  die500('Error or not implemented');
}

?>
