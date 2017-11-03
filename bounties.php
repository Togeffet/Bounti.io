<?php
session_start();
if (!($_SESSION["loggedIn"])) {
    $_SESSION['wantedPage'] = $_SERVER['REQUEST_URI'];
    echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage.php";</script>';
}
include 'scripts.php';
include_once '../../../unimportant.php';
$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);
if(!isset($_GET['id'])) {
  $page = 1;
} else {
  $page = $_GET['id'];
}
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - Home</title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>

    <body>
        <?php echoNavbar() ?>
        <div class="mainspace" style="padding-bottom: 8vmin">
            <h1>Bounties</h1>
            <div id="bountiesPage">
                <?php

                $NUMRESULTS = 12;


                if ($stmt = mysqli_prepare($conn, 'SELECT * FROM bounties WHERE reviewer = ? AND success = 0')) {
                    mysqli_stmt_bind_param($stmt, "s", $_SESSION['rndid']);

                    // Execute the statement
                    mysqli_stmt_execute($stmt);

                    $result = $stmt->get_result();

                    $numRows1 = mysqli_num_rows($result);
                    while($row = $result->fetch_assoc()) {
                      $stripeReward = $row['stripeamount'] * .01;
                        echo '<a href="' . $siteRoot . '/submitbounti/'. $row['rndid'].'">
                          <div class="currentbounty">
                            <div class="timer" id="'.$row['rndid'].'">';
                            if ($row['success'] == 1) {
                              echo '<img src="' . $siteRoot . '/img/check.png" /> Success';
                            } else {
                              echo '...';
                            };
                            echo '</div>
                            <p id="currentTitle">"'. $row["title"] . '"</p>
                            <p class="currentReward">Reward: ' . money_format('$%i', $stripeReward). '</p>
                            <p class="currentBottom">Current Bounti</p>
                          </div>
                        </a>';
                        echo '<script type="text/javascript">CountDownTimer("'.$row['duedate'].'", "'.$row['rndid'].'", "b");</script>';
                      $NUMRESULTS--;
                    }
                  }


                $offset = ($page - 1) * $NUMRESULTS;
                $nextPage = $page + 1;
                $prevPage = $page - 1;
                // ORDER BY STR_TO_DATE(duedate, '%d/%m/%Y')
                if ($stmt = mysqli_prepare($conn, "SELECT * FROM bounties WHERE ((min_score <= ? AND mingrade <= ? AND success = 0 AND (reviewer IS NULL OR reviewer = ?)) OR (authorid = ? AND success = 0)) AND (UNIX_TIMESTAMP(DATE_ADD(STR_TO_DATE(duedate, '%m/%d/%Y'), INTERVAL +6 HOUR)) >= UNIX_TIMESTAMP(NOW())) ORDER BY UNIX_TIMESTAMP(STR_TO_DATE(duedate, '%m/%d/%Y')) ASC LIMIT ? OFFSET ?")) {
                  mysqli_stmt_bind_param($stmt, "iissii", $_SESSION['score'], $_SESSION['sessionGradeNum'], $_SESSION['rndid'], $_SESSION['rndid'], $NUMRESULTS, $offset);

                  // Execute the statement
                  mysqli_stmt_execute($stmt);

                  $result = $stmt->get_result();
                  $numRows2 = mysqli_num_rows($result);

                  while($row = $result->fetch_assoc()) {
                    $stripeReward = $row['stripeamount'] * .01;
                          echo '<a style="color:black" href="' . $siteRoot . '/fullbounti/' . $row["rndid"] . '">
                                  <div class="bounty">';

                                  echo '<div class="timer" id="'.$row['rndid'].'">';
                                    if ($row['success'] == 1) {
                                      echo '<img src="' . $siteRoot . '/img/check.png" /> Success';
                                    } else if ($row['reviewer'] == $_SESSION['rndid']){
                                      echo 'You are hunting this bounti';
                                    } else {
                                      echo '...';
                                    }
                                     echo '</div>';

                                  echo  '<div class="preview">
                                          <img src="' . $siteRoot . '/docs/' . $row['rndid'] . '/original/' . $row['rndid'] .'.jpg" />
                                      </div>

                                        <p class="title">'. $row["title"] .'</p>
                                        <div class="bottomRow">
                                        <p class="author">By: ' . $row["author"] . '</p>
                                        <p class="cost">' . money_format('$%i', $stripeReward)
                                        . '</p>
                                      </div>
                                  </div>
                              </a>';
                          echo '<script type="text/javascript">CountDownTimer("'.$row['duedate'].'", "'.$row['rndid'].'", "b");</script>';


                  }
                }

                if (($numRows1 == 0) && ($numRows2 == 0)) {
                  echo '<h1 style="font-size: 4vmin">No Bounties available</h1>';
                }

                ?>
              </div>
            <?php
            $fetchResults = $NUMRESULTS + 2; // TODO: Check this out!

            $stmt = mysqli_prepare($conn, 'SELECT ROW_COUNT() FROM bounties WHERE (min_score <= ? AND mingrade <= ? AND success = 0 AND (reviewer IS NULL OR reviewer = ?)) OR (authorid = ? AND (success != -2 AND success != -1)) AND (UNIX_TIMESTAMP(DATE_ADD(STR_TO_DATE(duedate, "%m/%d/%Y"),INTERVAL +6 HOUR)) >= UNIX_TIMESTAMP(NOW())) LIMIT ? OFFSET ?');
            // Set the parameter
            mysqli_stmt_bind_param($stmt, "iiii", $_SESSION['score'], $_SESSION['sessionGradeNum'], $fetchResults, $offset);
            // Execute the statement
            mysqli_stmt_execute($stmt);
            // Get the results
            $result = $stmt->get_result();

            $numRows = mysqli_num_rows($result);

            $outterRange = $page * $NUMRESULTS;


            //echo floor($numRows / 12);



            if ($numRows <= $outterRange) { // Show next page arrow
              $nextThingy = ' class="disabled"';
            } else {
              $nextThingy = ' href="' . $siteRoot . '/bounties/'.$nextPage.'"';
            }
            if ($page <= 1) {
              $prevThingy = ' class="disabled"';
            } else {
               $prevThingy = ' href="' . $siteRoot . '/bounties/'.$prevPage.'"';
            }

            echo '<a'.$prevThingy.'>
                    <div id="prevPage"><img src="' . $siteRoot . '/img/prev.png" /></div>
                  </a>';
            echo '<a'.$nextThingy.'>
                    <div id="nextPage"><img src="' . $siteRoot . '/img/next.png" /></div>
                  </a>';

            $totalLimit = 18446744073709551610;
            $totalOffset = 0;
            // Bottom row number links
            mysqli_stmt_bind_param($stmt, "iiii", $_SESSION['score'], $_SESSION['sessionGradeNum'], $totalLimit, $totalOffset);
            // Execute to get total bounti pages
            mysqli_stmt_execute($stmt);
            // Get the results
            $result = $stmt->get_result();
            $totalNumRows = mysqli_num_rows($result);
            $numPages = ceil($totalNumRows / $NUMRESULTS);

            echo '<div class="pageLinks">';
            for ($i = 1; $i <= $numPages; $i++) {
              if ($i == $page) {
                echo '<a class="activePage" href="' . $siteRoot . '/bounties/'.$i.'">'.$i.'</a>';
              } else {
                echo '<a href="' . $siteRoot . '/bounties/'.$i.'">'.$i.'</a>';
              }
            }
            echo '</div>';
            $conn->close();
            ?>

        </div>
        <?php echoFooter() ?>
    </body>

</html>
