<?php

require_once ("includes/klassica.php");

$header["bodyid"] = "home";
$header['show_sideboxes'] = true;
$header["css"] = array('main', 'categories');
$header["title"] = _HTML_TITLE_PREFIX." - Home";
require_once ("includes/header.php");

?>
        
        <h2 class="titleLabel">Welcome to Klassica Classifieds of Walla Walla University.</h2>

        <p class="b">Use your Walla Walla University username and password to log in to Klassica!</p>

        <p class="bodytext"> This service is an online classifieds system designed for the students, staff, and faculty of <a href="http://www.wallawalla.edu/">Walla Walla University</a>.
        </p>
        <p class="bodytext">
        If you have suggestions or comments, please <a href="<?php echo _SITE_URL;?>/contact/">contact Klassica's administrators.</a>
        </p>
        
        <p class="bodytext">
        If you want to post a <span class="b">lost &amp; found</span> item, use the <span class="i">"Post Announcements"</span> page.<br />
        If you want to post a <span class="b">"Wanted"</span> item, use the <span class="i">"Sell Items"</span> page, and post in that item's category.
        </p>
        
        <p class="bodytext">See something that shouldn't be here? Log in with your WWU account, and set a "moderation flag" on the item's page.</p>
        
        <?php include("includes/homepage/categories.php"); ?>


<?php include("includes/footer.php"); ?>
