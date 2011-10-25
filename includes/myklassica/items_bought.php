<?php
  $user_id = $_SESSION["auth"]["user_id"];
  $user_id = $_SESSION["auth"]["user_id"];
  
  // How many previous days items to show in this table (default is last 30 days (30))
  $SHOW_PREVIOUS_N_DAYS = 30;
  $buy_listings_start_date = $exptime = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d")-$SHOW_PREVIOUS_N_DAYS, date("Y")));
  
  $klistings = new Klassica_listing($dbconn);
  
  // show 10 messages at a time
  $klistings->set_limit(10);
  // allow expired items
  $klistings->set_show_expired();
 
  
  // If other date is requested, show items from after that date
  if ($_POST['buy_start_date']) {
    $buy_listings_start_date = $_POST['buy_start_date'];
  }
  
  // Show items starting at the specified date (default is 30 days ago)
  $klistings->set_date_range($buy_listings_start_date, false);
 
  // set order (by column) on request
  if ($_POST['buy_order_by'] && $_POST['buy_order_dir'])
    // switch order
    $order_direction = ($_POST['buy_order_dir'] == 'DESC')? 'ASC' : 'DESC';
    // set order_by
    $klistings->set_orderby($_POST['buy_order_by'], $order_direction);
  
  // advance page on request (start at page 0)
  $page = $_POST['buy_page'];
  if ($page)
    $klistings->set_viewpage($page);
  
  // Fetch array of messages
  $klistings->search_by_buyer($user_id);

?>
            
<div>
  <h3 class="titleLabel table_title">Items: Bought or Accepted</h3>
  <div id="table_container">
      <table class="messagestable">
        <tr class="header">
          <th class="th_title">Item</th>
          <th class="th_textline">Description</th>
          <th class="th_date">Posted</th>
        </tr>
      
<?php
  $result_count = $klistings->get_result_count();
  if ($result_count < 1) {
    echo "<tr class=\"odd\">\n<td colspan=\"3\">
            <span style=\"color: #444;\">No items were found.</span>
          </td>\n</tr>\n</table>\n</div>";
  } else {
    //gets a row and assigns mysql fields to vars, then prints out table with the data.
    $items_array = $klistings->get_item_results();
    
    foreach($items_array as $item)
    {
      // Show mark if buy_flag was set
      $buy = ($item['buy_flag'])? "X" : "";
      
      // alternating row colors
      $tr_class = (++$c%2)? " class=\"odd\"" : "";
      echo "<tr$tr_class>\n";
      echo "\t<td class=\"title\"><a href=\"../item/{$item['id']}/\">{$item['itemtitle']}</a></td>\n";
      echo "\t<td class=\"textline\">{$item['description']}...</td>\n";
      echo "\t<td class=\"date\">{$item['date']}</td>\n";
      echo "\t<tr>";
    }


?>
      </table>
    </div>
    <form name="buy_table1" id="buy_table1" method="post" action="<?php echo _SITE_PATH."/myklassica/"; ?>">
      <select type="select" name="buy_page">
        <?php
          // Get as many pages as the kmsg class calculates
          for ($i = 1; $i<=$klistings->get_page_count(); $i++) {
            // restore previously selected option (by ternary compare of $_POST var)
            $selected = ($_POST['buy_page'] == $i)? " selected=\"selected\"" :  "";
            
            echo "\t<option value=\"$i\" $selected>$i</option>\n";

           }
           
          /* Also, these two forms must be seperate, because you cannot change dates w/o resetting pages */
          ?>
      </select>
      <input type="hidden" name="buy_start_date" value="<?php echo $buy_listings_start_date; ?>" />
      <input type="submit" value="Change Page" />
    </form>
    <form name="buy_table2" id="buy_table2" method="post" action="<?php echo _SITE_PATH."/myklassica/"; ?>">      
      <label for="calendar_popup_container2"><span class="calendar_label">Show items after date:</b></label>
      <input type="text" name="buy_start_date" id="calendar_popup_container2" class="calendar_input" value="<?php echo $buy_listings_start_date; ?>" />
      <input type="submit" value="Show" />
    </form>

<?php
  }
  // if no items
  if ($result_count < 1) {
    // show only date widget
    echo "<form name=\"buy_table\" method=\"post\" action=\""._SITE_PATH."/myklassica/\">";
    echo "<label for=\"calendar_popup_container2\"><span class=\"calendar_label\">Show items after date:</b></label>";
    echo "<input type=\"text\" name=\"buy_start_date\" id=\"calendar_popup_container2\" class=\"calendar_input\" value=\"$buy_listings_start_date\" />";
    echo "<input type=\"submit\" value=\"Show\" />";
    echo "</form>";
  }

  echo "</div>";

   unset($klistings);
   unset($items_array);
   unset($item);

?>