<?php
function makeHtmlHeader($title) {
  echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\">\n";
  echo "<html><head><title>OpenLocation - $title</title>\n";
  echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no\" />\n";
  echo "</head><body>\n\n";
}

function makeHtmlFooter() {
  echo "\n</body></html>";
}


/* send http status(/error) codes */
function send400Header() {
    header('HTTP/1.1 400 Bad Request');
}
function die400($error) {
    header('HTTP/1.1 400 Bad Request');
    die($error);
}
function send401Header($realm) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="' . $realm .
           '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) .
           '"');
}
function send403Header() {
    header('HTTP/1.1 403 Forbidden');
}
function die403($error) {
    header('HTTP/1.1 403 Forbidden');
    die($error);
}
function send500Header() {
    header('HTTP/1.1 500 Internal Server Error');
}
function die500($error) {
    header('HTTP/1.1 500 Internal Server Error');
    die($error);
}


/* Send JSON, Receive JSON */
function doBlockingHttpJsonRequest($url, $req_json) {
    // Create map with request parameters
    $params = array("json" => base64_encode(gzdeflate($req_json)));

    // Build Http query using params
    $query = http_build_query($params);

    // Create Http context details
    $contextData = array (
                'method' => 'POST',
                'header' => "Connection: close\r\n".
                            "Content-Length: ".strlen($query)."\r\n".
                            "Content-Type: application/x-www-form-urlencoded",
                'content' => $query );
 
    // Create context resource for our request
    $context = stream_context_create (array ( 'http' => $contextData ));
 
    // Read page rendered as result of your POST request
    $http_result =  file_get_contents (
                  $url,  // page url
                  false,
                  $context);
 
    // Server response is now stored in $http_result variable so you can process it
    //die("Success, got: " . $http_result);

    $result = gzinflate(base64_decode($http_result));
    if ($result != FALSE) return $result;
    else return $http_result;
}


/* Helper to analyze HTTP-Auth-Header */
function http_digest_parse($txt) {
    // gegen fehlende Daten schützen
    $noetige_teile = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1,
                           'username'=>1, 'uri'=>1, 'response'=>1);
    $daten = array();
    $schluessel = implode('|', array_keys($noetige_teile));

    preg_match_all('@(' . $schluessel . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@',
                   $txt, $treffer, PREG_SET_ORDER);

    foreach ($treffer as $t) {
        $daten[$t[1]] = $t[3] ? $t[3] : $t[4];
        unset($noetige_teile[$t[1]]);
    }

    return $noetige_teile ? false : $daten;
}


/* Rubbish */
/*function http_digest_construct($username) {
  $realm = 'OpenLocation';
  

  header("Authorization: Digest username=\"$username\", realm=\"$realm\", nonce=\"$nonce\", uri=\"$uri\", qop=\"$qop\", nc=\"$nc\", cnonce=\"$cnonce\", response=\"$response\", opaque=\"$opaque\"");
}*/


/* HTTP Auth Digest validate user */
function validateUser() {
  (require_once('config.php')) or die("Please edit config.php.sample and save as config.php");  // Die Klammern sind schon richtig so

$realm = 'OpenLocation';

if (empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    send401Header($realm);
    echo '<p><a href="register.php">Register new user</a>' . "</p>\n";
    echo '<p><a href="http://' . $_SERVER['HTTP_HOST'] . '/?logout">Retry</a></p>';
    die('Abgebrochen. Nicht Autorisiert!'); // Text, der gesendet wird, falls der Benutzer auf Abbrechen drückt
}

// Force (re)auth (retry) TODO: do it right, http auth is fucked up beyond repair
if (isset($_GET["logout"])) {
    $realm = 'Openlocation_Logout';
    send401Header($realm);
    header('Location: http://' . $_SERVER['HTTP_HOST'] . '/');
    echo '<p><a href="register.php">Register new user</a>' . "</p>\n";
    echo '<p><a href="http://' . $_SERVER['HTTP_HOST'] . '/?logout">Retry</a></p>';
    die('Logged out. (Not yet implemented!)');
}

// Analysieren der Variable PHP_AUTH_DIGEST
if (!($daten = http_digest_parse($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))) {
  send401Header($realm);
  echo '<p><a href="http://' . $_SERVER['HTTP_HOST'] . '/?logout">Retry</a></p>';
  die('Falsche Zugangsdaten!');
}

if (strrpos($daten["username"], "@")) {
  $local = substr($daten["username"], 0, strrpos($daten["username"], "@"));
  $domain = substr($daten["username"], strrpos($daten["username"], "@")+1);
}
  else {
  $local = $daten["username"];
  $domain = $_SERVER['HTTP_HOST'];
}

// Überprüfen ob richtiger host
if (strrpos($daten["username"], "@") && strcmp($domain , $_SERVER['HTTP_HOST']) != 0) {
  send403Header();
  echo '<span style="color:#FF0000">Wrong Host! Expected: ...@' . $_SERVER['HTTP_HOST'] . '</span><br />Please register at <a href="http://' . $domain . '">http://' . $domain . '</a>.<hr />';
  echo '<p><a href="http://' . $_SERVER['HTTP_HOST'] . '/?logout">Retry</a></p>';
  writetolog("Error: username contains different host");
  die("Aborting");
}

if (strlen($local) < 1) {
  send401Header($realm);
  echo '<p><a href="register.php">Register new user</a>' . "</p>\n";
  echo '<p><a href="http://' . $_SERVER['HTTP_HOST'] . '/?logout">Retry</a></p>';
  writetolog("Username wrong format");
  die ("Username wrong format.");
}
connectToMySQL();
$query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($local) . "';";
$result = mysql_query($query) or die500("MySQL Error (SELECT *): " . mysql_error());

if (mysql_num_rows($result) == 1) {
  $row = mysql_fetch_object($result);
  // Benutzer => Passwort
  $benutzer = array(mysql_real_escape_string($daten['username']) => $row->password);
  mysql_free_result($result);
  mysql_close();
}
else {
  mysql_free_result($result);
  mysql_close();
  send401Header($realm);
  echo '<p><a href="http://' . $_SERVER['HTTP_HOST'] . '/?logout">Retry</a></p>';
  writetolog("Error: User \"" . $local . "\" not found.");
  die('User &quot;' . $local . '&quot; not found.');
}
//    !isset($benutzer[$daten['username']]))

// Erzeugen einer gültigen Antwort
$A1 = md5($daten['username'] . ':' . $realm . ':' .
          $benutzer[$daten['username']]);
$A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $daten['uri']);
$gueltige_antwort = md5($A1 . ':' . $daten['nonce'] . ':' . $daten['nc'] .
                        ':' . $daten['cnonce'] . ':' . $daten['qop'] . ':' .
                        $A2);

if ($daten['response'] != $gueltige_antwort)  {
  send401Header($realm);
  echo '<p><a href="http://' . $_SERVER['HTTP_HOST'] . '/?logout">Retry</a></p>';
  writetolog("Error: Falsche Zugangsdaten");
  die('Falsche Zugangsdaten!');
}

// OK, gültige Benutzername & Passwort
//echo 'Sie sind angemeldet als: ' . $daten['username'];
//$ret_user = $local;
//$ret_domain = $domain;
return array($local, $domain);
}


?>
