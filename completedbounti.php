<?php
session_start();

include_once '../../../unimportant.php';
include 'scripts.php';

//ini_set('display_errors',1);
//error_reporting(E_ALL);

if (!($_SESSION["loggedIn"])) {
  $_SESSION['wantedPage'] = $_SERVER['REQUEST_URI'];
  echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
  exit();
} else {
  $conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);
  // Check connection
  if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
  }

  $stmt = mysqli_prepare($conn, 'SELECT code FROM supertopsecret WHERE accountid = ?');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "s", $_SESSION['rndid']);
  // Execute the statement
  mysqli_stmt_execute($stmt);
  $result = $stmt->get_result();
  $userCode = $result->fetch_assoc();
  if ($_SESSION['sessionCode'] != $userCode['code']) { // Code is changed
    logOut();
    $_SESSION['wantedPage'] = $_SERVER['REQUEST_URI']; // TODO: GET THIS TO WORK
    echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
    exit();
  }
}

// Create connection
$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);
// Test connection
if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}


$stmt = mysqli_prepare($conn, 'SELECT B.title, B.reward, F.comments, F.rndid, F.reviewerid, B.authorid, B.rndid AS bountiid, U.username, U.img
  FROM finished AS F
  INNER JOIN users AS U
  ON U.rndid = F.reviewerid
  INNER JOIN bounties AS B
  ON B.rndid = F.paperid
  WHERE F.paperid = ? AND F.authorid = ?');
// Set the parameter
mysqli_stmt_bind_param($stmt, "ss", $_GET["id"], $_SESSION['rndid']);
// Execute the statement
mysqli_stmt_execute($stmt);
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$link = '';

if ($_SESSION['rndid'] != $row['authorid']) {
  echo '<!DOCTYPE html>
  <html>
    <head>
      <meta charset="UTF-8">
      <title>"Bounti.io - Access Denied</title>
      <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
      <link rel="stylesheet" href="'.$siteRoot .'/convstyle.css" />
      <link rel="icon" href="'.$siteRoot .'/img/bouti_title_logo_new.png" />
      <script type="text/javascript" src="'.$siteRoot .'/js/jquery-3.1.0.min.js"></script>
      <script type="text/javascript" src="'. $siteRoot .'/js/jquery-ui.min.js"></script>
      <script type="text/javascript" src="'.$siteRoot .'/js/jquery.form.min.js"></script>
      <!--script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script-->
      <script>var page = "completedbounti"</script>
      <script src="'.$siteRoot .'/convjs.js"></script>
    </head>

    <body>';
    echoNavbar();
    echo '
      <div class="mainspace justifyCenter">
        <h1>You can\'t view this Bounti</h1>
      </div>

    </body>
  </html>';
      exit;
} else { // If you are the author

  $old = '/var/www/documents/' . $row['bountiid'] . '/revised/' . $row['rndid'] . '.docx';
  $title = str_replace(' ', '', $row['title']);
  $new = '/var/www/documents/' . $row['bountiid'] . '/revised/' . $title . '.docx';
  if(!file_exists($new)) { // If the file doesn't already exist
    copy($old, $new);
    unlink($old);
  }
  if (file_exists($old)) {
    unlink($old);
  }
  $link = 'href="' . $siteRoot . '/docs/' . $row['bountiid'] . '/revised/' . $title . '.docx"';

}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Bounti.io - "<?php echo $row['title'] ?>"</title>
    <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
    <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
    <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
    <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery.form.min.js"></script>
    <!--script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script-->
    <script>var page = "completedbounti"</script>
    <script src="<?php echo $siteRoot ?>/convjs.js"></script>
  </head>

  <body>
    <?php echoNavbar() ?>
    <div class="mainspace">
      <div id="fullBounty">
        <?php
        echo '<div class="floatleft">
                <div class="authorpicandname">
                  <a href="' . $siteRoot . '/account/'.$row['username'].'">
                    <img src="' . $siteRoot . '/'.$row['img'].'" class="authorPic" />
                    <h2 class="fullAuthor">'.$row['username'].' - Bounti Hunter</h2>
                  </a>
                </div>
                <p class="fullTitle">"'.$row['title'].'"</p>
                <p class="fullReward">Rewarded: $'.sprintf("%01.2f", $row['reward']).'</p>
                <p class="fullBio">Comments: '.$row['comments'].'</p>';
                if ($row['authorid'] == $_SESSION['rndid']) {
                  echo '<p class="reportBounti" id="'.$row['rndid'].'">Report Bounti</p>';
                }
              echo '</div>
              <a '.$link.'>
                <div class="paperPreviewContainer">
                  <div id="edit"><p>Download Completed Bounti</p></div>
                  <img src="' . $siteRoot . '/docs/'.$row['bountiid'].'/revised/'.$row['rndid'].'.jpg" class="paperPreview" />
                </div>
              </a>';
        $conn->close();
        ?>
      </div>
      </div>
      <?php echoFooter() ?>
      <?php if ($_SESSION['rndid'] == $row['authorid']) {
        echo '<div class="darken">
          <div class="taskList">
            <h3 style="text-align: center">Are you sure?</h3>
            <div class="flexItem">
              <p style="text-align: center">If you feel like the Bounti Hunter who claimed your Bounti hasn\'t done a proper job, you can bring it to our attention and we can work with you to sort things out.</p>
              <form id="report-form" method="POST">
              <input type="hidden" value="'.$row['rndid'].'" name="paperid" />
                <div class="alignCenter">
                  <div class="formItem">
                    <label>Reason for reporting</label>
                    <input type="text" name="reasonforreporting" size="2" required />
                  </div>
                </div>
                <input type="submit" value="Report" style="margin-top:2vmin" />
              </form>
            </div>
          </div>
        </div>';
      }
      ?>
  </body>
</html>
