      </div>
      <div class="clear">
      </div>
    </div>
  </div> <?php // end of #container ?>
  
  <div id="footer">
    <p>Klassica.org - &copy; 2003-<?php echo date("Y"); ?> - v<?php echo _VERSION ?> - Klassica Software Co. - <a href="<?php echo _SITE_URL;?>/about/" class="nb-link">Legal Terms and Conditions</a>
    <?php 
      if (!$_SERVER['HTTPS'])
      {
        // Avoid putting not HTTPS objects on HTTPS pages (to prevent some browswers from freaking out, and theoretically block XSS attacks)
        echo '<a href="http://www.spreadfirefox.com/?q=affiliates&amp;id=0&amp;t=64"><img class="footerImage" id="firefoxImage" alt="Get Firefox!" title="Get Firefox!" src="http://sfx-images.mozilla.org/affiliates/Buttons/110x32/get.gif"/></a>';
      }
    ?>
    </p>

</div>
</body>
</html>
<?php
  // Close database connection
  if ($dbconn)
    $dbconn->disconnect();
?>