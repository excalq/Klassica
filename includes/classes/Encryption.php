<?php 
// *****************************************************************************
// Copyright 2003-2004 by A J Marston <http://www.tonymarston.net>
// Distributed under the GNU General Public Licence
// *****************************************************************************

class Encryption {

    var $scramble1;         // 1st string of ASCII characters
    var $scramble2;         // 2nd string of ASCII characters
    
    var $errors;            // array of error messages
    var $adj;               // 1st adjustment value (optional)
    var $mod;               // 2nd adjustment value (optional)
    
    // ****************************************************************************
    // class constructor
    // ****************************************************************************
    function encryption ()
    {
        $this->errors = array();
        
        // Each of these two strings must contain the same characters, but in a different order.
        // Use only printable characters from the ASCII table.
        // Do not use single quote, double quote or backslash as these have special meanings in PHP.
        // Each character can only appear once in each string EXCEPT for the first character
        // which must be duplicated at the end (this gets round a bijou problemette when the
        // first character of the password is also the first character in $scramble1).
        $this->scramble1 = '! #$%&()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_`abcdefghijklmnopqrstuvwxyz{|}~!';
        $this->scramble2 = 'f^jAE]okIOzU[2&q1{3`h5w_794p@6s8?BgP>dFV=m D<TcS%Ze|r:lGK/uCy.Jx)HiQ!#$~(;Lt-R}Ma,NvW+Ynb*0Xf';

        if (strlen($this->scramble1) <> strlen($this->scramble2)) {
            $this->errors[] = '** SCRAMBLE1 is not same length as SCRAMBLE2 **';
        } // if
        
        $this->adj = 1.75;  // this value is added to the rolling fudgefactors
        $this->mod = 3;     // if divisible by this the adjustment is made negative
        
    } // constructor
    
    // ****************************************************************************
    function decrypt ($key, $source) 
    // decrypt string into its original form
    {
        //DebugBreak();
        // convert $key into a sequence of numbers
        $fudgefactor = $this->_convertKey($key);
        if ($this->errors) return;
        
        if (empty($source)) {
            $this->errors[] = 'No value has been supplied for decryption';
            return;
        } // if
        
        $target = null;
        $factor2 = 0;
        
        for ($i = 0; $i < strlen($source); $i++) {
            // extract a character from $source
            $char2 = substr($source, $i, 1);
            
            // identify its position in $scramble2
            $num2 = strpos($this->scramble2, $char2);
            if ($num2 === false) {
                $this->errors[] = "Source string contains an invalid character ($char2)";
                return;
            } // if
            
            if ($num2 == 0) {
                // use the last occurrence of this letter, not the first
                $num2 = strlen($this->scramble1)-1;
            } // if
            
            // get an adjustment value using $fudgefactor
            $adj     = $this->_applyFudgeFactor($fudgefactor);
            
            $factor1 = $factor2 + $adj;                 // accumulate in $factor1
            $num1    = round($factor1 * -1) + $num2;    // generate offset for $scramble1
            $num1    = $this->_checkRange($num1);       // check range
            $factor2 = $factor1 + $num2;                // accumulate in $factor2
            
            // extract character from $scramble1
            $char1 = substr($this->scramble1, $num1, 1);
            
            // append to $target string
            $target .= $char1;

            //echo "char1=$char1, num1=$num1, adj= $adj, factor1= $factor1, num2=$num2, char2=$char2, factor2= $factor2<br />\n";
            
        } // for
        
        return rtrim($target);
        
    } // decrypt
    
