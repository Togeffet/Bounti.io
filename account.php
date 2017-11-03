<?php
session_start();
include 'scripts.php';

if (!($_SESSION["loggedIn"])) {
  $_SESSION['wantedPage'] = $_SERVER['REQUEST_URI'];
  echo '<script type="text/javascript">location.href = "'.$siteRoot.'/loginpage";</script>';
}

//ini_set('display_errors',1);
//error_reporting(E_ALL);

// Create connection
$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

/* check connection */
if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

$reviewsArray = array();
$requestsArray = array();
$canMessage = false;

if (!isset($_GET['id'])) {
  $getUser = $_SESSION['sessionUser'];
} else {
  $getUser = $_GET['id']; // Get the username stored in the URL
}

if (isset($getUser)){ // If there's a username (should be)
  //if ($stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE stripeacct = ? OR custacct = ?")) {
  if ($stmt = mysqli_prepare($conn,
  "SELECT 'papers' as type, COUNT(*) as userid, NULL as username, NULL as gradeletter,
  NULL as grade, NULL as img FROM users INNER JOIN bounties ON users.username = ? AND bounties.authorid =
  users.rndid AND bounties.success = 1

  UNION (SELECT 'bounties' as type, COUNT(*) as userid, NULL as username, NULL as gradeletter,
  NULL as grade, NULL as img FROM users INNER JOIN bounties ON users.username = ? AND bounties.reviewer = users.rndid
  AND bounties.success = 1)

  UNION (SELECT 'reviews' as type, reviews.ratingletter as userid, reviewer.username as username, reviews.contents as gradeletter, reviewer.grade as grade, reviewer.img as img FROM reviews
  INNER JOIN users AS reviewer ON reviewer.rndid = reviews.senderid
  INNER JOIN users ON reviews.recipient = users.rndid AND users.username = ?
  WHERE reviews.newest = 1
  ORDER BY reviews.timestamp ASC
  LIMIT 20)

  UNION (SELECT 'canmessage' as type, B.title as userid, C.rndid as username, NULL as gradeletter, NULL as grade, NULL as img FROM notifications AS N
  INNER JOIN bounties AS B ON B.rndid = N.paperid
  INNER JOIN users AS U ON N.recipient = U.rndid AND U.username = ?
  INNER JOIN conversations AS C ON (U.rndid = C.person1 AND C.person2 = ?) OR (C.person2 = U.rndid AND C.person1 = ?)
  WHERE N.messagetype = 'a' AND N.recipient = U.rndid AND N.senderid = ?
  ORDER BY N.timestamp ASC
  LIMIT 1)

  UNION (SELECT 'requests' as type, bounties.title as userid, notifications.rndid as username, bounties.rndid as gradeletter, NULL as grade, NULL as img FROM notifications
  INNER JOIN bounties ON bounties.rndid = notifications.paperid
  INNER JOIN users ON notifications.senderid = users.rndid AND users.username = ?
  WHERE messagetype = 'r' AND senderid = users.rndid AND showyes = 1)

  UNION (SELECT 'possible' as type, NULL as userid, COUNT(*) as username, NULL as gradeletter,
  NULL as grade, NULL as img FROM bounties INNER JOIN users ON bounties.reviewer = users.rndid AND users.username = ?
  WHERE bounties.authorid = ? AND bounties.success = 1)

  UNION (SELECT 'results' as type, rndid, username, score AS gradeletter, grade, img FROM users WHERE username = ?)")) {
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "sssssssssss", $getUser, $getUser, $getUser, $getUser, $_SESSION['rndid'], $_SESSION['rndid'], $_SESSION['rndid'], $getUser, $getUser, $_SESSION['rndid'], $getUser);
    // Execute the statement
    mysqli_stmt_execute($stmt);
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      if (($row['type'] == 'results') && isset($row['username']) && isset($row['grade'])) {
        $userName = $row['username'];
        $grade = $row['grade'];
        $id = $row['userid'];
        $score = $row['gradeletter'];

        if (!empty($row['img'])) {
          $profilePicture = $row["img"];
        } else {
          $profilePicture = 'img/default.png';
        }



        $gradeLetter = 'NA';
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
        } else if ($score == -1){
          $color = '#607D8B';
          $gradeLetter = 'NA';
        } else { // If they have something lower than -1 (They messed up on their first paper and got -11 score prolly)
          $color = '#E53935';
          $gradeLetter = 'F--';
        }
      } else if ($row['type'] == 'bounties'){ // If you're getting the count for bounties collected
        $bountiesCollected = $row['userid'];

        if ($bountiesCollected == 1)
          $bountiesCollected .= ' Bounti Collected';
        else
          $bountiesCollected .= ' Bounties Collected';

      } else if ($row['type'] == 'papers') {
        $papersReviewed = $row['userid'];

        if ($papersReviewed == 1)
          $papersReviewed .= ' Paper Reviewed';
        else
          $papersReviewed .= ' Papers Reviewed';

      } else if ($row['type'] == 'possible') {
        $possibleToReview = $row['username'];

      } else if ($row['type'] == 'reviews') {
        $reviewsArray[] = $row;
        //print_r($row);

      } else if ($row['type'] == 'requests') {
        $requestsArray[] = $row;
      } else if ($row['type'] == 'canmessage') {
        if (isset($row['userid'])) {
          $canMessage = true;
          $acceptedPaper = $row['userid'];
          $conversationID = $row['username'];
        } else {
          $canMessage = false;
        }

      }
    }
  }
}


