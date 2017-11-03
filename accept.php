<?php
session_start();
include_once '../../../unimportant.php';

// Create connection
$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);
// Test connection
if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}


$sql = "INSERT INTO notifications (sender, senderid, recipient, timestamp, paperid, papertitle, messagetype)
        VALUES ('" . $_SESSION['sessionName'] . "', " . $_SESSION['sessionID'] . ", " . $_GET['id'] . ", " .time().", " . $_GET['paper'] . ", '" . $_GET['title'] . "', 'a')";


if($conn->query($sql)) {
    echo $sql;
    $sql = "UPDATE bounties SET reviewer = " . $_GET['id'] . " WHERE id = " . $_GET['paper'];
    $conn->query($sql); // Set reviewer in the paper entry
    $sql = "UPDATE notifications SET showyes = 0, messagetype = 'ar' WHERE id = " . $_GET['m'];
    $conn->query($sql);
    echo $sql; // Delete this message
    $sql = "UPDATE users SET iscollecting = ".$_GET['paper']." WHERE id = " . $_GET['id'];
    $conn->query($sql); // Say the user is collecting bounti rn
    echo "Hey dude it worked I think!";
    echo $sql;
    // See if there's already a convo between them
    $sql = "SELECT * FROM conversations WHERE (person1 = ".$_GET['id']." AND person2 = ".$_SESSION['sessionID'].") OR (person2 = ".$_GET['id']." AND person1 = ".$_SESSION['sessionID'] .")";
    $result = $conn->query($sql);
    echo $sql;
    echo 'WHY IS THIS NOT MORE THAN 1 ->>>>>>' . mysqli_num_rows($result);
    if (mysqli_num_rows($result) > 0) {
      // Don't create conversation because there is already one
    } else {
      $sql = "INSERT INTO conversations (person1, person2, timestamp) VALUES (". $_SESSION['sessionID'] .", ". $_GET['id'] .", ".time().")";
      $conn->query($sql); // Create conversation between the two people
      echo $sql;
    }
} else {
    echo "Something went wrong";
    echo $sql;
};
$conn->close();
?>