    // ****************************************************************************
    function encrypt ($key, $source, $sourcelen = 0) 
    // encrypt string into a garbled form
    {
        //DebugBreak();
        // convert $key into a sequence of numbers
        $fudgefactor = $this->_convertKey($key);
        if ($this->errors) return;

        if (empty($source)) {
            $this->errors[] = 'No value has been supplied for encryption';
            return;
        } // if
        
        // pad $source with spaces up to $sourcelen
        while (strlen($source) < $sourcelen) {
            $source .= ' ';
        } // while
        
        $target = null;
        $factor2 = 0;
        
        for ($i = 0; $i < strlen($source); $i++) {
            // extract a character from $source
            $char1 = substr($source, $i, 1);
            
            // identify its position in $scramble1
            $num1 = strpos($this->scramble1, $char1);
            if ($num1 === false) {
                $this->errors[] = "Source string contains an invalid character ($char1)";
                return;
            } // if
            
            // get an adjustment value using $fudgefactor
            $adj     = $this->_applyFudgeFactor($fudgefactor);
            
            $factor1 = $factor2 + $adj;             // accumulate in $factor1
            $num2    = round($factor1) + $num1;     // generate offset for $scramble2
            $num2    = $this->_checkRange($num2);   // check range
            $factor2 = $factor1 + $num2;            // accumulate in $factor2
            
            // extract character from $scramble2
            $char2 = substr($this->scramble2, $num2, 1);
            
            // append to $target string
            $target .= $char2;

            //echo "char1=$char1, num1=$num1, adj= $adj, factor1= $factor1, num2=$num2, char2=$char2, factor2= $factor2<br />\n";
            
        } // for
        
        return $target;
        
    } // encrypt
    
    // ****************************************************************************
    function getAdjustment () 
    // return the adjustment value
    {
        return $this->adj;
        
    } // setAdjustment
    
    // ****************************************************************************
    function getModulus () 
    // return the modulus value
    {
        return $this->mod;
        
    } // setModulus
    
    // ****************************************************************************
    function setAdjustment ($adj) 
    // set the adjustment value
    {
        $this->adj = (float)$adj;
        
    } // setAdjustment
    
    // ****************************************************************************
    function setModulus ($mod) 
    // set the modulus value
    {
        $this->mod = (int)abs($mod);    // must be a positive whole number
        
    } // setModulus
    
    // ****************************************************************************
    // private methods
    // ****************************************************************************
    function _applyFudgeFactor (&$fudgefactor) 
    // return an adjustment value  based on the contents of $fudgefactor
    // NOTE: $fudgefactor is passed by reference so that it can be modified
    {
        $fudge = array_shift($fudgefactor);     // extract 1st number from array
        $fudge = $fudge + $this->adj;           // add in adjustment value
        $fudgefactor[] = $fudge;                // put it back at end of array
        
        if (!empty($this->mod)) {               // if modifier has been supplied
            if ($fudge % $this->mod == 0) {     // if it is divisible by modifier
                $fudge = $fudge * -1;           // make it negative
            } // if
        } // if
        
        return $fudge;
        
    } // _applyFudgeFactor
    
    // ****************************************************************************
    function _checkRange ($num) 
    // check that $num points to an entry in $this->scramble1
    {
        $num = round($num);         // round up to nearest whole number
        
        // indexing starts at 0, not 1, so subtract 1 from string length
        $limit = strlen($this->scramble1)-1;
        
        while ($num > $limit) {
            $num = $num - $limit;   // value too high, so reduce it
        } // while
        while ($num < 0) {
            $num = $num + $limit;   // value too low, so increase it
        } // while
        
        return $num;
        
    } // _checkRange
    
    // ****************************************************************************
    function _convertKey ($key) 
    // convert $key into an array of numbers
    {
        if (empty($key)) {
            $this->errors[] = 'No value has been supplied for the encryption key';
            return;
        } // if
        
        $array[] = strlen($key);    // first entry in array is length of $key
        
        $tot = 0;
        for ($i = 0; $i < strlen($key); $i++) {
            // extract a character from $key
            $char = substr($key, $i, 1);
            
            // identify its position in $scramble1
            $num = strpos($this->scramble1, $char);
            if ($num === false) {
                $this->errors[] = "Key contains an invalid character ($char)";
                return;
            } // if
            
            $array[] = $num;        // store in output array
            $tot = $tot + $num;     // accumulate total for later
        } // for
        
        $array[] = $tot;            // insert total as last entry in array
        
        return $array;
        
    } // _convertKey
    
// ****************************************************************************
} // end Encryption
// ****************************************************************************

?>