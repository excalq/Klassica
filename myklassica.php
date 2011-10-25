<?php

require_once ("includes/klassica.php");


$header["title"] = _HTML_TITLE_PREFIX." - My Klassica";
$header["bodyid"] = "myklassica";
$header["css"] = array('main', 'forms', 'categories', 'listings');
$header["include_calendar"] = true;

include("includes/header.php");

?>

        <?php echo $body_error;
        
        // if not logged in, display general information
        if (!$_SESSION["auth"]["authenticated"]) {
        
        ?>
          <h2 class="titleLabel">My Klassica</h2>
          <p>
          Please log in to display your sales, purchases, and listings. You can
          also edit your account settings and preferences here.
          </p>
          <p>
          Use your Walla Walla College Novell account to log in to klassica.
          The login box is in the upper right corner of this page.
          </p>
        <?php
        // if user is logged in, display thier account info
        } else {
        
        ?>
        <h2 class="titleLabel">My Klassica: <?php echo $_SESSION["auth"]["fullname"];?></h2>
        <p>
        <h3>Welcome to My Klassica!</h3>
        This page shows items being watched, response messages received and sent, previously posted items, and items that have been purchased or replied to.
        </p>
          <?php 
          // Watched Items
         include("includes/myklassica/watching.php"); 
          ?>
          
          <p></p>
          <hr />
          
          <?php 
          // Received Messages (Replies)
          include("includes/myklassica/messages_rcvd.php"); 
          ?>
          
          <?php 
          // Received Messages (Replies)
          include("includes/myklassica/messages_sent.php"); 
          ?>
          
          <p></p>
          <hr />

          <?php 
          // Past Purchases
          include("includes/myklassica/items_bought.php"); 
          ?>
                    
          <?php 
          // My Listings
         include("includes/myklassica/listings_posted.php"); 
          ?>

        <br />
          <?php // include("includes/myklassica/myprofile.php"); ?>
        <br />
          <?php // include("includes/myklassica/myprefs.php"); ?>
          
          <p style="height: 70px;"></p>
<?php    
   }
?>

<?php include("includes/footer.php"); ?>
