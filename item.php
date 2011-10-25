<?php
require_once ("includes/classes/Klassica_flags.php");

require_once ("includes/klassica.php");
// require_once ("includes/classes/Klassica_items.php");

$user_id = $_SESSION["auth"]["user_id"];

// if user_id is not set, user their ip address as a user_id
if (!$user_id) {
  $user_id = $_SERVER['REMOTE_ADDR'];
  $public_user = true;
}

// ID information stored as last part of URI path

// remove parameters from uri string
$uri = (strtok($_SERVER['REQUEST_URI'], "?"));
// if a trailing slash was added, disregard it
if ($uri[strlen($uri)-1] == "/") {
  $uri = substr($uri, 0, -1);
}

$uri = explode("/",$uri);
$itemid = $uri[count($uri) - 1];


// get $itemid from URL (it is a 13 character id)
if (strlen($itemid) == 13) {



  // initiate item object
  $kitem = new Klassica_item($dbconn, $itemid);
  
  $itemok = $kitem->get_item_data();
  $seller_id = $kitem->get_field("seller_id");
  
  // initiate flags object
  $kflags = new Klassica_flags($dbconn, $itemid);
//   $has_already_flagged = $kflags->user_already_flagged($user_id);
  
} else {
  $itemid = false;
  $itemok = false;
}

// process mesasge submission
// Validate message submission if public user
if ($public_user && $_POST['message']) {
  
  // Require captcha validation for public users
  // Include script do the heavy lifting
  require ("includes/forms/form_handler.php");
  $captca_isvalid = validate_captcha(clean_input($_POST['captcha_word']));

  if (!$_POST['pub_name'] || !$_POST['pub_email']) {
    $message_error = true;
    $notification_text .= "<h3 class=\"errorLabel\">You have submitted an incomplete message, please verify that all fields have been filled.</h3>";
  } elseif (!$captca_isvalid) {
    // failed the captcha test
    $message_error = true;
    $notification_text .= "<h3 class=\"errorLabel\">You did not enter the verification word correctly.</h3>";
  }
}

if (!$message_error && $_POST['message'] && ($_SESSION["auth"]["user_id"] || $_POST['pub_name'])) {
  
  // If a public user sent a message
  if ($_POST['pub_name']) {
    $pub_sender_name = clean_input($_POST['pub_name']);
    $pub_sender_email = clean_input($_POST['pub_email']);
  }

  $item_id = clean_input($_POST['item']);
  $sender_id = $user_id;
  $message = clean_input($_POST['message']);
  $buy_flag = clean_input($_POST['buy']);

  $kmsg = new klassica_message($dbconn);
  $msg_sent_ok = $kmsg->post_message($item_id, $sender_id, $pub_sender_name, $pub_sender_email, $message, $buy_flag);
  $msg_mailed_ok = $kmsg->mail_message($item_id, $sender_id, $pub_sender_name, $pub_sender_email, $message, $buy_flag);
  
  // error handling
  if ($msg_sent_ok) {
    $notification_text .= "<h2 class=\"notifyLabel\">Thank you. Your message has been delivered to the owner of this listing.</h2>";
    $notification_text .= "<p><a href=\""._SITE_URL."/myklassica\">Go to MyKlassica</a></p>";
    $notification_text .= "<p><a href=\""._SITE_URL."/announce/\">Return to homepage</a></p>";
    $notification_text .= "<hr />";
    
    // clear form after successful submission
    unset($_POST['pub_name']);
    unset($_POST['pub_email']);
    unset($_POST['message']);
    unset($_POST['buy']);
    
  } else {
    $notification_text .=  "<h2 class=\"errorLabel\">There was a problem sending your message.</h2>";
  }

}


// If edit was requested
if ($itemok && ($_GET['mod'] == 'edit') && ($user_id == $seller_id)) {
  // figure out if item is a sale item or an announcement item, and handle appropriately
  $item_type = $kitem->get_item_type($itemid);

  if ($item_type == "Goods and Items") {
    header("Location: "._SITE_URL."/sell/?edit=$itemid");
  } elseif ($item_type == "Housing") {
    header("Location: "._SITE_URL."/housing/?edit=$itemid");
  } elseif ($item_type == "Announcements and Services") {
    header("Location: "._SITE_URL."/announce/?edit=$itemid");
  }
}

// If delete was requested
if ($itemok && ($_GET['mod'] == 'delete') && ($user_id == $seller_id)) {
    $notification_text .= "<h2 class=\"errorLabel\" style=\"display: inline;\">Do you really want to delete this item?</h2> ";
    $notification_text .= " <ul class=\"button-boxes\">";
    $notification_text .= "<li><a href=\""._SITE_URL."/item/$itemid/?mod=confirmed-delete\">Yes</a></li>";
    $notification_text .= "<li><a href=\""._SITE_URL."/item/$itemid/\">No</a></li>";
    $notification_text .= "</ul><br /><br />";
}

