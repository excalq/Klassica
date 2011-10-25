<?php
/****************************************
Klassica_items Class
(c) 2006.09.18 Arthur Ketcham

Container for sale or announcement items
Uses external DB class
  Functions:
    klassica_items ($id) : Constructor
      if $id, set $id to given item id.
      we don't want to retrieve item data yet

    get_item_data(): Retrieve all data from item record
    create_item(): Create item in DB
    delete_item(): Delete item from DB

    set_xxx(): Update field xxx in DB
    get_xxx(): Read field from DB


Class Variables:
 (C = create, R = read, U = update, D = delete [to/from DB])  
  $db; (database object)
  id: CRD
  itemtitle: RU
  category_id: RU
  seller_id: RU
  buyer_id: R
  location: RU
  phone: RU (NULL ok)
  email: RU (NULL ok)
  email_pub: RU (NULL ok)
  phone_pub: RU
  description: RU
  short_desc: RU
  expire_date: RU
  price: RU (NULL ok)
  is_price_obo : RU (NULL ok)
  is_price_free: RU (NULL ok)
  cond: RU (NULL ok)
  cr_date: R
  mod_date: RU
  photo[] (array): RU (NULL ok)

Item record: Create, Delete


****************************************/
class Klassica_item {

  var $db              = '';
  
  // general item info
  var $id              = '';
  var $itemtitle       = '';
  var $category_id     = '';
  var $seller_id       = '';
  var $buyer_id        = '';
  var $location        = '';
  var $phone           = '';
  var $email           = '';
  var $email_pub       = '';
  var $phone_pub       = '';
  var $description     = '';
  var $short_desc      = '';
  var $expire_date     = '';
  var $price           = '';
  var $is_price_obo    = ''; 
  var $is_price_free   = '';
  var $is_wanted       = '';
  var $cond            = '';
  
  // housing items
  var $is_housing_item = '';
  var $contact_name    = '';
  var $beds            = '';
  var $baths           = '';
  var $rent            = '';
  var $deposit         = '';
  
  var $cr_date = '';
  var $mod_date = '';
  var $file = array();

  function klassica_item($db, $itemid=false) {
    $this->db = $db;
    $this->id = $itemid;
    return true;
  }

  // Get item data from item record
  function get_item_data() {

    if ($this->id) {
      $itemid = $this->id;
      $query = $this->db->query("SELECT * FROM items WHERE id = '$itemid' LIMIT 1");
      $num_rows = $this->db->count_rows();
      
      // If this item isn't in the "items" table, then check the "items_housing" table
      if ($num_rows < 1)
      {
        $query    = $this->db->query("SELECT * FROM items_housing WHERE id = '$itemid' LIMIT 1");
        $num_rows = $this->db->count_rows();
        if ($num_rows)
          $this->is_housing_item = true;
      }

      // If we did not find a vaid item, return false
      if ($num_rows < 1) {
        return false;
      } else {
        // we did find a valid item
        $item_arr = $this->db->get_row("ASSOC");
        if (!$item_arr) {
          $this->id = false;
          return false;
        }

        // put DB row data into clas variables
        foreach ($item_arr as $key=>$value) {
          if (isset($this->$key)) {
            $this->$key = $value;
          }
        }

        // get files attatched to items
        $query = $this->db->query("SELECT id, filepath FROM item_files WHERE item_id = '$itemid' ORDER BY id");
        $i = 0;
        while ($row = $this->db->get_row("ASSOC")) {
          $this->file[$i]['id']   = $row['id'];
          $this->file[$i]['name'] = $row['filepath'];
          $i++;
        }
// vardumper($this->location);
// vardumper($item_arr);

        return true;
      }
    }
  }

  function add_files($files_arr) {
    if ($files_arr) {
      foreach ($files_arr as $i => $value) {
        $this->files[$i] = $value;
      }
    }
  }
  
