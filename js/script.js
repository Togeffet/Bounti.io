/*jslint browser: true*/
/*global $, jQuery, alert, angular, console, gapi, profile*/

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
}
function closeError() {
  $('#errorBox').css('display', 'none');
}



function deleteMessage(messageID) {
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("errorBox").innerHTML = messageID;
            }
        };
        xmlhttp.open("GET","deletemessage.php?x="+messageID,true);
        xmlhttp.send();
    }
}





setInterval(function() {
  $('#notif').load('functions.php #unreadNum', function(responseText, status) {
    if(status != 'success') {
      $('#errorBox p').text('The page didn\'t do it :(');
      $('#errorBox').css('display', 'flex');
    } else {
      var notifCount = document.getElementById("unreadNum").innerHTML;
      if (notifCount > 0) {
        document.title = 'Bounti (' + notifCount +')';
      };
    };
  });
}, 10000);

$('#deleteMessageLink').click(function(event) {
  event.preventDefault();
  $('#messages').load('messages.php #messages > *', function(responseText, status) {
    if(status != 'success') {
      alert("Ahh");
      $('#errorBox p').text('The page didn\'t do it :(');
      $('#errorBox').css('display', 'flex');
    };
  });
});



$(document).ready(function () {
  $('#notif').load('functions.php #unreadNum', function(responseText, status) {
    if(status != 'success') {
      $('#errorBox p').text('The page didn\'t do it :(');
      $('#errorBox').css('display', 'flex');
    };
  });
  var notifCount = document.getElementById("notif").innerHTML;
  if (notifCount > 0) {
    document.title = 'Bounti (' + notifCount +')';
  };


  $('#grade').change(function() {
    if($(this).val() == 59){ // If the user selects 'F'
      $('#modifier').hide(); // Hide the + and -, because an F- doesn't exist
    } else {
      $('#modifier').show(); // Show it in case they change their mind
    }

  });




    $('#passbutton').click(function() {
        $('.passchange').show();
        document.getElementById("newpass").required = false;
        document.getElementById("newpass2").required = false;
    });




    $('.paperPreview').mouseleave(function(event){
        $('#edit p').finish();
    });


    $('#changeName').click(function() {
        editSettings('#name')
    });
    $('#changeUsername').click(function() {
        editSettings('#username')
    });
    $('#changeEmail').click(function() {
        editSettings('#email')
    });
    $('#changePic').click(function() {
        editSettings('#profpic')
    });
    $('#changePassword').click(function() {
        editSettings('#oldpass');
        editSettings('#newpass');
        editSettings('#newpass2');
    });




    $('#account').click(function(event){
        event.stopPropagation();
        $(".accountDropDown").toggleClass('show');
    });
    $(".accountDropDown").on("click", function (event) {
        event.stopPropagation();
    });
    $(document).on("click", function () {
        $(".accountDropDown").removeClass('show');
    });
    $('#notif').click(function () {
        window.location.href = "messages.php";
    });
    $('#logo').click(function () {
        window.location.href = "index.php";
    });
    $('#navName').click(function () {
        window.location.href = "index.php";
    });

    $('.paperPreviewContainer').mouseenter(function () {
        $('#edit p').fadeIn(250);
    }).mouseleave(function () {
        $('#edit p').fadeOut(250);
    });

    $('#errorBox').children('img').click(function() {
        $('#errorBox').fadeOut("fast");
    });

    $('#dropplease').mouseleave(function(){
        if(!file) {
            $('#dropplease').css('background-color','transparent');
            $("#dropplease").css('color', 'white');
            $("#dropplease p:first-of-type").css('display', 'block');
            $("#dropplease p:nth-child(2)").css('display', 'block');
            $('#dropplease').css('cursor','pointer');
        };
    })


    var dropTarget = $('.dropplease'),
    html = $('html'),
    showDrag = false,
    timeout = -1;

    html.bind('dragenter', function () {
        dropTarget.addClass('dragging');
        showDrag = true;
    });
    html.bind('dragover', function(){
        showDrag = true;
    });
    html.bind('dragleave', function (e) {
        showDrag = false;
        clearTimeout( timeout );
        timeout = setTimeout( function(){
            if( !showDrag ){ dropTarget.removeClass('dragging'); }
        }, 200 );
    });
    //document.getElementById("due").setAttribute("value", plusthreedays);
    //document.getElementById("due").setAttribute("min", today);
});

function alertFileName() {
    var thefile = document.getElementById('fileupload');
    //var file = thefile.substr(12,15);
    file = thefile.value.substr(12,(thefile.value).length);
    $("#dropplease").css('background-color', 'white');
    $("#dropplease").css('color', 'black');
    $("#dropplease").css('cursor', 'auto');
    $("#dropplease p:first-of-type").css('display', 'none');
    $("#dropplease p:nth-child(2)").css('display', 'block');
    $("#dropplease #fileName").html(file);
};


/*
var myItemsApp = angular.module('myItemsApp', []);

myItemsApp.factory('itemsFactory', ['$http', function ($http) {
    'use strict';

    var itemsFactory = {
        itemDetails: function () {
            return $http({
                url: "js/mockItems.json",
                method: "GET"
            })
                .then(function (response) {
                    return response.data;
                });
        }
    };
    return itemsFactory;

}]);


myItemsApp.controller('ItemsController', ['$scope', 'itemsFactory', function ($scope, itemsFactory) {
    'use strict';

    var promise = itemsFactory.itemDetails();

    promise.then(function (data) {
        $scope.itemDetails = data;
        console.log(data);
    });
    $scope.select = function (item) {
        $scope.selected = item;
    };
    $scope.selected = {};
}])
    .directive('bounti', function () {
        'use strict';

        return {
            template: "<div class=\"bounty\"><div class=\"preview\"><img src=\"{{ item.preview}}\" /></div><p class=\"title\">{{ item.title }}</p><p class=\"cost\">{{ item.cost | currency}}</p><p class=\"author\">By: {{ item.author }}</p></div>"
        };
    });



myItemsApp.controller('NavBar', function ($scope) {
    'use strict';

    $scope.profpic = profilepicture;
})
    .directive('navbar', function () {
        'use strict';

        return {
            restrict: 'E',
            templateUrl: 'js/navbar.php',
            link: function(scope, element, attrs) {
      scope.config = config;
    }
        };
    });

myItemsApp.controller('Account', function ($scope) {
    'use strict';

    $scope.accountname = username;
    $scope.profpic = profilepicture;
});

function allowDrop(file) {
    file.target.style.color = '#7CB342';
    file.target.style.border = '5px dashed #7CB342';
    file.preventDefault();
}

function drop(file) {
    file.preventDefault();
    var data = file.dataTransfer.getData("text");
    file.target.appendChild(document.getElementById(data));
}


function onSignIn(googleUser) {
    'use strict';
    var profile = googleUser.getBasicProfile();
    console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
    console.log('Name: ' + profile.getName());
    username = profile.getName();
    console.log('Image URL: ' + profile.getImageUrl());

    profilepicture = profile.getImageUrl();

    console.log('Email: ' + profile.getEmail());
}

function signOut() {
    'use strict';

    var auth2 = gapi.auth2.getAuthInstance();
    auth2.signOut().then(function () {
        console.log('User signed out.');
    });
}
*/
