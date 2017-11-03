/*jslint browser: true*/
/*global $, jQuery, alert, angular, console, gapi, profile, page, Stripe, stripeCCResponseHandler, stripeBAResponseHandler */

var profilepicture;
var username;
var file;
var fieldName, buttonName;
var today = new Date();
var plusthreedays;
var dd = today.getDate();
var mm = today.getMonth() + 1; //January is 0!
var yyyy = today.getFullYear();
var usernameOkay = false;
var emailOkay = false;
var emailMessage = 'Must be a vaild email address';
var passwordOkay = false;
var pageTitle = document.title;
var mouseover;
var alreadyLoaded;
var currentSelection;
var currentConvo;
var messagesOffset;
var noMoreMessages;
var errorTimeout;
var currentNotifCount,
    currentUnreadMessages;
var unconfirmedID = 0;
var giveUpBounti = '';
var notifInterval = 0;
var accountCreate = 0;


function hideError() {
    'use strict';
    errorTimeout = setTimeout(function () {
        $('#errorBox').fadeOut('fast');
    }, 5000);
}

function showError(text) {
    'use strict';
    if (!text) {
        return;
    } else {
        $('#errorBox p').text(text);
        $('#errorBox').css('display', 'flex');
        hideError();
    }
}

function keepError() {
    'use strict';
    clearTimeout(errorTimeout);
}

function removeDarken() {
    'use strict';
    $(".darken").fadeOut("fast").delay(100).remove();
}



/*
if(dd<10){
    dd='0'+dd;
};
if(mm<10){
    mm='0'+mm;
};*

today = yyyy+'-'+mm+'-'+dd;
dd+=3;
plusthreedays = mm+'/'+dd+'/'+yyyy;
*/



function editSettings(fieldName) {
    'use strict';
    $(fieldName).prop("disabled", false);
}

function say(message) {
    'use strict';
    window.alert(message);
}

function selectUser(varid) {
    'use strict';
  //$.get('messages.php', {id: varid, usr: userid}, function(responseText, status)
    //say('convoid: ' + varid +  ' userid: ' + userid);
    messagesOffset = 0; // Update messages offset back to 0
    noMoreMessages = ''; // Update lock on loading messages variable back to 0
    //say('convoid: ' + varid +  ' userid: ' + userid + ' offset: ' + messagesOffset);
    $('#messagesRS').attr('class', varid);
    if (currentConvo !== varid) {

        var selectUserSend = $.ajax({
            type: 'GET',
            url: 'https://www.bounti.io/functions.php',
            data: {
                conversationID: varid
            },
            dataType: 'html',
            timeout: 3000
        });

        selectUserSend.done(function (responseText) {
            $('#messagesRS').html(responseText);
            $('#messagesRS').scrollTop($('#messagesRS')[0].scrollHeight);
            document.getElementById("messageText").focus();

            $('#' + varid).addClass('activeMessager');
            $('#' + currentConvo).removeClass('activeMessager'); // Remove it from the last selected thing

            $('#' + varid).removeClass('new');

            currentConvo = varid;
        });

        selectUserSend.fail(function (e) {
            showError('Something went wrong');
            console.log(e);
        });

    }

}

function hideBanner() {
    'use strict';
    $('#notVerified').fadeOut('fast');
}

function showHunters(paperid) {
    'use strict';
    var showHuntersSend = $.ajax({
        type: 'POST',
        url: 'https://www.bounti.io/functions.php',
        data: {
            showhunters: 'yes',
            id: paperid
        },
        timeout: 3000,
        dataType: 'html'
    });
    showHuntersSend.always(function () {
        $('body').append('<div class="darken">' +
            '<script>' +
              '$(document).mouseup(function(e) {' +
                'var container = $(".bountihunters");' +
                'if (!container.is(e.target)' +
                  '&& container.has(e.target).length === 0)' +
                '{' +
                  '$(".darken").fadeOut("fast").remove();' +
                '};' +
              '});' +
            '</script>' +
            '<div class="bountihunters">' +
              '<h1>Bounti Hunters Interested</h1>' +
              '<h3>Loading...</h3>' +
            '</div>' +
            '</div>');
        $('.darken').css('display', 'flex').hide().fadeIn('fast');
    });

    showHuntersSend.done(function (responseText) {
        //say(responseText);
        $('.bountihunters').html(responseText);
    });

    showHuntersSend.fail(function (e) {
        showError('Something went wrong');
        console.log(e);
    });
}




/*function checkError(divToCheck) {
  if(!($(divToCheck).text())) { // If there is no text in the error
    $('#errorBox').css('display', 'none'); // Get rid of it
  } else { // If there is text in the error
    closeError(); // Wait 3 seconds and get rid of it
  }
}*/

function sendVerification() {
    'use strict';
    var verificationSend = $.ajax({
        type: 'POST',
        url: 'https://www.bounti.io/functions.php',
        data: {
            sendverification: 'yes'
        },
        timeout: 3000,
        dataType: 'json'
    });
    verificationSend.done(function (responseText) {
        showError(responseText.message);
        if (responseText.sent) { // If the email sent
            return true;
        } else {
            return false;
        }
    });
    verificationSend.fail(function (e) {
        showError("Something went wrong");

        console.log(e);
        return false;
    });
}


function recoverPassword() {
    'use strict';
    $('#recoverPassForm').find('.submit').prop('disabled', true);
    var emailToSendTo = $('.emailToSendTo').val(),

        passwordRecoverSend = $.ajax({
            type: 'POST',
            url: 'https://www.bounti.io/functions.php',
            data: {
                recoverPass: 'send',
                recoverEmail: emailToSendTo
            },
            timeout: 5000,
            dataType: 'json'
        });

    passwordRecoverSend.done(function (responseText) {

        if (responseText.sent) { // If the email sent
            $('.mainspace').html('<h1>Email sent to ' + emailToSendTo + '</h1>' +
                            '<h1 style="font-size: 4vmin; margin-top: 6vmin">Check your email for the link to reset your password</h1>');
            $('.mainspace').addClass('justifyCenter');
        } else {
            showError(responseText.message);
            $('#recoverPassForm').find('.submit').prop('disabled', false);
        }
    });
    passwordRecoverSend.fail(function (e) {
        showError("Something went wrong");
        console.log(e);
        $('#recoverPassForm').find('.submit').prop('disabled', false);
        return false;
    });
}

function deleteBounti(rndid) {
    'use strict';
    var deleteBountiSend = $.ajax({
        type: 'POST',
        url: 'https://www.bounti.io/functions.php',
        data: {
            delid: rndid
        },
        timeout: 3000,
        dataType: 'json'
    });

    // Refresh the mesages page
    deleteBountiSend.done(function (responseText) {
        if (responseText.deleted) {
            location.href = "https://www.bounti.io/bounties";
        } else {
            showError('Something went wrong');
        }
    });

    deleteBountiSend.fail(function (e) {
        showError('Something went wrong');
        console.log(e);
    });
}



function declineAcceptance(messageid, senderid, papertitle, paperid, type) {
    'use strict';
    var declineAcceptanceSend = $.ajax({
        type: 'POST',
        url: 'https://www.bounti.io/functions.php',
        data: {
            m: messageid,
            id: senderid,
            title: papertitle,
            papid: paperid,
            s: type
        },
        timeout: 3000,
        dataType: 'json'
    });

    // Refresh the mesages page
    declineAcceptanceSend.done(function (responseText) {
        $('#messages').load('https://www.bounti.io/notifications.php #messages');
    });

    declineAcceptanceSend.fail(function (e) {
        console.log(e);
    });
}


function declineRequest(senderid, messageid, paperid, papertitle, type) {
    'use strict';
    var declineRequestSend = $.ajax({
        type: 'POST',
        url: 'https://www.bounti.io/functions.php',
        data: {
            m: messageid,
            id: senderid,
            title: papertitle,
            papid: paperid,
            s: type
        },
        timeout: 3000,
        dataType: 'json'
    });

    declineRequestSend.done(function () {
        // Refresh the mesages page
        $('#messages').load('https://www.bounti.io/notifications.php #messages');
    });

    declineRequestSend.fail(function (e) {
        console.log(e);
    });

}

function acceptUser(messageid, userid, paperid) {
    'use strict';
    var acceptSend = $.ajax({
        type: 'POST',
        url: 'https://www.bounti.io/functions.php',
        data: {
            m: messageid,
            id: userid,
            paper: paperid,
            accept: 'yes'
        },
        dataType: 'json',
        timeout: 3000
    });

    acceptSend.done(function (responseText) {
        showError(responseText);
    });

    acceptSend.fail(function (e) {
        showError('Something went wrong');
        console.log(e);
    });

}


