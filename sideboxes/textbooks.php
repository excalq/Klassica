<?php
//include_once('../includes/klassica.php');

?>
<div class="sidebox">
<h3 class="sidebox-header">Textbooks</h3>
<?php

// Get by category
$sbox_klistings = new Klassica_listing($dbconn);
$sbox_klistings->search_by_category(54);

$sbox_klistings->set_limit(15); // Get this many items
$sbox_items = $sbox_klistings->get_item_results();

if (count($sbox_items) == 0) {
  echo "<p>No items currently.</p>";
}

for($i=0; $i<count($sbox_items); $i++) {
  $sbox_itm = $sbox_items[$i];
  $sbox_title    = ucfirst($sbox_itm['itemtitle']);
  $sbox_location = ucfirst($sbox_itm['location']);
  
  switch ($sbox_itm['price']) {
    case 'Wanted': $sbox_priceline = "({$sbox_itm['price']})"; break;
    case '': $sbox_priceline = ''; break;
    default: $sbox_priceline = '$'.$sbox_itm['price']; break;
  }
  
  if ($sbox_location && $sbox_priceline) {
    $sbox_item_loc = ', '.$sbox_location;
  } else {
    $sbox_item_loc = $sbox_location;
  }

  echo "<p class=\"sidebox-link\"><span class=\"sidebox-item\">
        <a href=\""._SITE_URL."/item/{$sbox_itm['id']}/\">{$sbox_title}</a>
        </span><br />";
  echo "<a href=\""._SITE_URL."/item/{$sbox_itm['id']}/\">{$sbox_priceline}{$sbox_item_loc}</a></p>\n";

}
unset($sbox_items);
?>
<br />
</div>
