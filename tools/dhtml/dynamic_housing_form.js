// Merged into housingform.js

function add_table_row(tblId)
{
  var tblBody = document.getElementById(tblId).tBodies[0];
  
  var row_count  = tblBody.rows.length;
  var row_class  = (row_count % 2) ? 'odd' : 'even'; // we're not counting the header row
  var newRow = tblBody.insertRow(row_count - 2);
  newRow.className = row_class;
  
  var newCell0 = newRow.insertCell(0);
  var newCell1 = newRow.insertCell(1);
  var newCell2 = newRow.insertCell(2);
  var newCell3 = newRow.insertCell(3);
  var newCell4 = newRow.insertCell(4);
  var newCell5 = newRow.insertCell(5);
  var newCell6 = newRow.insertCell(6);
  var newCell7 = newRow.insertCell(7);

  // Add form input elements       
  // beds                        
  var newInput0 = document.createElement('input');
  newInput0.type = 'text';       
  newInput0.name = 'beds[]';     
  newInput0.value = '';  
  newInput0.className = 'txtbed';
  newInput0.style.color = 'blue';
  
  // baths
  var newInput1 = document.createElement('input');
  newInput1.type = 'text';
  newInput1.name = 'baths[]';
  newInput1.value = '';
  newInput1.className = 'txtbath';
  newInput1.style.color = 'blue';
  
  // rent
  var newInput2 = document.createElement('input');
  newInput2.type = 'text';
  newInput2.name = 'rent[]';
  newInput2.value = '';
  newInput2.className = 'txtrent';
  newInput2.style.color = 'blue';
  
  // depost
  var newInput3 = document.createElement('input');
  newInput3.type = 'text';
  newInput3.name = 'deposit[]';
  newInput3.value = '';
  newInput3.className = 'txtdeposit';
  newInput3.style.color = 'blue';
  
  // descr
  var newInput4 = document.createElement('textarea');
  newInput4.name = 'descr[]';
  newInput4.value = '';
  newInput4.className = 'txadescr';
  newInput4.style.color = 'blue';
  
  // name
  var newInput5 = document.createElement('input');
  newInput5.type = 'text';
  newInput5.name = 'cname[]';
  newInput5.value = '';
  newInput5.className = 'txtcname';
  newInput5.style.color = 'blue';
  
  // phone
  var newInput6 = document.createElement('input');
  newInput6.type = 'text';
  newInput6.name = 'phone[]';
  newInput6.value = '';
  newInput6.className = 'txtphone';
  newInput6.style.color = 'blue';
  
  // date
  var newInput7 = document.createElement('input');
  newInput7.type = 'text';
  newInput7.name = 'date[]';
  newInput7.value = '';
  newInput7.className = 'calendar_input';
  newInput7.style.color = 'blue';
  
  newCell0.appendChild(newInput0);
  newCell1.appendChild(newInput1);
  newCell2.appendChild(newInput2);
  newCell3.appendChild(newInput3);
  newCell4.appendChild(newInput4);
  newCell5.appendChild(newInput5);
  newCell6.appendChild(newInput6);
  newCell7.appendChild(newInput7);
  
  return true;
}

function del_table_row(tblId) 
{
  var tblBody = document.getElementById(tblId).tBodies[0];
  tblBody.deleteRow(-1);
  return true;
}
