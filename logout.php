<?php
session_start();
include '../../../unimportant.php';
header("Location: " . $siteRoot . "/index");

$id = $_SESSION['rndid'];

if($stmt = mysqli_prepare($conn, 'UPDATE supertopsecret SET code = NULL WHERE accountid = ?')) {
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "s", $id);
  // Execute the statement
  if (mysqli_stmt_execute($stmt)) {
    $_SESSION = array(); // Clears all session variables
  }
}



$past = time() - 3600; // Clears all cookies
foreach ( $_COOKIE as $key => $value ) {
    setcookie( $key, $value, $past, '/' );
}
 ?>
