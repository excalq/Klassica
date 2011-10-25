<?php
/****************************************
Klassica_listings Class
(c) 2007.04.08 Arthur Ketcham - <dev [at] arthurk.com>

Returns a list of items meeting the 
specified criteria.

Usage: Set parameters for what you want returned or filtered
  using the set_??? and search_??? functions, then retrieve the 
  matchin results with get_item_results()
  
  Public Class Functions:
  
  klassica_listing()                    // Constructor
  
  set_thresholds($spam, $offensive, $miscat)  // Set numeric thresholds for moderation flag exclusion 
      [defaults are set in setup/config.php]
  set_show_current()                    // Include current items [default: true]
  set_show_expired()                    // Include expired items [default: false]
  set_date_range($start, $end)          // Set date range of items to be retreived
  set_limit($limit=10)                  // Set amount of items to retreive [default: 10, change by optional parameter]
  set_orderby($orderkey, $order_dir)    // Set column to order by and direction
  set_viewpage($page)                   // Set listings page (Sets offset in SQL select query)
  get_page_count()                      // Current listings page (based on offset of result list)
  search_by_seller($id)                 // Retrieve only items marked as bought by a specific person
  search_by_buyer($id)                  // Retrieve only items posted by a specific person
  search_by_category($cat_id)           // Retrieve only items in a specific category/subcategory
  get_item_results($count_only=false)   // Retreive listing of results [optional: optimize db query for count only]
  get_result_count()                    // Count results
  get_new_items($free=false, $textbooks=true) // Build listing of New Items 
                                              // [optional: show only free items, exclude textbooks]
  get_free_items()                      // Build listing of Free Items
  get_cat_name()                        // Name or Category
  get_subcat_name()                     // Name of Sub Category
  is_sales_listing($cat_id)             // Is this a 'sales' category? (Not announcements or services)
  
  

// TODO: Facilitate Searching for items, and return a message list of results

/****************************************/
class Klassica_listing {

  var $db           = false;
  var $ANNC_CATS    = array(1,7,8,9); // Non-sale categories: annoucements, employment, services, housing
  var $CURRENT_TIME = false; // set in constructor
  var $category     = false;
  var $cat_name     = false;
  var $subcategory  = false;
  var $subcat_name  = false;
  var $title        = false;
  var $location     = false;
  var $description  = false;
  var $condition    = false;
  var $price_low    = false;
  var $price_high   = false;
  var $seller       = false;
  var $buyer        = false;
  var $show_current = true;
  var $show_expired = false;
  var $date_start   = false;
  var $date_end     = false;
  var $date_col     = 'mod_date'; // cr_date: when item was first created, mod_date: when last edited

  var $order_by     = 'mod_date'; // show newly edited items first (even if they are older)
  var $order_dir    = 'DESC';
  
  var $limit = 10;
  var $offset = 0;
  var $count = 0;

  var $threshold_spam = _HIDE_SPAM_THRESHOLD;
  var $threshold_offensive = _HIDE_OFFENSIVE_THRESHOLD;
  var $threshold_miscat = _HIDE_MISCAT_THRESHOLD;


  // constructor
  function klassica_listing($db) {
    $this->db = $db;
    $this->CURRENT_TIME = date('Y-m-d H:i:s');
  }
  
  // Set thresholds for visible items
  // Items with at least the specified amount of flags will be hidden
  // 0 = never hide
  function set_thresholds($spam, $offensive, $miscat) {
    $this->threshold_spam = $spam;
    $this->threshold_offensive = $offensive;
    $this->threshold_miscat = $miscat;
    return true;
  }
  
  // Show items that are currently running
  // this is NOT mutually-excluive with show_expired
  function set_show_current() {
    $this->show_current = true;
  }
  
  // Show items that have expired
  // this is NOT mutually-excluive with show_current
  function set_show_expired() {
    $this->show_expired = true;
  }
  
  // Specify a date range to search - either parameter is optional
  function set_date_range($start, $end) {
    if ($start)
      $this->date_start = $start;
    if ($end)
      $this->date_end = $end;
  }
  
  // Set limit of retreived items
  // 0 is unlimited
  // Default: set limit to 10 items
  function set_limit($limit=10) {
    if ($limit == 0) {
      $this->limit = false;
    }
    $this->limit = $limit;
    return true;
  }
  
