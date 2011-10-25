<?php

require_once ("../includes/klassica.php");

// Logged in user
$user_id = $_SESSION["auth"]["user_id"];
$email = $_SESSION["auth"]["email"];

// Find out if user has admin status
$kuser   = new Klassica_user($dbconn, $user_id);
$kuser_role = $kuser->get_user_role();
if ($kuser_role == 'housing' || $kuser_role == 'admin')
  $is_housing_admin = TRUE;
else
  $is_housing_admin = FALSE;
  
  $listed_date = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d"), date("Y")));

// If edit of existng item was requested
if ($_GET["edit"]) {
  // Get itemid
  $item_id = $_GET["edit"];
  
// TODO: Fix this, or even better, convert editing to an inplace edit on item.php
//   // Create item object
//   $kitem = new Klassica_item($dbconn, $item_id);
//   $kitem->get_item_data();
//   $seller_id = $kitem->get_field("seller_id");
// 
//   // Test that logged in user is the owner of this item
//   if ($user_id == $seller_id) {
//     $item_exists = true;
//     
//     // populate $_POST variables (These are used to fill in the form fields)
//     $_POST['itemtitle']     = $kitem->itemtitle;
//     $_POST['item_category'] = $kitem->category_id;
//     $_POST['location']      = $kitem->location;$kitem->buyer_id; 
//     $_POST['phone']         = $kitem->phone;
//     $_POST['email']         = $kitem->email;
//     $_POST['description']   = $kitem->description;
//     $file_set               = $kitem->get_field("file"); 
//     $cr_date                = $kitem->cr_date;
//     $expire_date            = $kitem->expire_date;
// 
//   } else {
    // Access denied to edit item
    // Print error message
    $sumbission_error .= "Sorry, you cannot edit housing items, as this part of Klassica under maintenance.";
//   }
  unset($kitem);
  unset($seller_id);
}


