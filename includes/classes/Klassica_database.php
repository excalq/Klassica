<?php
/****************************************************
 Database class
 License:   GNU General Public License (GPL)
 http://phpclasses.promoxy.com/browse/package/881.html

 @author    Sven Wagener <wagener_at_indot_dot_de>
 @copyright Sven Wagener
 @include      Function:_include_
/****************************************************


/****************************************************
EXAMPLE USAGE:

include("database.class.php");

// Connecting to database
$db=new database("mysql","localhost","addresses","username","password");

// Switching to debug mode
$db->debug_mode();

// Making query
$db->query("SELECT * FROM addresses");

// Counting results and showing it
echo $db->count_rows();

// Printing results
while($my_row=$db->get_row('ASSOC'|'NUM'|'BOTH')){
    echo $my_row['name'];    
}


// **** Connecting ODBC database ******

$db=new database("odbc","","","","",false,"odbc-name-of-db"); 


****************************************/
class database{
    var $database_types="";
    
    var $db_connect="";
    var $db_close="";
    var $db_select_db="";
    var $db_query="";
    var $db_fetch_array="";
    var $db_num_rows="";
    var $db_affected_rows="";
    
    var $host;
    var $database;
    var $user;
    var $password;
    var $port;
    var $database_type;
    var $dsn;
    
    var $sql;
    
    var $is_connected=false; // Is the DB currently connected?
    var $valid_query=false; // boolean: set if a vaid query was just executed (to avoid errors on count_rows();
    var $con; // variable for connection id
    var $con_string; // variable for connection string
    var $query_id; // variable for query id
    
    var $errors; // variable for error messages
    var $error_count=0; // variable for counting errors
    var $error_nr;
    var $error;
    
    var $debug = false; // debug mode off (error msgs are pretty worthless anyway)
    
    /***************
    * Constructor of class - Initializes class and connects to the database
    * @param string $database_type the name of the database (ifx=Informix,msql=MiniSQL,mssql=MS SQL,mysql=MySQL,pg=Postgres SQL,sybase=Sybase)
    * @param string $host the host of the database
    * @param string $database the name of the database
    * @param string $user the name of the user for the database
    * @param string $password the passord of the user for the database
    * @desc Constructor of class - Initializes class and connects to the database.
    *
    *  You can use this shortcuts for the database type:
    *
    *         ifx -> INFORMIX
    *         msql -> MiniSQL
    *         mssql -> Microsoft SQL Server
    *         mysql -> MySQL
    *        odbc -> ODBC
    *         pg -> Postgres SQL
    *        sybase -> Sybase
    */
    function database($database_type,$host,$database,$user,$password,$port=false,$dsn=false){
        $database_type=strtolower($database_type);
        $this->host=$host;
        $this->database=$database;
        $this->user=$user;
        $this->password=$password;
        $this->port=$port;
        $this->dsn=$dsn;
        
        $this->database_types=array("ifx","msql","mssql","mysql","odbc","pg","sybase");
        
        // Setting database type and connect to database
        if(in_array($database_type,$this->database_types)){
            $this->database_type=$database_type;
            
            $this->db_connect=$this->database_type."_connect";
            $this->db_close=$this->database_type."_close";
            $this->db_select_db=$this->database_type."_select_db";
            
            if($database_type=="odbc"){
                $this->db_query=$this->database_type."_exec";
                $this->db_fetch_array=$this->database_type."_fetch_row";
            }else{
                $this->db_query=$this->database_type."_query";
                $this->db_fetch_array=$this->database_type."_fetch_array";
            }
            
            $this->db_num_rows=$this->database_type."_num_rows";
            
            // Mysql only ???
            $this->db_affected_rows=$this->database_type."_affected_rows";
            
            return $this->connect();
        }else{
            $this->halt("Database type not supported");
            return false;
        }
    }
    
    /***************
    * This function connects the database
    * @return boolean $is_connected Returns true if connection was successful otherwise false
    * @desc This function connects to the database which is set in the constructor
    */
    function connect(){
        // Selecting connection function and connecting
        
        if ($this->is_connected) {
          echo "CONENCTED";
        }
        if($this->con==""){
            // INFORMIX
            if($this->database_type=="ifx"){
                $this->con=call_user_func($this->db_connect,$this->database."@".$this->host,$this->user,$this->password);
            }else if($this->database_type=="mysql"){
                // With port
                if(!$this->port){
                    $this->con=call_user_func($this->db_connect,$this->host.":".$this->port,$this->user,$this->password);
                }
                // Without port
                else{
                    $this->con=call_user_func($this->db_connect,$this->host,$this->user,$this->password);
                }
                // mSQL
            }else if($this->database_type=="msql"){
                $this->con=call_user_func($this->db_connect,$this->host,$this->user,$this->password);
                // MS SQL Server
            }else if($this->database_type=="mssql"){
                $this->con=call_user_func($this->db_connect,$this->host,$this->user,$this->password);
                // ODBC
            }else if($this->database_type=="odbc"){
                $this->con=call_user_func($this->db_connect,$this->dsn,$this->user,$this->password);
                // Postgres SQL
            }else if($this->database_type=="pg"){
                // With port
                if(!$this->port){
                    $this->con=call_user_func($this->db_connect,"host=".$this->host." port=".$this->port." dbname=".$this->database." user=".$this->user." password=".$this->password);
                }
                // Without port
                else{
                    $this->con=call_user_func($this->db_connect,"host=".$this->host." dbname=".$this->database." user=".$this->user." password=".$this->password);
                }
                // Sybase
            }else if($this->database_type=="sybase"){
                $this->con=call_user_func($this->db_connect,$this->host,$this->user,$this->password);
            }
            
            // if connected was not successfull
            if(!$this->con){
                $this->halt("Wrong connection data! Can't establish connection to host.");
                return false;
            // if connection was successful
            }else{
                if($this->database_type!="odbc"){
                    if(!call_user_func($this->db_select_db,$this->database,$this->con)){
                        $this->halt("Wrong database data! Can't select database.");
                        return false;
                    }else{
                         // Conncted and verified!
                        $this->is_connected = true;
                        return true;
                    }
                }
            }
        }else{
            $this->halt("Already connected to database.");
            return false;
        }
    }
    