  // set column to order results by (orderkey must be an allowed value)
  function set_orderby($orderkey, $order_dir = 'ASC') {
    $this->order_by = $orderkey;
    $this->order_dir = $order_dir;
    return true;
  }
  
  // set the range of messages to show (offset = page * limit) (Start at page 1)
  function set_viewpage($page) {
    $this->offset = (($page - 1) * $this->limit);
    return true;
  }

  // count available pages or messages (round to next integer)
  function get_page_count() {
    return (int)(ceil(($this->count)/($this->limit)));
  }
  
  function search_by_seller($id) {
    $this->seller = $id;
  }
  
  function search_by_buyer($id) {
    $this->buyer = $id;
    // if searching buy first buyer, then return the buy time as the date, not the mod_date
    $this->date_col = 'first_buy_date';
    $this->order_by = 'first_buy_date';
  }
  
  function search_by_category($cat_id) {
  
    // Is this "category" id actually a category, or is it a subcategory?
    $classification = $this->get_cat_class($cat_id);

    if ($classification == 'subcategory') {
    
      $this->subcategory = $cat_id;
      $this->subcat_name = $this->get_subcat_name();
      
      // Get parent category information too
      $cat_query      = "SELECT cs.category_id AS cat FROM categories c, categories_subcategories cs 
                        WHERE cs.subcategory_id = '$cat_id' AND c.id = cs.category_id";
      
      $result         = $this->db->query($cat_query);
      $category       = $this->db->get_row("ASSOC");
      $category_id    = $category['cat'];
      $this->category = $category_id;
      $this->cat_name = $this->get_cat_name();
      
      return true;
      
    } elseif ($classification == 'category') {
    
      $this->category    = $cat_id;
      $this->subcategory = $cat_id; // if cat_id was for just a category, set subcategory to match
      $this->cat_name    = $this->get_cat_name();
      $this->subcat_name = $this->cat_name;
      return true;
      
    } else {
    
      return false;
    }
  }

