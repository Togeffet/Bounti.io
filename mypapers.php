<?php
session_start();
include_once '../../../unimportant.php';
include 'scripts.php';

if (!($_SESSION["loggedIn"])) {
    echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
}


if(!isset($_GET['page'])) {
  $page = 1;
} else {
  $page = $_GET['page'];
}

$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

/* check connection */
if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

if (!isset($_GET['id'])) {
  $getUser = $_SESSION['sessionUser'];
} else {
  $getUser = $_GET['id'];
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
    $id = $row["rndid"];
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
        <title>Bounti.io - <?php
        if($_GET['show'] == 'bounties') {
          if ($_SESSION['rndid'] == $id) {
            echo 'My Bounties';
          } else {
            echo $userName . "'s Bounties";
          }
        } else {
          if($_SESSION['rndid'] == $id) {
            echo 'My Papers';
          } else {
            echo $userName . "'s Papers";
          }
        }
        ?></title>
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
      <div class="mainspace" style="padding-bottom: 8vmin">

        <?php
        if($_GET['show'] == 'bounties') {
          if ($_SESSION['rndid'] == $id) {
            echo '<h1>My Bounties</h1>';
          } else {
            echo '<h1>' . $userName . "'s Bounties</h1>";
          }
        } else {
          if($_SESSION['rndid'] == $id) {
            echo '<h1>My Papers</h1>';
          } else {
            echo '<h1>' . $userName . "'s Papers</h1>";
          }
        }
        ?>

          <div id="bountiesPage">
              <?php


              $offset = ($page - 1) * 12;
              $nextPage = $page + 1;
              $prevPage = $page - 1;

              // Show pending bouties first
              if ($_GET['show'] == 'bounties') {
                $stmt = mysqli_prepare($conn, 'SELECT * FROM bounties WHERE reviewer = ? LIMIT 12 OFFSET ?');
              } else {
                $stmt = mysqli_prepare($conn, 'SELECT * FROM bounties WHERE authorid = ? LIMIT 12 OFFSET ?');
              }
                // Set the parameter
                mysqli_stmt_bind_param($stmt, "ss", $id, $offset);
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

              if ($numRows1 == 0) {
                echo '<h1 style="font-size: 4vmin">No Bounties available</h1>';
              }

              ?>
          </div>




          <?php
          if ($_GET['show'] == 'papers') {
            $stmt = mysqli_prepare($conn, 'SELECT ROW_COUNT() FROM bounties WHERE authorid = ? LIMIT ? OFFSET ?');
          } else {
            $stmt = mysqli_prepare($conn, 'SELECT ROW_COUNT() FROM bounties WHERE reviewer = ? LIMIT 16 OFFSET ?');
          }

          $limit = 16;
          // Set the parameter
          mysqli_stmt_bind_param($stmt, "sii", $id, $limit, $offset);
          // Execute the statement
          mysqli_stmt_execute($stmt);
          // Get the results
          $result = $stmt->get_result();

          $numRows = mysqli_num_rows($result);

          $outterRange = $page * 12;


          //echo floor($numRows / 12);



          if ($numRows < $outterRange) { // Show next page arrow
            $nextThingy = ' class="disabled"';
          } else {
            $nextThingy = ' href="' . $siteRoot . '/mypapers/'.$userName.'/'.$_GET['show'].'/&page='.$nextPage.'"';
          }
          if ($page <= 1) {
            $prevThingy = ' class="disabled"';
          } else {
             $prevThingy = ' href="' . $siteRoot . '/mypapers/'.$userName.'/'.$_GET['show'].'/&page='.$prevPage.'"';
          }

          echo '<a'.$prevThingy.'>
                  <div id="prevPage"><img src="' . $siteRoot . '/img/prev.png" /></div>
                </a>';
          echo '<a'.$nextThingy.'>
                  <div id="nextPage"><img src="' . $siteRoot . '/img/next.png" /></div>
                </a>';

          // Bottom row number links
          $limit = 18446744073709551610;
          // Set the parameter
          mysqli_stmt_bind_param($stmt, "sii", $id, $limit, $offset);
          // Execute the statement
          mysqli_stmt_execute($stmt);

          $result = $stmt->get_result();

          $totalNumRows = mysqli_num_rows($result);

          $numPages = ceil($totalNumRows / 12);
          echo '<div class="pageLinks">';
          for ($i = 1; $i <= $numPages; $i++) {
            if ($i == $page) {
              echo '<a class="activePage" href="' . $siteRoot . '/mypapers/'.$userName.'/'.$_GET['show'].'/?page='.$i.'">'.$i.'</a>';
            } else {
              echo '<a href="' . $siteRoot . '/mypapers/'.$userName.'/'.$_GET['show'].'/?page='.$i.'">'.$i.'</a>';
            }
          }
          echo '</div>';
          $conn->close();
          ?>

      </div>
      <?php echoFooter(); ?>
</body>

</html>
