<?php

/****************************************
Klassica_users Class
(c) 2006.09.13 Arthur Ketcham


****************************************/
class Klassica_user {

  var $userid;
  var $username;
  var $password;
  var $fname;
  var $lname;
  var $email;
  var $phone;
  var $location;
  var $user_type; // student, etc
  var $user_role; // admin or user
  var $locked;

  // constructor function
  // inputs: (optional) userid to fetch from database
  // returns true, unless an invalid userid was given (if so, false is returned)
  function klassica_user($db, $userid=false) {
    $this->db = $db;
    if ($userid) {
      $this->userid = $userid;
      return $this->fetch_user($this->userid, true);
    }
    return true;
  }
  
  // Authenticate user from database with username and password (md5)
  // Takes parameters: username, password (both plaintext), use_md5 (default: true)
  // Sucessful login sets $this->userid to user's id
  // Returns 0: success, 1: bad login, 2: bad password, 3: account locked, 4: remote user account (see note in header)
  //
  // SECURITY NOTE: Don't tell users which (username or pass) specificially failed, that enables brute force guessing!
  function authenticate_user($user, $pass = '', $use_md5=true) {
    if ($user == '') {
      return 1;
    }
    
    if ($use_md5)
      $pass = md5($pass);
    
    // Check username
    $sql = "SELECT id,is_locked,password FROM `users` WHERE username = '$user'";
    $this->db->query($sql);
    $rows = $this->db->count_rows();
    
    // if match was found, set userid, and return code
    if ($rows > 0) {
      $result = $this->db->get_row('ASSOC');
      
      // if account is locked
      if ($result['is_locked']) {
        $this->locked = true;
        return 3;
      }
      
      // successful login
      if ($result['password'] == $pass) {
        // good login, not locked
        $this->userid = $result['id'];
        return 0; // OK
        
      } else { // bad password or remote account
        if ($result['password'] == '') {
          return 4; // remote user account (see note in header)
        }
        return 2; // bad password
      }
    } else { // bad username
      return 1;
    }
  }

  function fetch_user_by_id($userid) {
    return fetch_user($userid, true);
  }

  // Retreive user from database, and set object variables
  // By username, or by userid if second arg is set
  function fetch_user($token, $by_id=false) {
    // fetch by id
    if ($by_id) {
      $where_clause = "id = '$token'";
    } else { // fetch by username
      $where_clause = "username = '$token'";
    }
    
    $sql = "SELECT id,username,firstname,lastname,user_role,user_type,location,
            email,phone,is_locked
            FROM `users` WHERE $where_clause";
            
//  vardumper($sql);
            
    $this->db->query($sql);
    $rows = $this->db->count_rows();

    // if match was found, set userid, and return code
    if ($rows > 0) {
      $result = $this->db->get_row('ASSOC');

//vardumper($result);

      // populate class variables
      $this->userid = $result['id'];
      $this->username = $result['username'];
      $this->fname = $result['firstname'];
      $this->lname = $result['lastname'];
      $this->email = $result['email'];
      $this->phone = $result['phone'];
      $this->location = $result['location'];
      $this->user_type = $result['user_type'];
      $this->user_role = $result['user_role'];
      $this->locked = $result['is_locked'];

//vardumper($this->fname);

      return true;

    } else {
      return false;
    }
  }

  // Create new user, and store in DB
  function create_new_user($username, $email, $firstname, $lastname, $status) {
    // Create unique, random id number for new user
    $uniqid = uniqid("");

    $sql = "INSERT INTO `users` (`id`, `username`, `email`, `firstname`, `lastname`, `user_type`) 
            VALUES ('$uniqid', '$username', '$email', '$firstname', '$lastname', '$status')";

    $this->db->query($sql);
    $result_count = $this->db->affected_rows();

    // Get new username
    $sql_uid = "SELECT id FROM users WHERE username = '$username'";
    $this->db->query($sql_uid);
    $result_uid_count = $this->db->affected_rows();
    if ($result_count == 1) {
      $result = $this->db->get_row('ASSOC');
      $this->userid = $result['id'];
    }

    // If user was added successfully
    if ($result_count == 1) {
      $this->userid = $uniqid;
      $this->username = $username;
      $this->fname = $firstname;
      $this->lname = $lastname;
      $this->email = $email;
      $this->user_type = $status;
      return true;
    } else {
      // this error might be kind of bad - should be set a warning?
      return false;
    }
  }

  function get_userid() {
    return $this->userid;
  }

  function get_username() {
    return $this->username;
  }
  
  function get_user_role() {
    return $this->user_role;
  }

  function get_fullname() {
    $fullname = $this->fname." ".$this->lname;
    return $fullname;
  }
}
?>
