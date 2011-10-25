<?php
/************************************************************\
*
*   freeCap v1.4.1 Copyright 2005 Howard Yeend
*   www.puremango.co.uk
*
*    This file is part of freeCap.
*
*    freeCap is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    freeCap is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with freeCap; if not, write to the Free Software
*    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
*
\************************************************************/

// this is the 'form' side of the script.
// I suggest making the user fill in the main form
// then take them here and update the information with a 'freecap_passed' flag if they enter the
// correct word. This way, if they don't enter the right word, they don't lose all their data
// and you don't have to code a form that remembers all their data
// also, if someone is spamming you, you've got a log of all the failed attempts
// which might prove useful for legal action or just for amusement, plus you'll be able to see
// if you're stopping spammers or if the majority of failed registrations are valid users who
// just can't read the word properly...

// To avoid blocking out partially sighted users, I'd suggest having a 'submit without entering word'
// button, which sends the info to you for manual verification. It's a lot simpler than trying to
// implement a secure audio-captcha.


if(!empty($_SESSION['freecap_word_hash']) && !empty($_POST['word']))
{
  // all freeCap words are lowercase.
  // font #4 looks uppercase, but trust me, it's not...
  if($_SESSION['hash_func'](strtolower($_POST['word']))==$_SESSION['freecap_word_hash'])
  {
    // reset freeCap session vars
    // cannot stress enough how important it is to do this
    // defeats re-use of known image with spoofed session id
    $_SESSION['freecap_attempts'] = 0;
    $_SESSION['freecap_word_hash'] = false;


    // now process form


    // now go somewhere else
    // header("Location: somewhere.php");
    $word_ok = "yes";
  } else {
    $word_ok = "no";
  }
} else {
  $word_ok = false;
}
?>