  // Returns array of items matching all of the search parameters
  // Inputs: class variables must be set first, and optional parameter $count_only is used to return only the count (no limit applied)
  // Returns: array (id, itemtitle, price, is_price_obo, is_price_free, condition, cr_date, mod_date, expire_date)
  function get_item_results($count_only=false) {
    $date_col = $this->date_col;
    
    // Housing items use a special table
    $category = $this->get_cat_name();
    if (strtolower($category) == 'housing')
    {
      $table = 'items_housing';
      $is_housing = true;
    } else {
      $table = 'items';
      $is_housing = false;
    }
    
    if ($count_only) {
      $select_clause = "SELECT count(i.id) AS count";
    } else {
      $select_clause = "SELECT i.id, itemtitle, location, price, is_price_obo, is_price_free, SUBSTRING(description FROM 1 FOR 100) AS description, cond, 
      $date_col AS date, 
      expire_date, buyer_id";
      if ($is_housing == 'housing')
        $select_clause = "SELECT i.id, itemtitle, 
          IF(c.name, c.name, s.name) AS cat_name,
          location, beds, baths, rent, deposit, 
          SUBSTRING(description FROM 1 FOR 100) AS description, $date_col AS date, expire_date, buyer_id";
    }


    if ($this->threshold_spam || $this->threshold_offensive || $this->threshold_miscat) {
      $ts = $this->threshold_spam;
      $to = $this->threshold_offensive;
      $tm = $this->threshold_miscat;

      $from_clause = " FROM $table AS i LEFT JOIN 
                        subcategories AS s ON (s.id = i.category_id) LEFT JOIN
                        categories AS c ON (c.id = i.category_id)";
      // Can this be done using JOINs/Groups/Having, instead of subqueries?
      $where_clause = " WHERE (SELECT count(flag_type) FROM item_flags f WHERE flag_type = 'spam' AND f.item_id = i.id AND f.vetoed != '1') < $ts
        AND (SELECT count(flag_type) FROM item_flags f WHERE flag_type = 'offense' AND f.item_id = i.id AND f.vetoed != '1') < $to
        AND (SELECT count(flag_type) FROM item_flags f WHERE flag_type = 'miscat' AND f.item_id = i.id AND f.vetoed != '1') < $tm
        AND deleted != '1'";
    } else {
      $from_clause = " FROM $table";
      $where_clause = " WHERE 1=1";
    }

    // Search by category/subcategory
    if ($this->subcategory) {
          $subcategory = $this->subcategory;
      // to get all items in a category
      if ($subcategory < 10)
        $search_subcats = "$subcategory%";
      else
         $search_subcats = $subcategory;
      $where_clause .= " AND category_id LIKE '$search_subcats'";
    }
    // Search by title OR description (this is a common case for searching)
    if ($this->title && $this->description) {
      $where_clause .= " AND (itemtitle LIKE '%{$this->title}%' OR description LIKE '%{$this->description}%')";
    } elseif ($this->title) { // title only search
      $where_clause .= " AND itemtitle LIKE '%{$this->title}%'";
    } elseif ($this->description) { // description only search
      $where_clause .= " AND description LIKE '%{$this->description}%'";
    }
    if ($this->seller)
      $where_clause .= " AND seller_id = '{$this->seller}'";
    if ($this->buyer) // TODO: What is a buyer? The first person to check the buy checkox? what about RSVP?
      $where_clause .= " AND buyer_id = '{$this->buyer}'";
    if ($this->location)
      $where_clause .= " AND location LIKE '%{$this->location}%'";

    if ($this->date_start) // Uses cr_date, since that is what is shown in table
    // TODO: Make a way to differentiate between expire_date (watched items expiration), and cr_date (searching by cr_date)
      $where_clause .= " AND $date_col >= '{$this->date_start}'";
    if ($this->date_end)
      $where_clause .= " AND expire_date <= '{$this->date_end}'";

    if ($this->price && !$is_housing)
      $where_clause .= " AND price = '{$this->price}'";
    if ($this->condition && !$is_housing)
      $where_clause .= " AND cond = '{$this->condition}'";
      
    if ($this->limit)
      $limit_clause = " LIMIT {$this->limit}";
    if ($this->offset)
      $limit_clause .= " OFFSET {$this->offset}";

    if($this->order_by) {
      $order_clause = " ORDER BY {$this->order_by}";
      if ($this->order_dir)
        $order_clause .= " {$this->order_dir}";
    }
      
    // by calling above object methods, you can change the age visibility
    // Options: 1) show both current and expired, 2) show only expired, 3) show only current
    // TODO: What if these conflict with date range (For now, they are ANDed with date ranges)
    
    if ($this->show_expired && !$this->show_current) // 2) show only expired
      $where_clause .= " AND expire_date <= '{$this->CURRENT_TIME}'";
    elseif ($this->show_current && !$this->show_expired) // 3) show only current
      $where_clause .= " AND expire_date > '{$this->CURRENT_TIME}'";
    // 1) show both will happen if no expire date is set
    
    // Assemble SQL clause
    $sql = $select_clause.$from_clause.$where_clause.$group_clause.$order_clause.$limit_clause;
    
// vardumper($where_clause);
//vardumper($sql);

    $itemlist = array();
    $query = $this->db->query($sql);
    
    $i = 0;
    while($itemrow = $this->db->get_row("ASSOC")) {
      $itemlist[$i] = $itemrow;
      $i++;
    }

    if ($count_only)
      return $itemlist['0']['count'];
    else
      return $itemlist;
  }
  
  // Return full count of items matching set parameters (ignore limit amount)
  function get_result_count() {
    $temp1 = $this->limit;
    $temp2 = $this->offset;
    $this->limit = $this->offset = false;
    $this->count = $this->get_item_results(true);
    $this->limit = $temp1;
    $this->offset = $temp2;
    
    unset($temp1);
    unset($temp2);
    
    return $this->count;
  }
  
