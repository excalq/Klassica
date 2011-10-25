/* Initialize Calendar Functions */
// Modified 2007-04-13 Arthur Ketcham
// Todo: Make this able to dynamically load calendars, without hardcoded div id's

  /* Set this for the oldest age, in days, to show past items (Items in myKlassica > My Listings table) */
  /* Default, 30 days ago */
  var defaultStartDate = 30;   
  
  var bas_cal,dp_cal,ms_cal,d,expDate;
  var cal1, cal2; 

  window.onload = function () {
      
    this.curDate = new Date();
    d = this.curDate;
    expDate = new Date(d.getFullYear(),d.getMonth(),d.getDate()-30);
    expDate = expDate.dateFormat(); 
      
  // For first parameter, choose between: 'epoch_basic', 'epoch_popup', 'epoch_multi'
  // For second parameter, choose between: 'flat', and 'popup'
      // OTHER EXAMPLES:
      //  bas_cal = new Epoch('epoch_basic','flat',document.getElementById('basic_container'));
      //  ms_cal  = new Epoch('epoch_multi','flat',document.getElementById('multi_container'),true);
            
    /* These HTML elements are dynamically created, but may no be created sometimes, so test for existence */
    if (cal1 = document.getElementById('calendar_popup_container1')) {
        dp_cal1  = new Epoch('epoch_popup','popup',document.getElementById('calendar_popup_container1'));
//         if (!cal1.value)
//           cal1.value = expDate;
    }
    if (cal2 = document.getElementById('calendar_popup_container2')) {
        dp_cal2  = new Epoch('epoch_popup','popup',document.getElementById('calendar_popup_container2'));
//         if (!cal2.value)
//           cal2.value = expDate;
    }
  }