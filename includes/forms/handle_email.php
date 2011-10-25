<?php


function is_valid_email($email) {
// returns TRUE if email address is valid

  $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
  $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
  $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'.
    '\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
  $quoted_pair = '\\x5c[\\x00-\\x7f]';
  $domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
  $quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
  $domain_ref = $atom;
  $sub_domain = "($domain_ref|$domain_literal)";
  $word = "($atom|$quoted_string)";
  $domain = "$sub_domain(\\x2e$sub_domain)*";
  $local_part = "$word(\\x2e$word)*";
  $addr_spec = "$local_part\\x40$domain";
  return preg_match("!^$addr_spec$!", $email) ? 1 : 0;
}

function injection_chars($s, $check_all_patterns = true) {
// returns TRUE if 'bad' characters are found
  if ($check_all_patterns) {
    return (eregi("\r", $s) || eregi("\n", $s) || eregi("%0a", $s) || eregi("%0d", $s) 
              || eregi("to:", $s) || eregi("from:", $s) || eregi("cc:", $s) || eregi("bcc:", $s)
             || eregi("content-type:", $s)) ? TRUE : FALSE;
  } else { // mainly for textareas, where new lines are ok
    return (eregi("to:", $s) || eregi("from:", $s) || eregi("cc:", $s) || eregi("bcc:", $s)
              || eregi("content-type:", $s)) ? TRUE : FALSE;
  }
}


function strip_colons($s) {
  return str_replace(array(':', '%3a'), " ", $s);
}



//clean input in case of header injection attempts!
function clean_input_4email($value, $check_all_patterns = true) {
 $patterns[0] = '/content-type:/';
 $patterns[1] = '/to:/';
 $patterns[3] = '/bcc:/';
 $patterns[2] = '/cc:/';
 
 if ($check_all_patterns)
 {
  $patterns[4] = '/\r/';
  $patterns[5] = '/\n/';
  $patterns[6] = '/%0a/';
  $patterns[7] = '/%0d/';
 }
 //NOTE: can use str_ireplace as this is case insensitive but only available on PHP version 5.0.
  return preg_replace($patterns, "", strtolower($value));
 // return eregi_replace($patterns, "", $value);

}


/*
  Based on:
  PHP Form Mailer - phpFormMailer (easy to use and more secure than many cgi form mailers)
   FREE from:

    www.TheDemoSite.co.uk
*/
?>