function request(paperid, authorid) {
    'use strict';
    var requestSend = $.ajax({
        type: 'POST',
        url: 'https://www.bounti.io/functions.php',
        data: {
            id: paperid,
            auth: authorid
        },
        dataType: 'json',
        timeout: 3000
    });

    requestSend.done(function (responseText) {
        showError(responseText.message);
    });

    requestSend.fail(function (e) {
        showError("Something went wrong");
        console.log(e);
    });
}

function checkPassword() {
    'use strict';
    if (passwordOkay) {
        $('#passwordOkayYes').attr('src', 'https://www.bounti.io/img/check.png');
        $('#passwordOkayYes').css('opacity', '1');
        $('#passWord-form').attr('disabled', true);
    } else {
        $('#passwordOkayYes').attr('src', 'https://www.bounti.io/img/xsmall.png');
        $('#passwordOkayYes').css('opacity', '1');
        $('#passSubmit').attr('disabled', false);
    }

    if (document.getElementById('changepassword').value.length <= 0) {
        $('#passwordOkayYes').attr('src', '');
        $('#passwordOkayYes').css('opacity', '0');
    }
}

function checkAccountValues() {
    'use strict';
    if (usernameOkay) {
        $('#usernameOkayYes').attr('src', 'https://www.bounti.io/img/check.png');
        $('#usernameOkayYes').css('opacity', '1');
    } else {
        $('#usernameOkayYes').attr('src', 'https://www.bounti.io/img/xsmall.png');
        $('#usernameOkayYes').css('opacity', '1');
    }
    if (emailOkay) {
        $('#emailOkayYes').attr('src', 'https://www.bounti.io/img/check.png');
        $('#emailOkayYes').css('opacity', '1');
    } else {
        $('#emailOkayYes').attr('src', 'https://www.bounti.io/img/xsmall.png');
        $('#emailOkayYes').css('opacity', '1');
    }
    if (passwordOkay) {
        $('#passwordOkayYes').attr('src', 'https://www.bounti.io/img/check.png');
        $('#passwordOkayYes').css('opacity', '1');
    } else {
        $('#passwordOkayYes').attr('src', 'https://www.bounti.io/img/xsmall.png');
        $('#passwordOkayYes').css('opacity', '1');
    }

    if (document.getElementById('newusername').value.length <= 0) {
        $('#usernameOkayYes').attr('src', '');
        $('#usernameOkayYes').css('opacity', '0');
    }
    if (document.getElementById('newemail').value.length <= 0) {
        $('#emailOkayYes').attr('src', '');
        $('#emailOkayYes').css('opacity', '0');
    }
    if (document.getElementById('newpassword').value.length <= 0) {
        $('#passwordOkayYes').attr('src', '');
        $('#passwordOkayYes').css('opacity', '0');
    }

    if (usernameOkay && emailOkay && passwordOkay) {
      accountCreate = 1;
      document.getElementById('submit').disabled = false;
    }
}






function newUsernameCheck(value) {
    'use strict';
    if (value === '') { // If there isn't anything written in the email box
        checkAccountValues();
        return;
    }
    if (/\s/g.test(value)) {
        usernameOkay = false;
        showError("Username can't contain spaces");
        checkAccountValues();
        return;
    }

    if (value.length >= 4) { // Pass typed value to functions.php as newuser
        var newUserSend = $.ajax({
            type: 'GET',
            url: 'https://www.bounti.io/functions.php',
            data: {
                newuser: value
            },
            dataType: 'json',
            timeout: 3000
        });

        newUserSend.done(function (responseText) {
            showError(responseText.exists);
            usernameOkay = responseText.usernameOkay;
            checkAccountValues();
        });

        newUserSend.fail(function (e) {
            showError('Something went wrong');
            console.log(e);
            checkAccountValues();
        });
    } else {
        showError('Must be between 4 and 25 characters');
        usernameOkay = false;
        checkAccountValues();
    }

}

function usernameCheck(value) {
    'use strict';
    if (value === '') { // If there isn't anything written in the username box
        return;
    }

    var userSend = $.ajax({
        type: 'GET',
        url: 'https://www.bounti.io/functions.php',
        data: {
            user: value
        },
        dataType: 'json',
        timeout: 3000
    });

    userSend.done(function (responseText) {
        showError(responseText);
    });

    userSend.fail(function (e) {
        showError('Something went wrong');
    });
}



function emailCheck(value) {
    'use strict';
    if (value === '') { // If there isn't anything written in the email box
        checkAccountValues();
        return;
    }

    var re = /\S+@\S+\.\S+/,
        emailSend = $.ajax({
            type: 'GET',
            url: 'https://www.bounti.io/functions.php',
            data: {
                email: value
            },
            dataType: 'json',
            timeout: 3000
        });

    if (re.test(value)) { // If it passes regex


        emailSend.done(function (responseText) {
            emailMessage = responseText.exists;
            showError(responseText.exists);
            emailOkay = responseText.emailOkay;
            checkAccountValues();
        });

        emailSend.fail(function (e) {
            showError('Something went wrong');
            console.log(e);
            checkAccountValues();
        });
    } else {
        emailOkay = false;
        showError('Must be a valid email address');
        checkAccountValues();
    }

}


function editBounti(paperid) {
    'use strict';
    location.href = 'https://www.bounti.io/editbounti/' + paperid;
}




function getNotifCount() { // Generic notification count getter on every other page
    'use strict';
    var notificationSend = $.ajax({
        type: 'POST',
        url: 'https://www.bounti.io/functions.php',
        data: {
            notif: 'yes'
        },
        dataType: 'json',
        timeout: 3000
    });

    notificationSend.done(function (responseText) {
        if (currentNotifCount !== responseText.notificationCount) { // If the number is different than what it was before
            $('#notif').text(responseText.notificationCount); // Put the number in the hidden bubble

            if (responseText.notificationCount > 0) { // If it's greater than 0
                $('#notif').css('display', 'flex'); // Show the bubble
                document.title =  pageTitle + ' (' + responseText.notificationCount + ')'; // Update the title tab
            } else { // If there are 0 new messages
                $('#notif').css('display', 'none'); // Hide the bubble
                document.title = pageTitle; // Set the title as the regular title
            }
        }

        if (currentUnreadMessages !== responseText.unreadMessageCount) { // If there's a different amt of messages
            if (responseText.unreadMessageCount > 0) {
                $('#mailbox').attr('src', 'https://www.bounti.io/img/email-filled.png');
            } else {
                $('#mailbox').attr('src', 'https://www.bounti.io/img/email-outline.png');
            }

            var getMessagesSend = $.ajax({
                type: 'GET',
                url: 'https://www.bounti.io/functions.php',
                data: {
                    getmessagesforthisguy: 'i'
                },
                dataType: 'html',
                timeout: 3000
            });

            getMessagesSend.done(function (responseText) {
                $('.messagesDropDown').html(responseText);
                alreadyLoaded = true;
            });

            getMessagesSend.fail(function (e) {
                $('.messagesDropDown').html('<div class="centerme">Something went wrong</div>');
                alreadyLoaded = false;
            });



        }

        currentUnreadMessages = responseText.unreadMessageCount;
        currentNotifCount = responseText.notificationCount; // Update the variable with correct count

        notificationSend.fail(function (e) {
            clearInterval(getNotifCount); // Stop the notification checker TODO: MAKE THIS JUST CHECK LESS??
            $('#notif').text('?'); // Put a question mark in the bubble
            $('#notif').css('display', 'flex'); // Show the bubble as sort of an error
        });
        if ((currentNotifCount !== responseText.notificationCount) || (currentUnreadMessages !== responseText.unreadMessageCount)) {
          notifInterval = 0; // Reset the notification interval
          setTimeout(getNotifCount, 10000);
        } else { // If there were no new messages
          notifInterval = notifInterval + 1;
          if (notifInterval < 3) {
            setTimeout(getNotifCount, 10000);
          } else if (notifInterval < 6) {
            setTimeout(getNotifCount, 30000);
          } else if (notifInterval >= 6) {
            setTimeout(getNotifCount, 60000);
          }
        }
    });
}

function alertFileName() {
    'use strict';
    if ($('#fileupload').val()) {

        var thefile = document.getElementById('fileupload');
        //var file = thefile.substr(12,15);
        file = thefile.value.substr(12, (thefile.value).length);
        $("#dropplease").css('background-color', 'white');
        $("#dropplease").css('color', 'black');
        $("#dropplease").css('cursor', 'auto');
        $("#dropplease p:first-of-type").css('display', 'none');
        $("#dropplease p:nth-child(2)").css('display', 'block');
        $("#dropplease #fileName").html(file);

    } else {

        $('#dropplease').css('background-color', 'transparent');
        $("#dropplease").css('color', 'white');
        $("#dropplease p:first-of-type").css('display', 'block');
        $("#dropplease p:nth-child(2)").css('display', 'block');
        $('#dropplease').css('cursor', 'pointer');
    }

}

