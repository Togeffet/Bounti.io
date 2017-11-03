<?php
session_start();
if (!($_SESSION["loggedIn"])) {
  echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
}
include_once '../../../unimportant.php';
include 'scripts.php';
?>
<!DOCTYPE html>
<html ng-app="myItemsApp">
  <head>
    <meta charset="UTF-8">
    <title>Bounti.io - Turn In Bounti</title>
    <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
    <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
    <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
    <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery.form.min.js"></script>
    <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
    <!--script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script-->
    <script>page = 'submitbounti'</script>
    <script src="<?php echo $siteRoot ?>/convjs.js"></script>
  </head>

  <body>
    <?php echoNavbar() ?>

    <?php
    // Create connection
    $conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);
    // Check connection
    if (mysqli_connect_errno()) {
      printf("Connect failed: %s\n", mysqli_connect_error());
      exit();
    }

    if (!(isset($_GET['id']))) {
      $stmt = mysqli_prepare($conn, 'SELECT * FROM bounties WHERE success = 0 AND reviewer = ? LIMIT 24');
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "s", $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt);
      // Get the results
      $result = $stmt->get_result();
      $currentBounties = mysqli_num_rows($result);
      if ($currentBounties > 1) {
        echo '<div class="mainspace">';
        echo '<h1>Turn In Bounti</h1>';
        echo '<div id="bountiesPage">';
        while ($row = $result->fetch_assoc()) {
          echo '<a style="color:black" href="' . $siteRoot . '/' . $row["rndid"] . '">
                  <div class="bounty">
                  <div class="timer" id="'.$row['rndid'].'">';
                  if ($row['success'] == 1) {
                    echo '<img src="' . $siteRoot . '/img/check.png" /> Success';
                  } else {
                    echo '...';
                  }
                   echo '</div>
                      <div class="preview">

                          <img src="' . $siteRoot . '/documents/'.$row["rndid"].'.jpg" />
                      </div>

                        <p class="title">'
                        . $row["title"] .'</p>
                        <div class="bottomRow">
                        <p class="author">By: ' . $row["authorname"] . '</p>
                        <p class="cost">' . money_format('$%i', $row["reward"])
                        . '</p>
                      </div>
                  </div>
              </a>';
          echo '<script type="text/javascript">CountDownTimer("'.$row['duedate'].'", \''.$row['rndid'].'\', "b");</script>';

        }
        echo '</div>';
        $id = 1;
      } else if ($currentBounties > 0) {
        $row = $result->fetch_assoc();
        $id = $row['rndid'];
      }

    } else {
      $id = $_GET['id'];
    }

    if (isset($id) && ($id != 1)) {
    if($stmt = mysqli_prepare($conn, 'SELECT * FROM bounties WHERE success = 0 AND rndid = ? AND reviewer = ?')) {
      // Set the parameter
        mysqli_stmt_bind_param($stmt, "ss", $id, $_SESSION['rndid']);
        // Execute the statement
        mysqli_stmt_execute($stmt);
        // Get the results
        $result = $stmt->get_result();

        if($row = $result->fetch_assoc()) {
          if($stmt = mysqli_prepare($conn, 'SELECT * FROM users WHERE rndid = ?')) {
            // Set the parameter
            mysqli_stmt_bind_param($stmt, "s", $row['authorid']);
            // Execute the statement
            mysqli_stmt_execute($stmt);
            // Get the results
            $result = $stmt->get_result();

            $author = $result->fetch_assoc();
            echo '<div class="mainspace">
                  <div class="uploadform">
                    <div class="floatleft">
                      <a href="' . $siteRoot . '/account/'.$author['username'].'" style="color: black">
                        <div class="authorpicandname">
                          <img src="' . $siteRoot . '/'.$author['img'].'" class="authorPic" />
                          <h2 class="fullAuthor">'.$author['fullname'].'</h2>
                        </div>
                      </a>
                      <p class="fullTitle">"'.$row['title'].'"</p>
                      <p class="fullReward">Reward: $'.sprintf("%01.2f", $row['reward']).'</p>
                      <p class="fullPages">Pages: '.$row['pages'].'</p>

                      <p class="fullBio">About: '.$row['bio'].'</p>
                      <form id="submitform" action="'.$siteRoot.'/submit.php" enctype="multipart/form-data" method="POST" style="width: 80vmin">
                        <input type="hidden" name="id" id="submitBountiID" value="'.$row['rndid'].'" />
                        <div class="formItem">
                          <label for="comments">Comments</label>
                          <textarea name="comments" rows="3" maxlength="500" required placeholder="Something you\'d like them to know about their paper..." style="width: 80vmin"></textarea>
                        </div>
                        <input type="hidden" name="MAX_FILE_SIZE" value="16777215" />
                        <input id="fileupload" name="userfile" type="file" onchange="alertFileName()" required /><br>
                        <div class="centerme"><input id="submit" type="submit" value="Submit" /></div>
                        <p class="text" style="text-align: left; width: 100%; margin-bottom: 1vmin"><a style="color: black" href="'.$siteRoot.'/fullbounti/'.$row['rndid'].'">Original Bounti</a></p>
                        <p class="text" style="text-align: left; width: 100%"><a id="giveUp">Give Up</a></p>
                      </div>
                      <label for="fileupload" id="dropplease">
                        <p style="font-size: 3.5vmin">Click to choose completed Bounti</p>
                        <p id="fileName"></p>
                      </label>
                    </form>
                  </div>';
          }

        }
      }
    } else {
      if ($id != 1) {
        echo '<div class="mainspace" style="justify-content: center">';
        echo "<h1>You currently aren't editing a bounti</h1>";
        echo "<h1 style='font-size: 6vmin'><a href='" . $siteRoot . "'/bounties'>Search for one now...</a></h1>";
      }
    }




    $conn->close();
    ?>

    </div>
    <?php echoFooter() ?>
  </body>
</html>
