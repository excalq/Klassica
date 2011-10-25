/* Forms */

/* Validate forms before submission */
var W3CDOM = (document.getElementsByTagName && document.createElement);

function checkForm() {
  validForm = true;
  firstError = null;
  errorstring = '';
  
  // accept terms checkbox
  tb = document.getElementById("terms");

  if(!(tb.checked)) {
    writeError(tb,'You must agree to these terms');
  }
  
  var reqSelect = new Array();
  var reqField = new Array();

  reqSelect[0] = document.getElementById("item_category");
  reqField[0] = document.getElementById("itemtitle");
  reqField[1] = document.getElementById("location");
  reqField[2] = document.getElementById("description");
  
  var postForm = document.postform;
  
  // Write error on required form items
  for (var i=0;i<reqField.length;i++) {
    if (!reqField[i].value)
      writeError(reqField[i],'This field is required');
  }
  
  // Write error on required select lists
  for (var i=0;i<reqSelect.length;i++) {
    if (!reqSelect[i].options[reqSelect[i].selectedIndex].value)
      writeError(reqSelect[i],'This field is required');
  }

//   if (x['email'].value.indexOf('@') == -1)
//     writeError(x['email'],'This is not a valid email address');
  if (!W3CDOM) {
    alert(errorstring);
  }
  if (firstError) {
    firstError.focus();
  }
  if (validForm) {
    return true;
  }
  /* There were some errors. Also flag the error message next to the submit button  */
  var errBox = document.getElementById("error-box");
  writeError(errBox,'You have not completed all required fields.');

  return false;
}


function writeError(obj,message) {
  validForm = false;
  if (obj.hasError) return;
  if (W3CDOM) {
    obj.className += ' error';
    obj.onchange = removeError;
    var sp = document.createElement('span');
    sp.className = 'error';
    sp.appendChild(document.createTextNode(message));
    obj.parentNode.appendChild(sp);
    obj.hasError = sp;
  }
  else {
    errorstring += obj.name + ': ' + message + '\n';
    obj.hasError = true;
  }
  if (!firstError)
    firstError = obj;
}

// Remove error class from form item, and remove warning label
function removeError() {
//   // There are three category select menus in the form.
//   // If one of them gets cleaned, the all should be cleaned
//   var selCatOne = document.getElementById("cat_type[0]");
//   var selCatTwo = document.getElementById("cat_type[1]");
//   var selCatThree = document.getElementById("cat_type[2]");
//      var errMsgBox = document.getElementById("error-box");
//     items = new Array();
//   items.push(selCatOne);
//   items.push(selCatTwo);
//   items.push(selCatThree);

//   if (errMsgBox.hasError) {
//     items.push(errMsgBox);
//   }

//   // otherwise, just clean that item and the error-box <span> at bottom of page
//   if ((this != items[0]) && (this != items[1]) && (this != items[2])) {
//     items.pop(); // remove above junk from array
//     items.pop();
//     items.pop();
//     items.pop();
//     items.push(this);
//   }
   
  items = new Array();
  items.push(this);

  var i = 0;
  for (i;i<items.length;i++) {
    items[i].className = items[i].className.substring(0,items[i].className.lastIndexOf(' '));
    items[i].parentNode.removeChild(items[i].hasError);
    items[i].hasError = null;
    items[i].onchange = null;
  }
}

    //args:  checkbox to be validated, id of element to receive info/error msg
function validateConfirm (vfld, ifld) {
  var stat = commonCheck2(vfld, ifld);
  if (stat != proceed) return stat;

  if (vfld.checked) return true;

  // if we get here then the validation has failed

  var errorMsg = 'Please read the above message and confirm you agree to it';

  msg (ifld, "error", errorMsg);
  return false;
}

function msg(fld,     // id of element to display message in
             msgtype, // class to give element ("warn" or "error")
             message) // string to display
{
  // setting an empty string can give problems if later set to a 
  // non-empty string, so ensure a space present. (For Mozilla and Opera one could 
  // simply use a space, but IE demands something more, like a non-breaking space.)
  var dispmessage;
  if (emptyString.test(message)) 
    dispmessage = String.fromCharCode(nbsp);    
  else  
    dispmessage = message;

  var elem = document.getElementById(fld);
  elem.firstChild.nodeValue = dispmessage;  
  
  elem.className = msgtype;   // set the CSS class to adjust appearance of message
}

// End forms validation

// Clear incompatible form fields
// This is used to restrict certain combinations of checkboxes
function clearPriceFields(f1, f2, f3) {
  if(f1) {
    f1.value = "";
  }
  if(f2) {
    f2.checked = "";
  }
  if(f3) {
    f3.checked = "";
  }
}