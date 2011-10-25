<?php
//include_once('../includes/klassica.php');

?>
<div class="sidebox">
<h3 class="sidebox-header">Newest Listings</h3>
<?php

$sbox_klistings = new Klassica_listing($dbconn);
$sbox_klistings->set_limit(15);
$sbox_items = $sbox_klistings->get_new_items(false, false); // exclude free stuff and textbooks

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
