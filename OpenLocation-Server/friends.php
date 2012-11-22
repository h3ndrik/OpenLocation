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

  list ($target_local, $target_domain, $target_fullusername) = explode_username($_POST["removefriend"]);

  $url = "http://" . $domain . "/api.php";
  $req_json = json_encode(array("request" => "removefriend", "sender" => $user, "token" => $token, "target" => $target_fullusername));

  $http_result = doBlockingHttpJsonRequest($url, $req_json);
  $result = json_decode($http_result);

  if ($result != null && $result->{'request'} == "removefriend" && $result->{'error'}[0] === "0") {
    echo "<span style=\"color:#00C000\">Successfully removed user &quot;" . htmlspecialchars($target_local) . "&quot</span>\n";
  }
  else die ('Bad Answer: ' . $http_result);
}


/* Request local requestfriend */
if (isset($_POST["localrequestfriend"])) {
  if (empty($_POST["localrequestfriend"])) die400("Malformed Request");

  list ($target_local, $target_domain, $target_fullusername) = explode_username($_POST["localrequestfriend"]);

  sendrequestfriend($user, $target_fullusername);
}


/* Request sendrequstfriend */
if (isset($_POST["sendrequestfriend"])) {
  list ($target_local, $target_domain, $target_fullusername) = explode_username($_POST["sendrequestfriend"]);

  $url = "http://" . $domain . "/api.php";
  $req_json = json_encode(array("request" => "sendrequestfriend", "sender" => $user . "@" . $domain, "token" => $token, "target" => $target_fullusername));

  $http_result = doBlockingHttpJsonRequest($url, $req_json);
  $result = json_decode($http_result);

  if ($result != null && $result->{'request'} == "sendrequestfriend" && $result->{'error'} == "0") {
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
    echo "<p><form action=\"\" method=\"POST\" style=\"display:inline;\"><a href=\"/?friend=" . $rows[$i] . "\">" . $rows[$i] . "</a> <input type=\"hidden\" name=\"removefriend\" value=\"".$rows[$i]."\" /><input type=\"submit\" value=\"DELETE\" /></form></p>\n";
  }
}
else echo "<p>none</p>\n";

if (isset($result->{'pending'})) $rows = $result->{'pending'};
else $rows = null;

if (count($rows) > 0) {
  echo "<h4>Pending:</h4>\n";
  for ($i=0; $i<count($rows); $i++) {
    echo "<p><form action=\"\" method=\"POST\" style=\"display:inline;\">" . $rows[$i] . " <input type=\"hidden\" name=\"removefriend\" value=\"".$rows[$i]."\" /><input type=\"submit\" value=\"DELETE\" /></form></p>\n";
  }
}

if (isset($result->{'incoming'})) $rows = $result->{'incoming'};
else $rows = null;

if (count($rows) > 0) {
  echo "<h4>Incoming Requests:</h4>\n";
  for ($i=0; $i<count($rows); $i++) {
    echo "<p><form action=\"\" method=\"POST\" style=\"display:inline;\">" . $rows[$i] . " <input type=\"hidden\" name=\"localrequestfriend\" value=\"".$rows[$i]."\" /><input type=\"submit\" value=\"ACCEPT\" /></form><form action=\"\" method=\"POST\" style=\"display:inline;\"><input type=\"hidden\" name=\"removefriend\" value=\"".$rows[$i]."\" /><input type=\"submit\" value=\"DELETE\" /></form></p>\n";
  }
}

echo "<form action=\"\" method=\"POST\">\n";
echo "<p>Request friendship with: <input type=\"text\" name=\"sendrequestfriend\" /><input type=\"submit\" /></p>\n";
echo "</form>\n";


makeHtmlFooter();
?>
