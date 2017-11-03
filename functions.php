<?php
header("Content-type:application/json");
session_start();
include_once '../../../unimportant.php';
require_once '../vendor/autoload.php';

ini_set('display_errors',1);
error_reporting(E_ALL);

try {

// Create connection
$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);
// Check connection
if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}


function sendEmail($address, $emailType, $extra = '') {
  if (!isset($conn)) {
    require '../../../unimportant.php';
    require '../vendor/autoload.php';
    // Create connection
    $conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);
  }

  $stmt = mysqli_prepare($conn, 'SELECT emailnotif FROM users WHERE email = ?');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "s", $address);
  // Execute the statement
  mysqli_stmt_execute($stmt);
  // Get the results
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();

  if ($row['emailnotif'] == 1) {

    require('../../../unimportant.php');
    require_once "../../../usr/share/php/Mail.php";
    require_once '../../../usr/share/php/Mail/mime.php';

    /*$message = '';
      $headers["Subject"] = '';
      $link = '';
      $linkText = '';
  */

    if ($emailType == 'req') { // Someone sent you a message TODO: FIGURE THIS OUT DUDE IDEVENK
      $headers["Subject"] = 'Somebody is requesting to review your paper on Bounti.io';
      $h1 = 'Someone requested your paper!';
      $message = 'Click the button below to be taken to your notifications on Bounti.io';
      $link = 'https://bounti.io/notifications';
      $linkText = 'Notifications';
    } else if ($emailType == 'rvw') { // Someone left a review
      $headers["Subject"] = 'Someone left you a review on Bounti.io!';
      $h1 = 'You have a new review';
      $message = 'Click the button below to be taken to your account on Bounti.io';
      $link = 'https://bounti.io/account/' . $extra;
      $linkText = 'Account';
    } else if ($emailType == 'cpl'){ // Someone completed your bounti
      $headers["Subject"] = 'Your Bounti has been completed!';
      $h1 = 'Your Bounti has been turned in!';
      $message = 'The Bounti hunter responsible for reviewing your paper has just turned it in! Click the
      button below to be taken to the completed bounti';
      $link = 'https://bounti.io/completedbounti/' . $extra;
      $linkText = 'Completed bounti';
    } else {
      $message = '';
        $headers["Subject"] = '';
        $link = '';
        $linkText = '';
    }

      $html_message = "<!DOCTYPE>
      <html>
        <head>
          <meta http-equiv='content-type' content='text/html; charset=UTF-8'>
        </head>
      <body style='font-family: sans-serif; color: black'>
      <div style='width: 600px'>
      <img src='".$siteRoot."'/img/bountiheader.png' style='width: 400px; height: auto; margin-left: 100px; margin-right: 100px' />
      <h1 align='center' style='font-size: 32px; color: black; text-decoration: none'>".$h1."</h1>
      <p style='font-size: 18px; text-align: center'>".$message."</p>
      <br><br>
      <table width='600px'>
      <tr>
      <td style='text-align: center'>
      <a style='border-radius: 4px; margin-top: 10px; padding: 7px; padding-left: 12px; padding-right: 12px;
                box-shadow: 0 8px 9px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
                background-color: #E0E0E0; color: black; text-decoration: none;
                cursor: pointer; text-decoration: none; font-size: 18px' href='".$link."'>
        ".$linkText."
      </a>
      </td>
      </tr>
      </table>
      <br><br>
      </div>
      </body>
      </html>";



    $headers["From"] = 'bountiteam@bounti.io';
    $headers["To"] = $address;
    $to = $address;
    $headers["Content-Type"] = 'text/html; charset=UTF-8';
    $headers["Content-Transfer-Encoding"]= "8bit";


    $mime = new Mail_mime;
    $mime->addHTMLImage("$siteRoot/img/bountiheader.png");
    $mime->setHTMLBody($html_message);
    $mimeparams=array();


    $mimeparams['text_encoding']="8bit";
    $mimeparams['text_charset']="UTF-8";
    $mimeparams['html_charset']="UTF-8";
    $mimeparams['head_charset']="UTF-8";


    $body = $mime->get($mimeparams);
    $headers = $mime->headers($headers);
    $page_content = "Mail now.";

    $host = "email-smtp.us-east-1.amazonaws.com";
    $port = "587";

    // SMTP server name, port, user/passwd
    $smtpinfo["host"] = $host;
    $smtpinfo["port"] = $port;
    $smtpinfo["auth"] = true;
    $smtpinfo["username"] = $emailUser;
    $smtpinfo["password"] = $emailPass;


    // Create the mail object using the Mail::factory method
    $mail = Mail::factory('smtp', $smtpinfo);

    $mail->send($to, $headers, $body);

    if (PEAR::isError($mail)) {
      return false;
    } else {
      return true;
    }
  } else {
    return true;
  }
}




if (isset($_POST['notif'])) { // If it's being called from the getUnread js function
  if(isset($_SESSION['loggedIn'])) {

    if($stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS notif, NULL AS messages FROM notifications WHERE recipient = ? AND unread = "unread" AND showyes = 1
                                      UNION (SELECT NULL AS notif, COUNT(*) AS messages FROM conversations WHERE (person1 = ? OR person2 = ?) AND latestsender != ? AND unread = 1)')) {
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "ssss", $_SESSION['rndid'], $_SESSION['rndid'], $_SESSION['rndid'], $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt);
      // Get the results
      $result = $stmt->get_result();

      $notifications = 0;
      $messages = 0;

      while ($row = $result->fetch_assoc()) {
        if (!empty($row['notif'])) {
          $notifications = $row['notif'];
        } else {
          $messages = $row['messages'];
        }
      }

      $data = ['notificationCount' => $notifications, 'unreadMessageCount' => $messages];
      echo json_encode($data);
    }
  }
}

if (isset($_POST['updateExternalAccount'])) {
  \Stripe\Stripe::setApiKey($secretKey);

  $account = \Stripe\Account::retrieve($_SESSION['stripeAcct']);
  $extAcct = $account->external_accounts->retrieve($_POST['updateExternalAccount']);
  $extAcct->default_for_currency = true;
  $extAcct->save();

  echo json_encode(true);
}

if (isset($_POST['updatePaymentMethod'])) {
  \Stripe\Stripe::setApiKey($secretKey);

  $customer = \Stripe\Customer::retrieve($_SESSION['custAcct']);
  $customer->default_source = $_POST['updatePaymentMethod'];

  //$paymentMethod->default_for_currency = true;
  $customer->save();


  echo json_encode(true);
}


if (isset($_POST['deletePaymentMethod'])) {
  \Stripe\Stripe::setApiKey($secretKey);

  $totalCount = \Stripe\Customer::retrieve($_SESSION['custAcct'])->sources->total_count;

  if ($totalCount > 1) { // If the user has at least one more payment method

    $customer = \Stripe\Customer::retrieve($_SESSION['custAcct']);
    $customer->sources->retrieve($_POST['deletePaymentMethod'])->delete();

    echo json_encode(true);
  } else { // User is trying to delete their last payment method
    echo json_encode(false);
  }
}

if (isset($_POST['deleteExtAccount'])) {
  \Stripe\Stripe::setApiKey($secretKey);

  $account = \Stripe\Account::retrieve($_SESSION['stripeAcct']);
  $account->external_accounts->retrieve($_POST['deleteExtAccount'])->delete();

  echo json_encode(true);
}



