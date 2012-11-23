<?php
/* Enable error logging if set in config.php */
require_once('config.php');
if (defined('DEBUG') && strcmp(DEBUG, "yesdoit") === 0) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    if (defined('DEBUGFILE')) {
        ini_set("error_log", DEBUGFILE);
        ini_set('log_errors', 1);
    }
}

/* include functions */
require_once('functions/helper.php');
require_once('functions/http.php');
require_once('functions/auth.php');
require_once('functions/db.php');
?>
