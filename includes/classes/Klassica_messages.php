<?php
/****************************************
Klassica_users Class
(c) 2006.09.13 Arthur Ketcham

Container for Message. A message is spawned from an item listing.
A message is a write-once object. It can be created and deleted (but not updated)

  Class Functions:
    klassica_messages()
      constructor function
      
    set_limit_date($st_date, $end_date)
      limit amount of messages recieved by constraining to date range 
    
    set_limit_count($limit)
      set limit number of messages by number
      
    get_messages_by_item($item_id, $orderby)
      returns array of messages about the indicated item
    
    get_messages_by_recipient($user_id, $orderby)
      returns array of messages received by user
    
    get_messages_by_sender($user_id, $orderby)
      returns array of messages received by user

    post_message($item_id, $sender_id, $message, $buy_flag)
    
    
  Class Variables:
    $limit        // limit of messages to retrieve (50 default)
    $page         // page number (each page contains $limit (number) of messages
    $date_start   // retrieve only messages after this date
    $date_end     // retrieve only messages before this date
    $offset       // offset of first message to be retreived (advance to next page)
    $limit        // limit of how many messages will be retreived
    
NOTES: Number of messages retrieved is limited by $limit and $date constraints.
If there are more messages in date range, then first page of messages will be the 
newest, and last page will be the oldest (descending date order), unless a specific order is given

TODO: Initalize user objects of user classes (for sender and recipient)
and call functions to check their prefs and send this message as an email copy to them

TODO: Make it so that only user's messages will appear attached to an item
(requires item_id and sender_id in WHERE clause)

TODO: Unkludge the hack for distinguishing between reg. items and housing items
      Probably, these will need to unioned together
****************************************/
class Klassica_message {
  var $db = false;
  var $items_table = 'items';
  var $sender_id = '';
  var $date_start = '';
  var $date_end = '';
  var $offset = 0;
  var $limit = 10;
  var $order_by = 'date';
  var $order_dir = 'DESC';
  var $count = 0;


  // Public classes
  function klassica_message($db, $housing=false) {
    $this->db = $db;
    if ($housing)
      $this->items_table = 'items_housing';
  }
 
  function set_date_range($start, $end) {
    $this->date_start = $start;
    $this->date_end = $end;
  }
  
  // set limit to 10 messages
  function set_limit($limit=10) {
    $this->limit = $limit;
    return true;
  }
  
  // set column to order results by (orderkey must be an allowed value)
  function set_orderby($orderkey, $order_dir) {
    if (in_array($orderkey, array('sender','item','buy','message','date')) && 
        in_array($order_dir, array('DESC','ASC'))) {
      $this->order_by = $orderkey;
      $this->order_dir = $order_dir;
      return true;
    } else
      return false;
  }
  
  // set the range of messages to show (offset = page * limit) (Start at page 1)
  function set_viewpage($page) {
    $this->offset = (($page - 1) * $this->limit);
    return true;
  }
  
  function get_messages_by_item($item_id) {
    $where_clause = " WHERE M.item_id = '$item_id'";
    return $this->find_messages($where_clause, 'recipient');
  }
    
  function get_messages_by_recipient($recipient_id) {
    
    $where_clause = " WHERE I.seller_id = '$recipient_id'";
    return $this->find_messages($where_clause, 'recipient');
  }
  
  function get_messages_by_sender($user_id) {
    $where_clause = " WHERE M.sender_id = '$user_id'";
    return $this->find_messages($where_clause, 'sender');
  }
  
  function get_result_count() {
    return $this->count;
  }
  
  // count available pages or messages (round to next integer)
  function get_page_count() {
    return (int)(ceil(($this->count)/($this->limit)));
  }
  
  // Private classes
  function build_order_clause() {
    // translate "order by" nmemonics to database fields
    switch ($this->order_by) {
      case 'sender': $order = 'M.sender_id'; break;
      case 'item': $order = 'M.item_id'; break;
      case 'buy': $order = 'M.buy_flag'; break;
      case 'message': $order = 'M.message'; break;
      case 'date': default: $order = 'M.cr_date'; break;
   }
   
    $order_clause = " ORDER BY $order $this->order_dir";
    return $order_clause;
  }
  
  function find_messages($where_clause, $byperson) {
    $st_date = $this->date_start;
    $end_date = $this->date_end;
    $offset = $this->offset;
    $limit = $this->limit;
    $order_clause = $this->build_order_clause();
    
    // Add extra stuff to join clause
    switch ($byperson) {
      case "sender": $xjoin = 'LEFT JOIN `users` AS U on I.seller_id = U.id'; 
      break;
      case "recipient": default: $xjoin = 'LEFT JOIN `users` AS U on M.sender_id = U.id'; 
      break;
    }
    
    if ($st_date)
      $where_clause .= " AND M.cr_date > '$st_date'";
    if ($end_date)
      $where_clause .= " AND M.cr_date < '$end_date'";
    if ($this->offset)
      $limit_clause = " LIMIT $limit OFFSET $offset";
    else
      $limit_clause = " LIMIT $limit";
  
    // Count the total results (No LIMIT clause!)
    $sql_count_only = "SELECT M.id FROM `messages` AS M LEFT JOIN {$this->items_table} AS I on M.item_id = I.id"
            .$where_clause.$order_clause;
            
    // fetch of the actual requested page only
    $sql_fetch_page = "SELECT M.id, M.item_id, M.sender_id, 
M.pub_sender_name AS pname, M.pub_sender_email AS pemail, 
U.firstname, U.lastname, U.email, I.itemtitle, 
I.seller_id, M.message, M.buy_flag, M.cr_date 
FROM `messages` AS M 
LEFT JOIN {$this->items_table} AS I on M.item_id = I.id ".$xjoin.$where_clause.$order_clause.$limit_clause;
    
//vardumper($sql_count_only);
//vardumper($sql_fetch_page);
    
    $this->db->query($sql_count_only);
    $this->count=$this->db->count_rows();
    
    // fetch results (Limited to one page only)
    $this->db->query($sql_fetch_page);
    for ($i = 0; $row=$this->db->get_row('ASSOC'); $i++) {
      $messages[$i] = $row;
    }
    return $messages;
    
  }
  
