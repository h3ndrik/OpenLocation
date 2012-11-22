<?php
/* Writes logfile when DEBUG and DEBUGFILE are set correctly */
function writetolog($error) {
  if (isset($DEBUG) && isset($DEBUGFILE) && strcmp($DEBUG, "yesdoit") == 0) {
    $myFile = $DEBUGFILE;
    $fh = fopen($myFile, 'a') or die500("FAILED");
    $timestamp = time();
    $stringData = "TIME: $timestamp, LOG: $error\n";
    fwrite($fh, $stringData);
    fclose($fh);
  }
}


/* Split username */
function explode_username($username) {
  if (strrpos($username, "@")) {
    if (validEmail($username)) {
      $local = substr($username, 0, strrpos($username, "@"));
      $domain = substr($username, strrpos($username, "@")+1);
    }
    else
      return null;
  }
  elseif (strlen($username) > 2) {
    $local = $username;
    $domain = $_SERVER['HTTP_HOST'];
  }
  else
    return null;
  $fullusername = $local . "@" . $domain;

  return array($local, $domain, $fullusername);
}


/* Returns true for syntactically correct email address */
function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if
(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}


function elapsed_time($timestamp, $precision = 2) {
  $time = time() - $timestamp;
  $a = array('decade' => 315576000, 'year' => 31557600, 'month' => 2629800, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'min' => 60, 'sec' => 1);
  $i = 0;
    foreach($a as $k => $v) {
      $$k = floor($time/$v);
      if ($$k) $i++;
      $time = $i >= $precision ? 0 : $time - $$k * $v;
      $s = $$k > 1 ? 's' : '';
      $$k = $$k ? $$k.' '.$k.$s.' ' : '';
      @$result .= $$k;
    }
  return $result ? $result.'ago' : '1 sec to go';
}


?>
