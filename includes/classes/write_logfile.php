<?php
/////////////////////////////
// write_logfile.php
// Log File writing class
// 2007 - Arthur Ketcham - dev <at> arthurk.com
//
// /* Example Usage */
// include ('include/writelog.php');
// $filepath = 'log/system_errors.log';
// $log = new write_logfile($filepath);
// $log->write('Authentication: User has logged in successfully');
//
// Inputs: - Log filename/path in constructor
//           Message to write to logfile in write()
// Ouptputs: - Constructor returns TRUE if logfile was writable,
//             FALSE if not writable or non-existant
//           - wrtite('text') outputs 'text' to logfile
//
//
//////////////////////////////


class write_logfile {
  // 'var $logfile' for php4
  // should to be 'private $logfile' if php 5 is supported
  private $logfile;
  
  // Constructor
  // Tests if logfile exists and is writable
  function write_logfile($logfile) {
    // Let's make sure the file exists and is writable first.
    if (is_writable($logfile) && fopen($logfile, 'a'))
      $this->logfile = $logfile;
    else
      return false;
  }

  function write($text) {
      // In our example we're opening $filename in append mode.
      // The file pointer is at the bottom of the file hence
      // that's where $somecontent will go when we fwrite() it.
      if (!$handle = fopen($this->logfile, 'a')) {
        return false;
      }
      
      $logtext = date('Y-m-d H:i:s')." $text\n";
    
      // Write $somecontent to our opened file.
      if (fwrite($handle, $logtext) === FALSE) {
        return false;
      }
    
      fclose($handle);
      return true;
  }
}
?>