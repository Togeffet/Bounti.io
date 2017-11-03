var profilepicture;
var username;
var file;
var fieldName, buttonName;
var today = new Date();
var plusthreedays;
var dd = today.getDate();
var mm = today.getMonth()+1; //January is 0!
var yyyy = today.getFullYear();

if(dd<10){
    dd='0'+dd;
};
if(mm<10){
    mm='0'+mm;
};

today = yyyy+'-'+mm+'-'+dd;
dd+=3;
plusthreedays = mm+'/'+dd+'/'+yyyy;

function editSettings(fieldName) {
    $(fieldName).prop("disabled", false);
};

function say(message) {
    window.alert(message);
}

function showHunters() {
  $('.darken').css('display', 'flex');
}

function showError(text) {
  $('#errorBox p').text(text);
  $('#errorBox').css('display', 'flex');
  $(document).delay(500).fadeOut("fast");
}
function closeError() {
  setTimeout(function() {
    $('#errorBox').fadeOut('fast');
  }, 3000); // Wait 3 seconds before it closes
}

function checkError() {
  if(!($('#userExists').text())) { // If there is no text in the error
    $('#errorBox').css('display', 'none'); // Get rid of it
  } else { // If there is text in the error
    closeError(); // Wait 3 seconds and get rid of it
  }
}


function usernameCheck(value) {
  if(value == '') {
    // If there isn't anything written in the username box
    return;
  }
/*
  if (window.XMLHttpRequest) {
    // code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp = new XMLHttpRequest();
  } else {
  // code for IE6, IE5
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      console.log(value);
    }
  };
  xmlhttp.open("GET", "functions.php?user="+value, true);
  xmlhttp.send();*/


  $('#errorBox p').load('functions.php?user=' + value +' #userExists', function(responseText, status) {
    if(status === 'success') {
      $('#errorBox').css('display', 'flex');
      checkError();
    }

});
}

/*
function usernameCheck() {
  var textBox = document.getElementById('username');
  say(textBox.value);
}

function usernameCheck() {
  var textBox = document.getElementById('username');
  var value = textBox.value;

  if ( window.console && window.console.log ) {
    console.log(value);
  }
  if (window.XMLHttpRequest) {
    // code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp = new XMLHttpRequest();
  } else {
  // code for IE6, IE5
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      console.log("heyo");
    }
  };
  xmlhttp.open("GET","login.php?username=" + value, true);
  xmlhttp.send();


  $('html').load('validate.php', function(responseText, status) {
    if(status != 'success') {
      $('#errorBox p').text('The page didn\'t do it :(');
      $('#errorBox').css('display', 'flex');
    } else {
      var notifCount = document.getElementById("yesorno").innerHTML;
      say(notifCount);
    };

});*/





$(document).ready(function () {


    $('#logo').click(function () { // If you click on the navbar logo
        window.location.href = "index.php"; // Go to the homepage
    });
    $('#navName').click(function () { // Click on the navbar name
        window.location.href = "index.php"; // Go to the homepage
    });


    $('#errorBox').children('img').click(function() { // Click on the errorbox x
        $('#errorBox').fadeOut("fast"); // Get rid of it
    });

  });
