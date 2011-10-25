<?php

$mail_destination = "k2www@klassica.com";

require_once ("includes/klassica.php");
require ("includes/forms/handle_email.php");

// If contact form was submitted

if ($_POST["submit"]) {
  // Retrieve values from post
  $name = $_POST["name"];
  $email = $_POST["email"];
  $subject = $_POST["subject"];
  $message = $_POST["message"];
  
  $subject_opt = "<option value=\"$subject\" selected=\"selected\">$subject</option>\n";
  
  $contact_form_ok = false;
  
  // check for valid referrer
  $correct_referrer = _SITE_URL."/contact";
  $correct_refurl = array($correct_referrer.'/',
                          $correct_referrer.'/index.php',
                          $correct_referrer.'/index.html',
                          $correct_referrer.'/index.htm');

  // Accept only if the page came from the correct referrer
  if (in_array($_SERVER["HTTP_REFERER"], $correct_refurl)) {

    // Verify captcha image
    if(!empty($_SESSION['freecap_word_hash']) && !empty($_POST['captcha_word']))
    {
      // all freeCap words are lowercase.
      // font #4 looks uppercase, but trust me, it's not...
      if($_SESSION['hash_func'](strtolower($_POST['captcha_word']))==$_SESSION['freecap_word_hash'])
      {
        // reset freeCap session vars
        // cannot stress enough how important it is to do this
        // defeats re-use of known image with spoofed session id
        $_SESSION['freecap_attempts'] = 0;
        $_SESSION['freecap_word_hash'] = false;

        // Make sure all required fields were entered
        if ($name && $email && $subject && $message) {
          $contact_form_ok = true;
          // run validation test - to prevent abuse
          if (!(is_valid_email($email))) {
            $email = "";
            $contact_form_ok = false;
            $bad_email = true;
          }
          
          // Protect against injection attack
          $formitem = array('name', 'email', 'subject', 'message');
          for ($i=0; $i<count($formitem); $i++) {
            $item = ${$formitem[$i]};
            // set $check_all to false if handling "$message"
            $check_all = true;
            ($item == "message")? true: $check_all = false;
            if (injection_chars($item, $check_all)) {
              $contact_form_ok = false;
              ${$formitem[$i]} = "";
            }
          }
          
          if ($contact_form_ok) {
            $name = trim(strip_colons($name));
            $email = trim(strip_colons($email));
            $subject = trim(strip_colons($subject));
            $smessage = trim($smessage);
          
            // send message
            $senders_ip = $_SERVER["REMOTE_ADDR"];
            $name = ucwords($name);
            $smessage = "$message\n-------------\n\nIP Address: $senders_ip;";
            if (mail("$mail_destination", "$subject", "$smessage", "From: $name <$email>\nReply-To: $email")) {
              // message sent ok
              $sent_ok = true;
            } else { // message failed
              $sumbission_error = "Sending message failed. Please try again, or contact administrator";
            }
          } else { // validation of fields failed (probably injection or invalid email address)
            if ($bad_email) {
              $sumbission_error = "Your email address appears to be invalid, please enter it again.";
            } else {
              $sumbission_error = "There was a problem processing your message. Please try again.";
            }
          }
        } else { // not all required fields were filled
          $sumbission_error = "Please fill in all required fields";
        }
      } else {
        $sumbission_error = "You did not enter the verification word correctly.";
      }
    } else {
       $sumbission_error = "You did not enter the verification word correctly.";
    } // end captcha test
  } else { // bad referrer, redirect them to proper form
    header ("Location: "._SITE_URL . "/contact");
  }
}


$header["title"] = _HTML_TITLE_PREFIX." - Contact";
$header["bodyid"] = "contact";
$header['show_sideboxes'] = true;
$header["css"] = array('main', 'forms','categories');
$header["auth"] = true;
$header["include_captcha"] = true;

include("includes/header.php");

?>

<?php
        
/* Display Form Submit Confirmation */
if ($sent_ok) {
  echo "<h2 class=\"notifyLabel\">Thank you for sending us a message. We value your input, and will use it to improve Klassica.</h2>";
      echo "<p><a href=\""._SITE_URL."/\">Return to homepage</a></p>";
      echo "<p><a href=\""._SITE_URL."/contact/\">Return to contact form</a></p>";
} else {
  echo "<h2 class=\"errorLabel\">$sumbission_error</h2>";

?>
  <p class="titleLabel">Klassica Website Feedback and Comments</p>
  <p>
  We appreciate your questions, comments, and feedback. We will respond to your question as soon as we possibly can. 
  Your email address will not be used for commercial purposes or given to a third-party.
  </p>
  <p class="i">
  All fields are required.
  </p>

	<form id="contactform" class="cssform" action="<?php echo _SITE_URL."/contact/"; ?>" method="post" onsubmit="return validateEmailForm();" enctype="multipart/form-data" accept-charset="UTF-8">

  <p id="foo">
    <label for="name">Name:</label>
    <span class="i">Enter your full name</span><br />
    <input type="text" name="name" id="name" class="text" value="<?php echo $name;?>" />
  </p>

  <p>
    <label for="email">Email Address:</label>
    <input type="text" name="email" id="email" class="text" value="<?php echo $email;?>" />
  </p>

	<p>
	  <label for="subject">Regarding:</label>
    <select id="subject" name="subject">
      <?php if ($subject_opt) {echo $subject_opt;} ?>
      <option value="General Question">General Question</option>
      <option value="Buying and Selling">Buying and Selling</option>
      <option value="User Accounts">User Accounts</option>
      <option value="Listing Moderation">Flagging and Moderation</option>
      <option value="Suggestions">Suggestions</option>
      <option value="Website Issue">Website Issue</option>
      <option value="Project Infomation">Project Info</option>
      
    </select><br />
	</p>

  <p>
  <label for="message">Request or Comment:</label>
  <textarea name="message" id="message" rows="10" cols="35"><?php echo $message;?></textarea>
  </p>

  <p>
  <label for="captcha_word">Enter Word in Image:</label>
  <input type="text" name="captcha_word" id="captcha_word" name="captcha_word" /> 

  <br /><a href="#" onclick="this.blur();new_freecap();return false;" style="text-decoration: none">
    <img src="../includes/captcha/freecap.php" id="captcha_img" alt="verification_image" /><br />
    Click the image if you are unable to read the word. A new word will be loaded.
  </a>
  <br />
  </p>
	
	<div style="margin-left: 150px;">
	<input type="submit" name="submit" value="Submit" /> <input type="reset" value="Reset" />
	</div>

	</form>

<?php 
}


include("includes/footer.php"); ?>