// Returns all messages from conversationid
if (isset($_GET['conversationID'])) {
  header("Content-type:text/html");

  if (!(isset($_GET['offset']))) { // Loading every message


    if (!(isset($_GET['getNewOnes']))) {
      $id = $_GET['conversationID'];
      //$user = $_GET['userID'];

      // Get total amount of messages
      // Prepare statement
      $stmt = mysqli_prepare($conn, 'SELECT * FROM messages WHERE convoid = ? AND ((userfrom = ? AND deletedbyuserfrom = 0) OR (userto = ? AND deletedbyuserto = 0)) LIMIT 18446744073709551610');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "sss", $id, $_SESSION['rndid'], $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      $result = $stmt->get_result();
      $totalMessages = mysqli_num_rows($result);


    if ($totalMessages > 0) {
      // Prepare statement
      $stmt = mysqli_prepare($conn, 'SELECT * FROM (SELECT * FROM messages WHERE convoid = ? AND ((userfrom = ? AND deletedbyuserfrom = 0) OR (userto = ? AND deletedbyuserto = 0)) ORDER BY timestamp DESC LIMIT 50) AS `table` ORDER BY timestamp ASC');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "sss", $id, $_SESSION['rndid'], $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt);


      $messagesResults = $stmt->get_result();

      if (mysqli_num_rows($messagesResults) == 50) {
        echo '<div class="centerme"><a id="loadMore">loading...</a></div>';
      }

      while($messages = $messagesResults->fetch_assoc()) {
        $class = '';
        if ($messages['userto'] == $_SESSION['rndid']) { // Other person
          $class = ' reciever';

          $userHey = $messages['userfrom'];

        } else { // Sender
          $class = ' sender';

          $userHey = $messages['userto'];
        }

        echo '<div class="message' . $class . '" id="'.$messages['rndid'].'" onclick="showDetails(\''.$messages['rndid'].'\')">' . $messages['contents'] . '<img id="x'.$messages['rndid'].'" class="messagesX" src="' . $siteRoot . '/img/xsmall.png" /></div>';
      }
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT person1, person2 FROM conversations WHERE rndid = ? AND (person1 = ? OR person2 = ?)');
        // Set the parameter
        mysqli_stmt_bind_param($stmt, "sss", $id, $_SESSION['rndid'], $_SESSION['rndid']);
        // Execute the statement
        mysqli_stmt_execute($stmt);

        $something = $stmt->get_result();
        $somethingElse = $something->fetch_assoc();

        if ($somethingElse['person1'] == $_SESSION['rndid']) {
          $userHey = $somethingElse['person2'];
        } else {
          $userHey = $somethingElse['person1'];
        }

      echo '<div class="centerme" id="noMessagesMessage"><div class="message" style="box-shadow: 0 0 0 0">No messages</div></div>';
    }
    echo '<input type="hidden" value="'.$id.'" id="convid" />
          <input type="hidden" value="'.$userHey.'" id="usrid" />
    </div>';
    /*echo '<span id="messageForm">
      <input type="text" id="messageText" rows="1" maxlength="1000" onkeyup="if (event.keyCode == 13) {sendMessage('. $id. ', '. $user .', ' . $_SESSION['sessionID'].')}"></textarea>
      <span onclick="sendMessage('.$id. ', '. $user .', ' . $_SESSION['sessionID'].')" id="sendImage"><img src="img/send.png" /></span>
    </span>';*/

      // Prepare statement
      $stmt = mysqli_prepare($conn, 'SELECT latestsender, unread FROM conversations WHERE rndid = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "s", $id);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      $result = $stmt->get_result();
      $convo = $result->fetch_assoc();

    if (($convo['latestsender'] != $_SESSION['rndid']) && ($convo['unread'] == 1)) { // If you are recieving the last message and haven't read it yet
      // Prepare statement
      $stmt = mysqli_prepare($conn, 'UPDATE conversations SET unread = 0 WHERE rndid = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "s", $id);
      // Execute the statement
      mysqli_stmt_execute($stmt); // TODO: You can make this into just one sql query, I believe in you

      // Prepare statement
      $stmt = mysqli_prepare($conn, 'UPDATE messages SET unread = 0 WHERE convoid = ? AND userto = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "ss", $id, $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt); // TODO: Please fix when you're in a better place
    }
  } else { // If you're just trying to get new messages
    header("Content-type:application/json");

    $id = $_GET['conversationID'];
    //$user = $_GET['userID'];

    // Prepare statement
    $stmt = mysqli_prepare($conn, 'SELECT * FROM messages WHERE convoid = ? AND userto = ? AND unread = 1 AND ((userfrom = ? AND deletedbyuserfrom = 0) OR (userto = ? AND deletedbyuserto = 0)) ORDER BY timestamp ASC');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "ssss", $id, $_SESSION['rndid'], $_SESSION['rndid'], $_SESSION['rndid']);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    $result = $stmt->get_result();

    if (mysqli_num_rows($result) > 0) {
      $bigArray = array();

      while($row = $result->fetch_assoc()) {
        $bigArray[] = $row;
      }

      // Prepare statement
      $stmt = mysqli_prepare($conn, 'UPDATE messages SET unread = 0 WHERE convoid = ? AND userto = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "ss", $id, $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt);// TODO: Please fix when you're in a better place

      echo json_encode($bigArray);
    } else { // If there aren't any new
      $data = ['Message' => 'No new messages'];
      echo json_encode($data);
    }
  }

  } else { // If they're fetching more from user being at top
    header("Content-type:text/html");

    $offset = $_GET['offset'];

    $id = $_GET['conversationID'];
    //$user = $_GET['userID'];

    // Get total amount of messages
    // Prepare statement
    $stmt = mysqli_prepare($conn, 'SELECT ROW_COUNT() FROM messages WHERE convoid = ? AND ((userfrom = ? AND deletedbyuserfrom = 0) OR (userto = ? AND deletedbyuserto = 0)) LIMIT 18446744073709551610 OFFSET ?');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "sssi", $id, $_SESSION['rndid'], $_SESSION['rndid'], $offset);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    $messagesResults = $stmt->get_result();
    $totalMessages = mysqli_num_rows($messagesResults);

    if ($totalMessages > 0) {

      // Prepare statement
      $stmt = mysqli_prepare($conn, 'SELECT * FROM (SELECT * FROM messages WHERE convoid = ? AND ((userfrom = ? AND deletedbyuserfrom = 0) OR (userto = ? AND deletedbyuserto = 0)) ORDER BY timestamp DESC LIMIT 50 OFFSET ?) AS `table` ORDER BY timestamp ASC');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "sssi", $id, $_SESSION['rndid'], $_SESSION['rndid'], $offset);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      $messagesResults = $stmt->get_result();

      if (mysqli_num_rows($messagesResults) >= 50) {
        echo '<div class="centerme"><a id="loadMore">loading...</a></div>';
      }

      while($messages = $messagesResults->fetch_assoc()) {
        $class = '';
        if ($messages['userto'] == $_SESSION['rndid']) { // Other person
          $class = ' reciever';
        } else { // Sender
          $class = ' sender';
        }

        echo '<div class="message' . $class . '" id="'.$messages['rndid'].'" onclick="showDetails(\''.$messages['rndid'].'\')">' . $messages['contents'] . '<img id="x'.$messages['rndid'].'" class="messagesX" src="' . $siteRoot . '/img/xsmall.png" /></div>';
      }
    } else  {
      echo 'none';
    }
  }
}


if (isset($_POST['messageIDToRead'])) { // If the user is reading a message
  $messageID = $_POST['messageIDToRead'];

  $stmt = mysqli_prepare($conn, 'UPDATE notifications SET unread = \'read\' WHERE rndid = ?');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "s", $messageID);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    echo json_encode('Message '.$messageID.' set as read');
}


if (isset($_POST['userto']) && isset($_POST['convoid']) && isset($_POST['contents']) && isset($_POST['unconfirmedid'])) { // If the user is sending a message
  if (($_POST['contents'] != '') && ($_POST['userto'] != '') && ($_POST['convoid'] != '')) {

    $userid = $_SESSION['rndid'];
    $unconfirmedid = $_POST['unconfirmedid'];
    $contents = $_POST['contents'];
    $peekcontents = substr($contents, 0, 75);
    $userto = $_POST['userto'];
    $convoid = $_POST['convoid'];
    $time = time();
    $rndid = uniqid('msg_');


    if($stmt = mysqli_prepare($conn, 'INSERT INTO messages (rndid, convoid, userto, userfrom, contents, timestamp) VALUES (?, ?, ?, ?, ?, ?)')) {
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "sssssi", $rndid, $convoid, $userto, $userid, $contents, $time);
      // Execute the statement
      mysqli_stmt_execute($stmt);

        if($stmt = mysqli_prepare($conn, 'UPDATE conversations SET timestamp = ?, latestmessage = ?, latestsender = ?, unread = 1 WHERE rndid = ?')) {
          // Set the parameter
          mysqli_stmt_bind_param($stmt, "isss", $time, $peekcontents, $userid, $convoid);
          // Execute the statement
          mysqli_stmt_execute($stmt);

          $data = ['id' => $rndid, 'unconfirmedid' => $unconfirmedid];
          echo json_encode($data);
        }
    } else {
      $data = ['message' => 'Not sent, try again later'];
    }
  }
  //echo json_encode($data);
}


if (isset($_POST['delid'])) {
  if($stmt = mysqli_prepare($conn, 'UPDATE bounties SET title = "[deleted]", success = -1, author = "[deleted]", authorid = 0 WHERE rndid = ? AND authorid = ?')) {
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "ss", $_POST['delid'], $_SESSION['rndid']);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    $data = ['deleted' => true];
  } else {
    $data = ['deleted' => false];
  }
  echo json_encode($data);
}