jQuery.fn.center = function () {
    'use strict';
    this.css("position", "absolute");
    //this.css("top", ( $(window).height() - this.height() ) / 2+$(window).scrollTop() + "px");
    this.css("right", ($(window).width() - this.width()) / 2 + $(window).scrollLeft() + "px");
    return this;
};


function scorePassword(pass) {
    'use strict';
    var score = 0,
        letters = {},
        i = 0,
        variations = {
            digits: /\d/.test(pass),
            lower: /[a-z]/.test(pass),
            upper: /[A-Z]/.test(pass),
            nonWords: /\W/.test(pass)
        },
        variationCount = 0,
        check;

    if (!pass) {
        return score;
    }
    // award every unique letter until 5 repetitions

    for (i; i < pass.length; i += 1) {
        letters[pass[i]] = (letters[pass[i]] || 0) + 1;
        score += 5.0 / letters[pass[i]];
    }

    for (check in variations) {
        if (variations.hasOwnProperty(check)) {
            variationCount += (variations[check] === true) ? 1 : 0;
        }
    }
    score += (variationCount - 1) * 10;

    return parseInt(score, 10);
}


function checkPassStrength(pass, settings) {
    'use strict';

    var score = scorePassword(pass),
        strength = '';
    passwordOkay = false;

    if (settings) {
        if (score > 80) {
            $('#changepassword').animate({backgroundColor: '#C8E6C9'}, 100);
            strength = "strong";
            passwordOkay = true;
        } else if (score > 60) {
            $('#changepassword').animate({backgroundColor: '#FFF9C4'}, 100);
            strength = "good";
            passwordOkay = true;
        } else if (score >= 1) {
            $('#changepassword').animate({backgroundColor: '#FFCDD2'}, 100);
            strength = "weak";
            passwordOkay = false;
        } else if (score === 0) {
            $('#changepassword').animate({backgroundColor: 'white'}, 100);
            strength = "";
            passwordOkay = false;
        }
        document.getElementById('validAccountDetails').innerHTML = strength;
        checkPassword();

    } else {
        if (score > 80) {
            $('#newpassword').animate({backgroundColor: '#C8E6C9'}, 100);
            strength = "strong";
            passwordOkay = true;
        } else if (score > 60) {
            $('#newpassword').animate({backgroundColor: '#FFF9C4'}, 100);
            strength = "good";
            passwordOkay = true;
        } else if (score >= 1) {
            $('#newpassword').animate({backgroundColor: '#FFCDD2'}, 100);
            strength = "weak";
            passwordOkay = false;
        } else if (score === 0) {
            $('#newpassword').animate({backgroundColor: 'white'}, 100);
            strength = "";
            passwordOkay = false;
        }

        document.getElementById('validAccountDetails').innerHTML = strength;

        if (typeof (page) !== "undefined" && page !== null) {
            if (page === "resetpassword") { // if user is on messages
              if (passwordOkay) {
                  document.getElementById('submit').disabled = false;
              } else {
                document.getElementById('submit').disabled = true;
              }
              return;
            }
        }
        checkAccountValues();
    }
}



function showMessages(id, event) { // Show message dropdown
    'use strict';
    event.stopPropagation();
    $(".messagesDropDown").toggleClass('show');
    $(".accountDropDown").removeClass('show');
    if (!alreadyLoaded) { // If it hasn't been loaded yet

        var messageDropDownSend = $.ajax({
            type: 'GET',
            url: 'https://www.bounti.io/functions.php',
            data: {
                getmessagesforthisguy: id
            },
            dataType: 'json',
            timeout: 3000
        });

        messageDropDownSend.always(function (responseText) {
            $('.messagesDropDown').html('<div class="centerme">Loading...</div>');
            alreadyLoaded = false;
        });

        messageDropDownSend.done(function (responseText) {
            $('.messagesDropDown').html(responseText);
            alreadyLoaded = true;
        });

        messageDropDownSend.fail(function (e) {
            $('.messagesDropDown').html('<div class="centerme">Something went wrong</div>');
            alreadyLoaded = false;
        });

    }
}

function showDetails(messageid) {
    'use strict';
    $('#x' + messageid).toggle();
}

function deleteConvo(convoid) {
    'use strict';
    removeDarken();
    var deleteMessagesSend = $.ajax({
        type: 'POST',
        url: 'https://www.bounti.io/functions.php',
        data: {
            convotodelete: convoid
        },
        dataType: 'json',
        timeout: 3000
    });

    deleteMessagesSend.done(function (responseText) {
        //showError(responseText);
        selectUser(currentSelection, currentConvo);
        $('#messagesLS').load(location.href + ' #messagesLS>*', function () {
            $('#' + currentConvo).addClass('activeMessager');
        });
    });

    deleteMessagesSend.fail(function (e) {
        showError('Something went wrong');
        console.log(e);
    });
}


function sendMessage() {
    'use strict';
    var varid = document.getElementById('convid').value,
        varto = document.getElementById('usrid').value,
        varcontents = document.getElementById('messageText').value,
        messageSend = $.ajax({
            type: 'POST',
            url: 'https://www.bounti.io/functions.php',
            data: {
                convoid: varid,
                userto: varto,
                contents: varcontents,
                unconfirmedid: unconfirmedID
            },
            timeout: 3000,
            dataType: 'json'
        });
    $('#noMessagesMessage').remove();

    $('#messagesRS').append('<div class="message sender unconfirmed" id="' + unconfirmedID + '">' + varcontents + '</div>');
    $("#messagesRS").animate({ scrollTop: $('#messagesRS').prop("scrollHeight")}, 1000); // Scroll down

    document.getElementById("messageText").focus();
    document.getElementById("messageText").value = '';


    messageSend.always(function () {
        unconfirmedID = unconfirmedID + 1;
    });

    messageSend.done(function (responseText) {
        $('#' + responseText.unconfirmedid).attr('id', responseText.id);
        $('#' + responseText.id).attr('onclick', 'showDetails(' + responseText.id + ')');
        $('#' + responseText.id).append('<img id="x' + responseText.id + '" class="messagesX" src="https://www.bounti.io/img/xsmall.png" />');

        $('#' + responseText.id).removeClass('unconfirmed');

        $('#messagesLS').load(location.href + ' #messagesLS>*', function () {
            $('#' + varid).addClass('activeMessager');
            currentSelection = varto;
            currentConvo = varid;
        });
    });

    messageSend.fail(function (e) {
        console.log(e);
    });

}


