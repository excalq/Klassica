<?php
//
//
// TODO: Develop a way to list all items in system using _GET var and listings class



require_once ("includes/klassica.php");

$header["title"] = _HTML_TITLE_PREFIX." - View Listings";
$header['show_sideboxes'] = true;
$header["css"] = array('main', 'categories', 'listings');
require_once ("includes/header.php");

?>

<?php



// get category id from URL (with bounds checking)
// TODO: Maybe we should check the number of subcats and set the bounds from that
// catid information stored as last part of URI path
$uri = explode("/",$_SERVER['REQUEST_URI']);
$catid = $uri[count($uri) - 1];

if (($catid > 0 && $catid < 1000) || ($catid == 'all')) {
  $cat_id = $catid;
}

// declare this var as false by default
$listing_is_valid = false;

if ($cat_id) {
  $klistings = new klassica_listing($dbconn);
  #########################################
  // Testing Only
  // $klistings->set_show_expired();
  #########################################

  // $cat_id '0' means show all items
  if ($cat_id != 'all') {
    $klistings->search_by_category($cat_id);
  }
  
  // if listings object failed to initialize (returned -1)
  if ($klistings == false) {
    $listing_is_valid = false;
  } else {
    // otherwise, get listing items as normal
    $listing_is_valid = true;
    $items = $klistings->set_limit(0); // unlimited limit
        
    $count = $klistings->get_result_count();
    $items = $klistings->get_item_results();
  }
  
  // Print Category Listing title
  if ($category_title = $klistings->get_cat_name());
  elseif ($category_title = $klistings->get_subcat_name());
  else $category_title = "All Items";
  
  // Should we show the price and condition columns?
  $show_price_cols = $klistings->is_sales_listing($cat_id);
  
  echo "<p><span class=\"titleLabel\">Recent Listings in: </span><strong>$category_title</strong>";
  echo " :: ($count Items)</p>";
}

if ($listing_is_valid) {

  // if no results were found, display such a message
  if ($count == 0) {
    echo "<p><span class=\"b\">Sorry, No items are avaliable in this category.</strong></p>";
    echo "<p><a href=\""._SITE_URL."/\">Return to homepage</a></p>";
  // otherwise, list items as normal
  } else {


    echo '<div id="table_container">';
    echo "\t<table class=\"itemstable\">";
    echo "\t\t<tr class=\"header\">";
    echo "\t\t\t<th class=\"th_title\">Item Title</th>";
    if ($show_price_cols) {
      echo "\t\t\t<th class=\"th_price\">Price</th>";
      echo "\t\t\t<th class=\"th_condition\">Condition</th>";
    }
    echo "\t\t\t<th class=\"th_datetime\">Last Updated</th>";
    echo "\t\t</tr>";

      $i = 0;
      while($items[$i]['id']) {
      
      $id      = $items[$i]['id'];
      $title   = ucfirst($items[$i]['itemtitle']);
      $ls_type = ($items[$i]['cat_name']) ? $items[$i]['cat_name']: "General $category_title";
      $title   = ucfirst($items[$i]['itemtitle']);
      $cond    = ucfirst($items[$i]['cond']);
      $date    = relative_date($items[$i]['date']);
      
      $price = $items[$i]['price'];
      switch ($price) {
        case 'Wanted': $price = '(Wanted)'; break;
        case '0': case '': case 'free': case 'Free' : $price = 'Free!'; break;
        default: 
          $price = ($items[$i]['is_price_obo'])? "$$price OBO" : "$$price";
        break;
      }
    
      if ($i%2)
        echo "<tr class=\"odd\">";
      else
        echo "<tr class=\"even\">";   
          echo "<td class=\"td_first\"><a href=\"../item/$id\">$title</a></td>";
        if ($show_price_cols) {
          echo "<td class=\"\">$price</td>";
          echo "<td class=\"\">$cond</td>";
        }
          echo "<td class=\"\">$date</td>";
        echo "</tr>";
        $i++;
    }

    echo "\t\t<tr class=\"header\">";
    echo "\t\t\t<th class=\"th_item\">Item Title</th>";
    
    if ($show_price_cols) {
      echo "\t\t\t<th class=\"th_price\">Price</th>";
      echo "\t\t\t<th class=\"th_condition\">Condition</th>";
    }
    
    echo "\t\t\t<th class=\"th_datetime\">Posted</th>";
    echo "\t\t</tr>";
    echo "\t\t</table>";
    echo "</div>";

  } // end else
} else {  // the class was not able to retrive valid listingings in the category
  echo "<h2 class=\"errorLabel\">There was a problem retrieving results.</h2>";
} // end else


?>

<?php include("includes/footer.php"); ?>
