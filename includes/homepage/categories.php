<?php
include_once (_SERV_PATH . "/includes/klassica.php");

// TODO: make this more dymanic, and improve performance

  // Build array of item counts for each category
  $klistings = new klassica_listing($dbconn);
  
  $icount = array();
  $icount[0] = 0;
  
  $CATEGORY_COUNT = 9; // How many top-level categories are there?
  
  // Get values from 1 to n, and assign total sum to $icount[0]
  for ($i = 1; $i<=$CATEGORY_COUNT; $i++) {
    $klistings->search_by_category($i);
    $icount[$i] = $klistings->get_result_count();
    $icount[0] += $icount[$i];
  }

?>
<h3>There are <?php echo $icount[0]; ?> items in all categories. <a href="<?php echo _SITE_URL;?>/listings/all">View All Items</a></h3>

<div class="category_container">
  <a href="<?php echo _SITE_URL; ?>/listings/1">
    <img src="<?php echo _SITE_URL;?>/images/icons/announce.png" alt="Announcements" class="catImg" />
  </a>
  <h4>
    <a href="<?php echo _SITE_URL; ?>/listings/1" class="cat_title">Announcements</a> 
    [<?php echo "$icount[1] ";  echo ($icount[1] == 1)? "Item": "Items"; ?>]
  </h4>
  <p>
    <a href="<?php echo _SITE_URL; ?>/listings/10">WWU Events</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/11">WWU Clubs</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/12">Community Events</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/13">News</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/14">Lost &amp; Found</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/15">Ride Finder</a>
  </p>
</div>

<div class="category_container">
  <a href="<?php echo _SITE_URL; ?>/listings/2">
    <img src="<?php echo _SITE_URL;?>/images/icons/general.png" alt="General Merchandise" class="catImg" />
  </a>
  <h4>
    <a href="<?php echo _SITE_URL; ?>/listings/2" class="cat_title">General Items</a> 
    [<?php echo "$icount[2] ";  echo ($icount[2] == 1)? "Item": "Items"; ?>]
  </h4>
  <p>
    <a href="<?php echo _SITE_URL; ?>/listings/20">Free Stuff</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/21">Books</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/22">Clothing</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/23">House &amp; Yard</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/24">Musical Eq.</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/25">Tools &amp; HW</a>
  </p>
</div>

<div class="category_container">
  <a href="<?php echo _SITE_URL; ?>/listings/3">
    <img src="<?php echo _SITE_URL;?>/images/icons/auto.png" alt="Automotive" class="catImg" />
  </a>
  <h4>
    <a href="<?php echo _SITE_URL; ?>/listings/3" class="cat_title">Machines and Automotive</a> 
    [<?php echo "$icount[3] ";  echo ($icount[3] == 1)? "Item": "Items"; ?>]
  </h4>
  <p>
    <a href="<?php echo _SITE_URL; ?>/listings/30">Cars &amp; Trucks</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/31">Bicycles</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/32">Sportscraft</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/33">Utility Machines</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/34">Parts &amp; Tools</a>
  </p>                           
</div>                           
                                 
<div class="category_container"> 
  <a href="<?php echo _SITE_URL; ?>/listings/4">
    <img src="<?php echo _SITE_URL;?>/images/icons/comp.png" alt="Computers" class="catImg" />
  </a>
  <h4>
    <a href="<?php echo _SITE_URL; ?>/listings/4" class="cat_title">Computers and Electronics</a> 
    [<?php echo "$icount[4] ";  echo ($icount[4] == 1)? "Item": "Items"; ?>]
  </h4> 
  <p>
    <a href="<?php echo _SITE_URL; ?>/listings/40">Desktops &amp; Laptops</a> |
    <a href="<?php echo _SITE_URL; ?>/listings/41">Computer Hardware</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/42">Accessories</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/43">Electronics</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/44">Audio/Visual Eq.</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/45">CDs/DVDs/Media</a>
  </p>                           
</div>

