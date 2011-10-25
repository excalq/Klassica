<?php
// Begin Client Example
  require 'wwuauth.php';
  
  if (!isset($_SESSION['refreshes']))
  {
    $_SESSION['refreshes'] = 0;
  } 
  else
  {
    $_SESSION['refreshes']++;
  }
  
  if($_GET['action']=='logout') 
  {
    dirLogout();
  }
  elseif($userInfo = dirIsLoggedIn()) 
  {
    echo '<a href="?action=logout">logout</a><br>';
  }
  elseif($_POST['action'] == 'login') 
  {
    $userInfo = dirLogin($_POST['username'],$_POST['pw']);
  }
  else 
  {
    echo "
      <form method='post'>
        Username: <input type='text' name='username'><br />
        Password: <input type='password' name='pw'><br />
        <input type='submit' name='action' value='login'>
        user: webtest, pw: typo3456 may be used for testing.
      </form>
    ";
  }
  
  echo "<p>There were refreshed: {$_SESSION['refreshes']} times.</p>";
  
  $basicUserInfo = dirMaybeLoggedIn(); 
  
  unset($_SESSION['refreshes']);
  
  pr2($userInfo,'$userInfo');
  pr2($basicUserInfo,'$basicUserInfo');
// End Client Example

?>