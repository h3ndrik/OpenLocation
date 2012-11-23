<?php
if (isset($DEBUG) && strcmp($DEBUG, "yesdoit") == 0) {
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
}

require_once('functions.php');

/* HttpAuth */
list ($user, $domain) = validateUser();

/* Get own token */
$token = getowntoken($user);

/* Request deletelocation */
if (isset($_POST["deletelocation"])) {
  if (empty($_POST["deletelocation"])) die400("Malformed Request");

  $url = "http://" . $domain . "/api.php";
  $req_json = json_encode(array("request" => "deletelocation", "sender" => $user, "token" => $token, "interval" => $_POST["deletelocation"]));

  $http_result = doBlockingHttpJsonRequest($url, $req_json);
  $result = json_decode($http_result);

  if ($result != null && $result->{'request'} == "deletelocation" && $result->{'error'}[0] === "0") {
    echo "<span style=\"color:#00C000\">Successfully deleted locations in interval: &quot;" . htmlspecialchars($_POST["deletelocation"]) . "&quot</span>\n";
  }
  else die ('Bad Answer: ' . $http_result);
}

/* Request deleteuser */
if (isset($_POST["deleteuser"])) {

  $url = "http://" . $domain . "/api.php";
  $req_json = json_encode(array("request" => "deleteuser", "sender" => $user, "token" => $token));

  $http_result = doBlockingHttpJsonRequest($url, $req_json);
  $result = json_decode($http_result);

  if ($result != null && $result->{'request'} == "deleteuser" && $result->{'error'} == "0") {
    echo "<span style=\"color:#00C000\">Successfully deleted user</span>\n";
  }
  else die ('Bad Answer: ' . $http_result);
}









/* Now the page */
makeHtmlHeader("Status");

echo "<a href=\"/\"><center><h1>OpenLocation - " . $user . "</h1></center></a>";
echo "<hr />";

//echo "<form action=\"\" method=\"POST\">\n";
//echo "<p>Delete locations interval: <input type=\"text\" name=\"deletelocation\" /><input type=\"submit\" /></p>\n";
//echo "</form>\n";

$now = time()*1000;
$quarter = $now - 15*60*1000;
$half = $now - 30*60*1000;
$hour = $now - 1*60*60*1000;
$two = $now - 2*60*60*1000;
$four = $now - 4*60*60*1000;
$eight = $now - 8*60*60*1000;
$day = $now - 1*24*60*60*1000;
$week = $now - 7*24*60*60*1000;
echo "<p>\n";
echo "<form action=\"\" method=\"POST\" style=\"display:inline;\">Delete last: ";
echo "<input type=\"hidden\" name=\"deletelocation\" value=\"" . $quarter . "-" . $now . "\" />";
echo "<input type=\"submit\" value=\"15min\" />";
echo "</form>\n";

echo "<form action=\"\" method=\"POST\" style=\"display:inline;\">";
echo "<input type=\"hidden\" name=\"deletelocation\" value=\"" . $half . "-" . $now . "\" />";
echo "<input type=\"submit\" value=\"30min\" />";
echo "</form>\n";

echo "<form action=\"\" method=\"POST\" style=\"display:inline;\">";
echo "<input type=\"hidden\" name=\"deletelocation\" value=\"" . $hour . "-" . $now . "\" />";
echo "<input type=\"submit\" value=\"1h\" />";
echo "</form>\n";

echo "<form action=\"\" method=\"POST\" style=\"display:inline;\">";
echo "<input type=\"hidden\" name=\"deletelocation\" value=\"" . $two . "-" . $now . "\" />";
echo "<input type=\"submit\" value=\"2h\" />";
echo "</form>\n";

echo "<form action=\"\" method=\"POST\" style=\"display:inline;\">";
echo "<input type=\"hidden\" name=\"deletelocation\" value=\"" . $four . "-" . $now . "\" />";
echo "<input type=\"submit\" value=\"4h\" />";
echo "</form>\n";

echo "<form action=\"\" method=\"POST\" style=\"display:inline;\">";
echo "<input type=\"hidden\" name=\"deletelocation\" value=\"" . $eight . "-" . $now . "\" />";
echo "<input type=\"submit\" value=\"8h\" />";
echo "</form>\n";

echo "<form action=\"\" method=\"POST\" style=\"display:inline;\">";
echo "<input type=\"hidden\" name=\"deletelocation\" value=\"" . $day . "-" . $now . "\" />";
echo "<input type=\"submit\" value=\"1d\" />";
echo "</form>\n";

echo "<form action=\"\" method=\"POST\" style=\"display:inline;\">";
echo "<input type=\"hidden\" name=\"deletelocation\" value=\"" . $week . "-" . $now . "\" />";
echo "<input type=\"submit\" value=\"7d\" />";
echo "</form>\n";

echo "<form action=\"\" method=\"POST\" style=\"display:inline;\">";
echo "<input type=\"hidden\" name=\"deletelocation\" value=\"0-" . $now . "\" />";
echo "<input type=\"submit\" value=\"ALL\" />";
echo "</form>\n";

echo "</p>";

echo "<hr />\n";

echo "<form action=\"\" method=\"POST\">\n";
echo "<p>Delete user <input type=\"hidden\" name=\"deleteuser\" value=\"true\" /><input type=\"submit\" /></p>\n";
echo "</form>\n";

echo "<hr />\n";

echo "<div align=\"right\"><a href=\"/\">back</a></div>\n";

makeHtmlFooter();
?>