// Handle form submission
if ($_POST["submit"]) {

//   vardumper($_POST);

  $saved_item_id = array();
  foreach($_POST['cname'] as $key => $value) {
  
    // Clean post vars and copy to local vars
    $item_category = clean_input($_POST['item_category']); // not part of array
    $expire_days   = clean_input($_POST['expire_days']);
    
    $item_exists   = clean_input($_POST['item_exists'][$key]);
    $item_id       = clean_input($_POST['item_id'][$key]);
    $itemtitle     = clean_input($_POST['itemtitle'][$key]);
    $seller_id     = $user_id;
    $location      = clean_input($_POST['location'][$key]);
    $contact_name  = clean_input($_POST['cname'][$key]);
    $phone         = clean_input($_POST['phone'][$key]);
    $email         = clean_input($_POST['email'][$key]);
    $beds          = clean_numbers(clean_input($_POST['beds'][$key]));
    $baths         = clean_numbers(clean_input($_POST['baths'][$key]));
    $rent          = clean_numbers(clean_input($_POST['rent'][$key]));
    $deposit       = clean_numbers(clean_input($_POST['deposit'][$key]));
    $description   = clean_input($_POST['description'][$key]);
    $date_listed   = mysql_date(clean_input($_POST['date_listed'][$key]));
    
    // If existing item was updated, then $expire_date comes from $_POST['expire_date']
    if ($_POST['renew_item'][$key]) {
      $expire_date = clean_input($_POST['renew_item'][$key]);
    }
  
    // check for valid referrer
    // remove parameters from uri string
    $referrer = (strtok($_SERVER['HTTP_REFERER'], "?"));
  
    // check for valid referrer
    $correct_referrer = _SITE_URL."/housing";
    $correct_refurl = array($correct_referrer.'/',
                            $correct_referrer.'/index.php',
                            $correct_referrer.'/index.html',
                            $correct_referrer.'/index.htm');
  
    // Include script do the heavy lifting
    // It also retrieves the values from the submitted form
    require_once ("../includes/forms/form_handler.php");
    
    // Accept only if the page came from the correct referrer
    if (in_array($referrer, $correct_refurl)) {
        
      // put posted form data into an array  
      $field_data = array('item_id'      => $item_id,
                          'title'        => $itemtitle, 
                          'category'     => $item_category, 
                          'seller_id'    => $seller_id, 
                          'location'     => $location,
                          'phone'        => $phone, 
                          'email'        => $email, 
                          'description'  => $description,
                          'contact_name' => $contact_name, 
                          'beds'         => $beds, 
                          'baths'        => $baths, 
                          'rent'         => $rent, 
                          'deposit'      => $deposit, 
                          'date_listed'  => $date_listed, 
                          'expire_days'  => $expire_days);
      
      // verify required fields, return boolean variable $fields_isvalid
      if ($_POST['looking-form'])
        $validation = 'hlooking';
      else
        $validation = 'housing';
        
      $fields_isvalid = php_check_formfields($field_data, $validation);
      
      if ($fields_isvalid) {
        
        // validate data of form, return boolean $data_isvalid
        $data_isvalid = php_check_formdata($field_data);

        // vardumper($field_data);

        if ($data_isvalid) {
          // store stuff in database

          // save uploaded images
          $files_status = upload_files($key);
          
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
                $saved_item_id[] = update_item($dbconn, $field_data, $filenames); // edit old item
              } else {
                $saved_item_id[] = store_item($dbconn, $field_data, $filenames); // create new item
              }
              if ($saved_item_id) {
                  $form_submitted_ok = true;
              }

            if (!$form_submitted_ok) { // save operation failed
              $sumbission_error = "There was a problem saving your listing. Please try again, or contact administrator";
            }
            
          } else { // If uploaded files were named improperly
            $sumbission_error  = "There was a problem uploading attached files";
            $sumbission_error .= $files_errors;
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
  
  
  } // end foreach
} // end if $_POST


/* Useful Documentation on methods used in writing this page */
/* http:/simon.incutio.com/archive/2003/06/17/theHolyGrail */
// http:/particletree.com/features/degradable-ajax-form-validation/
// http:/www.yourhtmlsource.com/javascript/objectsproperties.html
// http:/www.quirksmode.org/
// http:/www.quirksmode.org/dom/error.html

$header["title"]            = _HTML_TITLE_PREFIX." - Housing";
$header["bodyid"]           = "housing";
$header["css"]              = array('main', 'forms', 'housing');
$header["javascript"]       = array('announceform', 'housingform');
$header["auth"]             = true;
$header["include_calendar"] = true;
$header["include_captcha"]  = true;

include ("../includes/header.php");


?>

        <h2 class="titleLabel">Announce and Find Apartments, Houses, and Roommates</h2>
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
      
      foreach($saved_item_id as $key => $iid) {
        $i = $key + 1;
        echo "<p><a href=\""._SITE_URL."/item/$iid/\">View listing #$i</a></p>";
      }
      
      echo "<p><a href=\""._SITE_URL."/\">Return to homepage</a></p>";
      echo "<p><a href=\""._SITE_URL."/housing/\">Submit more listings</a></p>";
    } else {
      if ($sumbission_error) {
        echo "<h2 class=\"errorLabel\">$sumbission_error</h2>";
      }
      // Form is fresh, and has not been submitted

?>

  <p>
  Please enter information about the listing you want to post. Be aware that your listing will
  be visible to the general public. You agree that anything you post will be in agreement with Walla Walla University's policies, and <a href="<?php echo _SITE_URL."/about/"; ?>">Klassica's Guidelines</a>.
  </p>
  
  <p>
    <span class="bi">Please tell us what you would like to do:</span>
    
    <ul class="form_links">
      <li><a href="#1" onclick="javascript:show_form('housing_form_listings');">I want to list an apartment or house</a></li>
      <li><a href="#2" onclick="javascript:show_form('housing_form_requests');">I am looking for an apartment, house, or room.</a></li>
      <li><a href="#3" onclick="javascript:show_form('housing_form_roommates');">I am looking for a roommate.</a></li>
      <?php
      if ($is_housing_admin)
      {
        echo '<li><a href="#3" onclick="javascript:show_form(\'housing_form_crlinfo\'); return false;">I want to edit the Community Rent List Infomation.</a></li>';
      }
      ?>
    </ul>
  </p>

  <!-- Listing an Apartment/House/Room -->
  <div id="housing_form_listings">
    <a name="1"></a>
    <p>
      <label for="item_category">Apartment Listings:</label><br />
  
      <?php include ('includes/apartments_form_table.php'); ?>
      
    </p>
    <p>
      <label for="item_category">House Listings:</label><br />
      
      <?php include ('includes/houses_form_table.php'); ?>
      
    </p>
  </div>
  
  <!-- Looking for a Apartment/House/Room -->
<form name="looking-form" action="" method="post">
  <input type="hidden" name="looking-form" value="true">
  <input type="hidden" name="itemtitle[]" value="Housing Wanted">
  <input type="hidden" name="item_category[]" value="92">
  <div id="housing_form_requests">
    <a name="2" />
    <p>
      
      <label for="item_category">Housing Request:</label>
      
      <span class="bi">Your Name</span><br />
      <input type="text" name="cname[]" value="<?php echo $_SESSION["auth"]["fullname"]; ?>" class="txtbox" /><br />
      
      <span class="bi">Contact Information</span><br />
      <input type="text" name="email[]" value="<?php echo $_SESSION["auth"]["email"]; ?>" class="txtbox" /><br /><br />
      
      <span class="bi">Please describe what you are looking for.
      Are you looking for an apartment, a house, a room?
      </span><br />
      
      <textarea class="house-request-textarea" name="description[]"></textarea>
    </p>
    <p>
      <input type="submit" name="submit" class="submit" value="Post Listings" />
    </p>
  </div>
  </form>
  
  <!-- Roommate Finder -->
  <form name="looking-form" action="" method="post">
  <input type="hidden" name="looking-form" value="true">
  <input type="hidden" name="itemtitle[]" value="Roommate Wanted">
  <input type="hidden" name="item_category[]" value="93">
  <div id="housing_form_roommates">
    <a name="3" />
    <p>
      
      <label for="item_category">Roommate Finder:</label>
      
      <span class="bi">Your Name</span><br />
      <input type="text" name="cname[]" value="<?php echo $_SESSION["auth"]["fullname"]; ?>" class="txtbox" /><br />
      
      <span class="bi">Contact Information</span><br />
      <input type="text" name="email[]" value="<?php echo $_SESSION["auth"]["email"]; ?>" class="txtbox" /><br /><br />
      
<!--      <span class="bi">Your Gender</span><br />
      <select name="rm_gender">
        <option value="f">Female</option>
        <option value="m">Male</option>
      </select>
      <br /><br />-->
      
      <span class="bi">Please describe yourself, and what you are looking for in a roommate.<br />
        Indicate where you live, and if you are willing to move.
      </span><br />
      
      <textarea class="house-request-textarea" name="description[]"></textarea>
    </p>
    <p>
      <input type="submit" name="submit" class="submit" value="Post Listings" />
    </p>
  </div>
  </form>
  
  <div id="housing_form_extra">
    <p>
    By submitting these items, you agree this posting is not spam, inappropriate, or offensive. You may not violate WWU campus policy, or any applicable laws, especially conserning <a href="http://www.fairhousinglaw.org/fair_housing_laws/" target="_blank">housing and discrimination.</a> See <a href="<?php echo _SITE_URL;?>/about/">Klassica's Guidelines</a>.<br />
    </p>
  
  <?php
  
  //   if ($item_exists) {
  //     echo '<input type="hidden" name="item_exists" value="true" />'."\n";
  //     echo '<input type="hidden" name="item_id" value="'.$item_id.'" />'."\n";
  //   }
  ?>
  
  
  </div>

<?php

      } // end else
    } // end else


  // If JS-enabled, hide the page's forms until one of the selection links is clicked
  echo 
  '<script type="text/javascript">
    set_form_defaults();
  </script>';

 include ("../includes/footer.php"); 
?>