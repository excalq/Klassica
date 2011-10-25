<?php
require 'wwuauth.php';

//   $user_info = wwu_loggedin();
// 
//   if ($_POST['username'] && $_POST['password']) {
// 
//     $user_info = wwu_ldap_auth($username, $password);
//   }
//     
//   // Check if already logged in to WWU
//   function wwu_loggedin() {
//     $user_info = dirIsLoggedIn();
//     return $user_info;
//   }
//   
//   function wwu_ldap_auth($username, $password) {
//     $userInfo = dirLogin($username, $password);
//     return $user_info;
//   }
//   
//   // OUTPUT FOR TESTING PURPOSES:
//   if ($user_info) {
//     echo "<pre>";
//     var_dump($user_info);
//     echo "</pre>";
//   }



// Begin Client Example
  if($_GET['action']=='logout') 
    dirLogout();
    
  elseif($userInfo=dirIsLoggedIn()) 
    echo 'Already logged in';
    
  elseif($_POST['username'] && $_POST['password']) 
    $userInfo = dirLogin($_POST['username'],$_POST['password']);
  
//   $basicUserInfo=dirMaybeLoggedIn(); 
  pr2($userInfo,'$userInfo');
  pr2($basicUserInfo,'$basicUserInfo');

?>
  <html>
  <p>
  <strong>It is recommended to only use this page over HTTPS</strong><br />
  <span style="width: 70em; font-weight: bold;">username</span>
  <span style="margin-left: 95px; font-weight: bold;">password</span>
  <form method="post" action="">
    <input type="text" name="username" />
    <input type="password" name="password" />
    <input type="submit">
  </form>
  <p><a href="?action=logout">Logout</a></p>
  </p>
</html>
