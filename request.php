<?php
header("Content-type:application/json");
session_start();
//echo $_GET['id'];
//echo $_GET['auth'];
//header("Location: bounties.php");
include_once '../../../unimportant.php';
include 'scripts.php';

// Create connection
$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}
// Checks to see if you've already sent this user a message to request
$query = "SELECT * FROM notifications WHERE senderid = ".$_SESSION['sessionID']." AND paperid = " . $_GET['id'] . " AND messagetype = 'r' AND showyes = 1";

$result = $conn->query($query);
$rowcount = mysqli_num_rows($result);

if ($rowcount > 0) {
  // send user that they've already sent the user a message
    //echo "<script type='text/javascript'>location.href = 'fullbounti.php?id=" . $_GET['id']."'</script>";
    //$response = 'You\'ve already requested to review this paper';
    $data = ['message' => 'You\'ve already requested to review this paper'];
    echo json_encode($data);
} else {

    $sql = "INSERT INTO notifications (sender, senderid, recipient, timestamp, paperid, papertitle, messagetype)
        VALUES ('" . $_SESSION['sessionName'] . "', " . $_SESSION['sessionID'] . ", " . $_GET['auth'] . ", " . time() . ", " .
         $_GET['id'] . ", '" . $_GET['paper'] . "', 'r')";


        if($conn->query($sql)) {
          $data = ['message' => 'Request sent'];
          echo json_encode($data);
            //echo "Hey dude it worked I think!";
            //echo $sql;
        }
    };
$conn->close();
?>
