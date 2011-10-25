<?php

/* Populates Select Lists with data from Database */


function get_subcategories($db, $cattype, $selected_opt=false) {

  switch ($cattype) {
    // get 'goods & items' list for 'sell' page
    case 'sell':
      $category_type = "Goods and Items";
    break;
    // get announcements, employment, services, and housing items for 'announce' page
    case 'announce':
      $category_type = "Announcements and Services";
    break;
    default:
      echo "System Error: invalid category type setup.";
      return false;
    break;
  }

  // Build select list with this structure:
  // <select>
  //   <option>Select Item</option>
  //   <optgroup>$category
  //     <option>$subcategory[0]</option>
  //     <option>$subcategory[1]</option>
  //     <option>$subcategory[2]</option>
  //   </optgroup>
  // </select>

  // TODO: Build this section with fetch array constructs, to avoid all these redundant loops because of get_row
  
  // Get category types from database
  $db->query("SELECT id FROM `category_types` WHERE name = '$category_type' LIMIT 1;");
  $result = $db->get_row('NUM');
  $cat_type_id = $result[0];
  
  // Get categories from database
  $db->query("SELECT id,name FROM `categories` WHERE category_type = '$cat_type_id' ORDER BY id;");
  for ($i = 0; ($result = $db->get_row('ASSOC')); $i++) {
    $cat_result[$i] = $result;
  }

  // Get subcategories from database (several subcats per cat
  for ($j = 0; $j < count($cat_result); $j++) {
    $cat_id = $cat_result[$j]['id'];
    $db->query("SELECT subcategory_id FROM categories_subcategories WHERE category_id = '$cat_id' ORDER BY subcategory_id;");
    for ($k = 0; ($result = $db->get_row('ASSOC')); $k++) {
      $subcat_result[$j][$k] = $result;
    }
  }

  // Build Select Menu
  echo "<select name=\"item_category\" id=\"item_category\" class=\"selectform\">\n";
    // give a default option
    echo "\t\t<option value=\"\">Choose Category</option>\n";
  // build optgroups from categories
  for ($i = 0; $i < count($cat_result); $i++) {
    $cat_id = $cat_result[$i]['id'];
    $cat_name = $cat_result[$i]['name'];
    echo "\t<optgroup label=\"$cat_name\">\n";

    // build options from subcategories
    for ($j = 0; $j < count($subcat_result[$i]); $j++) {
      $subcat_id = $subcat_result[$i][$j]['subcategory_id'];
      // select the posted value, if there was one
      ($subcat_id == $selected_opt)? $sel = ' selected="selected"': $sel = '';
      // get subcategory name
      $db->query("SELECT name FROM subcategories WHERE id = '$subcat_id' ORDER BY id;");
      $result = $db->get_row('ASSOC');
      $subcat_name = $result['name'];
      // build option
      echo "\t\t<option value=\"$subcat_id\"$sel>$subcat_name</option>\n";
    }
    // select the posted value, if there was one
    ($cat_id == $selected_opt)? $sel = ' selected="selected"': $sel = '';
    // print "other" option with the id of just the parent category (no subcat)
    echo "\t\t<option value=\"$cat_id\"$sel>Other</option>\n";
    echo "\t</optgroup>\n";
  }
  echo "</select>*";
}


?>