// Specific pages function overriding
if (typeof (page) !== "undefined" && page !== null) {
    if (page === "messages") { // if user is on messages

        $(document).on('contextmenu', '.messageDiv', function (event) {
            'use strict';
            event.preventDefault();

            // Show contextmenu
            $(".contextMenu").css({
                top: event.pageY + "px",
                left: event.pageX + "px",
                opacity: 1,
                visibility: 'visible'
            }).attr('id', $(this).attr('id'));
        });


        $(document).bind("mousedown", function (e) { // If the document is clicked somewhere
            'use strict';
            if ($(e.target).parents(".contextMenu").length <= 0) { // If the clicked element is not the menu
                $(".contextMenu").css({opacity: 0, visibility: 'visible'}).removeAttr('id'); // Hide it
            }
        });


        $(document).on('click', '.messagesX', function (event) {
            'use strict';
            event.preventDefault();
            var messageToDelete = $(this).attr('id').substr(1), // The id of the message to delete
                messageDeleteSend = $.ajax({
                    type: 'POST',
                    url: 'https://www.bounti.io/functions.php',
                    data: {
                        idToDelete: messageToDelete,
                        conversationID: currentConvo
                    },
                    timeout: 3000,
                    dataType: 'json'
                });
            $('#' + messageToDelete).hide();

            messageDeleteSend.done(function (responseText) {
                $('#' + messageToDelete).remove();

                $('#messagesLS').load(location.href + ' #messagesLS>*', function () {
                    $('#' + currentConvo).addClass('activeMessager');
                });
            });

            messageDeleteSend.fail(function (e) {
                showError('Something went wrong');
                console.log(e);
            });

        });

        function getNotifCount() {
            'use strict';

            $('#mailbox').attr('src', 'https://www.bounti.io/img/email-outline.png');

            var getNotifSend = $.ajax({
                type: 'POST',
                url: 'https://www.bounti.io/functions.php',
                data: {
                    notif: 'yes'
                },
                dataType: 'json',
                timeout: 3000
            });

            getNotifSend.done(function (responseText) {
                if (currentNotifCount !== responseText.notificationCount) { // If the number is different than what it was before
                    $('#notif').text(responseText.notificationCount); // Put the number in the hidden bubble

                    if (responseText.notificationCount > 0) { // If it's greater than 0
                        $('#notif').css('display', 'flex'); // Show the bubble
                        document.title =  pageTitle + ' (' + responseText.notificationCount + ')'; // Update the title tab
                    } else { // If there are 0 new messages
                        $('#notif').css('display', 'none'); // Hide the bubble
                        document.title = pageTitle; // Set the title as Bounti.io
                    }
                }


                if (currentUnreadMessages !== responseText.unreadMessageCount) { // If there's a different amt of messages
                    /*if (responseText.unreadMessageCount > 0) {
                      $('#mailbox').attr('src', 'img/email-filled.png');

                    } else {
                      $('#mailbox').attr('src', 'img/email-outline.png');
                    }*/
                    // Refresh messagesRS TODO: (PUT IN FUNCTION)
                    //selectUser(currentConvo, currentSelection);

                    var messageGet = $.ajax({
                        type: 'GET',
                        url: 'https://www.bounti.io/functions.php',
                        data: {
                            conversationID: currentConvo,
                            getNewOnes: 'yes'
                        },
                        timeout: 3000,
                        dataType: 'json'
                    }),
                        i = 0,
                        array,
                        getMessagesSend = $.ajax({
                            type: 'GET',
                            url: 'https://www.bounti.io/functions.php',
                            data: {
                                getmessagesforthisguy: 'i'
                            },
                            dataType: 'html',
                            timeout: 3000
                        });
                    messageGet.done(function (responseText) {
                        for (i; i < responseText.length; i = i + 1) {
                            array = responseText[i];
                            $('#messagesRS').append('<div class="message reciever" id="' + array.id + '" onclick="showDetails(' + array.id + ')">' + array.contents + '<img id="x' + array.id + '" class="messagesX" src="https://www.bounti.io/img/xsmall.png" /></div>'); // Attach recieved messages
                            messagesOffset = messagesOffset + 1;
                        }
                        // TODO: IF USER IS SCROLLED ALL THE WAY DOWN:

                        $("#messagesRS").animate({ scrollTop: $('#messagesRS').prop("scrollHeight")}, 1000); // Scroll down


                        $('#messagesLS').load(location.href + ' #messagesLS>*', function () {
                            $('#' + currentConvo).addClass('activeMessager');
                        });
                    });

                    messageGet.fail(function (e) {
                        console.log('Failure');
                        console.log(e);
                    });


                    getMessagesSend.done(function (responseText) {
                        $('.messagesDropDown').html(responseText);
                        alreadyLoaded = true;
                    });

                    getMessagesSend.fail(function (e) {
                        $('.messagesDropDown').html('<div class="centerme">Something went wrong</div>');
                        alreadyLoaded = false;
                    });
                }

                if ((currentNotifCount !== responseText.notificationCount) || (currentUnreadMessages !== responseText.unreadMessageCount)) {
                  notifInterval = 0; // Reset the notification interval
                  setTimeout(getNotifCount, 5000);
                } else { // If there were no new messages
                  notifInterval = notifInterval + 1;
                  if (notifInterval < 3) {
                    setTimeout(getNotifCount, 5000);
                  } else if (notifInterval < 6) {
                    setTimeout(getNotifCount, 10000);
                  } else if (notifInterval >= 6) {
                    setTimeout(getNotifCount, 30000);
                  }
                }

                currentUnreadMessages = responseText.unreadMessageCount;
                currentNotifCount = responseText.notificationCount; // Update the variable with correct count
            });

            getNotifSend.fail(function (e) {
                clearInterval(getNotifCount); // Stop the notification checker
                $('#notif').text('?'); // Put a question mark in the bubble
                $('#notif').css('display', 'flex'); // Show the bubble as sort of an error
            });
        }


    } else if (page === 'notifications') { // If the user is on the notifications page
        function getNotifCount() {
            'use strict';
            var getMessagesSend = $.ajax({
                type: 'POST',
                url: 'https://www.bounti.io/functions.php',
                data: {
                    notif: 'yes'
                },
                dataType: 'json',
                timeout: 3000
            });

            getMessagesSend.done(function (responseText) {
                if (currentNotifCount !== responseText.notificationCount) { // If the number is different than what it was before
                    $('#notif').text(responseText.notificationCount); // Put the number in the hidden bubble

                    if (responseText.notificationCount > 0) { // If it's greater than 0
                        $('#notif').css('display', 'flex'); // Show the bubble
                        document.title =  pageTitle + ' (' + responseText.notificationCount + ')'; // Update the title tab
                        $('#messages').load('https://www.bounti.io/notifications.php #messages > *'); // Refresh messages
                    } else { // If there are 0 new messages
                        $('#notif').css('display', 'none'); // Hide the bubble
                        document.title = pageTitle; // Set the title as Bounti.io
                    }
                }


                if (currentUnreadMessages !== responseText.unreadMessageCount) { // If there's a different amt of messages
                    if (responseText.unreadMessageCount > 0) {
                        $('#mailbox').attr('src', 'https://www.bounti.io/img/email-filled.png');
                    } else {
                        $('#mailbox').attr('src', 'https://www.bounti.io/img/email-outline.png');
                    }
                    // Put this in a function and have showMessages do that function and toggleClass(show)
                    var getMessagesSend = $.ajax({
                        type: 'GET',
                        url: 'https://www.bounti.io/functions.php',
                        data: {
                            getmessagesforthisguy: 'i'
                        },
                        dataType: 'html',
                        timeout: 3000
                    });

                    getMessagesSend.done(function (responseText) {
                        $('.messagesDropDown').html(responseText);
                        alreadyLoaded = true;
                    });

                    getMessagesSend.fail(function (e) {
                        $('.messagesDropDown').html('<div class="centerme">Something went wrong</div>');
                        alreadyLoaded = false;
                    });

                    $('#messagesLS').load(location.href + ' #messagesLS>*', function () {
                        $('#' + currentConvo).addClass('activeMessager');
                    });
                }

                if ((currentNotifCount !== responseText.notificationCount) || (currentUnreadMessages !== responseText.unreadMessageCount)) {
                  notifInterval = 0; // Reset the notification interval
                  setTimeout(getNotifCount, 5000);
                } else { // If there were no new messages
                  notifInterval = notifInterval + 1;
                  if (notifInterval < 3) {
                    setTimeout(getNotifCount, 5000);
                  } else if (notifInterval < 6) {
                    setTimeout(getNotifCount, 10000);
                  } else if (notifInterval >= 6) {
                    setTimeout(getNotifCount, 30000);
                  }
                }


                currentUnreadMessages = responseText.unreadMessageCount;
                currentNotifCount = responseText.notificationCount; // Update the variable with correct count
            });

            getMessagesSend.fail(function (e) {
                clearInterval(getNotifCount); // Stop the notification checker
                $('#notif').text('?'); // Put a question mark in the bubble
                $('#notif').css('display', 'flex'); // Show the bubble as sort of an error
            });
        }



        $(document).ready(function () {
            'use strict';



            $('#messages').on('click', '.unread', function () {
                $(this).removeClass('unread').addClass('read');

                var newNotifCount = currentNotifCount - 1,
                    messageToRead = $(this).attr('id'),
                    notifReadSend = $.ajax({
                        type: 'POST',
                        url: 'https://www.bounti.io/functions.php',
                        data: {
                            messageIDToRead: messageToRead
                        },
                        dataType: 'json',
                        timeout: 3000
                    });

                if (newNotifCount === 0) {
                    $('#notif').hide();
                } else {
                    $('#notif').text(newNotifCount);
                }

                document.title =  pageTitle + ' (' + newNotifCount + ')';


                notifReadSend.done(function (responseText) {
                    getNotifCount();
                });

                notifReadSend.fail(function (e) {
                    showError('Something went wrong');
                    console.log(e);
                });



            });
        });

        function giveUp(bountiID) {
          giveUpBounti = bountiID;

          $('body').append('<div class="darken">' +
              '<div class="taskList" style="width: 50vmin; padding-bottom: 1vmin">' +
              '<h3 style="text-align: center; margin-top:1vmin">Are you sure?</h3>' +
              '<p style="text-align: center">You will receive a score of 0 for this bounti and you won\'t be rewarded any money</p>' +
                '<div class="flexRow spaceAround alignCenter">' +
                  '<p style="cursor: pointer" onclick="removeDarken()">No</p>' +
                  '<div class="grayButton">Yes, I\'m sure</div>' +
                '</div>' +
              '</div>');
          $('.darken').css('display', 'flex').hide().fadeIn('fast');

        }

        $(document).on('click', '.grayButton', function (event) {
            'use strict';
            event.preventDefault();

            var deleteSend = $.ajax({
                type: 'POST',
                url: 'https://www.bounti.io/functions.php',
                data: {
                    giveUp: giveUpBounti
                },
                timeout: 3000,
                dataType: 'json'
            });
            deleteSend.done(function (responseText) {
              $('.darken').fadeOut('fast').remove();
              getNotifCount(); // TODO: Find a way to refresh the notifications
              showError(responseText);

            });

            deleteSend.fail(function (e) {
              showError('Something went wrong');
              $('.darken').fadeOut('fast').remove();
              console.log(e);
            });
        });

      } else if (page === 'createaccount') {
        /*$('#submit').click(function() {
          event.preventDefault();
          if (accountCreate === 1) { //COME HERE
            $('body').append('<div class="darken">' +
                '<div class="taskList" style="width: 50vmin; padding-bottom: 1vmin">' +
                '<h3 style="text-align: center; margin-top:1vmin">Bounti.io Beta Notice</h3>' +
                '<p style="text-align: center">I realize that Bounti.io is in the beta stages of testing, and I accept all responsibilities for whatever this amazing (and still in progress) website has in store for me.</p>' +
                  '<div class="flexRow spaceAround alignCenter">' +
                    '<p style="cursor: pointer" onclick="removeDarken()">No, not yet</p>' +
                    '<div class="grayButton">Yes, create my account!</div>' +
                  '</div>' +
                '</div>');
            $('.darken').css('display', 'flex').hide().fadeIn('fast');
          } else {
            console.log('Hey');
          }
        })


      $(document).on('click', '.grayButton', function (event) {
          'use strict';
          event.preventDefault();

          $('#createAccountForm').submit();

        })*/

    } else if (page === 'account') {

        function acceptUser(messageid, userid, paperid, papertitle) {
            'use strict';
            var acceptSend = $.ajax({
                type: 'POST',
                url: 'https://www.bounti.io/functions.php',
                data: {
                    m: messageid,
                    id: userid,
                    title: papertitle,
                    paper: paperid,
                    accept: 'yes'
                },
                dataType: 'json',
                timeout: 3000
            });

            acceptSend.done(function (responseText) {
                showError(responseText);
                $('#bottomRight').load(window.location.href + ' #bottomRight>*');
            });

            acceptSend.fail(function (e) {
                showError('Something went wrong');
                console.log(e);
            });

        }

        function declineRequest(senderid, messageid, paperid, type) {
            'use strict';
            var declineRequestSend = $.ajax({
                type: 'POST',
                url: 'https://www.bounti.io/functions.php',
                data: {
                    m: messageid,
                    id: senderid,
                    papid: paperid,
                    s: type
                },
                timeout: 3000,
                dataType: 'json'
            });

            declineRequestSend.done(function (responseText) {
                // Refresh the mesages page
                $('#bottomRight').load(window.location.href + ' #bottomRight>*');
            });

            declineRequestSend.fail(function (e) {
                console.log(e);
            });

        }


        $(document).ready(function () {
            'use strict';
            var ajaxFormStuff = {
                url: 'https://www.bounti.io/functions.php',
                type: 'post',
                dataType: 'json',
                timeout: 5000,
                resetForm: true,
                always: function() {
                  console.log('Always')
                },
                success: function (responseText) {
                  showError(responseText);
                  $('#reviews').load(location.href + ' #reviews>*');
                },
                error: function (e) {
                    showError('Something went wrong');
                    console.log(e);
                }
            };

            $('#review-form').ajaxForm(ajaxFormStuff);
        });


    } else if (page === 'resetpassword') {
        $(document).ready(function () {
            'use strict';
            var ajaxFormStuff = {
                url: 'https://www.bounti.io/functions.php',
                type: 'post',
                dataType: 'json',
                timeout: 5000,
                resetForm: true,
                success: function (responseText) {
                    if (responseText.passwordReset) {
                        showError(responseText.message);
                        setTimeout(function() {
                          location.href = "https://www.bounti.io/loginpage";
                        }, 3000)
                    } else {
                        showError(responseText.message);
                    }
                },
                error: function (e) {
                    showError('Something went wrong');
                    console.log(e);
                }
            };

            $('#resetPass').ajaxForm(ajaxFormStuff);
        });
    } else if (page === 'completedbounti') {
        $(document).mouseup(function (e) {
            'use strict';
            var container = $(".taskList");
            if (!container.is(e.target) && container.has(e.target).length === 0) {
                $(".darken").fadeOut("fast");
            }
        });
        $(document).ready(function () {
            'use strict';

            var ajaxFormStuff = {
                url: 'https://www.bounti.io/functions.php',
                type: 'post',
                dataType: 'json',
                timeout: 10000,
                //resetForm: true,
                always: function () {
                    showError('Updating...');
                },
                success: function (responseText) {
                    $('.taskList').html(
                        '<h3 style="text-align: center">Report Sent</h3>' +
                            '<div class="flexItem">' +
                            '<p style="text-align: center">The Bounti Team has been notified and will correspond with you via email when this report has been seen.</p>' +
                            '<div class="justifyCenter flexRow"><button id="reportSubmit" type="submit" style="margin-top:2vmin">Okay</button></div>' +
                            '</div>'
                    );
                //$('.darken').fadeOut();
                },
                error: function (e) {
                    showError('Something went wrong');
                    console.log(e);
                }
            };

            $('#report-form').ajaxForm(ajaxFormStuff);

            $('.taskList').on('click', '#reportSubmit', function () {
                $(".darken").fadeOut("fast");
            });

        });

    } else if (page === 'settings') {
        $(document).ready(function () {
            'use strict';

            $('.coolBackground').click(function () {
                var thing = $(this).attr('id');
                $('#' + thing + '-form').slideToggle('fast');

            });


            var ajaxFormStuff = {
                url: 'https://www.bounti.io/functions.php',
                type: 'post',
                dataType: 'json',
                timeout: 10000,
                //resetForm: true,
                always: function () {
                    showError('Updating...');
                },
                success: function (responseText) {
                    if (responseText.settingschanged) {
                        showError(responseText.message);
                        var picsrc = $('#account').attr('src');
                        $('#account').attr('src', picsrc + "?" + new Date().getTime());
                    } else {
                        showError(responseText.message);
                    }
                },
                error: function (e) {
                    showError('Something went wrong');
                    console.log(e);
                }
            };

            $('#name-form').ajaxForm(ajaxFormStuff);
            $('#email-form').ajaxForm(ajaxFormStuff);
            $('#dob-form').ajaxForm(ajaxFormStuff);
            $('#passWord-form').ajaxForm(ajaxFormStuff);
            $('#profpic-form').ajaxForm(ajaxFormStuff);
        });

    } else if (page === 'submitbounti') {


      $(document).ready(function() {
        var submitBountiID = $('input#submitBountiID').val();

        $('#giveUp').click(function() {
          $('body').append('<div class="darken">' +
              '<div class="taskList" style="width: 50vmin; padding-bottom: 1vmin">' +
              '<h3 style="text-align: center; margin-top:1vmin">Are you sure?</h3>' +
              '<p style="text-align: center">You will receive a 0 for this bounti and you won\'t be rewarded any money</p>' +
                '<div class="flexRow spaceAround alignCenter">' +
                  '<p style="cursor: pointer" onclick="removeDarken()">No</p>' +
                  '<div class="grayButton">Yes, I\'m sure</div>' +
                '</div>' +
              '</div>');
          $('.darken').css('display', 'flex').hide().fadeIn('fast');
        });

        $(document).mouseup(function (e) {
            'use strict';
            var container = $(".taskList"),
                giveUpButton = $(".grayButton");

            if (!container.is(e.target) && container.has(e.target).length === 0) {
                $(".darken").fadeOut("fast").delay(300).remove();
            } else if (giveUpButton.is(e.target)) { // If the person clicks that they want to give up
              var deleteSend = $.ajax({
                  type: 'POST',
                  url: 'https://www.bounti.io/functions.php',
                  data: {
                      giveUp: submitBountiID
                  },
                  timeout: 3000,
                  dataType: 'json'
              });

              deleteSend.done(function (responseText) {
                //showError('Grade decreased and Bounti given up on');
                location.href = "https://www.bounti.io/bounties";

              });

              deleteSend.fail(function (e) {
                showError('Something went wrong');
                $('.darken').fadeOut('fast').remove();
                console.log(e);
              });
            }
        });
/*
        var submitFormStuff = {
            url: 'https://www.bounti.io/functions.php',
            type: 'post',
            dataType: 'json',
            timeout: 10000,
            //resetForm: true,
            always: function () {
                showError('Submitting...');
            },
            success: function (responseText) {
                if (responseText.submitted) {
                    showError('Submitted');
                    location.href = 'https://www.bounti.io/bountisuccessful';
                } else {
                    showError(responseText.message);
                }
            },
            error: function (e) {
                showError('Something went wrong');
                console.log(e);
            }
        };

        $('#submitform').ajaxForm(submitFormStuff);


        //say(submitBountiID);*/
      });

    }


}



