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
  $klistings->set_show_expired();
  #########################################
  
  // sort by category and subcategory, then date, then bedrooms
  $klistings->set_orderby('s.name, c.name, mod_date DESC, i.beds');

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
    $klistings->set_limit(0); // unlimited limit
    
    $count = $klistings->get_result_count();
    $items = $klistings->get_item_results();
  }
  
  // Print Category Listing title
  $subcategory_title_result = $klistings->get_subcat_name();
  $category_title_result    = $klistings->get_cat_name();
  if ($subcategory_title_result)
  {
    $category_title = $subcategory_title_result;
  } elseif ($category_title_result)  {
    $category_title = $category_title_result;
  } else {
    $category_title = "All Items";
  }
  
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
    echo "<p><a href=\"#more-info\">Additional rental contact information</a></p>";
      
    echo '<div id="table_container">';
    echo "\t<table class=\"itemstable\">";
    echo "\t\t<tr class=\"header\">";
    echo "\t\t\t<th class=\"th_type\">Type</th>";
    echo "\t\t\t<th class=\"th_title\">Item Title</th>";
    echo "\t\t\t<th class=\"th_small\">Beds</th>";
    echo "\t\t\t<th class=\"th_small\">Baths</th>";
    echo "\t\t\t<th class=\"th_small\">Rent</th>";
    echo "\t\t\t<th class=\"th_small\">Deposit</th>";
    echo "\t\t\t<th class=\"th_datetime\">Last Updated</th>";
    echo "\t\t</tr>";

    $i = 0;
    while($items[$i]['id']) {
      if ($i%2)
        echo "<tr class=\"odd\">";
      else
        echo "<tr class=\"even\">";
          
          $id      = $items[$i]['id'];
          $ls_type = ($items[$i]['cat_name']) ? $items[$i]['cat_name']: "General $category_title";
          $beds    = $items[$i]['beds'];
          $baths   = $items[$i]['baths'];
          $title   = ucfirst($items[$i]['itemtitle']);
          $rent    = "$".$items[$i]['rent'];
          $deposit = "$".$items[$i]['deposit'];
          $date    = relative_date($items[$i]['date']);
          
          echo "<td class=\"td_first\">$ls_type</td>";
          echo "<td><a href=\"../item/{$id}/\">{$title}</a></td>";
          echo "<td>$beds</td>";
          echo "<td>$baths</td>";
          echo "<td>$rent</td>";
          echo "<td>$deposit</td>";
          echo "<td>$date</td>";
        echo "</tr>";
        $i++;
    }

    echo "\t\t<tr class=\"header\">";
    echo "\t\t\t<th class=\"th_item\">Type</th>";
    echo "\t\t\t<th class=\"th_item\">Item Title</th>";
    echo "\t\t\t<th class=\"condition\">Beds</th>";
    echo "\t\t\t<th class=\"condition\">Baths</th>";
    echo "\t\t\t<th class=\"condition\">Rent</th>";
    echo "\t\t\t<th class=\"condition\">Deposit</th>";
    echo "\t\t\t<th class=\"th_datetime\">Posted</th>";
    echo "\t\t</tr>";
    echo "\t\t</table>";
    echo "</div>";
?>

  <hr /><br />
  <a name="more-info">
  <h2 style="text-align: center;">Additional Local Rental Infomation:</h2>
  <br />
  
  <p style="text-align: center; font-size: 120%; font-weight: bold;">PROPERTY MANAGEMENT:
  </p>
    <table cellspacing="0" cellpadding="0" border="0" align="" style="margin: 0 auto; width: 508px; height: 130px;">
        <tbody>
            <tr>
                <td width="221" height="17" align="left"><font size="3">Coldwell Banker &ndash; First Realtors</font></td>
                <td width="122" align="left"><font size="3">526-9000</font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3">Walla Walla Realty Co.</font></td>
                <td align="left"><font size="3">525-4303</font></td>
            </tr>
            <tr>
                <td height="16" align="left"><font size="3">Peterson Properties</font></td>
                <td align="left"><font size="3">529-3211</font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3">Windermere</font></td>
                <td align="left"><font size="3">526-RENT (7368)</font></td>
            </tr>
        </tbody>
    </table>
    <br /><br />
    <p style="text-align: center; font-size: 120%; font-weight: bold;">APARTMENTS:
    </p>
    <table cellspacing="0" cellpadding="0" border="0" align="" style="margin: 0 auto; width: 508px;">
        <tbody>
            <tr>
                <td width="125" height="16" align="left"><font size="3">College Place</font></td>
                <td width="221" align="left"><font size="3">The Ferns</font></td>
                <td width="122" align="left"><font size="3">522-9459</font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">Redwood Apts.</font></td>
                <td align="left"><font size="3">522-9422</font></td>
            </tr>
            <tr>
                <td height="16" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">&ldquo;Prof&rdquo; Winter Rentals L.L.C.</font></td>
                <td align="left"><font size="3">525-4807</font></td>
            </tr>
            <tr>
                <td height="16" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3"><br />
                </font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3">Walla Walla</font></td>
                <td align="left"><font size="3">Birchway Apts</font></td>
                <td align="left"><font size="3">529-0213</font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">Blue Mountain View Apts</font></td>
                <td align="left"><font size="3">522-0447</font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">Brentwood Apts</font></td>
                <td align="left"><font size="3">529-1384</font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">Centenial West Apts</font></td>
                <td align="left"><font size="3">525-7744</font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">Clinton Court Apts</font></td>
                <td align="left"><font size="3">525-0820</font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">Garden Court Apts</font></td>
                <td align="left"><font size="3">529-4706</font></td>
            </tr>
            <tr>
                <td height="16" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">Marcus Whitman Homes</font></td>
                <td align="left"><font size="3">525-6880</font></td>
            </tr>
            <tr>
                <td height="16" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">Mardis Manor Complex</font></td>
                <td align="left"><font size="3">525-4944</font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">Melrose East Apts</font></td>
                <td align="left"><font size="3">529-7379</font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">St. Pauls Apts</font></td>
                <td align="left"><font size="3">529-7190</font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">South Wilbur Apts</font></td>
                <td align="left"><font size="3">529-9451</font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">Stonecreek Apts</font></td>
                <td align="left"><font size="3">526-7650</font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">Suncrest Apts</font></td>
                <td align="left"><font size="3">522-1371</font></td>
            </tr>
            <tr>
                <td height="16" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3"><br />
                </font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3">Milton Freewater</font></td>
                <td align="left"><font size="3">Cherry Hill Apts</font></td>
                <td align="left"><font size="3">541-938-0398</font></td>
            </tr>
            <tr>
                <td height="16" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">Pioneer Commons</font></td>
                <td align="left"><font size="3">541-938-4929</font></td>
            </tr>
            <tr>
                <td height="17" align="left"><font size="3"><br />
                </font></td>
                <td align="left"><font size="3">Washington Park Apts</font></td>
                <td align="left"><font size="3">541-938-7447</font></td>
            </tr>
        </tbody>
    </table>
    
<?php
  } // end else
} else {  // the class was not able to retrive valid listingings in the category
  echo "<h2 class=\"errorLabel\">There was a problem retrieving results.</h2>";
} // end else


?>

<?php include("includes/footer.php"); ?>