  // Create record from newly posted item
  // Input array:
  // array($itemtitle, $item_category, $seller_id, $location, $phone, 
  //       $email, $description, $expire_date, $cr_date, $mod_date, 
  //       $price, $is_price_obo, $is_price_free, $condition)
  function create_item($field_arr, $only_update=false) {
    
    // If existing item is being updated, do not create a new itemid
    
    // error check (if updating existing item, then the id should have been set)
    if ($only_update && !($this->id)) {
      return false;
    } elseif (!$this->id) {
      // Create unique, random id for new item
      $itemid = uniqid("");
      $this->id = $itemid;
    } else {
      // existing itemid
      $itemid = $this->id;
    }
    
    $curtime = date('Y-m-d H:i:s');
    // Add x days to now. (where x is value passed in $field_arr['expire_days']
    $exptime = date('Y-m-d H:i:s', mktime(date("H"), date("i"), date("s"), date("m"), date("d")+$field_arr['expire_days'], date("Y")));
    
    $this->itemtitle     = $field_arr['title'];
    $this->category_id   = $field_arr['category']; // could be a category or a subcategory in the DB
    $this->seller_id     = $field_arr['seller_id'];
    $this->buyer_id      = ''; // we don't know yet
    $this->location      = $field_arr['location'];
    $this->phone         = $field_arr['phone'];
    $this->email         = $field_arr['email'];
    $this->email_pub     = ''; // deprecated
    $this->phone_pub     = ''; // deprecated
    $this->description   = $field_arr['description'];
    $this->short_desc    = ''; // deprecated
    $this->expire_date   = $exptime;
    $this->price         = $field_arr['price'];
    $this->is_price_obo  = $field_arr['is_price_obo'];
    $this->is_price_free = $field_arr['is_price_free'];
    $this->is_wanted     = $field_arr['is_wanted'];
    $this->cond          = $field_arr['condition'];
    $this->cr_date       = $curtime;
    $this->mod_date      = $curtime;
    
    // check if this is a "housing item"
    $cat_name = $this->get_parent_cat_name($this->category_id);
    
    if (strtolower($cat_name) == 'housing') {
      $this->contact_name = $field_arr['contact_name'];
      
      $htype = $this->get_subcat_name();
      $htype = (substr($htype, -1) == 's') ? substr($htype, 0, -1) : $htype; // chop final 's' off
      
      $this->housing_type = $htype;
      $this->beds         = $field_arr['beds'];
      $this->baths        = $field_arr['baths'];
      $this->rent         = $field_arr['rent'];
      $this->deposit      = $field_arr['deposit'];
      $this->date_listed  = $field_arr['date_listed'];
        
      $table = 'items_housing';
      $is_housing = true;
    } else {
      $table = 'items';
      $is_housing = false;
    }
      
    // Updating existng item
    if ($only_update) {
      // Save cr_date of existing item
      $this->cr_date = $this->get_cr_date();
      
      // If item is being renewed
      if ($field_arr['expire_days'] == 'renew') {
        $next_week = date('Y-m-d H:i:s', mktime(date("H"), date("i"), date("s"), date("m") , date("d")+7, date("Y")));
        $sql_expiration = ", expire_date='$next_week'";
      }
      
      if($is_housing) {
        $sql_extra_update = ", housing_type='{$this->housing_type}', beds='{$this->beds}', baths='{$this->baths}', rent='{$this->rent}', 
        deposit='{$this->deposit}', contact_name='{$this->contact_name}', date_listed='{$this->date_listed}'";
      } else {
        $sql_extra_update = ", price='{$this->price}', is_price_obo='{$this->is_price_obo}', 
        is_price_free='{$this->is_price_free}', is_wanted='{$this->is_wanted}', cond='{$this->cond}'";
      }
  
      // Updating old item
      $sql = "UPDATE $table SET itemtitle='{$this->itemtitle}', category_id='{$this->category_id}', 
              seller_id='{$this->seller_id}', location='{$this->location}', phone='{$this->phone}', 
              email='{$this->email}', description='{$this->description}', mod_date='$curtime' 
              $sql_expiration $sql_extra_update 
              WHERE id = '$itemid'";
    
    } else { // New item
    
      if ($is_housing) {
        $sql_extra_cols = ", housing_type, beds, baths, rent, deposit, contact_name, date_listed";
        $sql_extra_vals = ", '{$this->housing_type}', '{$this->beds}', '{$this->baths}', '{$this->rent}', 
        '{$this->deposit}', '{$this->contact_name}', '{$this->date_listed}'";
      } else {
        $sql_extra_cols = ", price, is_price_obo, is_price_free, is_wanted, cond";
        $sql_extra_vals = ", '{$this->price}', '{$this->is_price_obo}', '{$this->is_price_free}', '{$this->is_wanted}', '{$this->cond}'";
      }
    
      $sql = "INSERT INTO $table 
      (id, itemtitle, category_id, seller_id, location, 
      phone, email, description, expire_date,
      cr_date, mod_date $sql_extra_cols) 
      VALUES ('$itemid','{$this->itemtitle}','{$this->category_id}', '{$this->seller_id}', 
      '{$this->location}', '{$this->phone}','{$this->email}', '{$this->description}', '$exptime', 
      '$curtime', '$curtime' $sql_extra_vals);";
      
    }
    // vardumper($field_arr);
    // vardumper($sql);
    
    $result = $this->db->query($sql);

    // verify successful insert
    if ($this->db->affected_rows() == 1) {
      
      // add files as well
      $files = $this->files;
      if (count($files)) {
        // TODO: This code block needs help (dammit, the sun is rising)
        foreach ($files as $i => $value) {
          if ($value) {
            // Insert photo to database
            $sql = "INSERT INTO item_files (filepath,item_id) VALUES ('$value', '$itemid');";
            $photo_insert = $this->db->query($sql);
            // TODO: Return status of file upload
          }
        }
      }
      return true; // sucessful insert of item
    } else {
      return false;
    }
  }
  