function deleteThis(messageID) {
    'use strict';
    var deleteSend = $.ajax({
        type: 'POST',
        url: 'https://www.bounti.io/functions.php',
        data: {
            x: messageID
        },
        timeout: 3000,
        dataType: 'json'
    });

    deleteSend.always(function () {
        $('#' + messageID).hide();
    });

    deleteSend.done(function () {
        $('#' + messageID).remove();
        //$('#messages').load('notifications.php #messages > *'); // Refresh messages
        getNotifCount(); // Refresh notification count (TODO: BE BETTER)
    });

    deleteSend.fail(function (e) {
        $('#' + messageID).show();
        showError('Something went wrong');
        console.log(e);
    });
}

/*
var notifChecker = setInterval(function () {
    'use strict';
    getNotifCount(); // Get the new notification count
}, 10 * 1000); // Every 10 seconds
*/
/*function loadMore() {
  var firstMsg = $('.message:first');
   var curOffset = firstMsg.offset().top - $('#messagesRS').scrollTop();

  messagesOffset += 50;
  //say('convoid: ' + currentConvo +  ' userid: ' + currentSelection + ' offset: ' + messagesOffset);
  //say('LOADING MORE :)');
  $.get('https://www.bounti.io/functions.php', {conversationID: currentConvo, userID: currentSelection, offset: messagesOffset}, function(responseText, status) {
    if(status === 'success') {
      //showError(currentConvo + ' user: ' + currentSelection);
      $('#messagesRS').prepend(responseText);
      $('#messagesRS').scrollTop(firstMsg.offset().top-curOffset);
    } else {
      say('error');
    }
  });

}*/
$(function () {
    'use strict';
    var $form = $('#cc-form');
    $form.submit(function (event) {
        // Disable the submit button to prevent repeated clicks:
        $form.find('.submit').prop('disabled', true);

        // Request a token from Stripe:
        Stripe.card.createToken({
            //name: $('.card-holder').val(),
            number: $('.card-number').val(),
            cvc: $('.card-cvc').val(),
            exp_month: $('.card-expiry-month').val(),
            exp_year: $('.card-expiry-year').val(),
            currency: 'USD'
        }, stripeCCResponseHandler);

        // Prevent the form from being submitted:
        return false;
    });
});