if (isset($_POST['idToDelete']) && isset($_POST['conversationID'])) {

  if($stmt = mysqli_prepare($conn, 'SELECT * FROM messages WHERE rndid = ? AND convoid = ?')) {
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "ss", $_POST['idToDelete'], $_POST['conversationID']);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()) {

      if ($row['userto'] == $_SESSION['rndid']) { // If the user is the reciever
        if($stmt = mysqli_prepare($conn, 'UPDATE messages SET deletedbyuserto = 1 WHERE rndid = ? AND convoid = ?')) {
          // Set the parameter
          mysqli_stmt_bind_param($stmt, "ss", $_POST['idToDelete'], $_POST['conversationID']);
          // Execute the statement
          mysqli_stmt_execute($stmt);

          echo json_encode('Message deleted');
        }
      } else if ($row['userfrom'] == $_SESSION['rndid']) { // If the user is the sender
        if($stmt = mysqli_prepare($conn, 'UPDATE messages SET deletedbyuserfrom = 1 WHERE rndid = ? AND convoid = ?')) {
          // Set the parameter
          mysqli_stmt_bind_param($stmt, "ss", $_POST['idToDelete'], $_POST['conversationID']);
          // Execute the statement
          mysqli_stmt_execute($stmt);

          echo json_encode('Message deleted');
        }
      }
    }

  } else {
    echo json_encode('Something went wrong');
  }

}


if (isset($_GET['newuser'])) { // If it's being called from the checkNewUser js function
  $userInput = $_GET['newuser'];

  if($stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?")) {
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "s", $userInput);
    // Execute the statement
    mysqli_stmt_execute($stmt);
    // Get the results
    $result = $stmt->get_result();

    if (mysqli_num_rows($result) > 0) {
      $data = ['exists' => 'This username is taken', 'usernameOkay' => false];
    } else {
      $data = ['exists' => '', 'usernameOkay' => true];
    }

    echo json_encode($data);
  }
}

if (isset($_GET['user'])) { // If it's being called from the checkUser js function
  $userInput = $_GET['user'];

  if($stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?")) {
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "s", $userInput);
    // Execute the statement
    mysqli_stmt_execute($stmt);
    // Get the results
    $result = $stmt->get_result();

    if (mysqli_num_rows($result) > 0) {
      $exists = '';
    } else {
      $exists = 'This username doesn\'t exist';
    }

    $result = $exists;
    echo json_encode($result);
  }
}

if (isset($_GET['email'])) { // If it's being run from the checkEmail js function
  $userInput = $_GET['email'];

  if($stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?")) {
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "s", $userInput);
    // Execute the statement
    mysqli_stmt_execute($stmt);
    // Get the results
    $result = $stmt->get_result();

    if (mysqli_num_rows($result) > 0) {
      $data = ['exists' => 'This email is already in use', 'emailOkay' => false];
    } else {
      $data = ['exists' => '', 'emailOkay' => true];
    }

    echo json_encode($data);
  }
}

