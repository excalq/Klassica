<?php

// Part of spam protection functionality
// See: http://jamesthornton.com/software/redirect-mailto.html
// redirect-mailto.php
 
header("Location: mailto:$_GET[u]@$_GET[d]");

?> 