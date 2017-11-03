<?php
session_start();
if (!($_SESSION["loggedIn"])) {
    echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
}
include 'scripts.php';
include_once '../../../unimportant.php';

//ini_set('display_errors',1);
//error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - Notifications</title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
        <script>var page = 'notifications'</script>
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>

    <body>
      <?php echoNavbar() ?>
        <div class="mainspace">
            <h1>Notifications</h1>
            <div id="messages">
                <?php

                // Create connection
                $conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

                /* check connection */
                if (mysqli_connect_errno()) {
                  printf("Connect failed: %s\n", mysqli_connect_error());
                  exit();
                }

                $stmt = mysqli_prepare($conn, "SELECT notifications.*, users.username, bounties.title
                  FROM notifications
                  LEFT JOIN users ON notifications.senderid = users.rndid
                  LEFT JOIN bounties ON notifications.paperid = bounties.rndid
                  WHERE recipient = ? AND showyes = 1 ORDER BY id DESC LIMIT 100");

                // Set the parameter
                mysqli_stmt_bind_param($stmt, "s", $_SESSION['rndid']);
                // Execute the statement
                mysqli_stmt_execute($stmt);
                // Get the results
                $result = $stmt->get_result();
                // output data of each row
                while($row = $result->fetch_assoc()) {
                  if ($row['messagetype'] == 'a') { // If the message is an acceptance
                    echo '<div class="acceptance '.$row['unread'].'" id="'.$row['rndid'].'">
                            <div class="toprow">
                                <img src="' . $siteRoot . '/img/bounti_logo_black.png" />
                                <div class="reviewname">Bounti Team</div>
                                <div class="dateandtime">'. time2str($row['timestamp']) .'</div>
                            </div>
                            <p>'.$row['username'].' has okayed you to review their paper, "'.$row['title'].'"</p>
                            <table class="bottombuttons">
                                <tr>
                                    <td><a onclick="giveUp(\''.$row['paperid'].'\')" style="font-family: inherit; font-weight: inherit; font-size: 2.5vmin">Decline</a></td>
                                    <td><a href="' . $siteRoot . '/fullbounti/'.$row['paperid'].'" style="color:inherit; font-family: inherit; font-weight: inherit; font-size: 2.5vmin; display: block">Review Now</a></td>

                                </tr>
                            </table>
                        </div>';
                    } else if ($row['messagetype'] == 'r' || $row['messagetype'] == 'ar') { // If the message is a request or acted upon request
                        echo '<div class="acceptance '.$row['unread'].'" id="'.$row['rndid'].'">
                            <div class="toprow">
                                <img src="' . $siteRoot . '/img/bounti_logo_black.png" />
                                <div class="reviewname">Bounti Team</div>
                                <div class="dateandtime">'.time2str($row['timestamp']).'</div>
                            </div>
                            <p>'.$row['username'].' wants to review your paper, "'.$row['title'].'"</p>
                            <table class="bottombuttons">
                                <tr>
                                    <td><a style="font-family: inherit; font-weight: inherit; font-size: 2.5vmin" onclick="declineRequest(\''.$row['senderid'].'\', \''.$row['rndid'].'\', \''.$row['paperid'].'\', \''.$row['title'].'\', \'r\')">Decline</a></td>
                                    <td><a style="color:inherit; font-family: inherit; font-weight: inherit; font-size: 2.5vmin; display: block;" href="' . $siteRoot . '/account/'.$row['username'].'">Details</a></td>

                                </tr>
                            </table>
                        </div>';
                    } else if ($row['messagetype'] == 'f') { // If the message is a finished bounti
                      echo '<div class="acceptance '.$row['unread'].'" id="'.$row['rndid'].'">
                          <div class="toprow">
                              <img src="' . $siteRoot . '/img/bounti_logo_black.png" />
                              <div class="reviewname">Bounti Team</div>
                              <div class="dateandtime">'.time2str($row['timestamp']).'</div>
                              <a onclick="deleteThis(\''.$row['rndid'].'\')"><img id="deleteMessage" src="' . $siteRoot . '/img/clear_black_small.png" /></a>
                          </div>
                          <p>'.$row['username'].' has completed your Bounti! Click below to view the revised copy of "'.$row['title'].'"</p>
                          <table class="bottombuttons">
                              <tr>
                                  <td><a style="color:inherit; font-family: inherit; display: block; font-weight: inherit; font-size: 2.5vmin" href="' . $siteRoot . '/completedbounti/'.$row['paperid'].'">Completed Bounti</a></td>
                              </tr>
                          </table>
                      </div>';
                    } else if ($row['messagetype'] == 'rs'){ // If review sent
                      echo '<div class="acceptance '.$row['unread'].'" id="'.$row['rndid'].'">
                          <div class="toprow">
                              <img src="' . $siteRoot . '/img/bounti_logo_black.png" />
                              <div class="reviewname">Bounti Team</div>
                              <div class="dateandtime">'.time2str($row['timestamp']).'</div>
                              <a onclick="deleteThis(\''.$row['rndid'].'\')"><img id="deleteMessage" src="' . $siteRoot . '/img/clear_black_small.png" /></a>
                          </div>
                          <p>'.$row['username'].' left a review on your account, click below to check it out!</p>
                          <table class="bottombuttons">
                              <tr>
                                  <td><a style="color:inherit; font-family: inherit; display: block; font-weight: inherit; font-size: 2.5vmin" href="' . $siteRoot . '/account/'.$_SESSION['sessionUser'].'">My Account</a></td>
                              </tr>
                          </table>
                      </div>';
                    } else if ($row['messagetype'] == 'ru'){ // If review updated
                      echo '<div class="acceptance '.$row['unread'].'" id="'.$row['rndid'].'">
                          <div class="toprow">
                              <img src="' . $siteRoot . '/img/bounti_logo_black.png" />
                              <div class="reviewname">Bounti Team</div>
                              <div class="dateandtime">'.time2str($row['timestamp']).'</div>
                              <a onclick="deleteThis(\''.$row['rndid'].'\')"><img id="deleteMessage" src="' . $siteRoot . '/img/clear_black_small.png" /></a>
                          </div>
                          <p>'.$row['username'].' updated a review on your account, click below to check it out now!</p>
                          <table class="bottombuttons">
                              <tr>
                                  <td><a style="color:inherit; font-family: inherit; display: block; font-weight: inherit; font-size: 2.5vmin" href="' . $siteRoot . '/account/'.$_SESSION['sessionUser'].'">My Account</a></td>
                              </tr>
                          </table>
                      </div>';
                    } else if ($row['messagetype'] == 'da'){ // If the message is a declined acceptance
                        echo '<div class="acceptance '.$row['unread'].'" id="'.$row['rndid'].'">
                            <div class="toprow">
                                <img src="' . $siteRoot . '/img/bounti_logo_black.png" />
                                <div class="reviewname">Bounti Team</div>
                                <div class="dateandtime">'.time2str($row['timestamp']).'</div>
                                <a id="deleteMessageLink" onclick="deleteThis(\''.$row['rndid'].'\')"><img id="deleteMessage" src="' . $siteRoot . '/img/clear_black_small.png" /></a>
                            </div>
                            <p>We\'re sorry, but '.$row['username'].' has changed their mind about your paper, "'.$row['title'].'". Don\'t worry, We\'re hard at work searching for another Bounti Hunter to review your paper!</p>
                            <table class="bottombuttons">
                                <tr>
                                    <td onclick="deleteThis(\''.$row['rndid'].'\')"><a style="color:inherit; font-family: inherit; font-weight: inherit; font-size: 2.5vmin">Okay</a></td>
                                </tr>
                            </table>
                        </div>';
                    } else if ($row['messagetype'] == 'dr'){ // Declined review
                        echo '<div class="acceptance '.$row['unread'].'" id="'.$row['rndid'].'">
                            <div class="toprow">
                                <img src="' . $siteRoot . '/img/bounti_logo_black.png" />
                                <div class="reviewname">Bounti Team</div>
                                <div class="dateandtime">'.time2str($row['timestamp']).'</div>
                                <a onclick="deleteThis(\''.$row['rndid'].'\')"><img id="deleteMessage" src="' . $siteRoot . '/img/clear_black_small.png" /></a>
                            </div>
                            <p>We\'re sorry, but '.$row['username'].' has decided to decline your offer to review their paper, "'.$row['title'].'".</p>
                            <table class="bottombuttons">
                                <tr>
                                    <td onclick="deleteThis(\''.$row['rndid'].'\')"><a style="color:inherit; font-family: inherit; display: block; font-weight: inherit; font-size: 2.5vmin">Okay</a></td>
                                </tr>
                            </table>
                        </div>';
                    } else if ($row['messagetype'] == 'om') {
                      echo '<div class="acceptance '.$row['unread'].'" id="'.$row['rndid'].'">
                          <div class="toprow">
                              <img src="' . $siteRoot . '/img/bounti_logo_black.png" />
                              <div class="reviewname">Bounti Team</div>
                              <div class="dateandtime">'.time2str($row['timestamp']).'</div>
                              <a onclick="deleteThis(\''.$row['rndid'].'\')"><img id="deleteMessage" src="' . $siteRoot . '/img/clear_black_small.png" /></a>
                          </div>
                          <p>Welcome to Bounti.io! Before you begin uploading or hunting bounties, make sure you add a payment method and destination account!</p>
                          <table class="bottombuttons">
                              <tr>
                                <td><a style="color:inherit; font-family: inherit; font-weight: inherit; font-size: 2.5vmin; display: block;" href="' . $siteRoot . '/managepaymentmethods">Manage payment methods</a></td>
                              </tr>
                          </table>
                      </div>';
                    } else if ($row['messagetype'] == 'pu') { // Penalized user ;)
                      echo '<div class="acceptance '.$row['unread'].'" id="'.$row['rndid'].'">
                          <div class="toprow">
                              <img src="' . $siteRoot . '/img/bounti_logo_black.png" />
                              <div class="reviewname">Bounti Team</div>
                              <div class="dateandtime">'.time2str($row['timestamp']).'</div>
                              <a onclick="deleteThis(\''.$row['rndid'].'\')"><img id="deleteMessage" src="' . $siteRoot . '/img/clear_black_small.png" /></a>
                          </div>
                          <p>You didn\'t turn in "'.$row['title'].'" on time. As a result, your grade has gone down by 10%</p>
                          <table class="bottombuttons">
                              <tr>
                                <td onclick="deleteThis(\''.$row['rndid'].'\')"><a style="color:inherit; font-family: inherit; display: block; font-weight: inherit; font-size: 2.5vmin">Okay</a></td>
                              </tr>
                          </table>
                      </div>';
                    }
                };
                if(mysqli_num_rows($result) == 0) {
                  echo '<h1 style="font-size: 4vmin; margin-top: 6vmin">There\'s nothing here...yet :)</h1>';
                }

                $conn->close();
                ?>



            </div>
            </div>
            <?php echoFooter(); ?>

    </body>
</html>
