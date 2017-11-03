<?php
session_start();
if (!($_SESSION["loggedIn"])) {
    echo '<script type="text/javascript">location.href = "loginpage.php";</script>';
};
include_once '../../../unimportant.php';

// Create connection
$conn = new mysqli($dblocation, $dbuser, $dbpass, $dbname);



if($_GET['s'] == 'a') { // If message declined is an acceptance message
    $sql = "INSERT INTO notifications (sender, recipient, timestamp, papertitle, paperid, messagetype, senderid)
            VALUES ('".$_SESSION['sessionName']."', ".$_GET['id'].", " . time() .", '".$_GET['title']."', ".$_GET['papid'].", 'da', ".$_SESSION['sessionID'].")";
    $conn->query($sql); // Create message back to writer
    $sql = "UPDATE bounties SET reviewer = NULL WHERE id = " . $_GET['papid'];
    $conn->query($sql);
    $sql = "UPDATE notifications SET showyes = 0 WHERE id = " . $_GET['m'];
    $conn->query($sql); // Delete message
    echo '<p id="message">This worked</p>';
} else if($_GET['s'] == 'r') { // If the message is requesting to review a paper
    $sql = "INSERT INTO notifications (sender, recipient, timestamp, papertitle, paperid,
    messagetype, senderid)
            VALUES ('".$_SESSION['sessionName']."', ".$_GET['id'].", ". time()
            .", '".$_GET['title']."', ".$_GET['paper'].", 'dr',
            ".$_SESSION['sessionID'].")";
    $conn->query($sql); // Send message saying they declined reviewer

    $sql = "UPDATE notifications SET showyes = 0 WHERE id = " . $_GET['m'];
    $conn->query($sql); // Delete message

    echo '<script type="text/javascript">location.href = "notifications.php";</script>';
} else if($_GET['s'] == 'd') { // If the message is the reviewer declining to review
    $sql = "UPDATE notifications SET showyes = 0 WHERE id = " . $_GET['m'];
    $conn->query($sql); // Delete message
    echo '<script type="text/javascript">location.href = "notifications.php";</script>';
} else if($_GET['x']) {
  $sql = "UPDATE notifications SET showyes = 0 WHERE id = " . $_GET['x'];
  $conn->query($sql);
  echo '<script type="text/javascript">location.href = "notifications.php?e=d";</script>';
}

$conn->close();
 ?>
