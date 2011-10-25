<?php
/********************************************
Klassica Classifieds
(c) 2006 Arthur Ketcham

config.inc.php
  Application configuration variables


********************************************/
// APPLICATION INSTALLED?
// Once you have configured the options
// in this file, change the value of this 
// to TRUE. This will be replaced in future
// versions with an install tool
//
// define('_INSTALLED', TRUE);
define('_INSTALLED', FALSE);


// DEBUG MODE
// NO errors will be shown unless the
// application is put in debug mode
// Also, the main logo is changed to note this mode (index.php)
//
// define('_DEBUG', FALSE);
define('_DEBUG', TRUE);


// The domain/subdomain of your website
// do not include a "/" at the end or "http://"
//
// define('_SITE_DOMAIN', "klassica.org");
define('_SITE_DOMAIN', "klassica.org");

// The url path to your installation of klassica
// start with "/" but do not include a "/" at the end
//
// define('_SITE_PATH', "");
define('_SITE_PATH', "");

// The file system path to your installation of klassica
// Give the absolute path with no ending "/"
//
// define('_SERV_PATH', "/var/www/klassica");
define('_SERV_PATH', "/var/www/klassica");

// Does your server have HTTPS (SSL) Avaliable?
// TRUE by default, but set to FALSE if this is not avaliable
//
// define('_HAS_HTTPS', TRUE);
define('_HAS_HTTPS', TRUE);

// Custom HTTPS PORT
// specify which port to use for SSL (leave blank for default (443))
//
// define('_CUSTOM_HTTPS_PORT', '');
define('_CUSTOM_HTTPS_PORT', '');


// Database Type
// ifx -> INFORMIX
// msql -> MiniSQL
// mssql -> Microsoft SQL Server
// mysql -> MySQL
// odbc -> ODBC
// pg -> Postgres SQL
// sybase -> Sybase
//
// define('_DB_TYPE', "mysql");
define('_DB_TYPE', "mysql");

// Database Host
//
// define('_DB_HOST', "localhost");
define('_DB_HOST', "localhost");

// Database Port
// Leave blank for default
//
// define('_DB_PORT', "");
define('_DB_PORT', "");

// Database Name
//
// define('_DB_NAME', "klassica");
define('_DB_NAME', "klassica");

// Database User
//
// define('_DB_USER', "klassica");
define('_DB_USER', "klassica");

// Database Password
//
// define('_DB_PASS', "***********");
define('_DB_PASS', "***********");

// HTML Page Title Prefix
//
// define('_HTML_TITLE_PREFIX', "Kaskadia Klassica");
define('_HTML_TITLE_PREFIX', "Kaskadia Klassica");

// Default AutoModeration hiding
// Hides listings if flagged beyond a certain threshold
// Increase these numbers if this site is used in a large community
//
// define('_HIDE_OFFENSIVE_THRESHOLD', "3");
// define('_HIDE_SPAM_THRESHOLD', "3");
// define('_HIDE_MISCAT_THRESHOLD', "5");
define('_HIDE_OFFENSIVE_THRESHOLD', "3");
define('_HIDE_SPAM_THRESHOLD', "3");
define('_HIDE_MISCAT_THRESHOLD', "15");

// By default, show items for this many days
//
// define('_DEFAULT_DAYS_EXPIRATION', "14");
define('_DEFAULT_DAYS_EXPIRATION', "14");

// Maximum days before new listings expire
//
// define('_MAX_DAYS_EXPIRATION', "21");
define('_MAX_DAYS_EXPIRATION', "21");

// File Upload Path (For uploaded item images)
// Do not add a forward slash
//
// define('_FILE_UPLOAD_DIR', "/fileupload");
define('_FILE_UPLOAD_DIR', "/fileupload");
$_FILE_UPLOAD_PATH = _SERV_PATH . _FILE_UPLOAD_DIR;
define('_FILE_UPLOAD_PATH', $_FILE_UPLOAD_PATH);


########################
// Klassica internal settings. Typically shouldn't be edited

define('_VERSION', "2.0.2");

// DONT CHANGE THIS
// Prevent include pages from being accessed
// include pages will proceed only if this variable is set
define('_VALID_INCLUDE', TRUE);
?>