function verifyCCNum(num) {
    'use strict';
    if (!Stripe.card.validateCardNumber(num)) {
        showError('Invalid credit card number');
    }
}


function stripeCCResponseHandler(status, response) {
    'use strict';
    // Grab the form:
    var $form = $('#cc-form'),
        token = response.id;

    if (response.error) { // Problem!

        // Show the errors on the form:
        showError(response.error.message);
        $form.find('.submit').prop('disabled', false); // Re-enable submission

    } else { // Token was created!

        // Insert the token ID into the form so it gets submitted to the server:
        $form.append($('<input type="hidden" name="stripeToken" value="' + token + '">'));
        $form.append($('<input type="hidden" name="formSubmitted" value="cc" />'));

        // Submit the form:
        $form.get(0).submit();
    }
}

$(function () {
    'use strict';
    var $form = $('#ba-form');

    $form.submit(function (event) {
        // Disable the submit button to prevent repeated clicks:
        $form.find('.submit').prop('disabled', true);

        // Request a token from Stripe:
        Stripe.bankAccount.createToken({
            country: 'US',
            currency: 'USD',
            routing_number: $('.routing-number').val(),
            account_number: $('.account-number').val(),
            account_holder_name: $('.account-name').val(),
            account_holder_type: 'individual'
        }, stripeBAResponseHandler);

        // Prevent the form from being submitted:
        return false;
    });
});

function stripeBAResponseHandler(status, response) {
    'use strict';
    // Grab the form:
    var $form = $('#ba-form'),
        token = response.id;

    if (response.error) { // Problem!

        // Show the errors on the form:
        showError(response.error.message);
        $form.find('button').prop('disabled', false); // Re-enable submission

    } else { // Token created!

        // Insert the token into the form so it gets submitted to the server:
        $form.append($('<input type="hidden" name="stripeToken" value="' + token + '" />'));
        $form.append($('<input type="hidden" name="formSubmitted" value="ba" />'));

        // Submit the form:
        $form.get(0).submit();

    }
}


function changeDefaultExtAccount(id) {
    'use strict';
    event.preventDefault();

    var defaultSend = $.ajax({
        type: 'POST',
        url: 'https://www.bounti.io/functions.php',
        data: {
            updateExternalAccount: id
        },
        dataType: 'json',
        timeout: 3000
    });

    defaultSend.done(function (responseText) {
        if (responseText) {
            $('#' + id).addClass('default').siblings().removeClass('default');
            showError('Default changed');
            return false;
        } else {
            showError('Something went wrong');
        }
    });

    defaultSend.fail(function (e) {
        showError('Something went wrong');
    });
}

function changeDefaultPayment(id) {
    'use strict';
    var defaultSend = $.ajax({
        type: 'POST',
        url: 'https://www.bounti.io/functions.php',
        data: {
            updatePaymentMethod: id
        },
        dataType: 'json',
        timeout: 3000
    });

    defaultSend.done(function (responseText) {
        if (responseText) {
            $('#' + id).addClass('default').siblings().removeClass('default');
            showError('Default changed');
            return false;
        } else {
            showError('Something went wrong');
        }
    });

    defaultSend.fail(function (e) {
        showError('Something went wrong');
    });

}

function deletePaymentMethod(id) {
    'use strict';

    if ($('#' + id).hasClass('default')) {
        showError('You can\'t delete your default payment method');
    } else {

        var deleteSend = $.ajax({
            type: 'POST',
            url: 'https://www.bounti.io/functions.php',
            data: {
                deletePaymentMethod: id
            },
            dataType: 'json',
            timeout: 3000
        });

        deleteSend.done(function (responseText) {
            if (responseText) {
                showError('Payment method deleted');
                $('#' + id).fadeOut('fast', function () {
                    $(this).remove();
                });
                return false;
            } else {
                showError('Something went wrong');
            }
        });

        deleteSend.fail(function (e) {
            showError('Something went wrong');
        });
    }
}


function deleteExtAccount(id) {
    'use strict';
    if ($('#' + id).hasClass('default')) {
        showError('You can\'t delete your default pay out method');
    } else {

        var deleteSend = $.ajax({
            type: 'POST',
            url: 'https://www.bounti.io/functions.php',
            data: {
                deleteExtAccount: id
            },
            dataType: 'json',
            timeout: 3000
        });

        deleteSend.done(function (responseText) {
            if (responseText) {
                showError('Pay out method deleted');
                $('#' + id).fadeOut('fast', function () {
                    $(this).remove();
                });
                return false;
            } else {
                showError('Something went wrong');
            }
        });

        deleteSend.fail(function (e) {
            showError('Something went wrong');
        });
    }
}


function confirmation() {
    'use strict';
    var idToDelete = $('.contextMenu').attr('id');

    $('body').append('<div class="darken">' +
        '<div class="taskList" style="width: 30vmin; padding-bottom: 1vmin">' +
        '<h3 style="text-align: center; margin-top:1vmin">Are you sure?</h3>' +
          '<div class="flexRow spaceAround alignCenter">' +
            '<p style="cursor: pointer" onclick="removeDarken()">No</p>' +
            '<div class="grayButton" onclick="deleteConvo(\'' + idToDelete + '\')">Yes, delete messages</div>' +
          '</div>' +
        '</div>');
    $('.darken').css('display', 'flex').hide().fadeIn('fast');
}