  // Finds newly added items (subject to constraints set in class vars), and returns array(id, itemtitle, price)
  // TODO: Is there a better way to do this w/o subqueries?
  function get_new_items($free=false, $textbooks=true) {
    if ($this->threshold_spam || $this->threshold_offensive || $this->threshold_miscat) {
      $ts = $this->threshold_spam;
      $to = $this->threshold_offensive;
      $tm = $this->threshold_miscat;
      $where_clause = " WHERE 
        (SELECT count(flag_type) FROM item_flags f 
        WHERE flag_type = 'spam' AND f.item_id = i.id AND f.vetoed != '1') < $ts
      AND 
        (SELECT count(flag_type) FROM item_flags f 
        WHERE flag_type = 'offense' AND f.item_id = i.id AND f.vetoed != '1') < $to
      AND 
        (SELECT count(flag_type) FROM item_flags f 
        WHERE flag_type = 'miscat' AND f.item_id = i.id AND f.vetoed != '1') < $tm
      AND deleted != '1'";
    } else {
      $where_clause = " WHERE 1=1";
    }
    if ($free) {
      // TODO: Replace this, and use the $this->is_sales_listing() method
      $where_clause .= " AND price IN (NULL, '0', '') AND ((category_id >= '20' AND category_id < '70') OR (category_id > '2' AND category_id < '7')) ";
    }
    // Exclude textbooks
    if (!$textbooks) {
      $where_clause .= " AND category_id != '54'";
    }
    
    // Show only current items
    if ($this->show_expired && !$this->show_current) // 2) show only expired
      $where_clause .= " AND expire_date <= '{$this->CURRENT_TIME}'";
    elseif ($this->show_current && !$this->show_expired) // 3) show only current
      $where_clause .= " AND expire_date > '{$this->CURRENT_TIME}'";  // 1) show both will happen if no expire date is set
    
    if ($this->limit) {
      $limit_clause = " LIMIT {$this->limit}";
    }
    
    $sql = "SELECT id, itemtitle, price, location FROM 
      ( SELECT id, itemtitle, price, location, deleted, category_id, expire_date, mod_date  FROM items
      UNION 
        SELECT id, 
               IF(housing_type = 'Roommate Wanted' OR housing_type = 'Housing Wanted',
                  housing_type,
                  CONCAT(beds, ' Bed ', housing_type)) AS itemtitle, 
               IF(housing_type = 'Roommate Wanted' OR housing_type = 'Housing Wanted',
                  '',
                  CONCAT(rent, '/mo')) AS price, location, deleted, category_id, expire_date, mod_date FROM items_housing
      ) AS i
      $where_clause ORDER BY mod_date DESC$limit_clause";
      
    //vardumper($sql);
      
    $this->db->query($sql);
    for ($i = 0; $row=$this->db->get_row('ASSOC'); $i++) {
      $items[$i] = $row;
    }
    
    return $items;
  }
  
  // Get recent free listings (only sale items)
  function get_free_items() {
    // set "free" parameter to true
    return $this->get_new_items(true);
  }
  
  // Is this cat_id actually a subcategory, or is a parent category
  function get_cat_class($cat_id) {
  
    $query = "SELECT name FROM categories c, categories_subcategories cs 
                  WHERE cs.subcategory_id = '$cat_id' AND c.id = cs.category_id";
    $result = $this->db->query($query);
    $num_rows = $this->db->count_rows();
    
    if ($num_rows) {
      return 'subcategory';
    } else {
      // Check to see if it's a category
      $query  = "SELECT name FROM categories WHERE id= '$cat_id'";
      $result = $this->db->query($query);
      $num_rows = $this->db->count_rows();
      if ($num_rows)
        return 'category';
      else
        return false;
    }  
  }
  
  // Get name of the listing's category. If it is part of a subcategory, get parent category name
  function get_cat_name() {
    $category = $this->category;
    
    $query  = "SELECT name FROM categories WHERE id= '$category'";
    $result = $this->db->query($query);
    $num_rows = $this->db->count_rows();
    
    if ($num_rows) {
      $cat_result = $this->db->get_row("NUM");
      $this->cat_name = $cat_result[0];
      return $this->cat_name;
      
    } else {
      return false;
    }
  }

  // Read subcategory name
  function get_subcat_name() {
    $subcategory = $this->subcategory;
    $query = $this->db->query("SELECT name FROM subcategories WHERE id= '$subcategory'");
    $num_rows = $this->db->count_rows();
  
    if ($num_rows) {
      $result = $this->db->get_row("NUM");
      $this->subcat_name = $result[0];
      return $this->subcat_name;
    } else { // listing did not have a subcategory
      return false;
    }
  }
  
  // Returns true if cat/subcat show show price and condition, false if not (sale item vs. announcement)
  function is_sales_listing($cat_id) {
    //TODO: These category numbers are hard coded here, but in the future, do this from the DB
    $cat_prefix = substr($cat_id, 0,1); // Get first digit of cat/subcategory id
    if (in_array($cat_prefix, $this->ANNC_CATS)) {
      return false;
    } else {
      return true;
    }
  
  }
  
} // end class




// Searching criteria notes:

// Search Types (Multiple Allowed)
//   - Title
//   - Category
//   - Date Range
//   - Price Range
//   - Location
// 
//   - My Items
//     - Bought
//     - Selling/Sold
//     - Watched
    
?>