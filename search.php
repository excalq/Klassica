<?php
require_once ("includes/klassica.php");

$header["title"] = _HTML_TITLE_PREFIX." - Search Items";
$header["bodyid"] = "search";
$header['show_sideboxes'] = true;
$header["css"] = array('main', 'categories', 'listings');

require_once ("includes/header.php");

// perform listings search

// no valid search has been performed yet
$search_is_valid = false;

// if request was made from the header search box:
if ($_GET['ksearch_q']) {
  $search_requested = true;
  $klistings = new klassica_listing($dbconn);

  $query = $_GET['ksearch_q'];
  
  $klistings->title = $query;
  $klistings->description = $query;
  
  $items = $klistings->set_limit(0); // unlimited limit
  $count = $klistings->get_result_count();
  if ($count > 0) {
    $search_is_valid = true;
  }
 
  $items = array();
  $items = $klistings->get_item_results();

} elseif ($_GET['query'] && $_GET['search_type']) {
  // if search was requested from (this) search page
  
  $search_requested = true;
  $query = clean_input($_GET['query']);
  $search_type = clean_input($_GET['search_type']);
  
  $klistings = new klassica_listing($dbconn);
  
  switch ($search_type) {
    case 'title':  
      $klistings->title = $query;
    break;
    case 'description':  
      $klistings->description = $query;
    break;
    case 'title-desc':
      $klistings->title = $query;
      $klistings->description = $query;
    break;
    case 'location': 
      $klistings->location = $query;
    break;
    default: 
      $search_error = 'bad_type';
    break;
  }
  
  if ($search_is_valid != 'bad_type') {
    $items = $klistings->set_limit(0); // unlimited limit
    $count = $klistings->get_result_count();
    if ($count > 0) {
      $search_is_valid = true;
    }
    
    $items = array();
    $items = $klistings->get_item_results();
  }
}

?>

        <p class="titleLabel">Klassica Search (New!)</p>
        <form name="klassica_search" action="<?php echo _SITE_URL;?>/search/" method="get">
        <p class="b">Search for listings by:</p>
        <select name="search_type">
          <option value="title-desc" 
            <?php if ($search_type == 'title-desc') echo 'selected="selected"'; ?>>Title &amp; Description</option>
          <option value="title" 
            <?php if ($search_type == 'title') echo 'selected="selected"'; ?>>Title</option>
          <option value="description" 
            <?php if ($search_type == 'description') echo 'selected="selected"'; ?>>Description</option>
          <option value="location" 
            <?php if ($search_type == 'location') echo 'selected="selected"'; ?>>Location</option>
        </select>
        <input type="text" name="query" size="20" />
        <input type="submit" value="Search Klassica" />
      </form>
      
      <p>
        More types of searches will be available soon. You will be able to search listings by start and end dates,  categories, price range, names of sellers, as well as by other criteria. You will also be able to search by multiple criteria. We are still working on these features, so check back soon!
      </p>

<?php

if ($search_requested && !$search_error) {
  
    echo "<p><span class=\"titleLabel\">Search Results for the term: </span>
          <span class=\"b\" style=\"font-size: 120%; text-decoration: underline;\">$query</span>";
    echo " :: ($count Items)</p>";
  
    // if no results were found, display such a message
    if ($count == 0) {
      echo "<p><span class=\"b\" style=\"font-size: 120%\">Sorry, your search found no items.</strong></p>";
      echo "<p><a href=\""._SITE_URL."/\">Return to homepage</a></p>";
      // otherwise, list items as normal
    } else {
  
    echo '<div id="table_container">';
      echo "\t<table class=\"itemstable\">";
        echo "\t\t<tr class=\"header\">";
        echo "\t\t\t<th class=\"th_item\">Item Title</th>";
        echo "\t\t\t<th class=\"th_price\">Price</th>";
        echo "\t\t\t<th class=\"th_condition\">Condition</th>";
        echo "\t\t\t<th class=\"th_datetime\">Posted</th>";
        echo "\t\t</tr>";
      
      $i = 0;
      while($items[$i]['id']) {
      if ($i%2)
      echo "<tr class=\"odd\">";
        else
        echo "<tr class=\"even\">";
          
          $price = $items[$i]['price'];
          switch ($price) {
          case 'Wanted': $price = '(Wanted)'; break;
          case '0': case '': case 'free': case 'Free' : $price = 'Free!'; break;
          default: 
          $price = ($items[$i]['is_price_obo'])? "$$price OBO" : "$$price";
          break;
          }
          
          echo "<td class=\"itemtitle\"><a href=\"../item/".$items[$i]['id']."/\">";
          echo $items[$i]['itemtitle']."</a></td>";
          echo "<td class=\"price\">$price</td>";
          echo "<td class=\"condition\">".$items[$i]['cond']."</td>";
          echo "<td class=\"date\">".$items[$i]['date']."</td>";
          echo "</tr>";
        $i++;
        }
        
        echo "\t\t<tr class=\"header\">";
          echo "\t\t\t<th class=\"th_item\">Item Title</th>";
          
          echo "\t\t\t<th class=\"th_price\">Price</th>";
          echo "\t\t\t<th class=\"th_condition\">Condition</th>";
          
          echo "\t\t\t<th class=\"th_datetime\">Posted</th>";
          echo "\t\t</tr>";
        echo "\t\t</table>";
      echo "</div>";
    
    } // end else
} elseif ($search_error) {  // the class was not able to retrive valid listingings in the category
      echo "<h2 class=\"errorLabel\">There was a problem retrieving results.</h2>";
} // end if ($search_requested)

?>


<?php include("includes/footer.php"); ?>
