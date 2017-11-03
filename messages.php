<?php
session_start();

if (!($_SESSION["loggedIn"])) {
  echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
}
include 'scripts.php';
include_once '../../../unimportant.php';
$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

/* check connection */
if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}
//ini_set('display_errors',1);
//error_reporting(E_ALL);
?>
<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Bounti.io - Messages</title>
    <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
    <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
    <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
    <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
    <script>var page = "messages"</script>
    <script src="<?php echo $siteRoot ?>/convjs.js"></script>
  </head>

  <body>
    <?php echoNavbar() ?>
    <div class="mainspace" style="margin-bottom: 0">
      <div id="messagesLS">
        <?php
        if($stmt = mysqli_prepare($conn, 'SELECT conversations.*, users.username, users.img, users.rndid AS userid, users.grade FROM conversations
        INNER JOIN users ON (users.rndid != ? AND (users.rndid = conversations.person1 OR users.rndid = conversations.person2))

        WHERE person1 = ? OR person2 = ? ORDER BY conversations.timestamp DESC')) {

        // Set the parameter
        mysqli_stmt_bind_param($stmt, "sss", $_SESSION['rndid'], $_SESSION['rndid'], $_SESSION['rndid']);
        // Execute the statement
        mysqli_stmt_execute($stmt);
        // Get the results
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
          $new = '';
          if (($row['unread'] == 1) && ($row['latestsender'] != $_SESSION['rndid'])) {
            $new = ' new';
          }

          $stmt = mysqli_prepare($conn, 'SELECT contents, userfrom, convoid FROM messages WHERE convoid = ? AND ((userfrom = ? AND deletedbyuserfrom = 0) OR (userto = ? AND deletedbyuserto = 0)) ORDER BY timestamp DESC LIMIT 1');
          // Set the parameter
          mysqli_stmt_bind_param($stmt, "sss", $row['rndid'], $_SESSION['rndid'], $_SESSION['rndid']);
          // Execute the statement
          mysqli_stmt_execute($stmt);

          $messageResult = $stmt->get_result();
          $latestMessage = $messageResult->fetch_assoc();


          //echo '<a href="messages.php?id='.$row['id'].'&usr='.$user['id'].'">
          echo '<a onclick="selectUser(\''.$row['rndid'].'\')">
              <div class="messageDiv'.$new.'" id="'.$row['rndid'].'">
                  <div class="messageTop">
                    <img src="' . $siteRoot . '/'.$row['img'].'" />
                    <div>
                      <p style="font-size: 3vmin;">'.$row['username'].'</p>
                      <p>'.$row['grade'].'</p>
                    </div>
                  </div>
                  <div class="messagePeek">';
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

            if(isset($firstid)) {
              // do nothing
            } else {
              $firstid = $row['rndid'];
            }

          }

          //$user['firstname'] . $user['lastname'] . '<img src="' .$user['img']. '" />';

          //echo $row['id'] . $row['person1'] . $row['person2'] . $row['timestamp'];
        }

        ?>



      </div>
        <div id="messagesRS">
          <?php
          if (isset($_GET['id'])) {
            $id = $_GET['id'];
          } else {
            $id = $firstid;
          }


          echo '<script type="text/javascript">selectUser(\''.$id.'\');</script>';
          // EVERYTHING IN HERE WILL BE REPLACED ONCE selectUser() FIRES, SO BE WARY OF THAT (YOU ALREADY GOT BURNED ONCE)
          ?>
        </div>
        <span id="messageForm">
          <input type="text" id="messageText" rows="1" maxlength="1000" onkeyup="if (event.keyCode == 13) {sendMessage()}"></textarea>
          <span onclick="sendMessage()" id="sendImage"><img src="<?php echo $siteRoot ?>/img/send.png" /></span>
        </span>




    </div>
    <ul class="contextMenu">
      <a onclick="confirmation"><li onclick="confirmation()">Delete conversation</li></a>
    </ul>
    <?php $conn->close(); ?>
  </body>
  <script>$(document).mouseup(function(e) {
    var container = $(".taskList");
    var contextmenu = $(".contextMenu");
    if (!container.is(e.target) && container.has(e.target).length === 0) {
      removeDarken();
    }
    if (!contextmenu.is(e.target) && contextmenu.has(e.target).length === 0) {
      $('.contextMenu').css({
          top: "-2000px",
          left: "-2000px",
          opacity: 0,
          visibility: 'hidden'
      });
    }

});


</script>


</html>