// If delete was confirmed
if ($itemok && ($_GET['mod'] == 'confirmed-delete') && ($user_id == $seller_id)) {
  $deleted = $kitem->delete_item($itemid);
  unset($itemok);
  unset($kitem);
}

// If delete file was requested
if ($_GET['rmfile'] && $user_id == $seller_id) {
  $deleted = $kitem->delete_file($_GET['rmfile']);
  // Reload this item
  header("Location: "._SITE_URL."/item/$itemid");
}

// If item was flagged - user must NOT be seller
// Add a flag to item_flags table
if ($itemok && $_GET['flag'] && ($user_id != $seller_id)) {
  $flag = $_GET['flag'];
  if (in_array($flag, array('spam', 'miscat', 'offense'))) {
    // check to see if user has already flagged this item
//     $has_already_flagged = $kflags->user_already_flagged($user_id);
    
    // Do not allow multiple flags from the same user
    if (!$has_already_flagged) {
      $flag_set = $kflags->set_flag($flag, $user_id);
      
      if ($flag_set) {
        $notification_text .= "<h3 class=\"errorLabel\">You have added a flag to this item. 
        A moderator will be notified and will review this posting.</h3>";
        $has_already_flagged = true;
      } else {
        $notification_text .= "<h3 class=\"errorLabel\">There was a problem adding your flag.</h2>";
      }
    }
  }
}


// if object found a good item in DB
if ($itemok) {
  $id             = $itemid;
  $seller_id      = $kitem->get_field("seller_id");
  
  $kuser = new klassica_user($dbconn, $seller_id);
  
  $item_type      = $kitem->get_item_type($id);
  if ($item_type == "Housing") {
    $seller_name  = $kitem->get_field("contact_name");
  } else {
    $seller_name = $kuser->get_fullname();
  }
  
  
  $itemtitle      = $kitem->get_field("itemtitle");
  $category_id    = $kitem->get_field("category_id");

  
  $phone          = $kitem->get_field("phone");
  $email          = $kitem->get_field("email");
  
  $buyer_id       = $kitem->get_field("buyer_id");
  $location       = $kitem->get_field("location");

  $email_pub      = $kitem->get_field("email_pub");
  $phone_pub      = $kitem->get_field("phone_pub");
  $description    = nl2br($kitem->get_field("description"));
  $short_desc     = $kitem->get_field("short_desc");
  $expire_date    = $kitem->get_field("expire_date");
  $price          = $kitem->get_field("price");
  $is_price_obo   = $kitem->get_field("is_price_obo"); 
  $is_price_free  = $kitem->get_field("is_price_free");
  $is_wanted      = $kitem->get_field("is_wanted");
  $condition      = $kitem->get_field("cond");
  $cr_date        = $kitem->get_field("cr_date");
  $mod_date       = $kitem->get_field("mod_date");
  
  $beds            = $kitem->get_field("beds");
  $baths           = $kitem->get_field("baths");
  $rent            = $kitem->get_field("rent");
  $deposit         = $kitem->get_field("deposit");
  
  $subcat_name     = $kitem->get_subcat_name();
  $cat_name        = $kitem->get_cat_name();
  
  $file_arr        = $kitem->get_field("file");
  

  if ($is_wanted) {
    $price = "(WANTED)";
  } elseif ($is_price_free) {
    $price = "FREE";
  } elseif ($price != '') {
    $price = "$".$price;
  }
  
  if ($is_price_obo) {
    $obo = "OBO";
  }

} else {
  $itemid = false;
}


$header["title"] = _HTML_TITLE_PREFIX." - View Item";
$header['show_sideboxes'] = true;
$header["include_captcha"] = true;
$header["css"] = array('main', 'item', 'listings');
require_once ("includes/header.php");

