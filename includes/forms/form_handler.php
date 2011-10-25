<?php
/*****************************************************************************
Copyright (C) 2006  Arthur Ketcham

Handle Sell/Post Announcement Form
  Validate Captcha
  Validate Data
  Submit data to klassica_items object


*****************************************************************************/


// validate captcha on posted form
function validate_captcha($captcha_word) {
  $word_ok = false;
  if(!empty($_SESSION['freecap_word_hash']) && !empty($captcha_word)) {
    // all freeCap words are lowercase.
    // font #4 looks uppercase, but trust me, it's not...
  if($_SESSION['hash_func'](strtolower($captcha_word)) == $_SESSION['freecap_word_hash']) {
      // reset freeCap session vars
      // cannot stress enough how important it is to do this
      // defeats re-use of known image with spoofed session id
      $_SESSION['freecap_attempts'] = 0;
      $_SESSION['freecap_word_hash'] = false;

      $word_ok = true;
    } else {
      $word_ok = false;
    }
  } else {
    $word_ok = false;
  }
  return $word_ok;

}


// validate posted form
// TODO: improve this
function php_check_formfields($field_data, $type = 'generic') {

  switch ($type) {
    case 'housing':
      if ($field_data['title']       &&
          $field_data['category']    &&
          $field_data['description'] &&
          $field_data['beds']        &&
          $field_data['baths']       &&
          $field_data['rent'])
        return true;
      else
        return false;  
      break;

    case 'hlooking':
      if ($field_data['contact_name'] &&
          $field_data['email']        &&
          $field_data['description'])
        return true;
      else
        return false;  
      break;
      
    default:
      return true;
      break;
  
  }
}

// Verify that only one category select menu has been selected
// Requires one value in array, fails if more than one was selected
// returns selected item, or FALSE
// TODO: Depricate this
function validate_catselects($cat_sel_array) {
      $selected = $cat_sel_array[$i];
    return $selected;
}

// TODO: Do stuff later
function php_check_formdata($field_data) {
  $form_ok = false;
  
  // category
  $cat_sel = $field_data['category'];

  // TODO: validate stuff (email addys, etc.)

  return true;
}

// Store item in database, and return id
// $item_array = array($itemtitle, $item_category, $seller_id, $location, $phone, 
//      $email, $description, $expire_date, $cr_date, $mod_date, $price, $is_price_obo, $is_price_free, $condition);

// NOTE: if file was posted from announcements, it will not have things like $price
function store_item($dbconn, $item_array, $files) {

  $kitem = new Klassica_item($dbconn);

  $kitem->add_files($files);
  $create_ok = $kitem->create_item($item_array);
  if (!$create_ok) {
    return false;
  } else {
    return $kitem->id;
  }
}

// If an existing item is being updated
function update_item($dbconn, $item_array, $files) {
  $itemid = $item_array['item_id'];
  $kitem = new Klassica_item($dbconn, $itemid);

  $kitem->add_files($files);
  $update_ok = $kitem->update_item($item_array);
  if (!$update_ok) {
    return false;
  } else {
    return $kitem->id;
  }
}

