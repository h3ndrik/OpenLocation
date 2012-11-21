<?php
function connectToMySQL() {
  (require_once('config.php')) or die500("Please edit config.php.sample and save as config.php");
  mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die500("Unable to connect to MySQL");
  mysql_select_db(DB_NAME) or die500("Unable to select database");
}


function removeUserAndToken($sender, $target, $column) {
  $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($sender) . "' AND " . $column . " LIKE '%" . mysql_real_escape_string($target) . "%';";
  $result = mysql_query($query) or die500("MySQL Error (SELECT *): " . mysql_error());
  if (mysql_num_rows($result) == 1) {
    $data = array();
    $row = mysql_fetch_array($result);
    $usersandtokens = explode(",", $row[$column]);
    foreach ($usersandtokens as $singleuserandtoken) {
      if (strpos($singleuserandtoken, $target) === 0) {
        $query = "UPDATE users SET " . $column . " = REPLACE(" . $column . ", '" . $singleuserandtoken . ",', '') WHERE username = '" . mysql_real_escape_string($sender) . "';";
        $result = mysql_query($query) or die500("MySQL Error (UPDATE): " . mysql_error());
      }
    }
  }
  else {
    mysql_free_result($result);
    return false;
  }
  mysql_free_result($result);
  return true;  
}


function storeUserAndToken($sender, $target, $delimiter, $token, $column) {
  $query = "UPDATE users SET " . $column . " = concat(" . $column . ", '" . mysql_real_escape_string($target) . $delimiter . $token . ",') WHERE username = '" . mysql_real_escape_string($sender) . "';";
  $result = mysql_query($query) or die500("MySQL Error (UPDATE): " . mysql_error());
  if (mysql_affected_rows() != 1) {
    mysql_close();
    writetolog("Error: User not found: " . $sender);
    die400("Error: User not found: " . $sender);
  }
}


?>