// TODO: Find a better way to do this, I mean what the hell man
$a = ''; $b = ''; $c = ''; $d = ''; $f = ''; $plus = ''; $minus = '';

// If you've already left a review, it'll allow you to update it
if($stmt = mysqli_prepare($conn, 'SELECT * FROM reviews WHERE senderid = ? AND recipient = ? AND newest = 1')) {
  // Set the parameter
    mysqli_stmt_bind_param($stmt, "ss", $_SESSION['rndid'], $id);
    // Execute the statement
    mysqli_stmt_execute($stmt);
    // Get the results
    $result = $stmt->get_result();

    $alreadyReviewed = mysqli_num_rows($result);
    $review = $result->fetch_assoc();
    if ($alreadyReviewed) {
      $placeholder = $review['contents'];
      $value = 'Update';

      switch(substr($review['ratingletter'], 0, 1)) {
      case 'A':
        $a = ' selected ';
        break;
      case 'B';
        $b = ' selected ';
        break;
      case 'C';
        $c = ' selected ';
        break;
      case 'D';
        $d = ' selected ';
        break;
      default:
        $f = ' selected ';
      }

      switch(substr($review['ratingletter'], 1, 1)) {
      case '+':
        $plus = ' selected ';
        break;
      case '-':
        $minus = ' selected ';
        break;
      }


      $box2 = 'placeholder="'.substr($review['ratingletter'], 1, 1).'"';
      //echo $box1 . ' ' . $box2;
    } else {
      $placeholder = 'Leave a review...';
      $value = 'Submit';
      $update = '';
    }
}






