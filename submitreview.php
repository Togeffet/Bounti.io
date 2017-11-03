<?php
session_start();
//ini_set('display_errors',1);
//error_reporting(E_ALL);
if (!($_SESSION["loggedIn"])) {
  echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
}
include_once '../../../unimportant.php';
include 'scripts.php';

// Create connection
$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

/* check connection */
if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

$totalScore = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $grade = $_POST['grade'];
  $modifier = $_POST['modifier'];
  $contents = $_POST['reviewBox'];
  $id = $_POST['userid']; // Id of account being reviewed
  echo $grade . $modifier;

  if($modifier == '+') { // If they chose +
    if ($grade == 95) { // If they chose A+
      $score = 100;
      $gradeLetter = "A+";
    } else if ($grade == 85) { // If they chose B+
      $score = 89;
      $gradeLetter = "B+";
    } else if ($grade == 75) { // If they chose C+
      $score = 79;
      $gradeLetter = "C+";
    } else if ($grade == 65) { // If they chose D+
      $score = 69;
      $gradeLetter = "D+";
    } else { // If they chose F+
      $score = $grade; // Set equal to 59
      $gradeLetter = "F";
    }
  } else if($modifier == '-') { // If they chose -
    if ($grade == 95) { // If they chose A-
      $score = 90;
      $gradeLetter = "A-";
    } else if ($grade == 85) { // If they chose B-
      $score = 80;
      $gradeLetter = "B-";
    } else if ($grade == 75) { // If they chose C-
      $score = 70;
      $gradeLetter = "C-";
    } else if ($grade == 65) { // If they chose D-
      $score = 60;
      $gradeLetter = "D-";
    } else { // If they chose F-
      $score = $grade; // Set equal to 59
      $gradeLetter = "F";
    }
  } else { // If they chose nothing
      $score = $grade; // Score is equal to whatever it was
      if ($grade == 95)
        $gradeLetter = "A";
      else if ($grade == 85)
        $gradeLetter = "B";
      else if ($grade == 75)
        $gradeLetter = "C";
      else if ($grade == 65)
        $gradeLetter = "D";
      else
        $gradeLetter = "F";
  }
  echo $score;
  echo $gradeLetter;
  $rndid = uniqid('review_');
  $notifRndid = uniqid('notif_');

  $stmt = mysqli_prepare($conn, 'SELECT ROW_COUNT() FROM reviews WHERE recipient = ? AND senderid = ?');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "ss", $id, $_SESSION['rndid']);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  $result = $stmt->get_result();
  $numRows = mysqli_num_rows($result);

  $time = time();

  if($numRows == 0) { // If the user is reviewing for the first time
    $messageType = "rs";

    $stmt = mysqli_prepare($conn, 'INSERT INTO reviews (rndid, recipient, senderid, rating, ratingletter, timestamp, contents)
            VALUES (?, ?, ?, ?, ?, ?, ?)');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "sssisis", $rndid, $id, $_SESSION['rndid'], $score, $gradeLetter, $time, $contents);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    $stmt = mysqli_prepare($conn, 'INSERT INTO notifications (rndid, senderid, recipient, timestamp, messagetype)
            VALUES (?, ?, ?, ?, ?)');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "sssis", $notifRndid, $_SESSION['rndid'], $id, $time, $messageType);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    $stmt = mysqli_prepare($conn, 'UPDATE users SET reviews = reviews + 1 WHERE rndid = ?'); // Say that they have another review to divide by
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "s", $id);
    // Execute the statement
    mysqli_stmt_execute($stmt);


  } else { // If the user is updating a review
    $messageType = "ru";

    $stmt = mysqli_prepare($conn, 'UPDATE reviews SET newest = 0 WHERE senderid = ? AND recipient = ?');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "ss", $_SESSION['rndid'], $id);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    $stmt = mysqli_prepare($conn, 'INSERT INTO reviews (rndid, recipient, senderid, rating, ratingletter, timestamp, contents)
            VALUES (?, ?, ?, ?, ?, ?, ?)');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "sssisis", $rndid, $id, $_SESSION['rndid'], $score, $gradeLetter, $time, $contents);
    // Execute the statement
    mysqli_stmt_execute($stmt);

    $stmt = mysqli_prepare($conn, 'INSERT INTO notifications (rndid, senderid, recipient, timestamp, messagetype)
            VALUES (?, ?, ?, ?, ?)');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "sssis", $notifRndid, $_SESSION['rndid'], $id, $time, $messageType);
    // Execute the statement
    mysqli_stmt_execute($stmt);

  }

  $stmt = mysqli_prepare($conn, 'SELECT reviews.*, U.reviews AS numofreviews
    FROM reviews
    INNER JOIN users AS U ON recipient = U.rndid
    WHERE recipient = ?');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "s", $id);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $totalScore += $row['rating'];
    $numOfReviews = $row['numofreviews'];
  }

  $newScore = $totalScore / $numOfReviews;
  echo $newScore;
  $newScore = round($newScore, 2); // Rounds to 2 decimal places
  echo $newScore;

  if ($newScore <= 100 && $newScore >= 98) {
    $scoreLetter = 'A+';
  } else if ($newScore < 98 && $newScore >= 94) {
    $scoreLetter = 'A';
  } else if ($newScore < 94 && $newScore >= 90) {
    $scoreLetter = 'A-';
  } else if ($newScore < 90 && $newScore >= 87) {
    $scoreLetter = 'B+';
  } else if ($newScore < 87 && $newScore >= 83) {
    $scoreLetter = 'B';
  } else if ($newScore < 83 && $newScore >= 80) {
    $scoreLetter = 'B-';
  } else if ($newScore < 80 && $newScore >= 77) {
    $scoreLetter = 'C+';
  } else if ($newScore < 77 && $newScore >= 73) {
    $scoreLetter = 'C';
  } else if ($newScore < 73 && $newScore >= 70) {
    $scoreLetter = 'C-';
  } else if ($newScore < 67 && $newScore >= 67) {
    $scoreLetter = 'D+';
  } else if ($newScore < 67 && $newScore >= 63) {
    $scoreLetter = 'D';
  } else if ($newScore < 63 && $newScore >= 60) {
    $scoreLetter = 'D-';
  } else if ($newScore < 60 && $newScore >= 0) {
    $scoreLetter = 'F';
  }

  $stmt = mysqli_prepare($conn, 'UPDATE users SET score = ?, gradeletter = ? WHERE rndid = ?');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "iss", $newScore, $scoreLetter, $id);
  // Execute the statement
  mysqli_stmt_execute($stmt);

}
?>