function verifyBankAccount(accountID) {
    'use strict';
    $('body').append('<div class="darken">' +
        '<div class="taskList">' +
        '<h3 style="text-align: center">Verify bank account</h3>' +
        '<div class="flexItem">' +
          '<p style="text-align: center">To verify your bank account, look in your bank transactions for two deposits with the description \'VERIFICATION\', and enter the numbers seperately below.</p>' +
          '<form id="verifyBank" action="https://www.bounti.io/managepaymentmethods.php" method="post" accept-charset="UTF-8">' +
            '<input type="hidden" name="accountid" value="' + accountID + '" />' +
            '<div class="flexRow spaceAround">' +
              '<label>Amount 1: </label>' +
              '<input type="text" name="amount1" size="2" required />' +
            '</div>' +
            '<div class="flexRow spaceAround">' +
              '<label>Amount 2: </label>' +
              '<input type="text" name="amount2" size="2" required />' +
            '</div>' +
            '<input type="submit" value="Verify" style="margin-top:2vmin" />' +
          '</form>' +
        '</div>' +
        '</div>' +
        '</div>');
    $('.darken').css('display', 'flex').hide().fadeIn('fast');
}

function recoverPass(event) {
    'use strict';
    event.preventDefault();
    recoverPassword();
}

$(document).ready(function () {
    'use strict';

    getNotifCount();




    $('.paymentMethod').click(function () {
        var methodID = $(this).attr('id');
        $(this).toggleClass('activePaymentMethod');
        $('#paymentButtons' + methodID).fadeToggle('fast').toggleClass('flex');
    });


    $('#reward').on('input', function () {
        var reward, fee, total;
        reward = this.value;


        if (reward < 20) {
            fee = 1;
        } else {
            fee = parseFloat(reward / 10).toFixed();
        }

        total = (+reward) + (+fee);
        fee = parseFloat(fee).toFixed(2);
        reward = parseFloat(reward).toFixed(2);
        total = parseFloat(total).toFixed(2);


        $('#rewardWorth').text('$' + reward);
        $('#feesWorth').text('$' + fee);
        $('#totalWorth').text('$' + total);
    });

    // TODO: FIGURE OUT WHAT THIS IS
    $("#bank-account-form").submit(function (event) {
        event.preventDefault();

        say('THIS IS THE FORM I WAS TALKING ABOUT');
        //say($('.country').val());
        // disable the submit button to prevent repeated clicks
        $('.submit-button').attr("disabled", "disabled");
        // createToken returns immediately - the supplied callback submits the form if there are no errors

        Stripe.bankAccount.createToken({
            country: 'US',
            currency: 'USD',
            routing_number: '110000000',
            account_number: '000123456789',
            account_holder_name: 'Frankie Fanelli',
            account_holder_type: 'individual'
        }, stripeCCResponseHandler);
        return false; // submit from callback
    });




    $('#messagesRS').scroll(function () {
        var scrollTop = $(this).scrollTop(),
            firstMsg = $('.message:first'),
            curOffset = firstMsg.offset().top - $('#messagesRS').scrollTop();

        if (scrollTop <= 0) {
          messagesOffset += 50;



            if (!noMoreMessages) { // If there are more messages
              var getMoreMessagesSend = $.ajax({
                  type: 'GET',
                  url: 'https://www.bounti.io/functions.php',
                  data: {
                      conversationID: currentConvo,
                      offset: messagesOffset
                  },
                  dataType: 'html',
                  timeout: 3000
              });

              getMoreMessagesSend.always(function () {
                $('#loadMore').show();
              });


                getMoreMessagesSend.done(function (responseText) {
                    if (responseText === 'none') {
                        $('#loadMore').hide();
                        noMoreMessages = true;
                    } else {
                        $('#loadMore').hide();
                        $('#messagesRS').prepend(responseText);
                        $('#messagesRS').scrollTop(firstMsg.offset().top - curOffset);
                    }
                });

                getMoreMessagesSend.fail(function (e) {
                    showError('Error loading messages');
                    console.log(e);
                    noMoreMessages = true;
                });
            }
        }
    });

    /*$('form').submit(function(e) {
      e.preventDefault();

      //say('ayo');
      return false;
    })*/

    $('#newemail').change(function () {
        var email = document.getElementById('newemail').value;
        emailCheck(email);
    });

    $('#newusername').change(function () {
        username = document.getElementById('newusername').value;
        newUsernameCheck(username);
    });

    $('#newpassword').keyup(function () {
        var password = document.getElementById('newpassword').value;
        checkPassStrength(password);
    });

    $('#newpassword').change(function () {
      if (typeof (page) !== "undefined" && page !== null) {
          if (page === "resetpassword") { // if user is on resetpassword
            return;
          }
      }
      checkAccountValues();
    });

    $('#changepassword').keyup(function () { // Called from settings
      password = document.getElementById('changepassword').value;
      checkPassStrength(password, true);
    });

    $('#changepassword').change(function() {
      checkPassword();
    });


  $('#grade').change(function() {
    if($(this).val() == 59){ // If the user selects 'F'
      $('#modifier').hide(); // Hide the + and -, because an F- doesn't exist
    } else {
      $('#modifier').show(); // Show it in case they change their mind
    }

  });



  $('#ccordc').click(function() {
    $('#cc-form').slideToggle('fast');
    $('#ba-form').slideUp('fast');
  })

  $('#ba').click(function() {
    $('#ba-form').slideToggle('fast');
    $('#cc-form').slideUp('fast');
  })



    $('#passbutton').click(function() {
        $('.passchange').show();
        document.getElementById("newpass").required = false;
        document.getElementById("newpass2").required = false;
    });




    $('.paperPreview').mouseleave(function(event){
        $('#edit p').finish();
    });


    $('#duedateOkayYes').click(function() {
      showError('Must be a day after today');
    });


    $('#usernameOkayYes').click(function() {
      showError('Username must be between 4 and 25 characters');
    });

    $('#emailOkayYes').click(function() {
      showError(emailMessage);
    });

    $('#passwordOkayYes').click(function() {
      showError('Must have a good combination of different characters, numbers, and symbols');
    })


    $('#changeName').click(function() {
        editSettings('#name')
    });
    $('#changeFirst').click(function() {
        editSettings('#firstname')
    });
    $('#changeLast').click(function() {
        editSettings('#lastname')
    });


    $('#changeUsername').click(function() {
        editSettings('#username')
    });
    $('#changeEmail').click(function() {
        editSettings('#email')
    });
    $('#changePassword').click(function() {
        editSettings('#oldpass');
        editSettings('#newpass');
        editSettings('#newpass2');
        $('.hiddenInput').show();
        $('#oldpasslabel').text('Current Password');
    });

    $('#bottomRightX').click(function() {
      $("#bottomRight").fadeOut('fast');
    })


    $('#account').click(function(event){
        event.stopPropagation();
        $(".accountDropDown").toggleClass('show');
        $(".messagesDropDown").removeClass('show');
    });

    $('.reportBounti').click(function() {
      $('.darken').css('display', 'flex').hide().fadeIn('fast');
    })




    /*$('.reportBounti').click(function() {
        finishedID = $(this).attr('id');

        var reportSend = $.ajax({
         type: 'POST',
         url: 'https://www.bounti.io/functions.php',
         data: {
           reportBounti: finishedID
         },
         timeout: 3000,
         dataType: 'json'
       })

       reportSend.done(function(responseText) {
         if (responseText.sent) {
           showError(responseText.message);
         } else {
           showError(responseText.message);
         }
       })

       reportSend.fail(function(e) {
         showError("Something went wrong");
         console.log(e);

       })



    })*/



    $(".accountDropDown").on("click", function (event) {
        event.stopPropagation();
    });

    $(".messagesDropDown").on("click", function (event) {
        event.stopPropagation();
    });
    $(document).on("click", function () {
        $(".accountDropDown").removeClass('show');
        $(".messagesDropDown").removeClass('show');
    });
    $('#notif').click(function () {
        window.location.href = "https://www.bounti.io/notifications";
    });
    $('#logo').click(function () {
        window.location.href = "https://www.bounti.io/index";
    });
    $('#navName').click(function () {
        window.location.href = "https://www.bounti.io/index";
    });
/*
    $('.paperPreviewContainer').mouseenter(function () {
        $('#edit p').fadeIn(250);
    }).mouseleave(function () {
        $('#edit p').fadeOut(250);
    });
*/
    $('#errorBox').children('img').click(function() {
        $('#errorBox').fadeOut("fast");
    });


    /*$('#dropplease').mouseleave(function(){
        if(!file) {
            $('#dropplease').css('background-color','transparent');
            $("#dropplease").css('color', 'white');
            $("#dropplease p:first-of-type").css('display', 'block');
            $("#dropplease p:nth-child(2)").css('display', 'block');
            $('#dropplease').css('cursor','pointer');
        };
    })*/
});







    function CountDownTimer(dt, id, t) {
      if (t == 'b') { // If it's being called for display on bounties

        if (document.getElementById(id).innerHTML[0] == '.') {
          var end = new Date(dt);

          var _second = 1000;
          var _minute = _second * 60;
          var _hour = _minute * 60;
          var _day = _hour * 24;
          var timer;

          function showRemaining() {
              var now = new Date();
              var distance = end - now;
              if (distance < 0) {

                  clearInterval(timer);
                  document.getElementById(id).innerHTML = 'Expired';

                  return;
              }
              var days = Math.floor(distance / _day);
              var hours = Math.floor((distance % _day) / _hour);
              var minutes = Math.floor((distance % _hour) / _minute);
              var seconds = Math.floor((distance % _minute) / _second);

              if (days > 0) {
                document.getElementById(id).innerHTML = days + 'd';
              } else {
                if (hours > 0) {
                  document.getElementById(id).innerHTML = hours + 'h ' + minutes + 'm';
                } else {
                  if (minutes > 0) {
                      document.getElementById(id).innerHTML = minutes + 'm ' + seconds + 's';
                  } else {
                    if (seconds > 0) {
                      document.getElementById(id).innerHTML = seconds + 's';
                    } else {
                      document.getElementById(id).innerHTML = 'Expired';
                    }
                  }
                }
              }

            }
            showRemaining();
            timer = setInterval(showRemaining, 1000);

          } else if (document.getElementById(id).innerHTML[0] == 'S'){
            $('.timer').css('display', 'none');
          }
        } else if (t == 'u'){ // If it's being called from the upload paper form
          var end = new Date(dt);

          var _second = 1000;
          var _minute = _second * 60;
          var _hour = _minute * 60;
          var _day = _hour * 24;
          var timer;

              var now = new Date();
              var distance = end - now;
              if (distance < 0) { // If it's in the past

              $('#duedateOkayYes').attr('src', 'https://www.bounti.io/img/xsmall.png');
              $('#duedateOkayYes').css('opacity', '1');
              document.getElementById('submit').disabled = true;
              } else {
                $('#duedateOkayYes').attr('src', '');
                $('#duedateOkayYes').css('opacity', '0');
                document.getElementById('submit').disabled = false;
              }


      } else if (t === 'm') { // If it's being called from messages
        // TODO: Do this!!
        if (document.getElementById(id).innerHTML[0] == '.') {
          var end = new Date(dt);

          var _second = 1000;
          var _minute = _second * 60;
          var _hour = _minute * 60;
          var _day = _hour * 24;
          var timer;

          function showRemaining() {
              var now = new Date();
              var distance = end - now;
              if (distance < 0) {

                  clearInterval(timer);
                  document.getElementById(id).innerHTML = 'Expired';

                  return;
              }
              var days = Math.floor(distance / _day);
              var hours = Math.floor((distance % _day) / _hour);
              var minutes = Math.floor((distance % _hour) / _minute);
              var seconds = Math.floor((distance % _minute) / _second);

              if (days > 0) {
                document.getElementById(id).innerHTML = days + 'd';
              } else {
                if (hours > 0) {
                  document.getElementById(id).innerHTML = hours + 'h';
                } else {
                  if (minutes > 0) {
                      document.getElementById(id).innerHTML = minutes + 'm';
                  } else {
                    if (seconds > 0) {
                      document.getElementById(id).innerHTML = minutes + 's';
                    } else {
                      document.getElementById(id).innerHTML = 'Expired';
                    }
                  }
                }
              }

            }
            showRemaining();
            timer = setInterval(showRemaining, 1000);

          }
      }
    }

    function convertTimestamp(timestamp) {
  var d = new Date(timestamp * 1000),	// Convert the passed timestamp to milliseconds
		yyyy = d.getFullYear(),
		mm = ('0' + (d.getMonth() + 1)).slice(-2),	// Months are zero based. Add leading 0.
		dd = ('0' + d.getDate()).slice(-2),			// Add leading 0.
		hh = d.getHours(),
		h = hh,
		min = ('0' + d.getMinutes()).slice(-2),		// Add leading 0.
		ampm = 'AM',
		time;

	if (hh > 12) {
		h = hh - 12;
		ampm = 'PM';
	} else if (hh === 12) {
		h = 12;
		ampm = 'PM';
	} else if (hh == 0) {
		h = 12;
	}

	// ie: 2016/02/18, 8:35 AM
	time = mm + '/' + dd + '/' + yyyy/*+ ', ' + h + ':' + min + ' ' + ampm*/;

	document.write(time);
}








