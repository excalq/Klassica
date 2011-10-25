<?php
include ("../../includes/klassica.php");

$header["title"]            = _HTML_TITLE_PREFIX." - Housing";
$header["bodyid"]           = "housing";
$header["css"]              = array('main', 'forms', 'housing');
$header["javascript"]       = array('announceform', 'housingform');
$header["auth"]             = true;
$header["include_calendar"] = true;
$header["include_captcha"]  = true;

include ("../../includes/header.php");

if ($_POST['housing-form']) {
  
  vardumper($_POST);
  
} else {
  
?>
  
  <html>
  <head>
  
  <script type="text/javascript" src="dynamic_housing_form.js"></script>
  </head>
    <body>
      <form name="apartment-form" action="housing.php" method="post">
        <input type="hidden" name="housing-form" value="true">
        <div class="cssform">
          <table id="apartments" class="listings" border="0" cellpadding="0" cellspacing="0" class="listings">
            <th>Beds</th>
            <th>Baths</th>
            <th>Rent</th>
            <th>Deposit</th>
            <th>Description</th>
            <th>Contact Name</th>
            <th>Contact Phone</th>
            <th>Date Listed</th>
            <tr class="odd">
              <td><input type="text" name="beds[]" value="1" class="txtbed" size="" /></td>
              <td><input type="text" name="baths[]" value="1" class="txtbath" size="" /></td>
              <td><input type="text" name="rent[]" value="$350" class="txtrent" size="" /></td>
              <td><input type="text" name="deposit[]" value="$300" class="txtdeposit" size="" /></td>
              <td><textarea name="descr[]" class="txadescr">A 9th floor apartment with a great view</textarea></td>
              <td><input type="text" name="cname[]" value="John Doe" class="txtcname" size="" /></td>
              <td><input type="text" name="phone[]" value="(509)-555-5555" class="txtphone" /></td>
              <td><input type="text" name="date[]" id="calendar_popup[]" class="calendar_input" value="Feb 12, 2008" /></td>
            </tr>
          </table>
        </div>
        <div class="h-form-buttons">
          <input type="submit" class="submit" value="Post Listings" />
          <input type="button" value="Add another row" onclick="add_table_row('apartments');" />
          <input type="button" value="Delete last row" onclick="del_table_row('apartments');" />
        </div>
      </form>
    </body>
  </html>

<?php
}

include ("../../includes/footer.php");
?>