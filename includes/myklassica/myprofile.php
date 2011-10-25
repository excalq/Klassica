<?php

  echo '<div id="profile_form">
        <h2>My Profile (This section does not work yet!)</h2>
        <form class="cssform">
        <p>
          <label for="profile_fname">First Name<br /> Last Name:</label>
          <input type="text" id="profile_fname" class="halftext" value="" />
          <input type="text" id="profile_lname" class="halftext" value="" /><br />
        </p>
        
        <p>
          <label for="profile_lname">Email:</label>
          Klassica allows people to send messages over the web without showing
          the recipient&quot;s email address. This is for privacy and to prevent spam.
          We suggest using this method, however in some listings you may wish to
          show your email address or phone number to viewers.<br />
          <input type="checkbox" name="profile_contact" id="profile_email_check" class="checkbox" value="email_check" />
          Show my email address publicly by default (Scrambled to prevent spam)<br />
          <input type="text" id="profile_email" class="text" value="user@domain.com" onfocus="clearDefaultText(this);" />
        </p>
        
        <p>
          <label for="item_title">Phone:</label>
          <input type="checkbox" name="profile_contact" id="profile_phone_check" class="checkbox" value="phone_check" />
          Show my phone number publicly by default<br />
          <input type="text" id="profile_phone" class="text" value="(555) 555-5555" onfocus="clearDefaultText(this);" />
        </p>
        
        <p>
          <label for="item_title">Location:</label>
          <input type="text" id="profile_lname" class="text" value="" />
        </p>
        
        <p>
          <label for="profile_photo">Photo: <br />(Optional)</label>
          Image should be 120x160 pixels.<br />
          <img src="../images/person.png" /><br />
          <input type="hidden" name="max_file_size" value="1048576" /> 
          <input type="file" name="profile_photo" id="profile_photo" class="file_upload" /><br />
        </p>
        <p>
          <label for="item_desc">About Me:</label>
          <textarea id="profile_about_me" rows="8" cols="40"></textarea><br />
        </p>
        
        <p>
          <input type="submit" value="Update Profile">
        </p>
        
        </form>
        </div>
        ';
?>