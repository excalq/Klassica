<?php

require_once ("../includes/klassica.php");

// WWU's official authentication script, provided by web services - Local copy updated 2008-03-18
require_once ('../includes/auth/wwuauth.php');

include ("../includes/classes/write_logfile.php");
$log = new write_logfile("../includes/auth/auth_log.txt");

// Persist the url to redirect to after authentication
if ($_SESSION['redirect']) {
  $redir_url = $_SESSION['redirect'];
} else {
  $klassica_home = _SITE_URL;
  $redir_url = ($_GET['redirect']) ? $_GET['redirect'] : $klassica_home; // Last page, or Klassica home
  $_SESSION['redirect'] = $redir_url;
}

// Handle a logout request (if logged in, and one was requested)
if($_GET['action']=='logout') {
  unset($_SESSION['auth']);
  dirLogout($redir_url);    // does stuff, then redirects back to this script
}

// After logout, get out of here
if ($_GET['returnPath']) {
  echo "Logged Out";
  vardumper($_SESSION);
  die();
  return_done();
}

// If user is currently logged in to Klassica (locally)
if ($_SESSION['auth']['authenticated']) {
  return_done();
} else {
  // Test if user is logged in to WWU auth system
  $wwu_user = wwu_loggedin();
  
  if ($wwu_user) {
    ldap_to_klassica_user($dbconn, $log, $wwu_user);
  }
}


// If login form was submitted, authenticate against Klassica's local auth system first, and then WWU's if that fails
if ($_POST['kauth'] && $_POST['kauthuname'] && $_POST['kauthtoken']) {
  $username = $_POST['kauthuname'];
  $password = $_POST['kauthtoken'];
  
  if (!isset($kuser)) {
      $kuser = new Klassica_user($dbconn);
  }
  // third argument is to translate password to md5 (use 'false' if already md5)
  // If user is only in LDAP and not local DB, login will fail
  // Values: 0: success, 1: bad login, 2: bad password, 3: account locked, 4: remote user account
  $local_auth = $kuser->authenticate_user($username, $password, true);
  //vardumper($local_auth); exit;
  
  if ($local_auth === 0)
  {
    $_SESSION['auth']['authenticated'] = true;
   
    $kuser->fetch_user($username);
    $_SESSION['auth']['user_id']       = $kuser->get_userid();
    $_SESSION['auth']['username']      = $username;
    $_SESSION['auth']['fullname']      = $kuser->get_fullname();
    $_SESSION['auth']['authenticated'] = true;

    // DEBUG:  Log sucessful authentication
    $log->write("User Logged in (Local): ".$username);
    
    // good login, we are finished here
    return_done();
    
  } elseif ($local_auth === 3) {
    // If user's account is locked
    $_SESSION['error'] = "Your account has been locked. Please contact an administrator.";
    $_SESSION["auth"]["account_locked"] = true;
    $log->write("User account locked, cannot login: ".$username);

  } elseif ($local_auth === 2) {
      // User found in Klassica DB, but password was bad
      $_SESSION['error'] = "Invalid username or password entered. Please try again.";
      $log->write("User login failure (bad password): ".$username);
  
  } elseif ($local_auth === 1 || $local_auth === 4) {
    // No login in found in Klassica DB, or is a known remote WWU account (has no local passwd)
    // Klassica Auth failed, so now try WWU LDAP login
    if ($_POST['kauth'] && $_POST['kauthuname'] && $_POST['kauthtoken']) {
      $wwu_user = wwu_ldap_auth($username, $password);

      if ($wwu_user) {
        vardumper($wwu_user); exit;
        
        ldap_to_klassica_user($dbconn, $log, $wwu_user);
      }
    }

  } else {
    // Neither WWU nor Klassica could authenticate this user
    
    //DEBUG: Log Error
    $log->write("ERROR failure with LDAP and Klassica login: ".$username);
    
    $_SESSION['error'] = "Your login attempt was unsucessful.";
  }
}


