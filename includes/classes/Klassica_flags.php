<?php
/****************************************
Klassica_flags Class
(c) 2006.09.18 Arthur Ketcham

functions:
  klassica_flags($db, $item_id=false)   // Init
  get_flaged_items($vetoed=false)       // Build listing of Item Flags [optional: include vetoed flags]
  set_flag($flag_type, $flagger_id)
  user_already_flagged($flagger_id)     // True if the given user has already flagged this item
  


****************************************/
class Klassica_flags {
  var $db      = false;
  var $item_id = false;

  // constructor
  function klassica_flags($db, $item_id=false) {
    $this->db = $db;
    $this->item_id = $item_id;
  }


// Set a moderation flag for this item. Works just like craigslist, and items are autohidden after a certain threshold.
  function set_flag($flag_type, $flagger_id) {
    
    if ($flag_type == '') {
      return false;
    }
  
    $item_id = $this->item_id;
    $curtime = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO item_flags (item_id, flagger_id, flag_type, date) VALUES ('$item_id', '$flagger_id', '$flag_type', '$curtime')";
    $query = $this->db->query($sql);
    if ($this->db->affected_rows() == 1) {
      return true;
    }
    return false;
  }

  
  // For moderators: this shows items that have been flagged by users
  // returns item array (flag_id, item_id, itemtitle, seller_fname, seller_lname, flagger_fname, flagger_lname,
  //   expire_date, flag_type, flag_count, vetoed)
  // (If muliple flags have been set, returns one row type of flag per item)
  // Inputs: Show vetoed flags (defaults to false)
  function get_flaged_items($vetoed=false) {
    if ($vetoed) {
      $xwhere = " AND f.vetoed != 1";
    }
    $sql = "SELECT f.id AS flag_id, f.item_id, f.flag_type, 
            count(f.flag_type) AS flag_count, f.vetoed, 
            i.itemtitle, i.expire_date, 
            us.firstname AS seller_fname, us.lastname AS seller_lname, 
            uf.firstname AS flagger_fname, uf.lastname AS flagger_lname
            FROM 
              item_flags AS f LEFT JOIN
              (SELECT itemtitle, expire_date, id, seller_id, deleted FROM items 
                UNION
              SELECT itemtitle, expire_date, id, seller_id, deleted  FROM items_housing) AS i ON (f.item_id = i.id)
              LEFT JOIN
              users AS us ON (i.seller_id = us.id) LEFT JOIN
              users AS uf ON (f.flagger_id = uf.id)
            WHERE i.deleted != '1'$xwhere
            GROUP BY f.item_id, f.flag_type";
  
    $this->db->query($sql);
  
    for ($i = 0; $row=$this->db->get_row('ASSOC'); $i++) {
      $items[$i] = $row;
    }
  
    return $items;
  }

  // Has user already flagged this item?
  function user_already_flagged($flagger_id) {
    $item_id = $this->item_id;
    $sql = "SELECT * FROM item_flags WHERE item_id = '$item_id' AND flagger_id = '$flagger_id'";
    $query = $this->db->query($sql);
    $found_flags = $this->db->count_rows();
    if ($found_flags >= 1) {
      return true;
    } else {
      return false;
    }
  }
}
?>