<div class="category_container">
  <a href="<?php echo _SITE_URL; ?>/listings/5">
    <img src="<?php echo _SITE_URL;?>/images/icons/school.png" alt="Employment" class="catImg" />
  </a>
  <h4>
    <a href="<?php echo _SITE_URL; ?>/listings/5" class="cat_title">School and Office</a> 
    [<?php echo "$icount[5] ";  echo ($icount[5] == 1)? "Item": "Items"; ?>]
  </h4>
  <p>
    <a href="<?php echo _SITE_URL; ?>/listings/50">Class Materials</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/51">Office Furniture</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/52">Office Supplies</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/53">Paper Supplies</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/54">Textbooks</a>
  </p>                           
</div>                           
                                 
<div class="category_container"> 
  <a href="<?php echo _SITE_URL; ?>/listings/6">
    <img src="<?php echo _SITE_URL;?>/images/icons/sports.png" alt="Sporting Goods" class="catImg" />
  </a>
  <h4>
    <a href="<?php echo _SITE_URL; ?>/listings/6" class="cat_title">Sporting Goods</a> 
    [<?php echo "$icount[6] ";  echo ($icount[6] == 1)? "Item": "Items"; ?>]
  </h4>
  <p>
    <a href="<?php echo _SITE_URL; ?>/listings/60">Team Sports</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/61">Summer Sports</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/62">Winter Sports</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/63">Camping &amp; Hiking</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/64">Fishing &amp; Hunting</a>
  </p>
</div>

<div class="category_container">
  <a href="<?php echo _SITE_URL; ?>/listings/7">
    <img src="<?php echo _SITE_URL;?>/images/icons/employment.png" alt="Services" class="catImg" />
  </a>
  <h4>
    <a href="<?php echo _SITE_URL; ?>/listings/7" class="cat_title">Employment</a> 
    [<?php echo "$icount[7] ";  echo ($icount[7] == 1)? "Item": "Items"; ?>]
  </h4>
  <p>
    <a href="<?php echo _SITE_URL; ?>/listings/70">On Campus Jobs</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/71">Off Campus Jobs</a> | 
    <a href="<?php echo _SITE_URL; ?>/listings/72">Graduate Opportunities</a> |
    <a href="<?php echo _SITE_URL; ?>/listings/73">Summer &amp; Internship</a>
  </p>
</div>

<div class="category_container">
  <a href="<?php echo _SITE_URL; ?>/listings/8">
    <img src="<?php echo _SITE_URL;?>/images/icons/services.png" alt="Services" class="catImg" />
  </a>
  <h4>
    <a href="<?php echo _SITE_URL; ?>/listings/8" class="cat_title">Services</a> 
    [<?php echo "$icount[8] ";  echo ($icount[8] == 1)? "Item": "Items"; ?>]
  </h4>
  <p>
    <a href="<?php echo _SITE_URL; ?>/listings/80">Academic help</a> |
    <a href="<?php echo _SITE_URL; ?>/listings/81">Caretaking</a> |
    <a href="<?php echo _SITE_URL; ?>/listings/82">Design &amp; Media</a> |
    <a href="<?php echo _SITE_URL; ?>/listings/83">Financial Services</a> |
    <a href="<?php echo _SITE_URL; ?>/listings/84">Computer Services</a>
  </p>
</div>

<div class="category_container">
  <a href="<?php echo _SITE_URL; ?>/listings/9">
    <img src="<?php echo _SITE_URL;?>/images/icons/house.png" alt="Housing" class="catImg" />
  </a>
  <h4>
    <a href="<?php echo _SITE_URL; ?>/listings-housing/9" class="cat_title">Housing</a> 
    [<?php echo "$icount[9] ";  echo ($icount[9] == 1)? "Item": "Items"; ?>]
  </h4>
  <p>
    <a href="<?php echo _SITE_URL; ?>/listings-housing/90">Apartments</a> |
    <a href="<?php echo _SITE_URL; ?>/listings-housing/91">Houses</a> |
    <a href="<?php echo _SITE_URL; ?>/listings-housing/92">Housing Wanted</a> |
    <a href="<?php echo _SITE_URL; ?>/listings-housing/93">Roommate Wanted</a> |
    <a href="<?php echo _SITE_URL; ?>/listings-housing/94">Roommate Avaliable</a>
  </p>
</div>