// if a valid item id was retrieved
if ($itemok && $id) {

  // if there is notification info that needs to appear before everything else
  if ($notification_text)
    echo "<div>\n$notification_text\n</div>";
    
  $itemtitle = ucfirst($itemtitle);
    
  // Get and format item heading data
  // WWU Specific formatting: Include link to mask search
  $mask_seller_name = urlencode($seller_name);
  $mask_seller_name = "<a href=\"http://mask.wallawalla.edu/search?name=$mask_seller_name\" onclick=\"window.open(this.href);
   return false;\">$seller_name</a>";

  if ($email) {
    $email_arr = explode('@', $email);
    $email_user = $email_arr[0];
    $email_domain = $email_arr[1];
    $email_info = "<tr><td class=\"left_col\"><span class=\"b\">Email Address:</span></td>
                       <td>".protect_email($email_user, $email_domain)."<br /></td>
                   </tr>";
  } else {
    $email_info = '';
  }
    
  if ($phone) {
    $phone_info = "<tr><td class=\"left_col\"><span class=\"b\">Phone Number:</span></td>
                       <td>$phone<br /></td>
                   </tr>";
  } else {
    $phone_info = '';
  }
    
 
  if ($location) {
    $location_info = "<tr><td class=\"left_col\"><span class=\"b\">This item is located in or around:</td>
                       <td>{$location}</span></td>
                   </tr>";
  } else {
    $location_info = '';
  }
    
  
?>
<div id="item_heading">
  <h1><?php echo $itemtitle; ?></h1>
  <p>
    <table cellspacing="0" border="0" class="details_table">
      <tr>
          <td class="left_col"><span class="b">Item #: </span></td>
          <td><a href="<?php echo _SITE_URL."/item/$itemid/"; ?>"><?php echo $itemid; ?></a></span></td>
        </tr>
        <tr>
          <td class="left_col"><span class="b">Listing Started: </span></td>
          <td><?php echo $cr_date; ?></span></td>
        </tr>
        <tr>
          <td class="left_col"><span class="b">Listing Ends: </span><br /><br /></td>
          <td><?php echo $expire_date; ?></span><br /><br /></td>
        </tr>
        <tr>
          <td class="left_col"><span class="b">Seller: </span></td>
          <td><?php echo $mask_seller_name; ?></td></tr>
        <?php
          echo $email_info;
          echo $phone_info;
          echo $location_info;
        ?>
    </table>
    <br />
  </p>

<?php
  // FLAGGING/EDITING:
  
  // if logged in user is the seller of this item, show edit/delete controls
  // otherwise, show moderation controls
  if ($user_id == $seller_id) {
?>
  <hr />
  <div id="flagging_container">
    <span class="titleLabel">Modify your listing:</span>
    <ul class="button-boxes">
      <li><a rel="nofollow" href="<?php echo _SITE_URL."/item/$itemid/?mod=edit" ?>">Edit</a></li>
      <li><a rel="nofollow" href="<?php echo _SITE_URL."/item/$itemid/?mod=delete" ?>">Delete</a></li>
    </ul>
  </div>
<?php
  // Allow flagging if logged in user has not already flagged, and is not owner of item
  } elseif ($user_id && (!$has_already_flagged) && ($user_id != $seller_id)) {
?>
  <div id="flagging_container">
    <span class="titleLabel">Flag this item as:</span>
    <ul class="button-boxes">
      <li><a rel="nofollow" href="<?php echo _SITE_URL."/item/$itemid/?flag=spam" ?>">Spam</a></li>
      <li><a rel="nofollow" href="<?php echo _SITE_URL."/item/$itemid/?flag=offense" ?>">Offensive</a></li>
      <li><a rel="nofollow" href="<?php echo _SITE_URL."/item/$itemid/?flag=miscat" ?>">Miscategorized</a></li>
    </ul>
  </div>

<?php
  }
  // End FLAGGING/EDITING SECTION
?>

</div>

<h2>Detailed Item Information</h2>


<?php
// Get and format item details
$price_info = $condition_info = $beds_info = $baths_info = $rent_info = $dep_info = '';

if ($item_type == "Housing")
  $listings_page = '/listings-housing/';
else
  $listings_page = '/listings/';
  
$category_info = "<a href=\""._SITE_URL."$listings_page$category_id\">$subcat_name</a>";

if ($price || $obo) {
  $price_info = "<tr><td class=\"left_col\"><span class=\"b\">Price:</td>
                       <td>{$price} {$obo}</span></td>
                   </tr>";
} elseif ($is_price_free) {
  $price_info = "<tr><td class=\"left_col\"><span class=\"b\">Price:</td>
                       <td>FREE!</span></td>
                   </tr>";
} else {
  $price_info = '';
}

if ($condition) {
  $condition_info = "<tr><td class=\"left_col\"><span class=\"b\">Condition:</td>
                       <td>{$condition}</span></td>
                   </tr>";
}


// Get information for housing items
if ($beds) {
  $beds_info = "<tr><td class=\"left_col\"><span class=\"b\">No. Bedrooms:</td>
                       <td>{$beds} Bedrooms</span></td>
                   </tr>";
}

if ($baths) {
  $baths_info = "<tr><td class=\"left_col\"><span class=\"b\">No. Baths:</td>
                       <td>{$baths} Baths</span></td>
                   </tr>";
}

if ($rent) {
  $rent_info = "<tr><td class=\"left_col\"><span class=\"b\">Rent:</td>
                       <td>$$rent per month</span></td>
                   </tr>";
}

if ($deposit) {
  $dep_info = "<tr><td class=\"left_col\"><span class=\"b\">Deposit:</td>
                       <td>$$deposit</span></td>
                   </tr>";
}

?>
<div id="item_details">
<table cellspacing="0" border="0">
  <tr>
    <td class="left_col"><span class="b">Category:</span></td>
    <td><?php echo $category_info; ?></td>
  </tr>
  <?php
    echo $beds_info;
    echo $baths_info;
    echo $rent_info;
    echo $dep_info;
    echo $price_info;
    echo $condition_info;
  ?>
</table>
</div>
<hr />
<p>
<div class="item_description">
  <?php echo make_html($description); ?>
</div>
</p>

<br />

<?php

  // Get files attached to listing
  // Display up to this number of files

  $MAX_FILES = 6;
  $count = min(count($file_arr), $MAX_FILES); // whichever is less
  for ($i = 0; $i < $count; $i++) {
    $file = $file_arr[$i]['name'];
    $fid  = $file_arr[$i]['id'];
    // TODO: Display image only if it of a certain filetype
    echo "<div class=\"item_file\" style=\"display: inline; float: left;\">";
    echo "<a href=\""._SITE_URL."/fileupload/$file\" onclick=\"document.getElementById('image_$i').src='"._SITE_URL."/fileupload/$file'; return false;\">";
    echo "<img id=\"image_$i\" src=\""._SITE_URL . _FILE_UPLOAD_DIR."/thumbnails/$file\" style=\"padding: 5px;\" />";
    echo "</a>";
    // Show link to delete file, if this is the user's item
    if ($user_id == $seller_id) {
      echo "<br /><a href=\"?rmfile=$fid\">Delete File</a>";
    }
    echo "</div>\n";
  }

  // Show received messages about this item if user is seller
  // Show only if the user is logged in
  if ($user_id && ($user_id == $seller_id)) {
    echo "<div style=\"clear: left;\">";
    echo "<br />";
    echo "<hr />";
    require_once ("includes/myklassica/messages_item.php");
    echo "</div>\n";
    
  } elseif ($user_id && ($user_id != $seller_id)) {
    // If user is logged in, but is not seller, show message form
    
?>
<div style="clear: left;">
  <hr />
  <h3 class="titleLabel">Response to Seller:</h3>
  <p>
  <form name="message_form" id="message_form"  action="<?php echo $_SITE_DOMAIN.$_SERVER["REQUEST_URI"]; ?>" method="post">
  
<?php 
  // If user is not a logged in user, force them to enter their name and address
  if ($public_user) {
    echo "Name: <input type=\"text\" name=\"pub_name\" value=\"{$_POST['pub_name']}\" />
          Email: <input type=\"text\" name=\"pub_email\" value=\"{$_POST['pub_email']}\" />
          <br /><br />";

  }
?>
  <textarea name="message" cols="60" rows="10"><?php echo $_POST['message'];?></textarea><br />
  <input type="checkbox" name="buy"<?php if ($_POST['buy']) { echo ' checked="checked"';}?> />
  I am buying this item, or RSVPing to an event.<br /><br />
<?php
  // If user is not a logged in user, they must prove they are not a bot
  if ($public_user) {
    echo '<p>
    <label for="captcha_word">Enter Word in Image:</label>
    <input type="text" name="captcha_word" id="captcha_word" name="captcha_word" /> 
    <br /><a href="#" onclick="this.blur();new_freecap();return false;" style="text-decoration: none">
      <img src="../../includes/captcha/freecap.php" id="captcha_img" alt="verification_image" /><br />
      Click the image if you are unable to read the word. A new word will be loaded.
    </a>
    <br />
    </p>';
  }
?>

  <input type="hidden" name="item" value="<?php echo $id; ?>" />
  <input type="submit" value="Send" />
  </p>
</div>
  
<?php
  } else {
    // If no user is logged in
    echo "<div style=\"clear: left;\">";
    echo  "<hr />";
    echo "<h3 class=\"titleLabel\">Please login to reply to this posting.</h3>";
    echo "</div>\n";
  }

// if the item is not valid
} else {
  
  if ($deleted) {
    $body_heading = "<h2 class=\"titleLabel\">Item has been marked as deleted.</h2>";
    $body_heading .= "<p><a href=\""._SITE_URL."/\">Return to homepage</a></p>";
    $body_heading .= "<p><a href=\""._SITE_URL."/myklassica/\">Go to My Klassica</a></p>";

  } else {
    $body_heading = "<h2 class=\"errorLabel\">An invalid item was requested.</h2>";
    $body_heading .= "<p><a href=\""._SITE_URL."/\">Return to homepage</a></p>";
    $body_heading .= "<p><a href=\""._SITE_URL."/myklassica/\">Go to My Klassica</a></p>";
  }
  
  echo $body_heading;

}

?>        
  
<?php include("includes/footer.php"); ?>
