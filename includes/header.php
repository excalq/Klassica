<?php
/**********************************************************
Klassica v0.2 - Klassica Software
(c) 2006-Aug Arthur Ketcham

header.php
general header includes, to be includesd on most pages

Forms:
  search - searches for items
    submits to /search.php
  login - allows user login
    submits to /myklassica/index.php


**********************************************************/

// If config file is not set, display error message
if (!_INSTALLED) {
  echo "<div class=\"system_notice\">Warning: This installation of Klassica is not properly configured.</div>";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
  <title>
    <?php echo $header["title"]."\n"; ?>
  </title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php

  // Graphic theme: Get from sessuion, or GET parameter
  // if neither set to default
  if ($_GET["theme"]) {
    $_SESSION["theme"] = $_GET["theme"];
  } else {
    if (!$_SESSION['theme']) {
      $_SESSION['theme'] = 'default';
    }
  }

  if ($header["css"]) {
     // If theme is set, use its stylesheet directory
    $hdr_theme = $_SESSION['theme'];

  // If sideboxes are hidden, dont include thier CSS file
  if ($header["show_sideboxes"]) {
    array_push($header["css"], "sidebox");
  }

    foreach ($header["css"] as $hdr_stylesheet) {
      echo "  <link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\""._SITE_URL."/theme/$hdr_theme/styles/$hdr_stylesheet.css\" />\n";
    }
  }
  echo "  <!--[if IE]>\n\t<link rel=\"stylesheet\" type=\"text/css\" href=\""._SITE_URL."/theme/$hdr_theme/styles/msiehacks.css\" />\n  <![endif]-->\n";  

  if ($header["javascript"]) {
    foreach ($header["javascript"] as $hdr_javascript) {
      echo "  <script type=\"text/javascript\" src=\""._SITE_URL."/scripts/$hdr_javascript.js\"></script>\n";
    }
  }

  /* If calendar widget should be includesd */
  if ($header["include_calendar"]) {
    echo '  <link rel="stylesheet" type="text/css" href="'._SITE_URL.'/includes/epoch_calendar/epoch_styles.css" />'."\n";
    echo '  <script type="text/javascript" src="'._SITE_URL.'/includes/epoch_calendar/epoch_classes.js"></script>'."\n";
    echo '  <script type="text/javascript" src="'._SITE_URL.'/includes/epoch_calendar/epoch_init.js"></script>'."\n";
}

  /* includes captcha mechanism */
if ($header["include_captcha"]) {
    echo '  <script type="text/javascript" src="'._SITE_URL.'/includes/captcha/freecap.js"></script>'."\n";
}

?>

  <script type="text/javascript" src="<?php echo _SITE_URL;?>/scripts/main.js"></script>
</head>
<body<?php if ($header["bodyid"]) { echo ' id="'.$header["bodyid"].'"'; } ?>>
  <div id="header">
    <a href="<?php echo _SITE_URL;?>/"><img src="<?php
      echo _SITE_URL;
      if (_DEBUG)
        echo "/images/header-dev-brushed.png";
      else
        echo "/images/header.png";
      ?>"
       alt="Klassica Classifieds" title="Klassica.org" /></a>
    <h1 id="header-text"><a href="<?php echo _SITE_URL;?>">Klassica Classifieds</a> </h1>
    <div id="top_navigation">
      <ul id="navtabs">
        <li class="home"><a href="<?php echo _SITE_URL;?>/" class="home">Home</a></li>
        <li class="search"><a href="<?php echo _SITE_URL;?>/search/" class="search">Search</a></li>
        <li class="sell"><a href="<?php echo _SITE_URL;?>/sell/" class="sell">Sell Items</a></li>
        <li class="announce"><a href="<?php echo _SITE_URL;?>/announce/" class="announce">Post Announcements</a></li>
        <li class="housing"><a href="<?php echo _SITE_URL;?>/housing/" class="housing">Housing</a></li>
        <li class="myklassica"><a href="<?php echo _SITE_URL;?>/myklassica/" class="myklassica">My Klassica</a></li>
        <li class="about"><a href="<?php echo _SITE_URL;?>/about/" class="about">About Klassica</a></li>
         <li class="contact"><a href="<?php echo _SITE_URL;?>/contact/" class="about">Contact Us!</a></li>
      </ul>
    </div>
    <div id="search_container">
      <form id="searchform" method="get" action="<?php echo _SITE_URL;?>/search/">
        <div>
          <input type="text" class="headerFormTextInput" name="ksearch_q" value="search for items" size="30" onfocus="clearDefaultText(this);" />
          <input type="submit" class="headerSubmitLink" value="Search" />
        </div>
      </form>
    </div>
    <div id="login_container">
<?php
// print "logged in as" notification, or display login boxes
$hdr_orignal_page = $_SITE_DOMAIN . $_SERVER["REQUEST_URI"];

if ($_SESSION["auth"]["authenticated"] == true) {
  $hdr_username = $_SESSION["auth"]["username"];
  $hdr_fullname = $_SESSION["auth"]["fullname"];
    
  //   vardumper($_SESSION);
  echo '<form id="logout" method="get" action="'._HTTPS_SITE_URL.'/login/?action=logout">
  <div id="loggedInAsText">Logged in as <b>'.$hdr_username.'</b>
    <input type="hidden" name="action" value="logout" />
    <input type="hidden" name="redirect" value="'.$hdr_orignal_page.'" />
    <input type="submit" class="headerSubmitLink" value="Log out" /></div>
  </form>';
  
} else {
  echo '<div>
          <a href="'._HTTPS_SITE_URL.'/login/?redirect='.$hdr_orignal_page.'">Log in to Klassica</a>
        </div>
        ';
}
?>

    </div>
  </div>
  <div id="container">
    <div id="container_inner">
      
<?php 
  if ($header["show_sideboxes"]) {
    echo "  <div class=\"sidebox_container\">";
    include "sideboxes/newstuff.php";
    include "sideboxes/freeitems.php";
    include "sideboxes/textbooks.php";
    include "sideboxes/lostfound.php";
    echo "  </div>";
  }      
?>
      <div id="main_content">
      
      <?php
      
      
        if ($_SESSION['error'])
          echo "<h3 class=\"errorLabel\">{$_SESSION['error']}</h3>";
        if ($_SESSION['info'])
          echo "<h3 class=\"notifyLabel\">{$_SESSION['info']}</h3>";
        // Clear these once they've been displayed
         unset($_SESSION['error']); 
         unset($_SESSION['info']);
      ?>