  function post_message($item_id, $sender_id, $pub_sender_name, $pub_sender_email, $message, $buy_flag) {
    $today = date('Y-m-d h:i:s');
    $buy_flag = ($buy_flag)? 1 : 0; // boolean value
    
    $sql = "INSERT INTO messages (item_id, sender_id,  pub_sender_name, pub_sender_email, message, buy_flag, cr_date) 
            VALUES ('$item_id', '$sender_id', '$pub_sender_name', '$pub_sender_email', '$message', '$buy_flag', '$today')";

    $this->db->query($sql);
    if ($this->db->affected_rows() == 1)
      $insert_ok = true;
    else
      $insert_ok = false;
      
    // If first person to mark 'buy', add this user to the 'buyer_id' field in the item record
    //NOTE: Maybe this isn't a good way of doing this (what if multiple buys or RSVPs are ok?), but that will be solved later
    if ($buy_flag) {
      $record_buyer_sql = "UPDATE {$this->items_table} SET buyer_id = '$sender_id', first_buy_date='$today' WHERE id = '$item_id' AND buyer_id IN ('NULL', '0','')";
      $this->db->query($record_buyer_sql);
    }
    
    if ($insert_ok)
      return true;
    else
      return false;
  }
  
  
  // Delete messages
  // Arguments: message id, and user id
  // Will delete only if user id is the recipient of the message
  // TODO: Perhaps we extend this to allow message senders to revoke thier sent messages?
  function delete_message($message_id, $user_id, $user_type) {

    // Make sure the current user is the actual recipient of the message (owns the associated item)
    if ($user_type == 'recipient') {
      $message_sender_id = "
        SELECT I.seller_id FROM 
          messages AS M 
        JOIN 
          {$this->items_table} AS I on (M.item_id = I.id)
        WHERE M.id = '{$message_id}'";
    
      $this->db->query($message_sender_id);
      $result = $this->db->get_row('ASSOC');
      
      // If this user does not own the item, they aren't the recipient of this msg!
      if ($result['seller_id'] != $user_id) {
        return false;
      } else {
        $sql_user_test = ''; // No further test needed
      }
    } elseif ($user_type == 'sender') {
      $sql_user_test = "sender_id = '{$user_id}' AND";
    } else {
      // A bad parameter was given
      return false;
    }

    $sql = "DELETE FROM messages WHERE {$sql_user_test} id = '$message_id'";

    $this->db->query($sql);

    $rows = $this->db->affected_rows();
    
    if ($rows == 1) {
      return true;
    } else {
      return false;
    }
  }
  
  
  function mail_message($item_id, $sender_id, $pub_sender_name, $pub_sender_email, $message, $buy_flag) {
    require ("includes/forms/handle_email.php");
    
    $item_url = _SITE_URL."/item/$item_id/";
 
    // if pub_ args are set, then we have sender's name and address
    if ($pub_sender_name && $pub_sender_email) {
      $sender_name = $pub_sender_name;
      $sender_addr = $pub_sender_email;
    } else {
      // if pub_ args are absent, then search the 'users' table for thier info
      $addr_sql = "SELECT firstname, lastname, email FROM users WHERE id = '$sender_id'";
      $this->db->query($addr_sql);
      $user = $this->db->get_row('ASSOC');
      $sender_name = trim(strip_colons($user['firstname'].' '.$user['lastname']));
      $sender_addr = trim(strip_colons($user['email']));
    }
    
    $item_sql = "SELECT I.itemtitle, U.firstname, U.lastname, U.email FROM {$this->items_table} AS I, users AS U WHERE I.id = '$item_id' AND I.seller_id = U.id";
    $this->db->query($item_sql);
    $item_info = $this->db->get_row('ASSOC');
    
    $item_title = trim(strip_colons($item_info['itemtitle']));
    $recp_name = trim(strip_colons($item_info['firstname'].' '.$item_info['firstname']));
    $recp_addr = trim(strip_colons($item_info['email']));
  
    
    // Send the email
    $Headers .= "From: WWC Klassica <$sender_addr>\r\n";
    $Headers .= 'X-Source-URI: '._SITE_URL.$_SERVER['PHP_SELF'] ."\r\n";
    $Headers .= 'X-Mailer: PHP '.phpversion()."\r\n";
    
    $MessageBody .= "Dear WWC Klassica user,\n\n";
    $MessageBody .= "Your item listed as: \"$item_title\", has received a response from: \n
$sender_name ($sender_addr)\n\n
Message Body:\n
$message\n\n
";
    if ($buy_flag) {
      $MessageBody .= "The sender indicates that they wish to buy or RSVP to this message or invitation.\n";
    }
    $MessageBody .= "Your listing is located at: $item_url\n";
    $MessageBody .= "-------------------------------------------\n
Thank you for using WWC Klassica!";
    

    if (@mail($recp_addr, "Klassica - A reply to your listing", $MessageBody, $Headers)) {
//    if (@mail($recp_addr, "Klassica - A reply to your listing", $MessageBody, $Headers)) {
      return true;
    } else {
      // if in debug mode, print error
    }
  }
}
?>