// If not logged in, show login form
if (!$_SESSION['auth']['authenticated']) {
  
  $error = '';
  if ($_SESSION['error']) {
    $error .= "<h3 style=\"errorLabel\">{$_SESSION['error']}</h3>";
    unset($_SESSION['error']);
  }
  if ($_GET['error']) {
    $error .= "<h3>Login Failed</h3>";
  }
  if ($_POST['kauth'] && (!$_POST['kauthuname'] || !$_POST['kauthtoken'])) {
    $error .= "<h3>A blank username or password was entered. Please try again.</h3>";
  }

  $header["bodyid"] = "home";
  $header['show_sideboxes'] = false;
  $header["css"] = array('main', 'login');
  $header["title"] = _HTML_TITLE_PREFIX." - Login";
  require_once ("../includes/header.php");
  
  echo $error;
?>
    <h2 class="titleLabel">Welcome to Klassica Classifieds of Walla Walla University.</h2>

    <p class="b">Use your Walla Walla University username and password if you have one to log in to Klassica!<br />
    Otherwise, use your Klassica user account username and password.</p>
    
    <p class="b">If you formerly used Klassica with an old WWC Novell account (like "LastFi"), but do not have a new WWU account (like "First.Lastname"),<br />
    (this is likely if you are an alumni),
    you will need to <a href="novelluser.php" />migrate your account</a> to continue using Klassica.</p>
      

    <form id="login_form" action="index.php" method='post'>
      <label for="kauthuname">Username</label>
      <input type='text' id="kauthuname" name="kauthuname" /><br>
    
      <label for="kauthtoken">Password</label>
      <input type='password' id="kauthtoken" name="kauthtoken" /><br>

      <image src="../images/ssl-lock.jpg" alt="Secure Login" title="Klassica uses a secure login" style="float: left; padding-right: 20px;" />
      <input type='submit' name='kauth' value='Log in' />
      <br />
    
    </form>

<?php 
  include("../includes/footer.php");

}

// Check if already logged in to WWU
function wwu_loggedin() {
  $user_info = dirIsLoggedIn();
  return $user_info;
}

function wwu_ldap_auth($username, $password) {
  $userInfo = dirLogin($username, $password);
  return $user_info;
}

// Takes WWU login information, and find user in Klassica database, 
// or creates one if not existing
function ldap_to_klassica_user($dbconn, $log, $wwu_user) {

  $username  = strtolower($wwu_user['username']);
  $email     = strtolower($wwu_user['email']);
  $firstname = $wwu_user['firstname'];
  $lastname  = $wwu_user['lastname'];
  $status    = strtolower($wwu_user['status']);

  // Detect if user exists in Klassica
  if (!isset($kuser)) {
    $kuser = new Klassica_user($dbconn);
  }
  
  // Returns "0" or "4" if this user was found in Klassica's database
  $local_auth = $kuser->authenticate_user($username, false, false);
  $log->write("Local Auth was: ".$local_auth);
  
  if ($local_auth === 3) {
    // This user is locked, don't log in
    $_SESSION['error'] = "Your account has been locked. Please contact an administrator.";
    $_SESSION["auth"]["account_locked"] = true;
    $log->write("User account locked, cannot login: ".$username);
    
  } elseif ($local_auth === 0 || $local_auth === 4) {
    // Retreive local user information
    $kuser->fetch_user($username);
    
  } else {
    // If there is no such remote user, create one
    
    // store wwu user in local db (with blank password (To always force LDAP auth))
    $adduser = $kuser->create_new_user($username, $email, $firstname, $lastname, $status);
    
    if (!$adduser) {
      $_SESSION['error'] = "There was a problem adding your WWU account to Klassica. Please contact an administrator.";
      
      //DEBUG: Log Error
      $log->write("ERROR with adding WWU user to klassica: ".$username);
      
      return_done(); // stop further authentication
      
    } else {
      $_SESSION['info'] = "New WWU user has been added to Klassica.";
      //DEBUG: Log sucessful authentication
      $log->write("New WWU User: ".$username);
    }
  }
  
  // Now get user information
  // Always run after successful wwu LDAP auth
  $_SESSION['auth']['user_id']       = $kuser->get_userid();
  $_SESSION['auth']['username']      = $username;
  $_SESSION['auth']['fullname']      = $kuser->get_fullname();
  $_SESSION['auth']['authenticated'] = true;
  
  //DEBUG:  Log sucessful authentication
  $log->write("User Logged in (LDAP): ".$username);
  return_done();
}

// Exit this page, redirecting to whatever came before (or the home page)
function return_done() {
  $redir_url = $_SESSION['redirect'];

  // clear username and password from session vars
  unset($_SESSION['auth']['kauthusername']);
  unset($_SESSION['auth']['kauthtoken']);
  unset($_SESSION['redirect']);
  
//   vardumper($_SESSION);
  
  header("Location: $redir_url");
  exit();
}
