<?php
/**********************************************************
Klassica v0.2 - Klassica Software
(c) 2006-Aug Arthur Ketcham

klassica.php
Main application initialization script
Sets variables, initializes objects, etc.
Reads config file


TODO:
  Security:
    disable errors, enable login
    magic_quotes?



**********************************************************/
session_start();

// if (!stristr($_SERVER["REQUEST_URI"], 'login'))
//   vardumper($_SESSION);

// If app is in debug mode, show all errors except notices, otherwise never show errors
if (_DEBUG) {
  error_reporting(E_ALL ^ E_NOTICE);
  
    
  #########################################
  ### DEBUG #########33
//   $user_id = '11111111';
//   $_SESSION["auth"]["user_id"] = '11111111';
//   $_SESSION["auth"]["authenticated"] = true;
  #########################################
  
} else {
  // Turn off all error reporting
  error_reporting(0);
}


// get filepath of this script
// This has to be used since this script is included at various
// depths within this application's file structure
$INCL_DIR = dirname(__FILE__);

// Read config file
if ((include_once ("$INCL_DIR/../setup/config.php")) != true) {
  $KLASSICA_NOTICE["configfile_missing"] = "<h3 class=\"errorLabel\">
  The configuration file <em> ".$KLASSICA_CONFIG["site_path"]
  ."/config.inc.php</em> was not found</h3>";
}

// include object class files
// TODO: Convert this to autoload!
require_once ("$INCL_DIR/classes/Klassica_database.php");
require_once ("$INCL_DIR/classes/Klassica_users.php");
require_once ("$INCL_DIR/classes/Klassica_items.php");
require_once ("$INCL_DIR/classes/Klassica_listings.php");
require_once ("$INCL_DIR/classes/Klassica_messages.php");

if (_INSTALLED != true) {
  die("Thank you for installing Klassica. Please configre options in <em>config.php</em>");
}

// IF _HAS_HTTPS was set, then set _HTTPS_SITE_PATH to https://
// Due to weird bugs, like safari complaining about HTTP over port 443,
// set _CUSTOM_HTTPS_PORT to blank if it is 443
if (_CUSTOM_HTTPS_PORT == '443') {
  define(_CUSTOM_HTTPS_PORT, '');
}
$custom_https_port = (_CUSTOM_HTTPS_PORT) ? ':'._CUSTOM_HTTPS_PORT : ''; // add colon if set

// If server is SSL capable
if (_HAS_HTTPS) {
  define('_HTTPS_SITE_DOMAIN', "https://{$_SITE_DOMAIN}{$custom_https_port}");
} else {
  define('_HTTPS_SITE_DOMAIN', "http://{$_SITE_DOMAIN}");
}

// If currently using SSL
if ($_SERVER['HTTPS']) {
  $_SITE_DOMAIN = "https://$_SITE_DOMAIN";
} else {
  $_SITE_DOMAIN = "http://{$_SITE_DOMAIN}";
}

// Set Main Site Url
define('_SITE_URL', $_SITE_DOMAIN . _SITE_PATH);
define('_HTTPS_SITE_URL', _HTTPS_SITE_DOMAIN . _SITE_PATH);


// Activate database object
try {
  $dbconn = new database(_DB_TYPE,_DB_HOST,_DB_NAME,_DB_USER,_DB_PASS,_DB_PORT);
} catch (DBConnectException $e) {
  die ($e->getTraceAsString ( ));
}

// // Activate database object
// function database_start($debug=false) {
//   $db = new database(_DB_TYPE,_DB_HOST,_DB_NAME,_DB_USER,_DB_PASS,_DB_PORT);
//   if ($debug) {
//     $db->debug_mode();
//   }
// 
//   return $db;
// }


// Custom var_dump function
function vardumper($var) {
  echo "<pre>";
  echo var_dump($var);
  echo "</pre><br />";
}


// for portability disable magic_quotes (see: http://us2.php.net/magic_quotes)

