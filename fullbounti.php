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

?>


        <?php
        // Create connection
        $conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

        // Get info for the bounti
        $stmt = mysqli_prepare($conn, 'SELECT B.authorid, B.title, B.rndid, B.reward, B.reviewer, B.duedate,
          B.pages, B.bio, B.success, B.stripeamount, U.username, U.img, U.grade, U.rndid AS userid
          FROM bounties AS B
          INNER JOIN users AS U ON U.rndid = B.authorid
          WHERE B.rndid = ?');
        // Set the parameter
        mysqli_stmt_bind_param($stmt, "s", $_GET["id"]);
        // Execute the statement
        mysqli_stmt_execute($stmt);
        // Get the results
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (isset($row['reviewer']) && $_SESSION['rndid'] == $row['authorid']) {
          $stmt = mysqli_prepare($conn, 'SELECT username FROM users WHERE rndid = ?');
          // Set the parameter
          mysqli_stmt_bind_param($stmt, "s", $row['reviewer']);
          // Execute the statement
          mysqli_stmt_execute($stmt);
          // Get the results
          $reviewerResult = $stmt->get_result();
          $reviewer = $reviewerResult->fetch_assoc();
        }

        $reward = $row['stripeamount'] * .01;


        // If you are the reviewer and it's currently being reviewed
        if (($_SESSION['rndid'] == $row['reviewer']) && $row['success'] == 0) {

          $editmessage = '<p>Review paper</p>';
          $old = '/var/www/documents/' . $row['rndid'] . '/original/' . $row['rndid'] . '.docx';
          $title = str_replace(' ', '', $row['title']);
          $new = '/var/www/documents/' . $row['rndid'] . '/original/' . $title . '.docx';
          if(!file_exists($new)) { // If the file doesn't already exist
            copy($old, $new);
            unlink($old);
          }
          if (file_exists($old)) {
            unlink($old);
          }
          $link = 'href="' . $siteRoot . '/docs/' . $row['rndid'] . '/original/' . $title . '.docx"';
          //$link = 'href="'.$new.'"';
          // If you aren't anyone special looking at this and it's done
        } else if (datePassed($row['duedate']) && ($row['success'] != 1)) { // If it's expired
          $editmessage = '<p>Paper expired</p>';
          $link = '';
        } else if (datePassed($row['duedate']) && ($row['success'] == 1) && ($row['reviewer'] == $_SESSION['rndid'])) { // If it's expired
          $editmessage = '<p>Bounti turned in</p>';
          $link = '';
        } else if (($row['success'] == 1) && ($row['authorid'] != $_SESSION['rndid'])) {
          $editmessage = '<p>This bounti has been completed</p>';
          $link = '';
          // If you are looking at a bounty with another current reviewer (you shouldn't be here)
        } else if(isset($row['reviewer']) && ($row['reviewer'] != $_SESSION['rndid']) && ($row['authorid'] != $_SESSION['rndid'])) {
          $editmessage = '<p>This Bounti is being claimed</p>';
          $link = '';
        } else if (($_SESSION['rndid'] == $row['authorid']) && ($row['success'] == 0) && (isset($row['reviewer']))) {
          $editmessage = '<p>View Bounti Hunter\'s profile</p>';
          $link = 'href="' . $siteRoot . '/account/'.$reviewer['username'].'"';
          // If you are the author and there isn't a reviewer
        } else if (($_SESSION['rndid'] == $row['authorid']) && ($row['success'] == 0) && (!isset($row['reviewer']))){

          $editmessage = '<p>View interested Bounti Hunters</p>';
          $link = 'onclick="showHunters(\''.$row['rndid'].'\')"';


        } else if(($_SESSION['rndid'] == $row['authorid']) && ($row['success'] == 1)){
          $editmessage = '<p>View Completed Bounti</p>';
          $link = 'href="' . $siteRoot . '/completedbounti/'.$_GET['id'].'"';

        /*} else if (($_SESSION['collecting']) && $_SESSION['collecting'] != $row['id']) {
          $editmessage = '<p>You must finish your bounti first</p>';
          $link = '';*/

        } else {
          $editmessage = '<p>Request to review</p>';
          $link = 'onclick="request(\''.$row['rndid'].'\', \''.$row['userid'].'\')"';
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
            <!--script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script-->
            <script src="<?php echo $siteRoot ?>/convjs.js"></script>
          </head>

          <body>
            <?php echoNavbar() ?>
            <div class="mainspace">
              <div id="fullBounty">
                <?php
        echo '<div class="floatleft">
                <a href="' . $siteRoot . '/account/'.$row['username'].'" style="color: black">
                <div class="authorpicandname">
                  <img src="' . $siteRoot . '/'.$row['img'].'" class="authorPic" />
                  <h2 class="fullAuthor">'.$row['username'].'</h2>
                </div>
                </a>
                <p class="fullTitle">"'.$row['title'].'"</p>
                <p class="fullReward" style="font-size: 2vmin; margin-bottom: 0vmin">Due: '.$row['duedate'].'</p>
                <p class="fullReward">Reward: $'.sprintf("%01.2f", $reward).'</p>

                <p class="fullPages">Pages: '.$row['pages'].'</p>
                <p class="fullBio">About: '.$row['bio'].'</p>';

                if (($_SESSION['rndid'] == $row['reviewer']) && $row['success'] == 0) {
                  echo '<p class="text"><a style="color:black" href="'.$siteRoot.'/submitbounti/'.$row['rndid'].'">Turn in Bounti</a></p>';
                }

              echo '</div>
              <a id="fullPaper" '.$link.'>
                <div class="paperPreviewContainer">';
                // If they're the authors, it doesn't have a reviewer, and it's not successful, show the edit pencil
                  if (($_SESSION['rndid'] == $row['authorid']) && (!isset($row['reviewer']) || datePassed($row['duedate'])) && ($row['success'] != 1)) {
                    echo '<div id="editPencil"><img src="' . $siteRoot . '/img/pencilwhite.png" /></div>';
                  }
                  echo '<div id="edit">'.$editmessage.'</div>
                  <img src="' . $siteRoot . '/docs/' . $row['rndid'] . '/original/' . $row['rndid'] .'.jpg" class="paperPreview" />
                </div>
              </a>';
        $conn->close();
        ?>
      </div>
      </div>
      <?php echoFooter(); ?>
      <script type="text/javascript">$('#editPencil').click(function(e) { e.preventDefault(); editBounti('<?php echo $row['rndid']?>')})</script>
  </body>
</html>
