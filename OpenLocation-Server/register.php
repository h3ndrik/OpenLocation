<?php
require_once('functions.php');


function displayForm() {
  echo "<h2>Register new user</h2>\n";
  echo "<form action=\"register.php\" method=\"POST\">\n";
  echo "<p>Username: <span class=\"function\"><input type=\"text\" name=\"username\" /></span></p>\n";
  echo "<p>Password: <span class=\"function\"><input type=\"password\" name=\"password\" /></span></p>\n";
  echo "<p><span class=\"function\"><input type=\"submit\" /></span>&nbsp;</p>\n";
  echo "</form>\n\n";
}

makeHtmlHeader($_SERVER['HTTP_HOST']);
echo "<h2><strong>OpenLocation - Register new user on " . $_SERVER['HTTP_HOST'] . "</strong></h2>\n\n";
echo "<p><div class=\"warningbox\">WARNING: This is alpha software. Encryption is not implemented (yet) and there may be major bugs. Your data is not safe in any way. Use it at your own risk!</div></p>";

if (defined('DEBUG') && strcmp(DEBUG, "yesdoit") === 0) {
echo "<p><div class=\"warningbox\">WARNING: Debug mode enabled on " . $_SERVER['HTTP_HOST'] . ".</div></p>";
}


if ($_SERVER['REQUEST_METHOD'] === "POST") {

  // "@host" hinzufügen, falls fehlend
  if (!strrpos($_POST["username"], "@") && strlen($_POST["username"]) >= 2) {
    $_POST["username"] = $_POST["username"] . '@' . $_SERVER['HTTP_HOST'];
  }

  if (!validEmail($_POST["username"])) {
    echo "<div class=\"errorbox\">Username must have form of: &quot;user@domain.com&quot;!</div>\n";
    displayForm();
  } 
  else if (strcmp($domain = substr($_POST["username"], strrpos($_POST["username"], "@")+1), $_SERVER['HTTP_HOST']) != 0) {
    echo "<div class=\"errorbox\">Wrong Host! Expected: ...@" . $_SERVER['HTTP_HOST'] . "</div>\nPlease register at <a href=\"http://" . $domain . "\">http://" . $domain . "</a>.\n";
    displayForm();
  }
  else if ((strlen($_POST["password"]) < 3) || (strlen($_POST["username"]) > 32) || $_POST["username"] == "users") {
    echo "<div class=\"errorbox\">Password not acceptable!</div>";
    displayForm();
  }
  else {
    connectToMySQL();
    $query = "CREATE TABLE IF NOT EXISTS users(username varchar(255), password varchar(32), password_fullusername varchar(32), token TEXT, friends TEXT, authorized TEXT, PRIMARY KEY (username));";
    $result = mysql_query($query) or die500("Unable to create table: " . mysql_error());
    $local = substr($_POST["username"], 0, strrpos($_POST["username"], "@"));
    $realm = 'OpenLocation';
    $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($local) . "';";
    $result = mysql_query($query) or die500("MySQL Error (SELECT *): " . mysql_error());
    if (mysql_num_rows($result) == 0) {
      mysql_free_result($result);
      $password = md5(mysql_real_escape_string($local) . ':' . $realm . ':' . mysql_real_escape_string($_POST['password']));
      $password_fullusername = md5(mysql_real_escape_string($local) . '@' . $_SERVER['HTTP_HOST'] . ':' . $realm . ':' . mysql_real_escape_string($_POST['password']));
      $query = "INSERT INTO users VALUES('" . mysql_real_escape_string($local) . "', '" . mysql_real_escape_string($password) . "', '" . mysql_real_escape_string($password_fullusername) . "', '" . newtoken() . "', '', '');";
      $result = mysql_query($query) or die500("MySQL Error (INSERT): " . mysql_error());
      // TODO: remove table if exists
      $query = "CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string($local) . "`(time BIGINT, latitude DOUBLE, longitude DOUBLE, altitude DOUBLE, accuracy FLOAT, speed FLOAT, bearing FLOAT, provider varchar(16), ip char(16), version INT, PRIMARY KEY (time));";
      $result = mysql_query($query) or die500("Unable to create table: " . mysql_error());
      echo "<div class=\"successbox\">Successfully created user &quot;" . htmlspecialchars($local) . "@" . $_SERVER['HTTP_HOST'] . "&quot;</div>";
      echo '<p>Please remember to log in with full name.</p>';
      echo '<script type="text/javascript">';
      //echo '<!--';
      echo 'setTimeout("window.location.href=\'http://' . $_SERVER['HTTP_HOST'] . '/index.php\';",5000);';
      //echo ' // -->';
      echo '</script>';
    }
    else {
      mysql_free_result($result);
      echo "<div class=\"errorbox\">Error: Username exists.</div>\n";
      displayForm();
    }
    mysql_close();
  }
}
else {
  displayForm();
}

makeHtmlFooter();

?>
