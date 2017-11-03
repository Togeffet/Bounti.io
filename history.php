<?php
session_start();
if (!($_SESSION["loggedIn"])) {
  $_SESSION['wantedPage'] = $_SERVER['REQUEST_URI'];
  echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
}
include_once '../../../unimportant.php';
include 'scripts.php';

//ini_set('display_errors',1);
//error_reporting(E_ALL);

$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

/* check connection */
if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

if (isset($_GET['id'])) {
  $getUser = $_GET['id'];
} else {
  $getUser = $_SESSION['sessionUser'];
}


if($stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?")) {
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "s", $getUser);
  // Execute the statement
  mysqli_stmt_execute($stmt); // Set message to read
  // Get the results
  $result = $stmt->get_result();

  if (mysqli_num_rows($result) > 0) {
    $row = $result->fetch_assoc();
    $userid = $row['rndid'];
    $userName = $row["username"];
    $fullName = $row["fullname"];
    $email = $row["email"];
    $grade = $row["grade"];
    $profilePicture = $row["img"];
    $score = $row["gradeletter"];

  }
}
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - History</title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>
  <body>
      <?php echoNavbar() ?>
      <div class="mainspaceHistory">
        <h1>History</h1>
        <span>
          <p id="historyLabel">Claimed Bounties</p>
          <a href="<?php echo $siteRoot ?>/mypapers/<?php echo $getUser ?>/bounties/?page=1" id="showAll">Show all</a>
        </span>
        <div id="historyList">
            <?php
            // Show pending bouties first
            if($stmt = mysqli_prepare($conn, 'SELECT * FROM bounties WHERE reviewer = ? LIMIT 8')) {
              // Set the parameter
              mysqli_stmt_bind_param($stmt, "s", $userid);
              // Execute the statement
              mysqli_stmt_execute($stmt);
              // Get the results
              $result = $stmt->get_result();

              $numRows1 = mysqli_num_rows($result);
              // output data of each row
              while($row = $result->fetch_assoc()) {
                $stripeReward = $row['stripeamount'] * .01;
                echo '<a style="color:black" href="' . $siteRoot . '/fullbounti/' . $row["rndid"] . '">
                        <div class="bounty">
                        <div class="timer" id="'.$row['rndid'].'">';
                        if ($row['success'] == 1) {
                          echo '<img src="' . $siteRoot . '/img/check.png" /> Success';
                        } else {
                          echo '...';
                        }
                         echo '</div>
                            <div class="preview">

                                <img src="' . $siteRoot . '/docs/'.$row["rndid"].'/original/'.$row['rndid'].'.jpg" />
                            </div>

                              <p class="title">'
                              . $row["title"] .'</p>
                              <div class="bottomRow">
                              <p class="author">By: ' . $row["author"] . '</p>
                              <p class="cost">' . money_format('$%i', $stripeReward)
                              . '</p>
                            </div>
                        </div>
                    </a>';
                echo '<script type="text/javascript">CountDownTimer("'.$row['duedate'].'", \''.$row['rndid'].'\', "b");</script>';
              }
            }
            if ($numRows1 == 0) {
              echo '<h1 style="font-size: 4vmin">No Bounties available</h1>';
            }
            ?>
        </div>

        <a id="anchor" name="myPapers"></a>
          <span>
            <p id="historyLabel"><?php if($_SESSION['rndid'] == $userid) {
              echo 'My ';
            } else {
              echo $userName . "'s ";
            }?>
            Papers</p>
            <a href="<?php echo $siteRoot ?>/mypapers/<?php echo $getUser?>/papers/?page=1" id="showAll">Show all</a>
          </span>
          <div id="historyList">
              <?php

              // Show pending bouties first
              if($stmt = mysqli_prepare($conn, 'SELECT * FROM bounties WHERE authorid = ? ORDER BY success ASC LIMIT 8')) {
                // Set the parameter
                mysqli_stmt_bind_param($stmt, "s", $userid);
                // Execute the statement
                mysqli_stmt_execute($stmt);
                // Get the results
                $result = $stmt->get_result();
                $numRows2 = mysqli_num_rows($result);
                // output data of each
                  while($row = $result->fetch_assoc()) {
                    $stripeReward = $row['stripeamount'] * .01;
                    echo '<a style="color:black" href="' . $siteRoot . '/fullbounti/' . $row["rndid"] . '">
                            <div class="bounty">
                            <div class="timer" id="'.$row['rndid'].'">';
                            if ($row['success'] == 1) {
                              echo '<img src="' . $siteRoot . '/img/check.png" /> Success';
                            } else {
                              echo '...';
                            }
                             echo '</div>
                                <div class="preview">

                                    <img src="' . $siteRoot . '/docs/'.$row["rndid"].'/original/'.$row['rndid'].'.jpg" />
                                </div>

                                  <p class="title">'
                                  . $row["title"] .'</p>
                                  <div class="bottomRow">
                                  <p class="author">By: ' . $row["author"] . '</p>
                                  <p class="cost">' . money_format('$%i', $stripeReward)
                                  . '</p>
                                </div>
                            </div>
                        </a>';
                    echo '<script type="text/javascript">CountDownTimer("'.$row['duedate'].'", \''.$row['rndid'].'\', "b");</script>';
                  }
              }

              if ($numRows2 == 0) {
                echo '<h1 style="font-size: 4vmin">No Bounties available</h1>';
              }
              $conn->close();
              ?>
          </div>




</div>

          <?php echoFooter() ?>

</body>

</html>
