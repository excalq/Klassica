<?php

require_once ("includes/klassica.php");
require_once ("includes/forms/populate_selectlists.php");

// Logged in user
$user_id = $_SESSION["auth"]["user_id"];

// If edit of existng item was requested
if ($_GET["edit"]) {
  // Get itemid
  $item_id = $_GET["edit"];
  
  // Create item object
  $kitem = new Klassica_item($dbconn, $item_id);
  $kitem->get_item_data();
  $seller_id = $kitem->get_field("seller_id");

  // Test that logged in user is the owner of this item
  if ($user_id == $seller_id) {
    $item_exists = true;
    
    // populate $_POST variables (These are used to fill in the form fields)
    $_POST['itemtitle']     = $kitem->itemtitle;
    $_POST['item_category'] = $kitem->category_id;
    $_POST['location']      = $kitem->location;$kitem->buyer_id; 
    $_POST['phone']         = $kitem->phone;
    $_POST['email']         = $kitem->email;
    $_POST['description']   = $kitem->description;
    $file_set               = $kitem->get_field("file"); 
    $cr_date                = $kitem->cr_date;
    $expire_date            = $kitem->expire_date;


  } else {
    // Access denied to edit item
    // Print error message
    $sumbission_error .= "You cannot edit this item (Access Denied).";
  }
  unset($kitem);
  unset($seller_id);
}


// Handle form submission
if ($_POST["submit"]) {

  // Clean post vars and copy to local vars
  $item_exists    = clean_input($_POST['item_exists']);
  $item_id        = clean_input($_POST['item_id']);
  $itemtitle      = clean_input($_POST['itemtitle']);
  $item_category  = clean_input($_POST['item_category']);
  $seller_id      = $user_id;
  $location       = clean_input($_POST['location']);
  $phone          = clean_input($_POST['phone']);
  $email          = clean_input($_POST['email']);
  $description    = clean_input($_POST['description']);
  $expire_days    = clean_input($_POST['expire_days']);

  // If existing item was updated, then $expire_days comes from $_POST['expire_days']
  if ($_POST['renew_item']) {
    $expire_days = clean_input($_POST['renew_item']);
  }

  // check for valid referrer
  // remove parameters from uri string
  $referrer = (strtok($_SERVER['HTTP_REFERER'], "?"));

  // check for valid referrer
  $correct_referrer = _SITE_URL."/announce";
  $correct_refurl   = array($correct_referrer.'/',
                          $correct_referrer.'/index.php',
                          $correct_referrer.'/index.html',
                          $correct_referrer.'/index.htm');

  // Include script do the heavy lifting
  // It also retrieves the values from the submitted form
  require ("includes/forms/form_handler.php");
  
  // Accept only if the page came from the correct referrer
  if (in_array($referrer, $correct_refurl)) {
       
    // put posted form data into an array
    
    $field_data = array('item_id'       => $item_id,
                        'title'         => $itemtitle, 
                        'category'      => $item_category, 
                        'seller_id'     => $seller_id, 
                        'location'      => $location,
                        'phone'         => $phone, 
                        'email'         => $email, 
                        'description'   => $description, 
                        'expire_days'   => $expire_days);
    

    // verify required fields, return boolean variable $fields_isvalid
    $fields_isvalid = php_check_formfields($field_data);
    
    if ($fields_isvalid) {
      
      // validate data of form, return boolean $data_isvalid
      $data_isvalid = php_check_formdata($field_data);

      if ($data_isvalid) {
      
        // save uploaded images
        $files_status = upload_files();
        
        $files_isvalid = true; // If there were no files uploaded, set pass test unconditionally
        $filenames     = array();
        foreach ($files_status as $fdata) {
          if ($fdata['error']) {
            $files_isvalid = false;
            $files_errors  = '<br />'.$fdata['error']; // build error message from each failure
            $filenames     = false;
          } else {
            $filenames[] = $fdata['file'];
          }
        }

        if ($files_isvalid) {
          // attempt to store data in database, also store $file array
          // THIS IS THE MAIN INSERTING FUCTION!
            if ($item_exists) {
              // vardumper($field_data);
              $saved_item_id = update_item($dbconn, $field_data, $filenames); // edit old item
            } else {
              $saved_item_id = store_item($dbconn, $field_data, $filenames); // create new item
            }
            if ($saved_item_id) {
                $form_submitted_ok = true;
            }

          if (!$form_submitted_ok) { // save operation failed
            $sumbission_error = "There was a problem saving your listing. Please try again, or contact administrator";
          }
          
        } else { // If uploaded files were named improperly
          $sumbission_error = "You must upload only .jpeg, .jpg, .gif, .png, .pdf files, or other allowed documents.";
        }

      } else { // validation of fields failed
        $sumbission_error = "There was a problem processing your listing. Please try again.";
      }

    } else { // not all required fields were filled
      $sumbission_error = "Please fill in all required fields";
    }
    
  } else { // bad referrer, redirect them to proper form
    header ("location: $correct_referrer");
  }
} // POST?


