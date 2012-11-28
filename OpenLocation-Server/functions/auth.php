<?php
/* validate token for remote api access */
function validateUserByToken($user, $token) {

  if (strlen($token) != 32) die403("Token malformed");

  if (strrpos($user, "@")) {
    $local = substr($user, 0, strrpos($user, "@"));
    $domain = substr($user, strrpos($user, "@")+1);
  }
    else {
    $local = $user;
    $domain = $_SERVER['HTTP_HOST'];
  }

  connectToMySQL();

  /* Check if authorized */
  $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($local) . "' AND token LIKE '%" . mysql_real_escape_string($token) . "%';";
  $result = mysql_query($query) or die500("MySQL Error (SELECT *): " . mysql_error());
  if (mysql_num_rows($result) != 1) {
    mysql_free_result($result);
    mysql_close();
    writetolog("Error (validateUserByToken()): Not Authorized: " . mysql_real_escape_string($user));
    die403("Error (validateUserByToken()): Not Authorized: " . mysql_real_escape_string($user));
  }
  mysql_free_result($result);

  mysql_close();

  return array($local, $domain);
}


function validateUserDB() {
  // TODO: move code from functions/http.php validateUser() here
}


/* Generates random string */
function newtoken() {
  if(function_exists('openssl_random_pseudo_bytes')) return bin2hex(openssl_random_pseudo_bytes(16, $cstrong));
  else {
    $alphabet = "0123456789abcdef";
    $token = "";
    for ($i=0; i<32; $i++) $token .= $alphabet[mt_rand(0, strlen($alphabet)-1)];
    return $token;
  }
}


/* get own token from database */
function getowntoken($user) {
  connectToMySQL();

  $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($user) . "';";
  $result = mysql_query($query) or die500("MySQL Error (SELECT *): " . mysql_error());
  if (mysql_num_rows($result) == 1) {
    $row = mysql_fetch_array($result);
    $tokens = explode(",", $row['token']);
    mysql_free_result($result);
    mysql_close();
    return $tokens[0];
  }
  else die500("Could not find own token");
  mysql_free_result($result);
  mysql_close();
}


/* get friend token from database */
/* returns last valid token or false if nothing found */
function getfriendtoken($user, $friend) {
  connectToMySQL();
  $token = false;
  $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($user) . "';";
  $result = mysql_query($query) or die500("MySQL Error (SELECT *): " . mysql_error());
  if (mysql_num_rows($result) == 1) {
    $row = mysql_fetch_array($result);
    $friendsandtokens = explode(",", $row['friends']);
    foreach ($friendsandtokens as $friendandtoken) {
      if (!empty($friendandtoken) && strpos($friendandtoken, $friend) === 0) {
        //if (strrpos($friend, "-")) $token = substr($friend, strrpos($friend, "-")+1);
        if (strrpos($friendandtoken, ":")) $token = substr($friendandtoken, strrpos($friendandtoken, ":")+1);
      }
    }
    mysql_free_result($result);
    mysql_close();
    return $token;
  }
  else die500("Could not find friend token");
  mysql_free_result($result);
  mysql_close();
}


function sendrequestfriend($user_local, $target) {
  list ($target_local, $target_domain, $target_fullusername) = explode_username($target);

  /* clear all previous tokens 'authorized' (going to generate new one) */
  removeToken($user_local, $target_fullusername, 'authorized', ':');
  removeToken($user_local, $target_fullusername, 'authorized', '-');

  /* generate new token authorizing target */
  $newtoken = newtoken();

  /* Send requestfriend to $target */
  $url = "http://" . $target_domain . "/api.php";
  $req_json = json_encode(array("request" => "requestfriend", "sender" => $user_local . "@" . $_SERVER['HTTP_HOST'], "target" => $target_fullusername, "token" => $newtoken));

  $http_result = doBlockingHttpJsonRequest($url, $req_json);
  $result = json_decode($http_result);

  if ($result != null && $result->{'request'} == "requestfriend" && $result->{'error'} == "0") {
    /* store new token $target 'authorized' */
    storeToken($user_local, $target_fullusername, $newtoken, 'authorized');

    /* if pending friends, mark tokens valid */
    if (isKnown($user_local, $target_fullusername, 'friends', '-')) {
      markTokenValid($user_local, $target_fullusername, 'authorized');
      markTokenValid($user_local, $target_fullusername, 'friends');
    }
    return 0;
  }
  elseif ($result != null && $result->{'request'} == "requestfriend" && $result->{'error'} == "User does not exist") {
    return -1;
  }
  elseif ($result != null && $result->{'request'} == "requestfriend" && $result->{'error'} == "Is already a friend") {
    return -2;
  }
  else die ('{"request":"sendrequestfriend", "error":"sendrequestfriend(): requestfriend returned bad answer: ' . htmlspecialchars($http_result) . '"}');
}
?>
