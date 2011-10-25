<form name="houses-form" action="" method="post" enctype="multipart/form-data">
  <input type="hidden" name="housing-form" value="true">
  <input type="hidden" name="item_category" value="91">
  <table border="0" cellpadding="0" cellspacing="0" id="houses" class="listings">
    <th>Beds</th>
    <th>Baths</th>
    <th>Rent</th>
    <th>Deposit</th>
    <th>Description</th>
    <th>Contact Name</th>
    <th>Contact Phone</th>
    <th>Contact Email</th>
    <th>Date Listed</th>
    <tr class="odd">
      <td><input type="text" name="beds[]" value="" class="txtbed" size="" /></td>
      <td><input type="text" name="baths[]" value="" class="txtbath" size="" /></td>
      <td><input type="text" name="rent[]" value="" class="txtrent" size="" /></td>
      <td><input type="text" name="deposit[]" value="" class="txtdeposit" size="" /></td>
      <td>
        <input type="text" name="itemtitle[]" value="" class="txttitle" size="" />
        <textarea name="description[]" class="txadescr"></textarea></td>
      </td>
      <td><input type="text" name="cname[]" value="" class="txtcname" size="" /><br />
          <br />
          Add a file:<br />
          <input type="hidden" name="MAX_FILE_SIZE" value="3000000" />
          <input type="file" name="item_file[]" value="" class="fileupbox" size="" 
          title="Edit item later to attach more files" />
          <div style="float:left; color: #888;">(Edit item later to add more files)</div>
          </td>
      <td><input type="text" name="phone[]" value="" class="txtphone" /></td>
      <td><input type="text" name="email[]" value="" class="txtphone" /></td>
      <td><input type="text" name="date_listed[]" id="calendar_popup[]" class="calendar_input" value="<?php echo date('M d, Y')?>" /></td>
    </tr>
    



    <tr class="odd">
      <td colspan="9" style="text-align: left;">
        <div class="h-form-buttons">
          <input type="submit" name="submit" class="submit" value="Post Listings" />
          <input type="button" value="Add another row" onclick="add_table_row('houses');" />
          <input type="button" value="Delete last row" onclick="del_table_row('houses');" />
        
          <label for="expire_days">Keep these Listing for:</label>
          <select name="expire_days" id="expire_days" class="selectform">
          <?php
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
          ?>
        </select> Days*
        </div>
      </td>
    </tr>
  </table>
</form>