if (isset($_POST['id']) && isset($_POST['auth'])) { // If user is requesting to review their paper
  $messageType = 'r';
  // Checks to see if you've already sent this user a message to request
  // Prepare statement
  $stmt = mysqli_prepare($conn, 'SELECT ROW_COUNT() FROM notifications
  WHERE senderid = ? AND paperid = ? AND messagetype = ? AND showyes = 1');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "sss", $_SESSION['rndid'], $_POST['id'], $messageType);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $rowcount = mysqli_num_rows($result);

  if ($rowcount > 0) {
    // send user that they've already sent the user a message
      //echo "<script type='text/javascript'>location.href = 'fullbounti.php?id=" . $_GET['id']."'</script>";
      //$response = 'You\'ve already requested to review this paper';
      $data = ['message' => 'You\'ve already requested to review this paper'];
      echo json_encode($data);
  } else {
    $rndid = uniqid('notif_');
      // Prepare statement
      $messageType = 'r';
      $time = time();

      $stmt = mysqli_prepare($conn, 'INSERT INTO notifications (rndid, senderid, recipient, timestamp, paperid, messagetype)
          VALUES (?, ?, ?, ?, ?, ?)');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "sssiss", $rndid, $_SESSION['rndid'], $_POST['auth'], $time, $_POST['id'], $messageType);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      $stmt = mysqli_prepare($conn, 'SELECT email FROM users WHERE rndid = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "s", $_POST['auth']);
      // Execute the statement
      mysqli_stmt_execute($stmt);
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();


      sendEmail($row['email'], 'req');
      $data = ['message' => 'Request sent'];
      echo json_encode($data);

  };
}


if (isset($_GET['getmessagesforthisguy'])) {
  $id = $_SESSION['rndid'];
  header("Content-type:text/html");

  if($stmt = mysqli_prepare($conn, 'SELECT C.*, U.rndid AS userid, U.img, U.username, U.grade FROM conversations as C
    INNER JOIN users AS U ON U.rndid != ? AND (C.person1 = U.rndid OR C.person2 = U.rndid)
    WHERE person1 = ? OR person2 = ? ORDER BY timestamp DESC LIMIT 5')) {
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "sss", $_SESSION['rndid'], $id, $id);
    // Execute the statement
    mysqli_stmt_execute($stmt);
    // Get the results
    $result = $stmt->get_result();

      while ($row = $result->fetch_assoc()) {
        $unread = '';

        if (($row['unread'] == 1) && ($row['latestsender'] != $_SESSION['rndid'])) {
          $unread = ' unread';
        }

        // Prepare statement
        $stmt = mysqli_prepare($conn, 'SELECT contents, userfrom FROM messages WHERE convoid = ? AND ((userfrom = ? AND deletedbyuserfrom = 0) OR (userto =? AND deletedbyuserto = 0)) ORDER BY timestamp DESC LIMIT 1');
        // Set the parameter
        mysqli_stmt_bind_param($stmt, "sss", $row['rndid'], $_SESSION['rndid'], $_SESSION['rndid']);
        // Execute the statement
        mysqli_stmt_execute($stmt);

        $messageResult = $stmt->get_result();
        $latestMessage = $messageResult->fetch_assoc();

        echo '<a href="' . $siteRoot . '/messages/'.$row['rndid'].'">
        <div class="messagesDropDiv'.$unread.'">
          <div class="messagesDropTop">
            <img src="' . $siteRoot . '/'.$row['img'].'" />
            <div>
              <p class="name">'.$row['username'].'</p>
              <p class="education">'.$row['grade'].'</p>
            </div>
          </div>
          <div class="messagePeek" style="color: rgba(0,0,0, 0.8); font-family: \'Roboto\', sans-serif">';
          if (mysqli_num_rows($messageResult) > 0) {
            if ($latestMessage['userfrom'] == $_SESSION['rndid']) {
              echo 'You: ';
            } else if(!(isset($row['latestsender']))) { // If no one sent a message
              echo 'Send a message to get started';
            } else {
              echo $row['username'] . ': ';
            }
            echo $latestMessage['contents']; // If this gets changed, must change .scroll function in javascript!
          } else {
            echo 'Send a message to get started';
          }
    echo '</div>
        </div>
      </a>';

    }
  }
  echo '<div class="centerme"><a href="' . $siteRoot . '/messages">See all</a></div>';
}

if (isset($_POST['giveUp'])) {
  $rndid = uniqid('notif_');
  $messageType = 'da';
  $time = time();

  // Prepare statement
  $stmt = mysqli_prepare($conn, 'SELECT B.authorid, R.score, R.reviews AS numofreviews, R.rndid AS reviewerid
    FROM bounties AS B
    INNER JOIN users AS R ON B.reviewer = R.rndid
    WHERE B.rndid = ?');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "s", $_POST['giveUp']);
  // Execute the statement
  mysqli_stmt_execute($stmt);
  $result = $stmt->get_result();
  $author = $result->fetch_assoc();

  $reviewerID = $author['reviewerid'];
  $numOfReviews = $author['numofreviews'];
  $newNumOfReviews = $numOfReviews + 1;
  $score = $author['score'];

  if ($score <= 0) { // If they haven't reviewed anything yet
    $newScore = 0;
  } else {
    $newScore = $score / $newNumOfReviews;
    $newScore = round($newScore, 2);
  }




  // Prepare statement
  $stmt = mysqli_prepare($conn, 'INSERT INTO notifications (rndid, recipient, timestamp, paperid, messagetype, senderid)
          VALUES (?, ?, ?, ?, ?, ?)');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "ssisss", $rndid, $author['authorid'], $time, $_POST['giveUp'], $messageType, $_SESSION['rndid']);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  $stmt = mysqli_prepare($conn,
  'UPDATE users
  SET score = ?, reviews = ?
  WHERE rndid = ?');

  // Set the parameter
  mysqli_stmt_bind_param($stmt, "sis", $newScore, $newNumOfReviews, $reviewerID);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  $stmt = mysqli_prepare($conn,
  'UPDATE bounties SET reviewer = NULL WHERE rndid = ? AND reviewer = ?');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "ss", $_POST['giveUp'], $_SESSION['rndid']);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  $stmt = mysqli_prepare($conn,
  'UPDATE notifications SET showyes = 0 WHERE paperid = ? AND recipient = ? AND messagetype = "a"');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "ss", $_POST['giveUp'], $_SESSION['rndid']);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  echo json_encode('Grade lowered and Bounti declined');
}


if (isset($_POST['s']) || isset($_POST['x'])) {
  if(isset($_POST['s']) && ($_POST['s'] == 'a')) { // If message declined is an acceptance message
    $rndid = uniqid('notif_');
    $messageType = 'da';
    $time = time();
    // Prepare statement
    $stmt = mysqli_prepare($conn, 'INSERT INTO notifications (rndid, recipient, timestamp, paperid, messagetype, senderid)
            VALUES (?, ?, ?, ?, ?, ?)');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "ssisss", $rndid, $_POST['id'], $time, $_POST['papid'], $messageType, $_SESSION['rndid']);
    // Execute the statement
    mysqli_stmt_execute($stmt);

      // Prepare statement
      $stmt = mysqli_prepare($conn, 'UPDATE bounties SET reviewer = NULL WHERE rndid = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "s", $_POST['papid']);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      // Prepare statement
      $stmt = mysqli_prepare($conn, 'UPDATE notifications SET showyes = 0 WHERE rndid = ? AND recipient = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "ss", $_POST['m'], $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      echo json_encode('User declined');

  } else if(isset($_POST['s']) && ($_POST['s'] == 'r')) { // If the message is requesting to review a paper
    $rndid = uniqid('notif_');
    $messageType = 'dr';
    $time = time();

    // Prepare statement
    $stmt = mysqli_prepare($conn, 'INSERT INTO notifications (rndid, recipient, timestamp, paperid, messagetype, senderid)
      VALUES (?, ?, ?, ?, ?, ?)');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "ssisss", $rndid, $_POST['id'], $time, $_POST['papid'], $messageType, $_SESSION['rndid']);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    echo json_encode('Dsikel');

    // Prepare statement
      $stmt = mysqli_prepare($conn, 'UPDATE notifications SET showyes = 0 WHERE rndid = ?  AND recipient = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "ss", $_POST['m'], $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt);


      //echo '<script type="text/javascript">location.href = "notifications.php";</script>';
  } else if(isset($_POST['s']) && ($_POST['s'] == 'd')) { // If the message is the reviewer declining to review
    // Prepare statement
      $stmt = mysqli_prepare($conn, 'UPDATE notifications SET showyes = 0 WHERE rndid = ? AND recipient = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "ss", $_POST['m'], $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt); // Delete message

      echo json_encode('Response');

  } else if (isset($_POST['x'])) {
    // Prepare statement
  $stmt = mysqli_prepare($conn, 'UPDATE notifications SET showyes = 0 WHERE rndid = ? AND recipient = ?');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "ss", $_POST['x'], $_SESSION['rndid']);
  // Execute the statement
  mysqli_stmt_execute($stmt);

    echo json_encode('Response');
    //echo '<script type="text/javascript">location.href = "notifications.php?e=d";</script>';
  }

}

if (isset($_POST['reportBounti'])) {
  include_once '../../../unimportant.php';
  require_once "../../../usr/share/php/Mail.php";
  include_once '../../../usr/share/php/Mail/mime.php';


  $html_message = "Username: " . $_SESSION['sessionUser'] .
  "\nID: " . $_SESSION['rndid'] .
  "\nBounti in question: " . $_POST['reportBounti'];

  $headers["From"] = 'support@bounti.io';
  $headers["To"] = 'support@bounti.io';
  $to = 'support@bounti.io';
  $headers["Subject"] = 'Reported Bounti';
  $headers["Content-Type"] = 'text/html; charset=UTF-8';
  $headers["Content-Transfer-Encoding"]= "8bit";


  $mime = new Mail_mime;
  $mime->setHTMLBody($html_message);
  $mimeparams=array();


  $mimeparams['text_encoding']="8bit";
  $mimeparams['text_charset']="UTF-8";
  $mimeparams['html_charset']="UTF-8";
  $mimeparams['head_charset']="UTF-8";


  $body = $mime->get($mimeparams);
  $headers = $mime->headers($headers);
  $page_content = "Mail now.";

  $host = "email-smtp.us-east-1.amazonaws.com";
  $port = "587";

  // SMTP server name, port, user/passwd
  $smtpinfo["host"] = $host;
  $smtpinfo["port"] = $port;
  $smtpinfo["auth"] = true;
  $smtpinfo["username"] = $emailUser;
  $smtpinfo["password"] = $emailPass;


  // Create the mail object using the Mail::factory method
  $mail = Mail::factory('smtp', $smtpinfo);

  $mail->send($to, $headers, $body);

  if (PEAR::isError($mail)) {
    $data = ['message' => 'Something went wrong', 'sent' => false];
  } else {
    $data = ['message' => 'Bounti Reported', 'sent' => true];
  }


  echo json_encode($data);
}


if (isset($_POST['id']) && isset($_POST['paper']) && isset($_POST['m']) && isset($_POST['accept'])) { // If the user is accepting someone to review their paper
  $rndid = uniqid('notif_');
  $messageType = 'a';
  $time = time();

  // Prepare statement
  $stmt = mysqli_prepare($conn, 'INSERT INTO notifications (rndid, senderid, recipient, timestamp, paperid, messagetype)
          VALUES (?, ?, ?, ?, ?, ?)');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "sssiss", $rndid, $_SESSION['rndid'], $_POST['id'], $time, $_POST['paper'], $messageType);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  // Prepare statement
  $stmt = mysqli_prepare($conn, 'UPDATE bounties SET reviewer = ? WHERE rndid = ?');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "ss", $_POST['id'], $_POST['paper']);
  // Execute the statement
  mysqli_stmt_execute($stmt); // Set reviewer in the paper entry

  $messageType = 'ar';
  // Prepare statement
  $stmt = mysqli_prepare($conn, 'UPDATE notifications SET showyes = 0, messagetype = ? WHERE rndid = ?');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "ss", $messageType, $_POST['m']);
  // Execute the statement
  mysqli_stmt_execute($stmt); // Delete this message

  // Prepare statement
  $stmt = mysqli_prepare($conn, 'SELECT * FROM conversations WHERE (person1 = ? AND person2 = ?) OR (person2 = ? AND person1 = ?)');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "ssss", $_POST['id'], $_SESSION['rndid'], $_POST['id'], $_SESSION['rndid']);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  $result = $stmt->get_result();

      if (mysqli_num_rows($result) > 0) {
        // Don't create conversation because there is already one
      } else {
        $rndid = uniqid('convo_');
        $time = time();

        // Prepare statement
        $stmt = mysqli_prepare($conn, 'INSERT INTO conversations (person1, person2, timestamp, rndid)
        VALUES (?, ?, ?, ?)');

        // Set the parameter
        mysqli_stmt_bind_param($stmt, "ssis", $_SESSION['rndid'], $_POST['id'], $time, $rndid);
        // Execute the statement
        mysqli_stmt_execute($stmt); // Create conversation between the two people
      }
      echo json_encode("User accepted");
}



if (isset($_POST['showhunters'])) {
    header("Content-type:text/html");
  if ($stmt = mysqli_prepare($conn, 'SELECT * FROM notifications WHERE paperid = ? AND messagetype = \'r\' AND showyes = 1')) {
    // Set the parameter
    mysqli_stmt_bind_param($stmt, 's', $_POST['id']);
    // Execute the statement
    mysqli_stmt_execute($stmt);
    // Get the results
    $result = $stmt->get_result();
    $rowcount = mysqli_num_rows($result);

    echo '<h1>Bounti Hunters Interested</h1>';

    if ($rowcount > 0) {
      while($message = $result->fetch_assoc()) {
        // Prepare the statement
        if($stmt = mysqli_prepare($conn, 'SELECT * FROM users WHERE rndid = ?')) {
          // Set the parameter
          mysqli_stmt_bind_param($stmt, "s", $message['senderid']);
          // Execute the statement
          mysqli_stmt_execute($stmt);
          // Get the results
          $result = $stmt->get_result();
          // Fetch data
          $user = $result->fetch_assoc();
          $score = $user['score'];

          if($score >= 90) { // if their score is in the A region
            $color = '#43A047';
            if ($score >= 98)
              $gradeLetter = 'A+';
            else if ($score >= 94)
              $gradeLetter = 'A';
            else
              $gradeLetter = 'A-';
          } else if($score >= 80) { // If it's in the B region
            $color = '#7CB342';
            if ($score >= 88)
              $gradeLetter = 'B+';
            else if ($score >= 84)
              $gradeLetter = 'B';
            else
              $gradeLetter = 'B-';
          } else if($score >= 70) { // If they have a  C
            $color = '#C0CA33';
            if ($score >= 78)
              $gradeLetter = 'C+';
            else if ($score >= 74)
              $gradeLetter = 'C';
            else
              $gradeLetter = 'C-';
          } else if($score >= 60) { // If they have a D
            $color = '#FB8C00';
            if ($score >= 68)
              $gradeLetter = 'D+';
            else if ($score >= 64)
              $gradeLetter = 'D';
            else
              $gradeLetter = 'D-';
          } else if ($score >= 0) { // If they have an F
            $color = '#E53935';
            $gradeLetter = 'F';
          } else {
            $color = '#607D8B';
            $gradeLetter = 'NA';
          }

          echo '<a href="' . $siteRoot . '/account/'.$user['username'].'">
                  <div class="hunter">
                    <img src="' . $siteRoot . '/'.$user['img'].'" />
                    <div>
                      <p>'.$user['username'].'</p>
                      <p>'.$user['grade'].'</p>
                    </div>
                    <div class="grade" style="color: '.$color.'">'.$gradeLetter.'</div>
                  </div>
                </a>';
        }
      }
    } else {
      echo '<h3>None</h3>';
    }
  }
}


if (isset($_POST['reviewBox']) && isset($_POST['userid']) && isset($_POST['grade'])) {
  $totalScore = 0;

    $grade = $_POST['grade'];
    $modifier = $_POST['modifier'];
    $contents = $_POST['reviewBox'];
    $id = $_POST['userid']; // Id of account being reviewed

    if($modifier == '+') { // If they chose +
      if ($grade == 95) { // If they chose A+
        $score = 100;
        $gradeLetter = "A+";
      } else if ($grade == 85) { // If they chose B+
        $score = 89;
        $gradeLetter = "B+";
      } else if ($grade == 75) { // If they chose C+
        $score = 79;
        $gradeLetter = "C+";
      } else if ($grade == 65) { // If they chose D+
        $score = 69;
        $gradeLetter = "D+";
      } else { // If they chose F+
        $score = $grade; // Set equal to 59
        $gradeLetter = "F";
      }
    } else if($modifier == '-') { // If they chose -
      if ($grade == 95) { // If they chose A-
        $score = 90;
        $gradeLetter = "A-";
      } else if ($grade == 85) { // If they chose B-
        $score = 80;
        $gradeLetter = "B-";
      } else if ($grade == 75) { // If they chose C-
        $score = 70;
        $gradeLetter = "C-";
      } else if ($grade == 65) { // If they chose D-
        $score = 60;
        $gradeLetter = "D-";
      } else { // If they chose F-
        $score = $grade; // Set equal to 59
        $gradeLetter = "F";
      }
    } else { // If they chose nothing
        $score = $grade; // Score is equal to whatever it was
        if ($grade == 95)
          $gradeLetter = "A";
        else if ($grade == 85)
          $gradeLetter = "B";
        else if ($grade == 75)
          $gradeLetter = "C";
        else if ($grade == 65)
          $gradeLetter = "D";
        else
          $gradeLetter = "F";
    }
    $rndid = uniqid('review_');
    $notifRndid = uniqid('notif_');

    $stmt = mysqli_prepare($conn, 'SELECT ROW_COUNT() FROM reviews WHERE recipient = ? AND senderid = ?');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "ss", $id, $_SESSION['rndid']);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    $result = $stmt->get_result();
    $numRows = mysqli_num_rows($result);

    $time = time();

    if($numRows == 0) { // If the user is reviewing for the first time
      $messageType = "rs";

      $stmt = mysqli_prepare($conn, 'INSERT INTO reviews (rndid, recipient, senderid, rating, ratingletter, timestamp, contents)
              VALUES (?, ?, ?, ?, ?, ?, ?)');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "sssisis", $rndid, $id, $_SESSION['rndid'], $score, $gradeLetter, $time, $contents);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      $stmt = mysqli_prepare($conn, 'INSERT INTO notifications (rndid, senderid, recipient, timestamp, messagetype)
              VALUES (?, ?, ?, ?, ?)');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "sssis", $notifRndid, $_SESSION['rndid'], $id, $time, $messageType);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      $stmt = mysqli_prepare($conn, 'UPDATE users SET reviews = reviews + 1 WHERE rndid = ?'); // Say that they have another review to divide by
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "s", $id);
      // Execute the statement
      mysqli_stmt_execute($stmt);


    } else { // If the user is updating a review
      $messageType = "ru";

      $stmt = mysqli_prepare($conn, 'UPDATE reviews SET newest = 0 WHERE senderid = ? AND recipient = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "ss", $_SESSION['rndid'], $id);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      $stmt = mysqli_prepare($conn, 'INSERT INTO reviews (rndid, recipient, senderid, rating, ratingletter, timestamp, contents)
              VALUES (?, ?, ?, ?, ?, ?, ?)');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "sssisis", $rndid, $id, $_SESSION['rndid'], $score, $gradeLetter, $time, $contents);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      $stmt = mysqli_prepare($conn, 'INSERT INTO notifications (rndid, senderid, recipient, timestamp, messagetype)
              VALUES (?, ?, ?, ?, ?)');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "sssis", $notifRndid, $_SESSION['rndid'], $id, $time, $messageType);
      // Execute the statement
      mysqli_stmt_execute($stmt);

    }

    $stmt = mysqli_prepare($conn, 'SELECT reviews.*, U.reviews AS numofreviews, U.email, U.username
      FROM reviews
      INNER JOIN users AS U ON recipient = U.rndid
      WHERE recipient = ? AND newest = 1');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "s", $id);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
      $totalScore += $row['rating'];
      $numOfReviews = $row['numofreviews'];
      $email = $row['email'];
      $username = $row['username'];
    }

    $newScore = $totalScore / $numOfReviews;

    $newScore = round($newScore, 2); // Rounds to 2 decimal places
    $message = $totalScore . ' ' . $numOfReviews . ' ' . $newScore;
    /*
    if ($newScore <= 100 && $newScore >= 98) {
      $scoreLetter = 'A+';
    } else if ($newScore < 98 && $newScore >= 94) {
      $scoreLetter = 'A';
    } else if ($newScore < 94 && $newScore >= 90) {
      $scoreLetter = 'A-';
    } else if ($newScore < 90 && $newScore >= 87) {
      $scoreLetter = 'B+';
    } else if ($newScore < 87 && $newScore >= 83) {
      $scoreLetter = 'B';
    } else if ($newScore < 83 && $newScore >= 80) {
      $scoreLetter = 'B-';
    } else if ($newScore < 80 && $newScore >= 77) {
      $scoreLetter = 'C+';
    } else if ($newScore < 77 && $newScore >= 73) {
      $scoreLetter = 'C';
    } else if ($newScore < 73 && $newScore >= 70) {
      $scoreLetter = 'C-';
    } else if ($newScore < 67 && $newScore >= 67) {
      $scoreLetter = 'D+';
    } else if ($newScore < 67 && $newScore >= 63) {
      $scoreLetter = 'D';
    } else if ($newScore < 63 && $newScore >= 60) {
      $scoreLetter = 'D-';
    } else if ($newScore < 60 && $newScore >= 0) {
      $scoreLetter = 'F';
    }
    */

    $stmt = mysqli_prepare($conn, 'UPDATE users SET score = ? WHERE rndid = ?');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "ss", $newScore, $id);
    // Execute the statement
    mysqli_stmt_execute($stmt);


    sendEmail($email, 'rvw', $username);

    echo json_encode('Review submitted');
}

/* TODO: Make submitting better!!
if (isset($_POST['comments']) && isset($_POST['id'])) { // If the user is turning in a bounti
  require_once '../vendor/autoload.php';

  $rndid = uniqid('finished_');

  // Prepare statement
  $stmt = mysqli_prepare($conn, 'SELECT U.rndid AS authorid, B.rndid AS bountiid, B.total, B.fee, U.custacct, U.email, U.rndid, U.fullname
    FROM bounties AS B
    INNER JOIN users AS U ON U.rndid = B.authorid
    WHERE B.rndid = ? AND reviewer = ?');

  // Set the parameter
  mysqli_stmt_bind_param($stmt, "ss", $_POST["id"], $_SESSION['rndid']);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  $result = $stmt->get_result();

  $row = $result->fetch_assoc();

  $time = time();
  $messageType = 'f';

  // Prepare statement
  $stmt = mysqli_prepare($conn, 'INSERT INTO finished (rndid, authorid, reviewerid, paperid, comments, timestamp)
          VALUES (?, ?, ?, ?, ?, ?)');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "sssssi", $rndid, $row['authorid'], $_SESSION['rndid'], $row['bountiid'], $_POST['comments'], $time);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  $uploaddir ='/var/www/documents/' . $row['bountiid'] . '/revised/'; // Directory where files are saved
  $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

  //$uploaddir = '/revised/' . $rndid . '/'; // Directory where files are saved
  //$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

  if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {

    exec('doc2pdf "' . $uploadfile .'" 2>&1', $output);
    print_r($output);

    $info = new SplFileInfo($uploadfile);
    $ext = $info->getExtension();

    $rawfilename = basename($uploadfile, ("." . $ext));

    $paper_file = $rawfilename . ".pdf";
    $save_to = $uploaddir . $rndid . '.jpg';

    $img = new imagick();
    $img->setResolution(150,150);
    $img->readImage($uploaddir . $paper_file . '[0]');
    //set new format
    $img->setImageFormat('jpg');

    //save image file
    $img->writeImage($save_to);

    rename($uploadfile, ($uploaddir . $rndid . '.docx'));
    unlink($uploaddir . $paper_file);


    // Create the charge
    \Stripe\Stripe::setApiKey($secretKey);

    $charge = \Stripe\Charge::create(array(
      'customer' => $row['custacct'],
      'amount' => $row['total'],
      'application_fee' => $row['fee'],
      'currency' => 'usd',
      'destination' => $_SESSION['stripeAcct'],
      'description' => $row['email'] . ' (' . $row['rndid'] . ') to ' . $_SESSION['sessionEmail'] . ' (' . $_SESSION['rndid'] .') at ' . time(),
      'metadata' => array('To' => $_SESSION['sessionName'], 'From' => $row['fullname'])
    ));

    echo 'ID: ' . $charge['id'] . "\n" . 'Amount: ' .$charge['amount']. "\n" . 'Fee: ' . $charge['application_fee'] . "\n" .'Customer: ' . $charge['customer'];
    print_r($charge);



$rndid = uniqid('notif_');

// Prepare statement
$stmt = mysqli_prepare($conn, 'INSERT INTO notifications (rndid, senderid, recipient, timestamp, paperid, messagetype)
VALUES (?, ?, ?, ?, ?, ?)');
// Set the parameter
mysqli_stmt_bind_param($stmt, "sssiss", $rndid, $_SESSION['rndid'], $row['authorid'], $time, $row['bountiid'], $messageType);
// Execute the statement
mysqli_stmt_execute($stmt);

// Prepare statement
$stmt = mysqli_prepare($conn, 'UPDATE bounties SET success = 1 WHERE rndid = ?');
// Set the parameter
mysqli_stmt_bind_param($stmt, "s", $row['bountiid']);
// Execute the statement
mysqli_stmt_execute($stmt);

$messageType = 'a';

// Prepare statement
$stmt = mysqli_prepare($conn, 'UPDATE notifications
  SET showyes = 0
  WHERE paperid = ? AND senderid = ? AND recipient = ? AND messagetype = ?');
// Set the parameter
mysqli_stmt_bind_param($stmt, "ssss", $row['rndid'], $row['authorid'], $_SESSION['rndid'], $messageType);
// Execute the statement
mysqli_stmt_execute($stmt);

}

}*/


// If the user is updating settings
if (isset($_POST['settingsupdate']) && (isset($_POST['first']) || isset($_POST['last']) || isset($_POST['email']) ||
isset($_POST['newpass']) || isset($_POST['newpass2']) || isset($_POST['currentpass']) ||
isset($_POST['month']) || isset($_POST['day']) || isset($_POST['year']) || !empty($_FILES['userfile']['name']))) {

  \Stripe\Stripe::setApiKey($secretKey);

  $stmt = mysqli_prepare($conn, 'SELECT * FROM users WHERE rndid = ?');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "s", $_SESSION['rndid']);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  $result = $stmt->get_result();
  // Fetch data
  $row = $result->fetch_assoc();

  $first = $row['firstname'];
  $last = $row['lastname'];
  $email = $row['email'];
  $month = substr($row['dob'], 0, 2);
  $day = substr($row['dob'], 3, 2);
  $year = substr($row['dob'], 6, 4);
  $currentHash = $row['password'];

  $account = \Stripe\Account::retrieve($row['stripeacct']);

  if (isset($_POST['first']) || isset($_POST['last'])) { // If they're changing their name

    if (!empty($_POST['first']) && empty($_POST['last'])) { // If they're just changing firstname
      $account->legal_entity->first_name = $_POST['first'];

      $fullname = $_POST['first'] . ' ' . $last;

      $stmt = mysqli_prepare($conn, 'UPDATE users SET firstname = ?, fullname = ? WHERE rndid = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "sss", $_POST['first'], $fullname, $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      $_SESSION['sessionFirst'] = $_POST['first'];
      $_SESSION['sessionLast'] = $last;

      $message = 'First name updated';
    } else if (empty($_POST['first']) && !empty($_POST['last'])) { // If they're just changing lastname
      $account->legal_entity->last_name = $_POST['last'];

      $fullname = $first . ' ' . $_POST['last'];

      $stmt = mysqli_prepare($conn, 'UPDATE users SET lastname = ?, fullname = ? WHERE rndid = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "sss", $_POST['last'], $fullname, $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      $_SESSION['sessionFirst'] = $first;
      $_SESSION['sessionLast'] = $_POST['last'];

      $message = 'Last name updated';
    } else if (!empty($_POST['first']) && !empty($_POST['last'])) { // If they're changing both
      $account->legal_entity->first_name = $_POST['first'];
      $account->legal_entity->last_name = $_POST['last'];

      $fullname = $_POST['first'] . ' ' . $_POST['last'];

      $stmt = mysqli_prepare($conn, 'UPDATE users SET firstname = ?, lastname = ?, fullname = ? WHERE rndid = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "ssss", $_POST['first'], $_POST['last'], $fullname, $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      $_SESSION['sessionFirst'] = $_POST['first'];
      $_SESSION['sessionLast'] = $_POST['last'];

      $message = 'Full name updated';
    }

    $_SESSION['sessionName'] = $fullname;


    $data = ['settingschanged' => true, 'message' => $message];
  }


  if (isset($_POST['email']) || isset($_POST['notif-checkbox'])) { // If they're changing their email
    if (isset($_POST['email']) && (strlen($_POST['email']) > 0)) {
      $stmt = mysqli_prepare($conn, 'SELECT * FROM users WHERE email = ? AND rndid != ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "ss", $_POST['email'], $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      $result = $stmt->get_result();

      if (mysqli_num_rows($result) > 0) {
        $message = 'Email already in use';
      } else {
        //change customer and stripe account
        $customer = \Stripe\Customer::retrieve($row['custacct']);
        $account->email = $_POST['email']; // Update the stripe account
        $customer->email = $_POST['email']; // Update the stripe customer
        $customer->save();

        $stmt = mysqli_prepare($conn, 'UPDATE users SET email = ? WHERE rndid = ?');
        // Set the parameter
        mysqli_stmt_bind_param($stmt, "ss", $_POST['email'], $_SESSION['rndid']);
        // Execute the statement
        mysqli_stmt_execute($stmt);

        $_SESSION['sessionEmail'] = $_POST['email'];

        $message = "Email settings updated";
      }
    }

      if (isset($_POST['notif-checkbox'])) {
        if ($_POST['notif-checkbox'] == 'yes') {
          $stmt = mysqli_prepare($conn, 'UPDATE users SET emailnotif = 1 WHERE rndid = ?');
          // Set the parameter
          mysqli_stmt_bind_param($stmt, "s", $_SESSION['rndid']);
          // Execute the statement
          mysqli_stmt_execute($stmt);

          $message = 'Email settings updated';
        } else {
          $stmt = mysqli_prepare($conn, 'UPDATE users SET emailnotif = 0 WHERE rndid = ?');
          // Set the parameter
          mysqli_stmt_bind_param($stmt, "s", $_SESSION['rndid']);
          // Execute the statement
          mysqli_stmt_execute($stmt);

          $message = 'Email settings updated';
        }
      } else {
        $message = 'Dang what now';
      }

      $data = ['settingschanged' => true, 'message' => $message];
  }


  if (isset($_POST['month']) || isset($_POST['day']) || isset($_POST['year'])) {
    if ((isset($_POST['month']) && is_numeric($_POST['month'])) ||
    (isset($_POST['day']) && is_numeric($_POST['day'])) || (isset($_POST['year']) && is_numeric($_POST['year']))) {

    if (!empty($_POST['month'])) {

      $month = $_POST['month'];

      if (strlen($month) == 1) {
        $month = '0' . $month;
      }

      $account->legal_entity->dob->month = $month;

    }

    if (!empty($_POST['day'])) {
      $day = $_POST['day'];

      if (strlen($day) == 1) {
        $day = '0' . $day;
      }

      $account->legal_entity->dob->day = $day;
    }
    if (!empty($_POST['year'])) {
      $account->legal_entity->dob->year = $_POST['year'];
      $year = $_POST['year'];
    }

    $dob = $month . '/' . $day . '/' . $year;

    $stmt = mysqli_prepare($conn, 'UPDATE users SET dob = ? WHERE rndid = ?');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "ss", $dob, $_SESSION['rndid']);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    $data = ['settingschanged' => true, 'message' => 'Date of birth updated'];
  } else {
    $data = ['settingschanged' => true, 'message' => 'Must be a valid date'];
  }
}


  if (isset($_POST['newpass']) || isset($_POST['currentpass'])) {
    if (!empty($_POST['newpass']) && !empty($_POST['currentpass'])) {
      $currentPass = $_POST['currentpass'];
      $newPass = $_POST['newpass'];

      if (password_verify($currentPass, $currentHash)) { // If the password is correct
          $newHash = password_hash($newPass, PASSWORD_BCRYPT);

          $stmt = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE rndid = ?');
          // Set the parameter
          mysqli_stmt_bind_param($stmt, "ss", $newHash, $_SESSION['rndid']);
          // Execute the statement
          mysqli_stmt_execute($stmt);


          $message = 'Password updated';
        } else {
          $message = 'Incorrect password';
        }

    } else {
      $message = 'Something went wrong';
    }

    $data = ['settingschanged' => true, 'message' => $message];
  }

  if (!empty($_FILES['userfile']['name'])) {
    $alias = 'img/profpics/'; // Alias to directory
    $uploaddir = '/var/www/profilepictures/'; // Real directory where images are stored
    $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
    $userid = $_SESSION['rndid'];

    if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
      $info = new SplFileInfo($uploadfile);
      $ext = $info->getExtension();

      $aliasSaveTo = $alias . $userid . '.' . $ext;
      $save_to = $uploaddir . $userid . '.' . $ext;

      $img = new imagick();
      $img->readImage($uploadfile);
      $img->resizeImage(250,0,Imagick::FILTER_LANCZOS,1);

      //set new format
      //$img->setImageFormat('jpg');

      //save image file
      $img->writeImage($save_to);

      //rename($uploadfile, ($uploaddir . $userid. '.docx'));
      unlink($uploadfile);
      $img = $aliasSaveTo;

      // Prepare statement
      $stmt = mysqli_prepare($conn, 'UPDATE users SET img = ? WHERE rndid = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "ss", $img, $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt);


      $_SESSION['sessionIMG'] = $img;

      $message = 'Profile picture updated';



    }


    $data = ['settingschanged' => true, 'message' => $message];
  }


  $account->save();
  echo json_encode($data);
}

if (isset($_POST['convotodelete'])) {
  $userid = $_SESSION['rndid'];
  $user2id = $_POST['convotodelete'];

  if($stmt = mysqli_prepare($conn, 'SELECT rndid FROM conversations WHERE (person1 = ? AND person2 = ?) OR (person2 = ? AND person1 = ?)')) {
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "ssss", $userid, $user2id, $userid, $user2id);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if($stmt = mysqli_prepare($conn, 'UPDATE messages SET deletedbyuserto = 1 WHERE convoid = ? AND userto = ?')) {
    // Set the parameter
      mysqli_stmt_bind_param($stmt, "ss", $row['rndid'], $userid);
      // Execute the statement
      if (mysqli_stmt_execute($stmt)) {
        if($stmt = mysqli_prepare($conn, 'UPDATE messages SET deletedbyuserfrom = 1 WHERE convoid = ? AND userfrom = ?')) {
        // Set the parameter
          mysqli_stmt_bind_param($stmt, "ss", $row['rndid'], $userid);
              // Execute the statement
              if (mysqli_stmt_execute($stmt)) {
                echo json_encode($user2id);
              } else {
                echo json_encode('Nope1');
              }
            } else {
              echo json_encode('Nope2');
            }
          } else {
            echo json_encode('Nope3');
          }
        } else {
          echo json_encode('Nope4');
        }

      } else {
        echo json_encode('Nope2');
      }
    }


if (isset($_POST['sendverification']) && $_POST['sendverification'] == 'yes') {
  include_once '../../../unimportant.php';
  require_once "../../../usr/share/php/Mail.php";
  include_once '../../../usr/share/php/Mail/mime.php';

  $html_message = "<!DOCTYPE>
  <html>
    <head>
      <meta http-equiv='content-type' content='text/html; charset=UTF-8'>
    </head>
  <body style='font-family: sans-serif; color: black'>
  <div style='width: 600px'>
  <img src='' . $siteRoot . '/img/bountiheader.png' style='width: 400px; height: auto; margin-left: 100px; margin-right: 100px' />
  <h1 align='center' style='font-size: 32px; color: black; text-decoration: none'>Welcome to Bounti.io!</h1>
  <p style='font-size: 18px'>".$_SESSION['sessionUser'].", you're so close, all you need to do now is click the button below to verify your email!
  Then you'll be free to begin getting your bounties reviewed or get paid
  for reviewing others'.</p>
  <br><br>
  <table width='600px'>
  <tr>
  <td style='text-align: center'>
  <a style='border-radius: 4px; margin-top: 10px; padding: 7px; padding-left: 12px; padding-right: 12px;
            box-shadow: 0 8px 9px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            background-color: #E0E0E0; color: black; text-decoration: none;
            cursor: pointer; text-decoration: none; font-size: 18px' href='http://bounti.io/verify/".
            $_SESSION['sessionCode']."'>
    Verify email
  </a>
  </td>
  </tr>
  </table>
  <br><br>
  </div>
  </body>
  </html>";

  $headers["From"] = 'support@bounti.io';
  $headers["To"] = $_SESSION['sessionEmail'];
  $to = $_SESSION['sessionEmail'];
  $headers["Subject"] = "Verify your Bounti.io account";
  $headers["Content-Type"] = 'text/html; charset=UTF-8';
  $headers["Content-Transfer-Encoding"]= "8bit";


  $mime = new Mail_mime;
  $mime->addHTMLImage("' . $siteRoot . '/img/bountiheader.png");
  $mime->setHTMLBody($html_message);
  $mimeparams=array();


  // It refused to change to UTF-8 even if the header was set to this, after adding the following lines it worked.

  $mimeparams['text_encoding']="8bit";
  $mimeparams['text_charset']="UTF-8";
  $mimeparams['html_charset']="UTF-8";
  $mimeparams['head_charset']="UTF-8";


  $body = $mime->get($mimeparams);
  $headers = $mime->headers($headers);
  $page_content = "Mail now.";

  $host = "email-smtp.us-east-1.amazonaws.com";
  $port = "587";

  // SMTP server name, port, user/passwd
  $smtpinfo["host"] = $host;
  $smtpinfo["port"] = $port;
  $smtpinfo["auth"] = true;
  $smtpinfo["username"] = $emailUser;
  $smtpinfo["password"] = $emailPass;


  // Create the mail object using the Mail::factory method
  $mail = Mail::factory('smtp', $smtpinfo);

  $mail->send($to, $headers, $body);

  if (PEAR::isError($mail)) {
    $data = ['message' => 'Something went wrong when emailing you', 'sent' => false];
  } else {
    $data = ['message' => 'Request sent', 'sent' => true];
  }
    echo json_encode($data);
}


if (isset($_POST['recoverEmail']) && $_POST['recoverPass'] == 'send') {

  // Prepare statement
  $stmt = mysqli_prepare($conn, 'SELECT ROW_COUNT() FROM users WHERE email = ?');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "s", $_POST['recoverEmail']);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  $result = $stmt->get_result();

  if (mysqli_num_rows($result) > 0) {

    include_once '../../../unimportant.php';
    require_once "../../../usr/share/php/Mail.php";
    include_once '../../../usr/share/php/Mail/mime.php';

    $token = bin2hex(random_bytes(12));

    $html_message = "<!DOCTYPE>
    <html>
      <head>
        <meta http-equiv='content-type' content='text/html; charset=UTF-8'>
      </head>
    <body style='font-family: sans-serif; color: black'>
    <div style='width: 600px'>
    <img src='".$siteRoot."/img/bountiheader.png' style='width: 400px; height: auto; margin-left: 100px; margin-right: 100px' />
    <h1 align='center' style='font-size: 32px; color: black; text-decoration: none'>Reset password</h1>
    <p style='font-size: 18px; text-align: center'>Click the button below to reset your password</p>
    <br><br>
    <table width='600px'>
    <tr>
    <td style='text-align: center'>
    <a style='border-radius: 4px; margin-top: 10px; padding: 7px; padding-left: 12px; padding-right: 12px;
              box-shadow: 0 8px 9px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
              background-color: #E0E0E0; color: black; text-decoration: none;
              cursor: pointer; text-decoration: none; font-size: 18px' href='https://bounti.io/resetpassword/".$token."'>
      Reset password
    </a>
    </td>
    </tr>
    </table>
    <br><br>
    </div>
    </body>
    </html>";

    $headers["From"] = 'support@bounti.io';
    $headers["To"] = $_POST['recoverEmail'];
    $to = $_POST['recoverEmail'];
    $headers["Subject"] = "Bounti.io - Recover Password for " . $_POST['recoverEmail'];
    $headers["Content-Type"] = 'text/html; charset=UTF-8';
    $headers["Content-Transfer-Encoding"]= "8bit";


    $mime = new Mail_mime;
    $mime->addHTMLImage("$siteRoot/img/bountiheader.png");
    $mime->setHTMLBody($html_message);
    $mimeparams=array();


    // It refused to change to UTF-8 even if the header was set to this, after adding the following lines it worked.

    $mimeparams['text_encoding']="8bit";
    $mimeparams['text_charset']="UTF-8";
    $mimeparams['html_charset']="UTF-8";
    $mimeparams['head_charset']="UTF-8";


    $body = $mime->get($mimeparams);
    $headers = $mime->headers($headers);
    $page_content = "Mail now.";

    $host = "email-smtp.us-east-1.amazonaws.com";
    $port = "587";

    // SMTP server name, port, user/passwd
    $smtpinfo["host"] = $host;
    $smtpinfo["port"] = $port;
    $smtpinfo["auth"] = true;
    $smtpinfo["username"] = $emailUser;
    $smtpinfo["password"] = $emailPass;


    // Create the mail object using the Mail::factory method
    $mail = Mail::factory('smtp', $smtpinfo);

    $mail->send($to, $headers, $body);

    if (PEAR::isError($mail)) {
      $data = ['message' => 'Something went wrong', 'sent' => false];
    } else {
      // Prepare statement
      $stmt = mysqli_prepare($conn, 'UPDATE supertopsecret SET code = ? WHERE email = ?');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "ss", $token, $_POST['recoverEmail']);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      $_SESSION['sessionCode'] = $token;

      $data = ['message' => 'Email sent', 'sent' => true];
    }
  } else {
    $data = ['message' => 'No user with this email', 'sent' => false];
  }
    echo json_encode($data);
}


if (isset($_POST['emailNotif'])) {

}





// If they're reporting a bounti
if (isset($_POST['reasonforreporting']) && $_POST['paperid']) {

  include_once '../../../unimportant.php';
  require_once "../../../usr/share/php/Mail.php";
  include_once '../../../usr/share/php/Mail/mime.php';


  $html_message = 'Username: ' . $_SESSION['sessionUser'] . '<br>' .
  'ID: ' . $_SESSION['rndid'] . '<br>' .
  'Email: ' . $_SESSION['sessionEmail'] . '<br><br>' .
  'Paper in question: ' . $_POST['paperid'] . '<br>' .
  'Reason for reporting: ' . $_POST['reasonforreporting'];

  $headers["From"] = 'support@bounti.io';
  $headers["To"] = 'support@bounti.io';
  $to = 'support@bounti.io';
  $headers["Subject"] = $_SESSION['sessionUser']. ' reporting a paper';
  $headers["Content-Type"] = 'text/html; charset=UTF-8';
  $headers["Content-Transfer-Encoding"]= "8bit";


  $mime = new Mail_mime;
  $mime->addHTMLImage("' . $siteRoot . '/img/bountiheader.png");
  $mime->setHTMLBody($html_message);
  $mimeparams=array();


  // It refused to change to UTF-8 even if the header was set to this, after adding the following lines it worked.

  $mimeparams['text_encoding']="8bit";
  $mimeparams['text_charset']="UTF-8";
  $mimeparams['html_charset']="UTF-8";
  $mimeparams['head_charset']="UTF-8";


  $body = $mime->get($mimeparams);
  $headers = $mime->headers($headers);
  $page_content = "Mail now.";

  $host = "email-smtp.us-east-1.amazonaws.com";
  $port = "587";

  // SMTP server name, port, user/passwd
  $smtpinfo["host"] = $host;
  $smtpinfo["port"] = $port;
  $smtpinfo["auth"] = true;
  $smtpinfo["username"] = $emailUser;
  $smtpinfo["password"] = $emailPass;


  // Create the mail object using the Mail::factory method
  $mail = Mail::factory('smtp', $smtpinfo);

  $mail->send($to, $headers, $body);

  if (PEAR::isError($mail)) {
    $message = 'Something went wrong';
  } else {
    $message = 'Report sent';
  }
$message = 'wew';
  echo json_encode($message);
}



// If the user is resetting their password
if (isset($_POST['newpass']) && isset($_POST['newpass2']) && isset($_POST['id'])) {
  $newpass = $_POST['newpass'];
  $newpass2 = $_POST['newpass2'];
  $code = $_POST['id'];

  if($stmt = mysqli_prepare($conn, 'SELECT email, accountid FROM supertopsecret WHERE code = ?')) {
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "s", $code);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ((mysqli_num_rows($result) > 0)) { // If there was a code in the db

      if ($newpass == $newpass2) {
        $pass = password_hash($newpass, PASSWORD_BCRYPT);
        // Prepare statement
        $stmt = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE rndid = ? AND email = ?');
        // Set the parameter
        mysqli_stmt_bind_param($stmt, "sss", $pass, $row['accountid'], $row['email']);
        // Execute the statement
        mysqli_stmt_execute($stmt);

        $passwordReset = true;
        $message = 'Password changed';

/*
        // log the user in
        if($stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE rndid = ?")) {
          // Set the parameter
          mysqli_stmt_bind_param($stmt, "s", $row['accountid']);
          // Execute the statement
          mysqli_stmt_execute($stmt);
          // Get the results
          $result = $stmt->get_result();

          if ($result->num_rows > 0) { // If the cookie matches a current user
            $row = $result->fetch_assoc(); // Grab the row's info
            $_SESSION["sessionID"] = $row["id"];
            $_SESSION["rndid"] = $row["rndid"];

            $_SESSION["sessionUser"] = $row["username"];
            $_SESSION["sessionName"] = $row["fullname"];
            $_SESSION["sessionFirst"] = $row["firstname"];
            $_SESSION["sessionLast"] = $row["lastname"];
            $_SESSION["sessionEmail"] = $row["email"];

            $_SESSION["sessionGrade"] = $row["grade"];
            $_SESSION['sessionGradeNum'] = $row['gradenum'];

            $_SESSION["sessionIMG"] = $row["img"];
            $_SESSION["loggedIn"] = TRUE;
            $_SESSION["collecting"] = $row["iscollecting"];


            $_SESSION['gradeLetter'] = $row['gradeletter'];
            $_SESSION['score'] = $row['score'];

            $_SESSION['stripeAcct'] = $row['stripeacct'];
            $_SESSION['custAcct'] = $row['custacct'];

            $_SESSION['extAcctMade'] = $row['extacctmade'];
            $_SESSION['payMethodMade'] = $row['paymethodmade'];

            $token = bin2hex(random_bytes(12));

            // Prepare statement
            $stmt = mysqli_prepare($conn, 'UPDATE supertopsecret SET code = ? WHERE accountid = ?');
            // Set the parameter
            mysqli_stmt_bind_param($stmt, "ss", $token, $_SESSION['rndid']);
            // Execute the statement
            mysqli_stmt_execute($stmt);

            $_SESSION['sessionCode'] = $token;
          }
        }*/

      } else {
        $passwordReset = false;
        $message = 'Passwords must match';
      }
    } else {
      $passwordReset = false;
      $message = 'Something went wrong';
    }
  } else {
    $passwordReset = false;
    $message = 'Something went wrong';
  }
  $data = ['passwordReset' => $passwordReset, 'message' => $message];
  echo json_encode($data);
}

} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
}

mysqli_close($conn);

?>
