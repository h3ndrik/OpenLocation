<?php
require_once('functions.php');


function displayForm() {
  echo "<form action=\"register.php\" method=\"POST\">\n";
  echo "<p>Username: <input type=\"text\" name=\"username\" /></p>\n";
  echo "<p>Password: <input type=\"password\" name=\"password\" /></p>\n";
  echo "<p><input type=\"submit\" /></p>\n";
  echo "</form>\n\n";
}

makeHtmlHeader('Register User');
echo "<strong>OpenLocation - Register new user on " . $_SERVER['HTTP_HOST'] . "</strong>\n\n";


if ($_SERVER['REQUEST_METHOD'] === "POST") {

  // "@host" hinzufügen, falls fehlend
  if (!strrpos($_POST["username"], "@") && strlen($_POST["username"]) >= 2) {
    $_POST["username"] = $_POST["username"] . '@' . $_SERVER['HTTP_HOST'];
  }

  if (!validEmail($_POST["username"])) {
    echo '<span style="color:#FF0000">Username must have form of: &quot;user@domain.com&quot;!</span><hr />';
    displayForm();
  } 
  else if (strcmp($domain = substr($_POST["username"], strrpos($_POST["username"], "@")+1), $_SERVER['HTTP_HOST']) != 0) {
    echo '<span style="color:#FF0000">Wrong Host! Expected: ...@' . $_SERVER['HTTP_HOST'] . '</span><br />Please register at <a href="http://' . $domain . '">http://' . $domain . '</a>.<hr />';
    displayForm();
  }
  else if ((strlen($_POST["password"]) < 3) || (strlen($_POST["username"]) > 32)) {
    echo '<span style="color:#FF0000">Password not acceptable!</span><hr />';
    displayForm();
  }
  else {
    connectToMySQL();
    $query = "CREATE TABLE IF NOT EXISTS users(username varchar(255), password varchar(32), token TEXT, friends TEXT, authorized TEXT, PRIMARY KEY (username));";
    $result = mysql_query($query) or die("Unable to create table: " . mysql_error());
    $local = substr($_POST["username"], 0, strrpos($_POST["username"], "@"));
    $query = "SELECT * FROM users WHERE username = '" . mysql_real_escape_string($local) . "';";
    $result = mysql_query($query) or die("MySQL Error (SELECT *): " . mysql_error());
    if (mysql_num_rows($result) == 0) {
      mysql_free_result($result);
      $query = "INSERT INTO users VALUES('" . mysql_real_escape_string($local) . "', '" . mysql_real_escape_string($_POST['password']) . "', '" . newtoken() . "', '', '');";
      $result = mysql_query($query) or die("MySQL Error (INSERT): " . mysql_error());
      $query = "CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string($local) . "`(time BIGINT, latitude DOUBLE, longitude DOUBLE, altitude DOUBLE, accuracy FLOAT, speed FLOAT, bearing FLOAT, provider varchar(16), ip char(16), PRIMARY KEY (time));";
      $result = mysql_query($query) or die("Unable to create table: " . mysql_error());
      echo '<span style="color:#00C000">Successfully created user &quot;' . htmlspecialchars($local) . '@' . $_SERVER['HTTP_HOST'] . '&quot;</span>';
      echo '<p>Please remember to log in with full name.</p>';
      echo '<script type="text/javascript">';
      //echo '<!--';
      echo 'setTimeout("window.location.href=\'http://' . $_SERVER['HTTP_HOST'] . '/index.php\';",5000);';
      //echo ' // -->';
      echo '</script>';
    }
    else {
      mysql_free_result($result);
      echo '<span style="color:#FF0000">Error: Username exists.</span>';
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