/* Useful Documentation on methods used in writing this page */
/* http:/simon.incutio.com/archive/2003/06/17/theHolyGrail */
// http:/particletree.com/features/degradable-ajax-form-validation/
// http:/www.yourhtmlsource.com/javascript/objectsproperties.html
// http:/www.quirksmode.org/
// http:/www.quirksmode.org/dom/error.html

$header["title"] = _HTML_TITLE_PREFIX." - Post Announement";
$header["bodyid"] = "announce";
$header["css"] = array('main', 'forms','categories');
$header["javascript"] = array('announceform');
$header["auth"] = true;
$header["include_calendar"] = false;
$header["include_captcha"] = true;

include ("includes/header.php");


?>

        <h2 class="titleLabel">Post an Annoucement, Service, Employment, or Housing Listing</h2>
        <?php

// if not logged in, display general information
if (!$_SESSION["auth"]["authenticated"]) {

?>
  
  <p class="please-login">
  Please log in to post an announcement to the Klassica listings.
  </p>
  <p>
  Use your Walla Walla University campus account to log in to klassica.
  The login box is in the upper right corner of this page.
  </p>
<?php

  } else {

    /* Display Form Submit Confirmation */
    if ($form_submitted_ok) {
      echo "<h2 class=\"notifyLabel\">Thank you for posting an item</h2>";
      echo "<p><a href=\""._SITE_URL."/item/$saved_item_id/\">View your item listing</a></p>";
      echo "<p><a href=\""._SITE_URL."/\">Return to homepage</a></p>";
      echo "<p><a href=\""._SITE_URL."/announce/\">Submit another item</a></p>";
    } else {
      if ($sumbission_error) {
        echo "<h2 class=\"errorLabel\">$sumbission_error</h2>";
      }
      // Form is fresh, and has not been submitted

?>

        <p>
        Please enter information about the announcement you want to post. Be aware that your listing will
        be visible to the general public. You agree that anything you post will be in agreement with Walla Walla University's policies, and <a href="<?php echo _SITE_URL."/about/"; ?>">Klassica's Guidelines</a>.
        </p>
        <p>
        (*) indicates a required form item.
        </p>
<!-- Category -->
  <form name="postform" id="postform" class="cssform" action="<?php echo _SITE_URL."/announce/"; ?>" method="post" onsubmit="return checkForm();" enctype="multipart/form-data" accept-charset="UTF-8">

  <p>
  <label for="item_category">Category:</label>
    <span class="bi">Choose from Announements, Services, or Housing</span><br />
    <?php

      // function from "../include/populate_selectlists.php"
      get_subcategories($dbconn, 'announce', $_POST['item_category']);
    ?>
  </p>

<!-- Item Title -->
  <p>
  <label for="itemtitle">Item Title:</label>
  <span class="i">What is the item? Give a descriptive and brief title</span><br />
  <input type="text" name="itemtitle" id="itemtitle" class="text" value="<?php echo $_POST['itemtitle']; ?>" />
  *
  </p>

<!-- Location -->
  <p>
  <label for="location">Event or Item Location:</label>
  <span class="i">Examples: Alaska Room, Village, Portland, etc.</span><br />
  <input type="text" name="location" id="location" class="text" value="<?php echo $_POST['location']; ?>" />
  </p>

<!-- Contact Method -->
  <p>
  <label for="phone_pub">Contact Information:</label>
  <span class="b notifyLabel">Klassica has a built-in messaging system, so these are optional!</span><br />
       
    Email Address:<br />
    <input type="text" name="email" id="email" class="contact" value="<?php echo $_POST['email']; ?>" /><br />
  
    Phone Number:<br />
    <input type="text" name="phone" id="phone" class="contact" value="<?php echo $_POST['phone']; ?>" /><br />
  </p>

<!-- Expiration -->
  <p>
<?php
  
  // If updating an existing item, permit only a seven day renewal
  if ($item_exists) {
    echo '<label for="renew_item">Renew item:</label>';
    if ($_POST['renew_item']) {
      $checked = " checked=\"checked\"";
    }
    
    $expires  = date("F j, Y", strtotime($expire_date));
    $nextweek = date("F j, Y", mktime(0, 0, 0, date("m") , date("d")+7, date("Y")));
    
    echo "<span class=\"i\">Your listing expires on $expires</span><br />";
    echo '<span>If your item is ending soon, you may renew it.</span><br /><br />';

    echo "<input type=\"checkbox\" name=\"renew_item\" id=\"renew_item\" class=\"checkbox\" value=\"renew\"$checked />\n";
    echo "Renew item for seven days (Will be listed until: ".$nextweek.")";
    
  } else {
    echo '<label for="expire_days">Keep Listing for:</label>';
    echo '<select name="expire_days" id="expire_days" class="selectform">';

        // display dropdown of choice for listing expiration
        // admin can set max and default
        $default_exp = _DEFAULT_DAYS_EXPIRATION;
        $max_exp = _MAX_DAYS_EXPIRATION;
        // if form has been submitted,  saved value
        if ($expire_days) {
          $default_exp = $_POST['expire_days'];
        }
        for ($i=1; $i<=$max_exp; $i++) {
          if ($i == $default_exp) {$s = "selected=\"selected\"";}
          echo "<option value=\"$i\" $s>$i</option>\n";
          $s = '';
        }

    echo '</select>';
    echo 'Days*';
  }
?>
  </p>

<!-- Main Item Description -->
  <p>
  <label for="description">Item Details:</label>
  <span class="i">Please describe the item in detail, providing useful information.*<br />
  Allowed HTML tags are: &lt;p&gt;,&lt;br&gt;,&lt;a&gt;,&lt;b&gt;&lt;strong&gt;,&lt;i&gt;,&lt;em&gt;</span><br />
  <textarea name="description" id="description" rows="8" cols="40"><?php echo $_POST['description']; ?></textarea>
  <br />
  <span class="b">Attach Images (optional):</span><br />
   <input type="hidden" name="MAX_FILE_SIZE" value="10485760" /> 
   Upload an image: <input type="file" name="item_file[]" class="file_upload" value="<?php echo $_POST['item_photo_1']; ?>" /><br />
   Upload an image: <input type="file" name="item_file[]" class="file_upload" value="<?php echo $_POST['item_photo_2']; ?>" /><br />

  <?php
  if ($file_set) {
    echo "<ul class=\"files_list\">";
    foreach($file_set as $file_loc) {
      echo "<li class=\"b\">Attached File: <a href=\"../fileupload/$file_loc\">$file_loc</a></li>\n";
    }
    echo "</ul>";
  }
  ?>
  
  </p>

  
  <p>
  <label for="terms">Agree to Terms?</label>
  <input type="checkbox" name="terms" id="terms" class="checkbox" <?php if ($_POST['terms']) {echo "checked=\"checked\""; }?>/>
  This posting is not spam, inappropriate, or offensive. You may not discriminate against persons or groups, or violate any applicable laws. See <a href="<?php echo _SITE_URL;?>/about/">Klassica's Guidelines</a>.<br />
  </p>
  
  <p>
<?php
  if ($item_exists) {
    echo '<input type="hidden" name="item_exists" value="true" />'."\n";
    echo '<input type="hidden" name="item_id" value="'.$item_id.'" />'."\n";
  }
?>
  <input type="submit" name="submit" value="Post Item" />
  <span id="error-box"></span>
  </p>

  </form>

<?php

      } // end else
    } // end else

?>

<?php include ("includes/footer.php"); ?>
