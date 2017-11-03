<?php
session_start();
header('Location: https://bounti.io/bountisuccessful');

//ini_set('display_errors',1);
//error_reporting(E_ALL);
if (!($_SESSION["loggedIn"])) {
  echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
}
include_once '../../../unimportant.php';
include 'scripts.php';
require 'functions.php';

// Create connection
$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);
// Test connection
if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  require_once '../vendor/autoload.php';

  $rndid = uniqid('finished_');
/*
  // Prepare statement
  $stmt = mysqli_prepare($conn, );
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "", );
  // Execute the statement
  mysqli_stmt_execute($stmt);
*/

  // Prepare statement
  $stmt = mysqli_prepare($conn, 'SELECT U.rndid AS authorid, B.rndid AS bountiid, B.total, B.fee, U.custacct, U.email, U.rndid, U.fullname
    FROM bounties AS B
    INNER JOIN users AS U ON U.rndid = B.authorid
    WHERE B.rndid = ? AND reviewer = ?');

  // Set the parameter
  mysqli_stmt_bind_param($stmt, "ss", $_POST["id"], $_SESSION['rndid']);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  $result = $stmt->get_result();

  $row = $result->fetch_assoc();

  $time = time();
  $messageType = 'f';

  // Prepare statement
  $stmt = mysqli_prepare($conn, 'INSERT INTO finished (rndid, authorid, reviewerid, paperid, comments, timestamp)
          VALUES (?, ?, ?, ?, ?, ?)');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "sssssi", $rndid, $row['authorid'], $_SESSION['rndid'], $row['bountiid'], $_POST['comments'], $time);
  // Execute the statement
  mysqli_stmt_execute($stmt);

  $uploaddir ='/var/www/documents/' . $row['bountiid'] . '/revised/'; // Directory where files are saved
  $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

  //$uploaddir = '/revised/' . $rndid . '/'; // Directory where files are saved
  //$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

  if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {

    exec('doc2pdf "' . $uploadfile .'" 2>&1', $output);
    print_r($output);

    $info = new SplFileInfo($uploadfile);
    $ext = $info->getExtension();

    $rawfilename = basename($uploadfile, ("." . $ext));

    $paper_file = $rawfilename . ".pdf";
    $save_to = $uploaddir . $rndid . '.jpg';

    $img = new imagick();
    $img->setResolution(150,150);
    $img->readImage($uploaddir . $paper_file . '[0]');
    //set new format
    $img->setImageFormat('jpg');

    //save image file
    $img->writeImage($save_to);

    rename($uploadfile, ($uploaddir . $rndid . '.docx'));
    unlink($uploaddir . $paper_file);


    // Create the charge
    \Stripe\Stripe::setApiKey($secretKey);

    $charge = \Stripe\Charge::create(array(
      'customer' => $row['custacct'],
      'amount' => $row['total'],
      'application_fee' => $row['fee'],
      'currency' => 'usd',
      'destination' => $_SESSION['stripeAcct'],
      'description' => $row['email'] . ' (' . $row['rndid'] . ') to ' . $_SESSION['sessionEmail'] . ' (' . $_SESSION['rndid'] .') at ' . time(),
      'metadata' => array('To' => $_SESSION['sessionName'], 'From' => $row['fullname'])
    ));

    //echo 'ID: ' . $charge['id'] . "\n" . 'Amount: ' .$charge['amount']. "\n" . 'Fee: ' . $charge['application_fee'] . "\n" .'Customer: ' . $charge['customer'];
    //print_r($charge);

  }
}

$rndid = uniqid('notif_');

// Prepare statement
$stmt = mysqli_prepare($conn, 'INSERT INTO notifications (rndid, senderid, recipient, timestamp, paperid, messagetype)
VALUES (?, ?, ?, ?, ?, ?)');
// Set the parameter
mysqli_stmt_bind_param($stmt, "sssiss", $rndid, $_SESSION['rndid'], $row['authorid'], $time, $row['bountiid'], $messageType);
// Execute the statement
mysqli_stmt_execute($stmt);

// Prepare statement
$stmt = mysqli_prepare($conn, 'UPDATE bounties SET success = 1 WHERE rndid = ?');
// Set the parameter
mysqli_stmt_bind_param($stmt, "s", $row['bountiid']);
// Execute the statement
mysqli_stmt_execute($stmt);

$messageType = 'a';

// Prepare statement
$stmt = mysqli_prepare($conn, 'UPDATE notifications
  SET showyes = 0
  WHERE paperid = ? AND senderid = ? AND recipient = ? AND messagetype = ?');
// Set the parameter
mysqli_stmt_bind_param($stmt, "ssss", $row['bountiid'], $row['authorid'], $_SESSION['rndid'], $messageType);
// Execute the statement
mysqli_stmt_execute($stmt);

sendEmail($row['email'], 'cpl', $row['bountiid']);

unset($_SESSION['collecting']);

$conn->close();

//echo '<script type="text/javascript">location.href = "' . $siteRoot . '/bountisuccessful";</script>';
 ?>
