<?php
session_start();

//ini_set('display_errors',1);
//error_reporting(E_ALL);
include_once '../../../unimportant.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Create connection
  $conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

  // check connection
  if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
  }

  $uploaddir = 'documents/'; // Directory where files are saved
  $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

  // collect value of input field
  $title = $_REQUEST['title'];
  $bio = $_REQUEST['bio'];
  $pages = $_REQUEST['pages'];
  $author = $_SESSION["sessionUser"];
  $reward = $_REQUEST['reward'];
  $minscore = $_REQUEST['minscore'];
  $authorname = $_SESSION["sessionName"];
  $authorid = $_SESSION['sessionID'];

  $duedate = $_REQUEST['date'];

  $uploaddate = time();
  $mingrade = $_REQUEST['grade'];

  if ((isset($_GET['u']) && $_GET['u'] == 'y') && isset($_GET['id'])) { // If they're updating a bounti
    echo $duedate . ' ' . $_GET['id'];

    if($stmt = mysqli_prepare($conn, "SELECT * FROM bounties WHERE id = ? AND authorid = ?")) {
      mysqli_stmt_bind_param($stmt, "si", $_GET['id'], $authorid);
      // Execute the statement
      mysqli_stmt_execute($stmt);
      // Get the results
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();

      echo $row['title'];

      if ($title == $row['title']) {
        echo "You didn't edit this";
      } else {
        echo "You changed it from " . $row['title'] . " to " . $title;
      }
    }

    $titleOutput = '';
    $bioOutput = '';
    $pagesOutput = '';
    $rewardOutput = '';
    $duedateOutput = '';
    $minscoreOutput = '';
    $mingradeOutput = '';


    if ($row['title'] != $title) {
      $titleOutput = 'title = "' . $title . '"';
      if (($bio != $row['bio']) || ($pages != ($row['pages'])) || ($reward != $row['reward']) || ($duedate != $row['duedate']) || ($minscore != $row['min_score']) || ($mingrade != $row['mingrade'])) {
        $titleOutput .= ', ';
      }
    }

    if ($row['bio'] != $bio) {
      $bioOutput = 'bio = "' . $bio . '"';
      if (($pages != ($row['pages'])) || ($reward != $row['reward']) || ($duedate != $row['duedate']) || ($minscore != $row['min_score']) || ($mingrade != $row['mingrade'])) {
        $bioOutput .= ', ';
      }
    }

    if ($row['pages'] != $pages) {
      $pagesOutput = 'pages = ' . $pages;
      if (($reward != $row['reward']) || ($duedate != $row['duedate']) || ($minscore != $row['min_score']) || ($mingrade != $row['mingrade'])) {
        $pagesOutput .= ', ';
      }
    }

    if ($row['reward'] != $reward) {
      $rewardOutput = 'reward = "' . $reward .'"';
      if (($duedate != $row['duedate']) || ($minscore != $row['min_score']) || ($mingrade != $row['mingrade'])) {
        $rewardOutput .= ', ';
      }
    }

    if ($row['duedate'] != $duedate) {
      $duedateOutput = 'duedate = "' . $duedate . '"';
      if (($minscore != $row['min_score']) || ($mingrade != $row['mingrade'])) {
        $duedateOutput .= ', ';
      }
    }

    if ($row['min_score'] != $minscore) {
      $minscoreOutput = 'min_score = ' . $minscore;
      if (($mingrade != $row['mingrade'])) {
        $minscoreOutput .= ', ';
      }
    }

    if ($row['mingrade'] != $mingrade) {
      $mingradeOutput = 'mingrade = ' . $mingrade;
    }


    if ($titleOutput || $bioOutput || $pagesOutput || $rewardOutput || $duedateOutput || $minscoreOutput || $mingradeOutput) {
      $sql = "UPDATE bounties SET " . $titleOutput . $bioOutput . $pagesOutput . $rewardOutput . $duedateOutput . $minscoreOutput . $mingradeOutput . " WHERE id = " . $row['id'];

      $conn->query($sql);
      echo $sql;
    } else {
      echo 'You edited NOTHING';
    }


    if (!($_FILES['userfile']['name'])) {
      echo 'There is no userfile';
    } else {
      if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) { // If it gets uploaded correctly


            exec('doc2pdf "/var/www/html/' . $uploadfile .'" 2>&1', $output);
            $info = new SplFileInfo($uploadfile);
            $ext = $info->getExtension();

            $rawfilename = basename($uploadfile, ("." . $ext));

            $paper_file = $rawfilename . ".pdf";
            $save_to = $uploaddir . $row['id'] .'.jpg';

            $img = new imagick();
            $img->setResolution(100,100);
            $img->setSize(618,800);
            $img->readImage($uploaddir . $paper_file . '[0]');
            //set new format
            $img->setImageFormat('jpg');

            //save image file
            $img->writeImage($save_to);

            rename($uploadfile, ($uploaddir . $row['id']. '.docx')); // Rename uploaded document
            rename($save_to, ($uploaddir . $row['id']. '.jpg')); // Rename the image to overwrite
            unlink($uploaddir . $paper_file);
            echo $uploaddir . $row['id'] . '.jpg';
            echo '<img src="' . $uploaddir . $row['id'] . '.jpg" />';
            //echo '<script type="text/javascript">location.href = "mypapers.php?id=3&show=papers&page=1";</script>';
          }
        }










  } else { // If they're uploading for the first time*/
    $secret = '6Ld0ww4UAAAAALZCeIIaiMMWHp0PWWMIAT4ZdGuu';
    //get verify response data
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
    $responseData = json_decode($verifyResponse);
    if($responseData->success) {
      echo 'It gud';
      if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) { // If it gets uploaded correctly

        if($stmt = mysqli_prepare($conn, "INSERT INTO bounties (title, author, reward, bio, pages, min_score, authorname, authorid, duedate, timestamp, mingrade) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
          // Set the parameter
          mysqli_stmt_bind_param($stmt, "ssdsiisissi", $title, $author, $reward, $bio, $pages, $minscore, $authorname, $authorid, $duedate, $uploaddate, $mingrade);
          // Execute the statement
          if(mysqli_stmt_execute($stmt) == TRUE) { // If it successfully gets inserted into database
            // Get the paper that was just uploaded
            if($stmt = mysqli_prepare($conn, "SELECT * FROM bounties WHERE title = ? AND authorid = ?")) {
              mysqli_stmt_bind_param($stmt, "si", $title, $authorid);
              // Execute the statement
              mysqli_stmt_execute($stmt);
              // Get the results
              $result = $stmt->get_result();
              $row = $result->fetch_assoc();
              $paperid = $row["id"]; // Grabs the id of the paper to give a new name to the file


              // Actually upload it
              exec('doc2pdf "/var/www/html/' . $uploadfile .'" 2>&1', $output);
              $info = new SplFileInfo($uploadfile);
              $ext = $info->getExtension();

              $rawfilename = basename($uploadfile, ("." . $ext));

              $paper_file = $rawfilename . ".pdf";
              $save_to = $uploaddir . $paperid . '.jpg';

              $img = new imagick();
              $img->setResolution(100,100);
              $img->setSize(618,800);
              $img->readImage($uploaddir . $paper_file . '[0]');
              //set new format
              $img->setImageFormat('jpg');

              //save image file
              $img->writeImage($save_to);

              rename($uploadfile, ($uploaddir . $paperid. '.docx'));
              unlink($uploaddir . $paper_file);

              echo '<script type="text/javascript">location.href = "mypapers.php?id=3&show=papers&page=1";</script>';
            }
          }
        }
      }
    } else {
      echo 'Captcha ain\'t good';
    }
  }
}

$conn->close();
?>