// Upload submitted files to filesystem
// Note: the $_FILES variable has global scope
// Use $key to limit to certain files in the array (needed if adding multiple items)
// Returns array of uploaded filenames, and errors
function upload_files($key = false) {
  $return_data = $file_errors = array(array());
  $new_size    = 600;     // size to resize file to
  $thumb_size  = 250;     // size to make thumbnail
  
  
  // If there were no files uploaded (which is normal), return
  if (!isset($_FILES) || empty($_FILES) || ($key && !$_FILES['item_file']['name'][$key])) {
    return $file_errors;
  }

  if (defined('_FILE_UPLOAD_PATH'))
    $fs_path = _FILE_UPLOAD_PATH; // defined in Klassica config
  else
    $fs_path = '/var/www/klassica/dev';
  
  // Which uploaded files to look at
  if ($key || $key === 0) {
    $k0 = $k1 = $key;
  } else {
    $k0 = 0;
    $k1 = count($_FILES['item_file']['error']);
  }
  
  for ($i = $k0; $i<=$k1; $i++) {
  
    $error = $_FILES['item_file']['error'][$i];
    if ($error == UPLOAD_ERR_OK) { // if the 'error' is not an error
      
      $orig_filename = basename($_FILES['item_file']['name'][$i]);
      $file_basename = substr($orig_filename, 0, strripos($orig_filename, '.')); // strip extention
      $file_ext      = strtolower(strrchr($orig_filename, '.'));
      
      // Allowed file formats
      if (in_array($file_ext, array('.jpeg', '.jpg', '.gif', '.png', '.pdf', '.odt', 
          '.doc', '.docx', '.ods', '.xls', '.dwg'))) {
    
        // Move temp file to uploads dir
        $file_basename = preg_replace('/[^a-zA-Z0-9\.]/', '-', $file_basename);
        $ftime         = date('YmdHis');
        $main_file     = $ftime .'-'. $file_basename . $file_ext;
        $file_path     = $fs_path .'/'. $main_file;
        $thumbs_path   = $fs_path .'/thumbnails/'. $main_file;

        $file_name     = move_uploaded_file($_FILES['item_file']['tmp_name'][$i], $file_path);
        
        if($file_name) {
        
          // If file is an image, detect type
          $image_info = @getImageSize($file_path);
          
          if ($image_info) {
            switch ($image_info['mime']) {
              case 'image/gif':
                if (imagetypes() & IMG_GIF)  { // not the same as IMAGETYPE
                  $old_image = imageCreateFromGIF($file_path) ;
                } else {
                  $return_data[] = array('file' => $main_file, 'error' => 'GIF images are not supported<br />');
                }
                break;
              case 'image/jpeg':
                if (imagetypes() & IMG_JPG)  {
                  $old_image = imageCreateFromJPEG($file_path) ;
                } else {
                  $return_data[] = array('file' => $main_file, 'error' => 'JPEG images are not supported<br />');
                }
                break;
              case 'image/png':
                if (imagetypes() & IMG_PNG)  {
                  $old_image = imageCreateFromPNG($file_path) ;
                } else {
                  $return_data[] = array('file' => $main_file, 'error' => 'PNG images are not supported<br />');
                }
                break;
              }
            
            // Resize image and create thumbnail
            if ($old_image) {
              $old_x = imagesx($old_image);
              $old_y = imagesy($old_image);
              
              if($old_x > $old_y) {
                $new_size = min($new_size, $old_x);
                $new_x    = $new_size;
                $new_y    = round(($new_size / $old_x) * $old_y);
                $thb_x    = $thumb_size;
                $thb_y    = round(($thumb_size / $old_x) * $old_y);
              } else {
                $new_size = min($new_size, $old_y);
                $new_x    = $new_size;
                $new_y    = round(($new_size / $old_y) * $old_x);
                $thb_x    = $thumb_size;
                $thb_y    = round(($thumb_size / $old_y) * $old_x);
              }
              
              $new_image = imageCreateTrueColor($new_x, $new_y);
              $new_thumb = imageCreateTrueColor($thb_x, $thb_y);
            
              imageCopyResampled($new_image, $old_image, 0, 0, 0, 0, $new_x, $new_y, $old_x, $old_y);
              imageCopyResampled($new_thumb, $old_image, 0, 0, 0, 0, $thb_x, $thb_y, $old_x, $old_y);
            
              // write image and thumbnail to uploads dir
              imageJPEG($new_image, $file_path);
              imageJPEG($new_thumb, $thumbs_path);
            
              imageDestroy($old_image);
              imageDestroy($new_image);
              imageDestroy($new_thumb);
              
              chmod("$file_path", 0664);
              chmod("$thumbs_path", 0664);
              
              $return_data[] = array('file' => $main_file, 'error' => '');
            
            } else {
              $return_data[] = array('file' => $main_file, 'error' => "File \"$orig_filename\" was not of an allowed type.");
            }
            
          } // end if is_image
          
        } else {
          $return_data[] = array('file' => $main_file, 'error' => "There was a problem saving the file \"$orig_filename\".");
        }
      }
    }
  } // end for
  
  return $return_data;

}
?>