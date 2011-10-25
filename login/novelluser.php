<?php
// Old WWC Novell Account Registration Form
// 2008-07-07 Arthur Ketcham
//
// Used mainly by alumni
// Checks for existence of an old WWC Novell account. If one is in the Klassica database, it can be migrated to Klassica
// without admin approval. If there is no record of this type of account, just redirect to the new user registration page.
//
// Upon submission, migration stuff happens, then either this page is reloaded, or the user is taken to the new reg. page
// This page will be reloaded with either success messages or a failure notice

// Steal most of the code from new-registation.php


require_once ("../includes/klassica.php");

$header["bodyid"] = "home";
$header['show_sideboxes'] = false;
$header["css"] = array('main', 'login');
$header["title"] = _HTML_TITLE_PREFIX." - Login";
require_once ("../includes/header.php");
?>

<h3>This page is under maintenance, please try again later.</h3>

<?php
require_once ("../includes/footer.php");
?>