<?php
if (isset($DEBUG) && strcmp($DEBUG, "yesdoit") == 0) {
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
}

require_once('functions.php');

/* HttpAuth */
list ($user, $domain) = validateUser();

makeHtmlHeader("Friends");

/* Get own token */
$token = getowntoken($user);

/* Request removefriend */
if (isset($_POST["removefriend"])) {
  if (!validEmail($_POST["removefriend"])) die400("Malformed Request");

  list ($target_local, $target_domain, $target_fullusername) = explode_username($target);

  $url = "http://" . $target_domain . "/api.php";
  $req_json = json_encode(array("request" => "removefriend", "sender" => $user, "token" => $token, "target" => $_POST["removefriend"]));

  $http_result = doBlockingHttpJsonRequest($url, $req_json);
  $result = json_decode($http_result);

  if ($result != null && $result->{'request'} == "removefriend" && $result->{'error'} == "0") {
    echo "<span style=\"color:#00C000\">Successfully removed user &quot;" . htmlspecialchars($target_local) . "&quot</span>\n";
  }
  else die ('Bad Answer: ' . $http_result);
}


/* Request requestfriend */
if (isset($_POST["requestfriend"])) {
  if (empty($_POST["requestfriend"])) die400("Malformed Request");

  list ($target_local, $target_domain, $target_fullusername) = explode_username($_POST["requestfriend"]);

  sendrequestfriend($user, $target_fullusername);
}


/* Request grantfriend */
if (isset($_POST["grantfriend"])) {
  if (!validEmail($_POST["grantfriend"])) die400("Malformed Request");
  if (strrpos($_POST["grantfriend"], "@")) {
    $local = substr($_POST["grantfriend"], 0, strrpos($_POST["grantfriend"], "@"));
    $remotedomain = substr($_POST["grantfriend"], strrpos($_POST["grantfriend"], "@")+1);
  }
    else {
    $local = $_POST["grantfriend"];
    $remotedomain = $_SERVER['HTTP_HOST'];
  }

  $url = "http://" . $domain . "/api.php";
  $req_json = json_encode(array("request" => "grantfriend", "sender" => $user, "token" => $token, "target" => $_POST["grantfriend"]));

  $http_result = doBlockingHttpJsonRequest($url, $req_json);
  $result = json_decode($http_result);

  if ($result != null && $result->{'request'} == "grantfriend" && $result->{'error'} == "0") {
    echo "<span style=\"color:#00C000\">Successfully granted friendship to user &quot;" . htmlspecialchars($local) . "&quot</span>\n";
  }
  else die ('Bad Answer: ' . $http_result);
}




/* Request list of friends */
$url = "http://" . $domain . "/api.php";
$req_json = json_encode(array("request" => "getfriends", "sender" => $user, "token" => $token));

$http_result = doBlockingHttpJsonRequest($url, $req_json);
$result = json_decode($http_result);

if ($result == null || empty($result)) die ('Bad json: ' . $http_result);

if (isset($result->{'data'})) $rows = $result->{'data'};
else $rows = null;

echo "<h3>Friends:</h3>\n";
if (count($rows) > 0) {
  for ($i=0; $i<count($rows); $i++) {
    echo "<p>" . $rows[$i] . " <form action=\"\" method=\"POST\"><input type=\"hidden\" name=\"removefriend\" value=\"".$rows[$i]."\" /><input type=\"submit\" value=\"DELETE\" /></form></p>\n";
  }
}
else echo "<p>none</p>\n";

if (isset($result->{'pending'})) $rows = $result->{'pending'};
else $rows = null;

if (count($rows) > 0) {
  echo "<h4>Pending:</h4>\n";
  for ($i=0; $i<count($rows); $i++) {
    echo "<p>" . $rows[$i] . " <form action=\"\" method=\"POST\"><input type=\"hidden\" name=\"removefriend\" value=\"".$rows[$i]."\" /><input type=\"submit\" value=\"DELETE\" /></form></p>\n";
  }
}

if (isset($result->{'incoming'})) $rows = $result->{'incoming'};
else $rows = null;

if (count($rows) > 0) {
  echo "<h4>Incoming Requests:</h4>\n";
  for ($i=0; $i<count($rows); $i++) {
    echo "<p>" . $rows[$i] . " <form action=\"\" method=\"POST\"><input type=\"hidden\" name=\"grantfriend\" value=\"".$rows[$i]."\" /><input type=\"submit\" value=\"ACCEPT\" /></form> <form action=\"\" method=\"POST\"><input type=\"hidden\" name=\"removefriend\" value=\"".$rows[$i]."\" /><input type=\"submit\" value=\"DELETE\" /></form></p>\n";
  }
}

echo "<form action=\"\" method=\"POST\">\n";
echo "<p>Request friendship with: <input type=\"text\" name=\"requestfriend\" /><input type=\"submit\" /></p>\n";
echo "</form>\n";


makeHtmlFooter();
?>