/*

function selectUser(varid, userid) {
  //$.get('messages.php', {id: varid, usr: userid}, function(responseText, status) {
    //say('convoid: ' + varid +  ' userid: ' + userid);
    $('#messagesRS').load(location.href + '?id='+varid+'&usr='+userid+' #messagesRS>*', function() {
      $('#messagesRS').scrollTop($('#messagesRS')[0].scrollHeight);
      document.getElementById("messageText").focus();
    });
  //})
}
function selectDueDate() {
  //say("Date today: " + mm + dd);

  // Select current day
  switch (mm) {
    case 01:
      $('#jan').attr('selected', true);
      break;
    case 02:
      $('#feb').attr('selected', true);
      break;
    case 03:
      $('#march').attr('selected', true);
      break;
    case 04:
      $('#april').attr('selected', true);
      break;
    case 05:
      $('#may').attr('selected', true);
      break;
    case 06:
      $('#june').attr('selected', true);
      break;
    case 07:
      $('#july').attr('selected', true);
      break;
    case 08:
      $('#aug').attr('selected', true);
      break;
    case 09:
      $('#sept').attr('selected', true);
      break;
    case 10:
      $('#oct').attr('selected', true);
      break;
    case 11:
      $('#nov').attr('selected', true);
      break;
    case 12:
      $('#dec').attr('selected', true);
      break;
  }
  // Select current day
  $('#' + dd).attr('selected', true);

}

function hasDatePassed() {
  var month = document.getElementById('month').value;
  var day = document.getElementById('day').value;
  var year = document.getElementById('year').value;

  //say("Date: " + month +"/"+ day +"/" + year);
  CountDownTimer(month +"/"+ day +"/" + year, 'please', 'u');

}
var timer = 0,
timerInterval;

function mouseDownEvent() {
  timerInterval = setInterval(function(){
    timer += 1;
        document.getElementById("timer").innerText = timer;

        if(timer == 3) {
          callDeclineAcceptance();
        }
  }, 1000);




};
function mouseUpEvent() {
  timer = 0;
  clearInterval(timerInterval);
  document.getElementById("timer").innerText = timer;
}

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

  // Refresh messages to see if it sent
  $.get('functions.php', {convoid: varid, userto: varto, contents: varcontents, unconfirmedid: unconfirmedID}, function(responseText, status) {
    unconfirmedID++; // Add one, for the next message the user sends
    say(status);
      //say(responseText.message);
      // refresh the rhs!!
      if (status === 'success') {
        say ('Success!');
        // Give it attributes now that you have the id
        $('#' + responseText.unconfirmedID).attr('id', responseText.id);
        $('#' + responseText.id).attr('onclick', 'showDetails('+responseText.id+')');
        $('#' + responseText.id).append('<img id="x'+responseText.id+'" class="messagesX" src="img/xsmall.png" />');

        $('#' + responseText.id).removeClass('unconfirmed');

        $('#messagesLS').load(location.href + ' #messagesLS>*', function() {
          $('#' + varto).addClass('activeMessager');
          currentSelection = varto;
          currentConvo = varid;
        });

        //say(responseText.id);

        /*
        $.get('functions.php', {conversationID: varid, userID: varto}, function(responseText, status) {
          if(status === 'success') {

            sentMessageID++;
            messagesOffset++;
            //$('#messagesRS').html(responseText); // Refreshes RS, deleting all retrieved messages and screwing stuff up
            //$("#messagesRS").animate({ scrollTop: $('#messagesRS').prop("scrollHeight")}, 1000); // Scroll down
            //document.getElementById("messageText").focus();
            //document.getElementById("messageText").value = '';


          }
        });*/


      /*$('#messagesRS').load(location.href + '?id=' + varid + '&usr=' + varto + ' #messagesRS>*', function () {
        $("#messagesRS").animate({ scrollTop: $('#messagesRS').prop("scrollHeight")}, 1000); // Scroll down
        document.getElementById("messageText").focus();
        $('#messagesLS').load(location.href + ' #messagesLS>*');
      })/

  });
*/
