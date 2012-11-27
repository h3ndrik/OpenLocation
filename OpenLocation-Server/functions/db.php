<?php
function connectToMySQL() {
  (require_once('config.php')) or die500("Please edit config.php.sample and save as config.php");
  mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die500("Unable to connect to MySQL");
  mysql_select_db(DB_NAME) or die500("Unable to select database");
}


function removeToken($user, $target, $column, $delimiter) {
  connectToMySQL();
  $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($user) . "' AND " . $column . " LIKE '%" . mysql_real_escape_string($target) . $delimiter . "%';";
  $result = mysql_query($query) or die500("MySQL Error (SELECT *): " . mysql_error());
  if (mysql_num_rows($result) == 1) {
    $data = array();
    $row = mysql_fetch_array($result);
    $usersandtokens = explode(",", $row[$column]);
    foreach ($usersandtokens as $singleuserandtoken) {
      if (strpos($singleuserandtoken, $target . $delimiter) === 0) {
        $query = "UPDATE users SET " . $column . " = REPLACE(" . $column . ", '" . $singleuserandtoken . ",', '') WHERE username = '" . mysql_real_escape_string($user) . "';";
        $result2 = mysql_query($query) or die500("MySQL Error (UPDATE): " . mysql_error());
      }
    }
  }
  else {
    mysql_free_result($result);
    mysql_close();
    return false;
  }
  mysql_free_result($result);
  mysql_close();
  return true;
}


function storeToken($user, $target, $token, $column) {
  connectToMySQL();
  $query = "UPDATE users SET " . $column . " = concat(" . $column . ", '" . mysql_real_escape_string($target) . "-" . $token . ",') WHERE username = '" . mysql_real_escape_string($user) . "';";
  $result = mysql_query($query) or die500("MySQL Error (UPDATE): " . mysql_error());
  if (mysql_affected_rows() != 1) {
    mysql_close();
    writetolog("Error: User not found: " . $user);
    die400("Error: User not found: " . $user);
  }
  mysql_close();
}


function markTokenValid($user, $target, $column) {
  connectToMySQL();
  $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($user) . "' AND " . $column . " LIKE '%" . mysql_real_escape_string($target) . "%';";
  $result = mysql_query($query) or die500("MySQL Error (SELECT *): " . mysql_error());
  if (mysql_num_rows($result) == 1) {
    $row = mysql_fetch_array($result);
    $usersandtokens = explode(",", $row[$column]);
    foreach ($usersandtokens as $singleuserandtoken) {  // TODO: there should be only one token, invalidate all previous!!!
      if (strpos($singleuserandtoken, $target) === 0) {
        $updateduserandtoken = $singleuserandtoken;
        $updateduserandtoken[strlen($updateduserandtoken)-33] = ":";  // TODO: take fixed char (token is 32 char long)
        $query = "UPDATE users SET " . $column . " = REPLACE(" . $column . ", '" . $singleuserandtoken . "', '" . $updateduserandtoken . "') WHERE username = '" . mysql_real_escape_string($user) . "';";
        $result2 = mysql_query($query) or die500("MySQL Error (UPDATE): " . mysql_error());
      }
    }
  }
  else {
    // Could not find $target in $column of user $user
    mysql_free_result($result);
    mysql_close();
    return false;
  }
  mysql_free_result($result);
  mysql_close();
  return true;
}


function isKnown($user, $target, $column, $delimiter) {
  connectToMySQL();
  $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($user) . "' AND " . $column . " LIKE '%" . mysql_real_escape_string($target) . $delimiter . "%';";
  $result = mysql_query($query) or die500("MySQL Error (SELECT *): " . mysql_error());
  if (mysql_num_rows($result) == 1) {
    // is known
    mysql_free_result($result);
    mysql_close();
    return true;
  }
  else {
    // Could not find $target in $column of user $user
    mysql_free_result($result);
    mysql_close();
    return false;
  }
}


function isUser($user) {
  connectToMySQL();
  $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($user) . "';";
  $result = mysql_query($query) or die500("MySQL Error (SELECT *): " . mysql_error());
  if (mysql_num_rows($result) == 1) {
    // is known
    mysql_free_result($result);
    mysql_close();
    return true;
  }
  else {
    // not known
    mysql_free_result($result);
    mysql_close();
    return false;
  }
}


/* get friends */
/* Usage: list ($friends, $friends_pending, $friends_incoming) = getfriends($user); */
function getfriends($user_local) {
  connectToMySQL();
  $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($user_local) . "';";
  $result = mysql_query($query) or die500("MySQL Error (SELECT *): " . mysql_error());
  if (mysql_num_rows($result) == 1) {
    $data = array();
    $data_pending = array();
    $data_incoming = array();
    $row = mysql_fetch_array($result);
    $friendsandtokens = explode(",", $row['friends']);
    $i = 0; $j = 0;
    foreach ($friendsandtokens as $friend) {
      if (!empty($friend) && strrpos($friend, ":")) {
        $data[$i] = substr($friend, 0, strrpos($friend, ":"));
        $i++;
      }
      elseif (!empty($friend) && strrpos($friend, "-")) {
        $data_incoming[$j] = substr($friend, 0, strrpos($friend, "-"));
        $j++;
      }
    }
    $incomingandtokens = explode(",", $row['authorized']);
    $k = 0;
    foreach ($incomingandtokens as $friend) {
      if (!empty($friend) && strrpos($friend, "-")) {
        $data_pending[$k] = substr($friend, 0, strrpos($friend, "-"));
        $k++;
      }
    }
  }
  else {
    mysql_free_result($result);
    mysql_close();
    die500("MySQL Error (SELECT): " . mysql_error());
  }
  mysql_free_result($result);

  mysql_close();
  return array($data, $data_pending, $data_incoming);
}


?>