//Prevent Magic Quotes from affecting scripts, regardless of server settings

//Make sure when reading file data,
//PHP doesn't "magically" mangle backslashes!
set_magic_quotes_runtime(FALSE);

if (get_magic_quotes_gpc()) {
   /*
   All these global variables are slash-encoded by default,
   because    magic_quotes_gpc is set by default!
   (And magic_quotes_gpc affects more than just $_GET, $_POST, and $_COOKIE)
   */
   $_SERVER = stripslashes_array($_SERVER);
   $_GET = stripslashes_array($_GET);
   $_POST = stripslashes_array($_POST);
   $_COOKIE = stripslashes_array($_COOKIE);
   $_FILES = stripslashes_array($_FILES);
   $_ENV = stripslashes_array($_ENV);
   $_REQUEST = stripslashes_array($_REQUEST);
   $HTTP_SERVER_VARS = stripslashes_array($HTTP_SERVER_VARS);
   $HTTP_GET_VARS = stripslashes_array($HTTP_GET_VARS);
   $HTTP_POST_VARS = stripslashes_array($HTTP_POST_VARS);
   $HTTP_COOKIE_VARS = stripslashes_array($HTTP_COOKIE_VARS);
   $HTTP_POST_FILES = stripslashes_array($HTTP_POST_FILES);
   $HTTP_ENV_VARS = stripslashes_array($HTTP_ENV_VARS);
   if (isset($_SESSION)) {    #These are unconfirmed (?)
       $_SESSION = stripslashes_array($_SESSION, '');
       $HTTP_SESSION_VARS = stripslashes_array($HTTP_SESSION_VARS, '');
   }
   /*
   The $GLOBALS array is also slash-encoded, but when all the above are
   changed, $GLOBALS is updated to reflect those changes.  (Therefore
   $GLOBALS should never be modified directly).  $GLOBALS also contains
   infinite recursion, so it's dangerous...
   */
}

function stripslashes_array($data) {
   if (is_array($data)){
       foreach ($data as $key => $value){
           $data[$key] = stripslashes_array($value);
       }
       return $data;
   }else{
       return stripslashes($data);
   }
}

/* Function to sanitize form input data 
   trims and coverts input to htmlspecialchars */
function clean_input($data) {
  return htmlspecialchars(strip_tags(trim($data), '<p><br><a><b><strong><i><em>'), ENT_QUOTES);
}

function clean_numbers($data) {
  return preg_replace('/[^\d\.]/', '', $data); 
}

/* Turn coded html entities back into real html symbols*/
function make_html($data) {
  return htmlspecialchars_decode($data);
}

// protect email addresses from spam
function protect_email($user, $domain) {
  return "<nobr><a href=\""._SITE_URL."/redirect-mailto/?u=$user&d=$domain\">$user<img src=\""._SITE_URL."/images/at.gif\" align=\"absbottom\" border=\"0\" alt=\"@\" />$domain</a></nobr>";

}

// Turns SQL date into a user friendly relative date (e.g. 4 hours ago)
function relative_date($d) {
    $ts = time() - strtotime(str_replace("-","/",$d));
    
    if($ts>31536000) $val = round($ts/31536000,0).' year';
    else if($ts>2592000) $val = round($ts/2419200,0).' month'; // after 30 days
    else if($ts>1209600) $val = round($ts/604800,0).' week';   // after 2 weeks ago
    else if($ts>86400) $val = round($ts/86400,0).' day';
    else if($ts>3600) $val = round($ts/3600,0).' hour';
    else if($ts>60) $val = round($ts/60,0).' minute';
    else $val = $ts.' second';
    
    if($val>1) 
      $val .= 's';
      
    $val .= " ago";
    return $val;
}

// Convert a US English date to MYSQL format
function mysql_date($date) {

  if (strtotime($date) > 1) {
    return date('Y-m-d', strtotime($date));  
  } else {
    return '';
  }
}

?>