?>
<?php
  if (!isset($userName)) { // If this person doesn't exist

    echo '<!DOCTYPE html>
    <html>

        <head>
            <meta charset="UTF-8">
            <title>Bounti.io - Account not found</title>
            <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
            <link rel="stylesheet" href="'.$siteRoot .'/css/dropzone.css" />
            <link rel="stylesheet" href="'.$siteRoot .'/convstyle.css" />
            <link rel="icon" href="'.$siteRoot .'/img/bouti_title_logo_new.png" />
            <script type="text/javascript" src="'.$siteRoot .'/js/jquery-3.1.0.min.js"></script>
            <script type="text/javascript" src="'.$siteRoot .'/js/jquery-ui.min.js"></script>
            <script type="text/javascript" src="'.$siteRoot .'/js/jquery.form.min.js"></script>
            <script>var page = "account"</script>
            <script src="'.$siteRoot.'/convjs.js"></script>
        </head>

        <body>';
        echoNavbar();
        echo '<div class="mainspace" style="justify-content:center">
              <h1 style="position: relative; bottom: 7vmin">User not found :(</h1>
              <a href="'.$siteRoot.'/bounties" style="margin-top: 5vmin">
                <div class="coolBackground" style="width: 60vmin; text-align: center">
                  <h1 style="font-size: 3vmin; cursor: pointer">Click here to go back home</h1>
                </div>
              </a>
            </div>';
            echoFooter();
        echo '</body>
        </html>';

        exit();

  }
?>

<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title><?php echo 'Bounti.io - ' . $userName ?></title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <!--<link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>-->
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/css/dropzone.css" />
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery.form.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
        <!--<script type="text/javascript" src="js/materialize.min.js"></script>-->
        <script>var page = "account"</script>
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>

    <body>
        <?php echoNavbar() ?>
        <div class="mainspace">
            <div class="accountTop">
                <a href="<?php echo $siteRoot ?>/history/<?php echo $userName?>"><?php echo $bountiesCollected ?></a>
                <div class="imageAndGrade">
                  <div class="accountImgDiv">
                    <img class="accountImg" src="<?php echo $siteRoot ?>/<?php echo $profilePicture ?>" />
                  </div>
                  <div id="accountGrade" style="background-color: <?php echo $color ?>"><?php echo $gradeLetter ?></div>
                </div>
                <a href="<?php echo $siteRoot ?>/history/<?php echo $userName?>#myPapers"><?php echo $papersReviewed ?></a>
            </div>
            <h1 style="font-family: 'Oswald'; font-weight: 500; margin-bottom: 0;"><?php echo $userName ?></h1>
            <h1 style="font-family: 'Oswald'; font-size: 5vmin; margin-top: 0;font-weight: 500"><?php echo $grade ?></h1>
            <div id="reviews">
              <?php

              if($possibleToReview > 0) {
                echo '<div class="review">
                        <div class="hunter">
                          <img src="'.$siteRoot.'/'.$_SESSION['sessionIMG'].'" />
                          <div>
                            <p style="font-size: 3vmin">'.$_SESSION['sessionUser'].'</p>
                            <p>'.$_SESSION['sessionGrade'].'</p>
                          </div>
                        </div>
                        <form id="review-form" method="post">
                          <input type="hidden" value="'.$id.'" name="userid" />
                          <div class="score">
                            <select id="grade" name="grade">
                              <option value="95"'.$a.'>A</option>
                              <option value="85"'.$b.'>B</option>
                              <option value="75"'.$c.'>C</option>
                              <option value="65"'.$d.'>D</option>
                              <option value="59"'.$f.'>F</option>
                            </select>
                            <select id="modifier" name="modifier">
                              <option value=""></option>
                              <option value="+"'.$plus.'>+</option>
                              <option value="-"'.$minus.'>-</option>
                            </select>
                          </div>
                          <textarea rows="5" required name="reviewBox" id="reviewBox" placeholder="'.$placeholder.'"></textarea>
                          <input type="submit" value="'.$value.'" />
                        </form>
                      </div>';
              }
              if (count($reviewsArray) > 0) {
                  foreach($reviewsArray as $review){
                    echo '<div class="review">

                            <div class="hunter">
                            <a href="'.$siteRoot.'/account/'.$review['username'].'">
                              <img src="'.$siteRoot.'/'.$review['img'].'" />
                              <div>
                                <p style="font-size: 3vmin;">'.$review['username'].'</p>
                                <p>'.$review['grade'].'</p>
                              </div>
                              </a>
                              <div class="grade">'.$review['userid'].'</div>
                            </div>
                            <p>'.$review['gradeletter'].'</p>
                          </div>';
                  }
                } else {
                  echo '<span>No reviews</span>';
                }

              ?>
            </div>
            <div id="bottomRight">
              <img src="<?php echo $siteRoot ?>/img/xsmall.png" id="bottomRightX" />
            <?php
            if(count($requestsArray) > 0) {
                  echo '<script>$("#bottomRightX").css("display","flex")</script>';
                  foreach ($requestsArray as $request) {
                    echo '<div id="acceptDeclineBar">
                            <p>This user is requesting to review "'.$request['userid'].'"</p>
                            <div id="acceptdecline">
                              <a onclick="acceptUser(\''.$request['username'].'\', \''.$id.'\', \''.$request['gradeletter'].'\')">
                                <div id="accept">Accept</div>
                              </a>
                              <a onclick="declineRequest(\''.$id.'\', \''.$request['username'].'\', \''.$request['gradeletter'].'\', \'r\')">
                                <div id="decline">Decline</div>
                              </a>
                            </div>
                          </div>';
                        }
            }
            if ($canMessage) {
                echo '<script type="text/javascript">$(\'#bottomRightX\').show()</script>';
                echo '<div id="acceptDeclineBar">
                        <p>You\'ve accepted this user to review "'.$acceptedPaper.'"</p>
                        <a href="'.$siteRoot.'/messages/'.$conversationID.'"><div id="viewMessages"><img src="'.$siteRoot.'/img/message-text.png" />Messages</div></a>
                      </div>';
            }
            ?>
            </div>
            </div>
            <?php echoFooter() ?>


    </body>
</html>
