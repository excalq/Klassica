// Housing Page Form functions
// 1. Showing/Collapsing Appropriate Forms
// 2. Adding/Deleting Rows to Apartments/Housing 
//

// Hides all the form sections until the user chooses one
function set_form_defaults() {
  var div1 = 'housing_form_listings';
  var div2 = 'housing_form_requests';
  var div3 = 'housing_form_roommates';
  var div4 = 'housing_form_crlinfo';
  var div5 = 'housing_form_extra';
  
  if (document.getElementById) // DOM3 = IE5, NS6
  {
    document.getElementById(div1).style.display = 'none';
    document.getElementById(div2).style.display = 'none';
    document.getElementById(div3).style.display = 'none';
    document.getElementById(div4).style.display = 'none';
    document.getElementById(div5).style.display = 'none';
  }                         
  else                      
  {
    if (document.layers) // Netscape 4
    {
      document.div1.display = 'none';
      document.div2.display = 'none';
      document.div3.display = 'none';
      document.div4.display = 'none';
      document.div5.display = 'none';
    }
    else // IE 4
    {
      document.all.div1.style.display = 'none';
      document.all.div2.style.display = 'none';
      document.all.div3.style.display = 'none';
      document.all.div4.style.display = 'none';
      document.all.div5.style.display = 'none';
    }
  }
  
  return true;
}


// When a user selects a form portion, hide other portions
function show_form(show_div) {

  var show_extra = 'housing_form_extra';
  
  switch (show_div)
  {
    case 'housing_form_listings':
      var hide1 = 'housing_form_requests';
      var hide2 = 'housing_form_roommates';
      var hide3 = 'housing_form_crlinfo';
      break;
    case 'housing_form_requests':
      var hide1 = 'housing_form_listings';
      var hide2 = 'housing_form_roommates';
      var hide3 = 'housing_form_crlinfo';
      break;
    case 'housing_form_roommates':
      var hide1 = 'housing_form_listings';
      var hide2 = 'housing_form_requests';
      var hide3 = 'housing_form_crlinfo';
      break;
    case 'housing_form_crlinfo':
      var hide1 = 'housing_form_listings';
      var hide2 = 'housing_form_requests';
      var hide3 = 'housing_form_roommates';
      break;
  }
  
  if (document.getElementById) // DOM3 = IE5, NS6
  {
    document.getElementById(show_div).style.display   = 'block';
    document.getElementById(show_extra).style.display = 'block';
    document.getElementById(hide1).style.display = 'none';
    document.getElementById(hide2).style.display = 'none';
    document.getElementById(hide3).style.display = 'none';
  }
  else 
  {
    if (document.layers) // Netscape 4
    {
      document.show_div.display   = 'block';
      document.show_extra.display = 'block';
      document.hide1.display = 'none';
      document.hide2.display = 'none';
      document.hide3.display = 'none';
    }
    else // IE 4
    {
      document.all.show_div.style.display   = 'block';
      document.all.show_extra.style.display = 'block';
      document.all.hide1.style.display = 'none';
      document.all.hide2.style.display = 'none';
      document.all.hide3.style.display = 'none';
    }
  }
  
  return true;
}

function add_table_row(table_id)
{

  var tblBody = document.getElementById(table_id).tBodies[0];
  var row_count  = tblBody.rows.length;
  var row_class  = (row_count % 2) ? 'even' : 'odd'; // we're not counting the header row
  var newRow = tblBody.insertRow(row_count - 1); // or footer
  newRow.className = row_class;

  
  var newCell0 = newRow.insertCell(0);
  var newCell1 = newRow.insertCell(1);
  var newCell2 = newRow.insertCell(2);
  var newCell3 = newRow.insertCell(3);
  var newCell4 = newRow.insertCell(4);
  var newCell5 = newRow.insertCell(5);
  var newCell6 = newRow.insertCell(6);
  var newCell7 = newRow.insertCell(7);
  var newCell8 = newRow.insertCell(8);

  // Add form input elements       
  // beds                        
  var newInput0 = document.createElement('input');
  newInput0.type = 'text';       
  newInput0.name = 'beds[]';     
  newInput0.value = '';  
  newInput0.className = 'txtbed';
  
  // baths
  var newInput1 = document.createElement('input');
  newInput1.type = 'text';
  newInput1.name = 'baths[]';
  newInput1.value = '';
  newInput1.className = 'txtbath';
  
  // rent
  var newInput2 = document.createElement('input');
  newInput2.type = 'text';
  newInput2.name = 'rent[]';
  newInput2.value = '';
  newInput2.className = 'txtrent';
  
  // depost
  var newInput3 = document.createElement('input');
  newInput3.type = 'text';
  newInput3.name = 'deposit[]';
  newInput3.value = '';
  newInput3.className = 'txtdeposit';
  
  // title & descr
  var newInput4a = document.createElement('input');
  newInput3.type = 'text';
  newInput4a.name = 'itemtitle[]';
  newInput4a.value = '';
  newInput4a.className = 'txttitle';
  
  var newInput4b = document.createElement('textarea');
  newInput4b.name = 'description[]';
  newInput4b.value = '';
  newInput4b.className = 'txadescr';
  
  // name
  var newInput5 = document.createElement('input');
  newInput5.type = 'text';
  newInput5.name = 'cname[]';
  newInput5.value = '';
  newInput5.className = 'txtcname';
  
  // phone
  var newInput6 = document.createElement('input');
  newInput6.type = 'text';
  newInput6.name = 'phone[]';
  newInput6.value = '';
  newInput6.className = 'txtphone';
  newInput6.style.color = '';
  
  // email
  var newInput7 = document.createElement('input');
  newInput7.type = 'text';
  newInput7.name = 'email[]';
  newInput7.value = '';
  newInput7.className = 'txtphone';
  newInput7.style.color = '';
  
  // date
  var newInput8 = document.createElement('input');
  newInput8.type = 'text';
  newInput8.name = 'date_listed[]';
  newInput8.value = '';
  newInput8.className = 'calendar_input';
  newInput8.style.color = '';
  
  // file upload
  var newInput9 = document.createElement('input');
  newInput9.type = 'file';
  newInput9.name = 'item_file[]';
  newInput9.value = '';
  newInput9.className = 'fileupbox';

  newCell0.appendChild(newInput0);
  newCell1.appendChild(newInput1);
  newCell2.appendChild(newInput2);
  newCell3.appendChild(newInput3);
  newCell4.appendChild(newInput4a);
    newCell4.appendChild(document.createElement('br'));
    newCell4.appendChild(newInput4b);
  newCell5.appendChild(newInput5);
    newCell5.appendChild(document.createElement('br'));
    newCell5.appendChild(document.createElement('br'));
    newCell5.appendChild(document.createTextNode('Add a file:'));
    newCell5.appendChild(document.createElement('br'));
    newCell5.appendChild(newInput9);
  newCell6.appendChild(newInput6);
  newCell7.appendChild(newInput7);
  newCell8.appendChild(newInput8);
  
  return true;
}

function del_table_row(table_id) 
{
  var tblBody = document.getElementById(table_id).tBodies[0];
  var row_count  = tblBody.rows.length;
  
  // Don't delete the header or footer rows
  if (row_count > 2)
  {
    tblBody.deleteRow(row_count - 2);
    return true;
  } else {
    return false;
  }
}