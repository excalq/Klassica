<?php
  
  $user_id = $_SESSION["auth"]["user_id"];
  
  $kmsg = new Klassica_message($dbconn);
  

  // If user requested a message be deleted
  // Verify the message's recipient as current user
  if ($_GET['ut'] == 'recv' && $_GET['deletemsg']) {
    $del_message_id = addslashes((int) $_GET['deletemsg']);
    if ($del_message_id > 0) {
      // Zero is reserved for system-wide admin messages that everyone gets
  
      // Send userid to function for security validation (will delete only if user is recipient of message)
      $kmsg->delete_message($del_message_id, $user_id, 'recipient');
    }
  }

  // show 10 messages at a time
  $kmsg->set_limit(10);
  
  // set order (by column) on request
  if ($_POST['message1_order_by'] && $_POST['message1_order_dir']) {
    // switch order
    $order_direction = ($_POST['message1_order_dir'] == 'DESC')? 'ASC' : 'DESC';
    // set order_by
    $kmsg->set_orderby($_POST['message1_order_by'], $order_direction);
  }
  
  // advance page on request (start at page 0)
  $page = $_POST['message1_page'];
  if ($page)
    $kmsg->set_viewpage($page);
  
  // Fetch array of messages
  $messages_arr = $kmsg->get_messages_by_recipient($user_id);
  

?>
            
<div>
  <h3 class="titleLabel table_title">Messages: Received</h3>
  <div id="table_container">
      <table class="messagestable">
        <tr class="header">
          <th class="th_delete"></th>
          <th class="th_from">From</th>
          <th class="th_item">Item</th>
          <th class="th_buy">Buy/RSVP</th>
          <th class="th_textline">Message</th>
          <th class="th_date">Received</th>
        </tr>
      
<?php
  if ($kmsg->get_result_count() < 1) {
    echo "<tr class=\"odd\">\n<td colspan=\"6\">
            <span style=\"color: #444;\">You have received no item responses at this time.</span>
          </td>\n</tr>\n</table>\n</div>";
  } else {
    //gets a row and assigns mysql fields to vars, then prints out table with the data.
    foreach($messages_arr as $msg)
    {
      // Show mark if buy_flag was set
      $buy = ($msg['buy_flag'])? "X" : "";
      
      // If message was sent by a public user (no account), get their name and email from
      // different fields
      if ($msg['pname'] && $msg['pemail']) {
        $name = $msg['pname'];
        $email = $msg['pemail'];
      } else {
        // Otherwise use name and email from the users database
        $name = $msg['firstname'].' '.$msg['lastname'];
        $email = $msg['email'];
      }
      
      // alternating row colors
      $tr_class = (++$c%2)? " class=\"odd\"" : "";
      echo "\n<tr$tr_class>\n";
      //echo "\t<td>$msg['sender_id']</td>\n";
      echo "\t<td class=\"delete\"><a href=\""._SITE_PATH."/myklassica/?deletemsg={$msg['id']}&ut=recv\" onclick=\"return confirm('Are you sure you want to delete this message?');\">
              <img src=\"/images/delete.png\" /></a></td>\n";
      echo "\t<td class=\"from\"><a href=\"mailto:$email\">$name</a></td>\n";
      echo "\t<td class=\"item\"><a href=\"../item/{$msg['item_id']}/\">{$msg['itemtitle']}</a></td>\n";
      echo "\t<td class=\"buy\">$buy</td>\n";
      echo "\t\t<td class=\"mtextline\"><a href=\"#\">".nl2br($msg['message'])."</a></td>\n";
      echo "\t<td class=\"date\">{$msg['cr_date']}</td>\n";
      echo "\t</tr>";
    }


?>
      </table>
    </div>
    <form name="message1_page" method="post" action="<?php echo _SITE_PATH."/myklassica/"; ?>">
      <select type="select" name="message1_page">
        <?php
          // Get as many pages as the kmsg class calculates
          for ($i = 1; $i<=$kmsg->get_page_count(); $i++) {
            // restore previously selected option (by ternary compare of $_POST var)
            $selected = ($_POST['message1_page'] == $i)? " selected=\"selected\"" :  "";
            
            echo "\t<option value=\"$i\" $selected>$i</option>\n";
          }
          ?>
      </select>
      <input type="submit" value="Change Page" />
    </form>
  </div>
<?php
   }
   
   // clear variables to avoid interference with other scripts
   unset($messages_arr);
   unset($msg);
   unset($kmsg);
   unset($c);
?>