  // Delete a certain file from this item
  function delete_file($id) {
    $this->db->query("SELECT filepath FROM item_files WHERE id='{$id}' AND item_id='{$this->id}'");
    $row      = $this->db->get_row("ASSOC");
    $filename = $row['filepath'];
        
        
    if ($filename) {
      // Delete from DB
      $sql = "DELETE FROM item_files WHERE id='{$id}' AND item_id='{$this->id}'";
      $query = $this->db->query($sql);
      
      if ($query) {
        // Delete File
        $file  = _FILE_UPLOAD_PATH .'/'. $filename;
        $thumb = _FILE_UPLOAD_PATH .'/thumbnails/'. $filename;
          ##################
          // For debugging
          // $fh = fopen($file, 'w') or die("can't open file");
          // fclose($fh);
          ##################
        unlink($file);
        unlink($thumb);
        return true;
      } else {
        return false;
      }
    }
  }
  
  // Delete record
  function delete_item($id) {
  //TODO: Set item to deleted, but dont actually delete
  // Maybe this will change in the future
  
    $type = strtolower($this->get_item_type());
    if ($type == 'housing')
      $table = 'items_housing';
    else
      $table = 'items';
      
    $query = $this->db->query("UPDATE $table SET deleted = '1' WHERE id = '$id'");
    // $query = $this->db->query("DELETE FROM items WHERE id = '$id'");
    if ($query) {
      return true;
    } else {
      return false;
    }
  }
  
  
  // Update Functions
  
  // Update existing items, pseudo-function for outside interface
  function update_item($field_arr) {
    return $this->create_item($field_arr, true);
  }
    
  function set_field($field, $value) {
    if (isset($this->$field)) {
      $this->$field = $value;
      
      $type = strtolower($this->get_item_type());
      if ($type == 'housing')
        $table = 'items_housing';
      else
        $table = 'items';
      
      $query = $this->db->query("UPDATE $table SET $field = '$value' WHERE id= '$id' ");
      if ($query) {
        return true;
      }
    }
      return false;
  }
  
  // Read Functions
  function get_field($field) { 
    if (isset($this->$field))
      return $this->$field;
    else
      return false;
  }

  // Get Category Name
  // TODO: Fix this shit
  function get_cat_name() {
    if ($this->category_id) {
      return $this->category_id;
    } else {
      return false;
    }
  }
    
  // Get Subcategory Name
  function get_subcat_name() {
    $catid = $this->category_id;
    if ($this->category_id) {
      // if the cat_id < 10, it is a category, otherwise, it's a subcategory
      ($catid < 10)? $table = "categories" : $table = "subcategories";
      $query = $this->db->query("SELECT name FROM $table WHERE id= '$catid'");
      $result = $this->db->get_row("NUM");
      $subcat_name = $result[0];
      return $subcat_name;
    } else {
      return false;
    }
  }
  
  function get_parent_cat_name($subcat) {
      // Get parent category information too
      $cat_query = "SELECT c.name FROM categories c, categories_subcategories cs 
                        WHERE cs.subcategory_id = '$subcat' AND c.id = cs.category_id";
      
      $result   = $this->db->query($cat_query);
      $category = $this->db->get_row("ASSOC");
      $cat_name = $category['name'];
      
      return $cat_name;
  }
  
  function get_cr_date() {
    if (!$this->id)
      return false;
  
    $type = strtolower($this->get_item_type());
    if ($type == 'housing')
      $table = 'items_housing';
    else
      $table = 'items';
      
    $query = "SELECT i.cr_date FROM $table i WHERE i.id = '{$this->id}'";
    $this->db->query($query);
    $result   = $this->db->get_row("NUM");
    $cr_date  = $result[0];
    
    return $cat_name;
  }
  
  // Returns name of the category_type of the item, as defined in the database
  // For now, this will be "Announcements and Services", "Goods and Items", or "Housing"
  // if itemid was valid
  function get_item_type() {
    if ($this->id) {
      $catid = $this->category_id;
      
      // Find out if item is in a top level category or in a subcategory
     if ($this->is_top_level_category($catid)) {
       $sql = "SELECT t.name FROM category_types AS t, categories AS c
              WHERE t.id = c.category_type AND c.id = '$catid'";
     } else {
       $sql = "SELECT t.name FROM category_types AS t, categories_subcategories AS s, categories AS c
              WHERE t.id = c.category_type AND  c.id = s.category_id AND s.subcategory_id = '$catid'";
      }

      $this->db->query($sql);
      $result = $this->db->get_row("NUM");
      $item_type = $result[0];
      return $item_type;
    } else {
      return false;
    }
  }
  
  // It is important to distinguish between subcategories and top-level categories
  // 
  function is_top_level_category($catid) {
    $sql = "SELECT count(*) FROM categories WHERE id = '$catid'";
    $query = $this->db->query($sql);
    $result = $this->db->get_row('NUM');
    $count = $result[0];
    if ($count > 0) {
      return true;
    } else {
      return false;
    }
  }

}
?>