    /***************
    * This function disconnects from the database
    * @desc This function disconnects from the database
    */
    function disconnect(){
        if(@call_user_func($this->db_close,$this->con)){
            $this->is_connected = false;
            return true;
        }else{
            $this->halt("Not connected yet");
            return false;
        }
    }
    
    /***************
    * This function starts the sql query
    * @param string $sql_statement the sql statement
    * @return boolean $successfull returns false on errors otherwise true
    * @desc This function disconnects from the database
    */
    function query($sql_statement){
        // set to false, until a valid query happens
        $this->valid_query = false;
        
        if ($this->is_connected == false) {
          return false;
        }
        $this->sql=$sql_statement;
        if($this->debug){
            printf("SQL statement: %s\n",$this->sql);
        }
        if($this->database_type=="odbc"){
            // ODBC
            if(!$this->query_id=call_user_func($this->db_query,$this->con,$this->sql)){
                $this->halt("No database connection exists or invalid query");
            }else{
                if (!$this->query_id) {
                    $this->halt("Invalid SQL Query");
                    return false;
                }else{
                    $this->valid_query = true;
                    return true;
                }
            }
        }else{
            // All other databases (MySQL, etc.)
             //var_dump ($this->con) . "<br />";
            if(!$this->query_id=call_user_func($this->db_query,$this->sql,$this->con)){
                $this->halt("No database connection exists or invalid query");
                return false;
            }else{
                if (!$this->query_id) {
                    $this->halt("Invalid SQL Query");
                    return false;
                }else{
                    $this->valid_query = true;
                    return true;
                }
            }
        }
    }
    
/****************************
* This function returns a row of the resultset
* @return array $row the row as array or false if there is no more row
* @desc This function returns a row of the resultset
* 
* Modified by Arthur Ketcham <ketcar @ (google's email service)>
* Takes optional argument: result_type [assoc|num|both] (both is default) 
*/
function get_row($result_type="BOTH"){
    if ($this->valid_query == false) {
      return false;
    }
    if($this->database_type=="odbc"){
        // ODBC database
        if($row=call_user_func($this->db_fetch_array,$this->query_id)){
            
            for ($i=1; $i<=odbc_num_fields($this->query_id); $i++) {
                $fieldname=odbc_field_name($this->query_id,$i);
                $row_array[$fieldname]=odbc_result($this->query_id,$i);
            }
            return $row_array;
        }else{
            return false;
        }
    }else{
        // All other databases
        // result type argument [assoc|num|both] (both is default) 
        $fetch_array_arg = $this->database_type."_".$result_type;
        $fetch_array_arg = constant(strtoupper($fetch_array_arg));

        $row=call_user_func($this->db_fetch_array,$this->query_id,$fetch_array_arg);
        return $row;
    }
}
    
    /***************
    * This function returns number of rows affected by INSERT, UPDATE, and DELETE statements
    * @return int $row_count the nuber of rows affected last statement
    * @desc This function returns number of rows affected last statement
    * NOTE: This might only work with MYSQL
    */
    function affected_rows(){
        if ($this->valid_query == false) {
          $this->halt("Can't count rows until valid query was made");
          return false;
        }
        $row_count=call_user_func($this->db_affected_rows);
        if($row_count>=0){
            return $row_count;
        }else{
            $this->halt("No rows returned from database");
            return false;
        }
    }

    /***************
    * This function returns number of rows in the resultset
    * @return int $row_count the nuber of rows in the resultset
    * @desc This function returns number of rows in the resultset
    */
    function count_rows(){
        if ($this->valid_query == false) {
          $this->halt("Can't count rows until valid query was made");
          return false;
        }
        $row_count=call_user_func($this->db_num_rows,$this->query_id);
        if($row_count>=0){
            return $row_count;
        }else{
            $this->halt("No rows returned from database");
            return false;
        }
    }
    /***************
    * This function returns all tables of the database in an array
    * @return array $tables all tables of the database in an array
    * @desc This function returns all tables of the database in an array
    */
    function get_tables(){
        if($this->database_type=="odbc"){
            // ODBC databases
            $tablelist=odbc_tables($this->con);
            
            for($i=0;odbc_fetch_row($tablelist);$i++) {
                $tables[$i]=odbc_result($tablelist,3);
            }
            return $tables;
        }else{
            // All other databases
            $tables = "";
            $sql="SHOW TABLES";
            $this->query_id($sql);
            for($i=0;$data=$this->get_row();$i++){
                $tables[$i]=$data['Tables_in_'.$this->database];
            }
            return $tables;
        }
    }
    
    /***************
    * Prints out a error message
    * @param string $message all occurred errors as array
    * @desc Returns all occurred errors
    */
    function halt($message){
        if($this->debug){
            printf("Database error: %s\n", $message);
            if($this->error_nr!="" && $this->error!=""){
                printf("MySQL Error: %s (%s)\n",$this->error_nr,$this->error);
            }
            echo "<h3>Backtrace</h3>
              <pre>";
            debug_print_backtrace();
            echo "</pre>";
            die ("Session halted.");
        }
    }
    
    /***************
    * Switches to debug mode
    * @param boolean $switch
    * @desc Switches to debug mode
    */
    function debug_mode($debug=true){
        $this->debug=$debug;
    }
    
    
}
?>