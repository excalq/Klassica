<?php
// New Account Registration Form
// 2008-07-07 Arthur Ketcham
//
// Can be loaded straight, or loaded from result of Novell migration
// 
// If loaded by Novell migration page, some old user account information may be passed along
// such as old username, old user id, and the fact that they had an account already
//
// If this is loaded straight, there is a form displayed for the user to fill in with their information
// If the user had an account previously, they are exempt from admin approval
//
// Upon submission, this page will reload with either an error message, or a success message which may give notice of pending admin approval



require_once ("../includes/klassica.php");

// WWU's official authentication script, provided by web services - Local copy updated 2008-03-18
require_once ('../includes/auth/wwuauth.php');

// Debug Logging
include ("../includes/classes/write_logfile.php");
$log = new write_logfile("../includes/auth/auth_log.txt");



// Handle log in, check klassica user db, and migrate account
if ($_POST['username'] && $_POST['username']) {
  
  if (!isset($kuser)) {
      $kuser = new Klassica_user($dbconn);
  }
  
  $has_old_account = FALSE;
  
  // third argument is to translate password to md5 (use 'false' if already md5)
  // If user is only in LDAP and not local DB, login will fail
  // Values: 0: success, 1: bad login, 2: bad password, 3: account locked, 4: remote user account
  $local_auth = $kuser->authenticate_user($username, $password, true);
  
  // There are several options here. Warn user if account exists, or if WWU user, do simple migration
  // Or if WWU auth also doesn't work give them the outside registration form
  switch($local_auth) {
    case 1:
      // This username was not found in Klassica
      // Invite the user to create a new account
      show_outsider_reg_form();
      break;
    case 2: 
      $error = 'Incorrect username or password'; 
      break;
    case 3:
      $account_locked = TRUE; // User's account is locked. Also flow to next statement
    case 0: $error = 'Your account exists in Klassica and does not require migration.
      Please use the regular login page with this username and your password.'; 
      break;
    case 4:
      // In Klassica database, but set as a "remote wwc user"
      // If this user isn't in WWU LDAP anymore, we'll let them migrate and keep their user data
      $has_old_account = TRUE;
      break;
  }
  
  // If the user does not exist locally, try a WWU LDAP login. If that works, do a simple migration
  if ($user_not_local) {
    $wwu_user = wwu_ldap_auth($username, $password);
    if ($wwu_user) {
      // WWU LDAP auth success
      $log->write("Migrate Script: User found in WWU LDAP, migrating to Klassica: ".$username);
      $result = ldap_to_klassica_user($dbconn, $log, $wwu_user);
      
      if (!$result) {
        // Attempt to migrate WWU to Klassica failed
        $log->write("Migrate Script: WWU to Klassica migration failed: ".$username);
        $error = "An error occured while migrating your WWU account. Please contact Klassica's Administrators.";
      } else {
        $log->write("Migrate Script: Successful WWU Migration: ".$username);
        $user_migrated = TRUE;
        $message = "Your account has been migrated, please log in with your WWU username and password.";
      }
    } else { 
      // WWU Auth failed, so invite user to create a new account.
      $log->write("Migrate Script: User not found in WWU LDAP: ".$username);
      
      // Invite user to create new account. If they had and old account, pass on that info
      show_outsider_reg_form($has_old_account);
    }
  }

  
// Novell user has migrated, and set a password
} elseif ($_POST['new_password']) {
  if (!$_POST['username'])
    $error = '';
  

} elseif ($_POST['new_registration']) {

} elseif ($_POST) {
  $error = "You have entered a blank username or password.";
}



if ($_SESSION['error']) {
  $error = $_SESSION['error'];
}

// HTML Page Header
$header["bodyid"] = "home";
$header['show_sideboxes'] = false;
$header["css"] = array('main', 'login');
$header["title"] = _HTML_TITLE_PREFIX." - Migrate Account";
require_once ("../includes/header.php");
echo $error;
unset($_SESSION['error']);

echo '<h2 class="titleLabel">Welcome to Klassica Classifieds of Walla Walla University.</h2>';
    
    
// Users without WWU accounts will be taken here to create Klassica accounts (pending admin approval)
if ($_GET['a'] == 'register') {


  show_outsider_reg_form();

} 

// Users with old Novell accounts will be taken here to migrate Klassica accounts
if ($_GET['a'] == 'migrate') { 

  echo '<p class="b">Use this form to migrate an old Novell account. Enter your old Novell username and password.</p>';
  
  echo '<p class="b">If you never had a Novell account, or have never used Klassica, please use the <a href="?a=register">new account registration form</a> instead.</p>';

  // Show form to check if their account already exists.
  show_password_form();
}



// have user log in

  
  echo $error;
?>


    <p class="b">Use this form to migrate an old account. Enter your old Novell username and password.</p>
    
    <p class="b">Use this form to migreate an old account.</p>
      

    <form id="login_form" action="index.php" method='post'>
      <label for="kauthuname">Username</label>
      <input type='text' id="kauthuname" name="kauthuname" /><br>
    
      <label for="kauthtoken">Password</label>
      <input type='password' id="kauthtoken" name="kauthtoken" /><br>

      <image src="../images/ssl-lock.jpg" alt="Secure Login" title="Klassica uses a secure login" style="float: left; padding-right: 20px;" />
      <input type='submit' name='kauth' value='Log in' />
      <br />
    
    </form>
  (login form here)
  
<?php
}


function show_password_form() {
  // Have user enter: 
  //  username (prepopulated)
  //  password
  //  current email address
  
}


function show_outsider_reg_form($has_old_account) {
  // show message about admin approval

  // desired username
  // new password
  // email address
  // fname
  // lname
  // city
  // state
  // connection to WWU
  // how user intends to use klassica
  // captcha
  

}

// Check if already logged in to WWU
function wwu_loggedin() {
  $user_info = dirIsLoggedIn();
  return $user_info;
}

// Try to log into WWU LDAP
function wwu_ldap_auth($username, $password) {
  $userInfo = dirLogin($username, $password);
  return $user_info;
}

// Takes WWU login information, and find user in Klassica database, 
// or creates one if not existing
// TODO: Put this and the similar function in the main login script into one class
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
      
      return false; // stop further authentication
      
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
  
  return true; 
}


?>