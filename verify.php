<?php
session_start();
include_once '../../../unimportant.php';
include 'scripts.php';

$id = $_GET['id'];
$verified = '';
?>


<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - Verify Account</title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>

    <body>
      <?php
      $conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

      if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
      }
      $stmt = mysqli_prepare($conn, "SELECT accountid, email FROM supertopsecret WHERE code = ?");
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "s", $_GET['id']);
      // Execute the statement
      mysqli_stmt_execute($stmt);

      $result = $stmt->get_result();
      $row = $result->fetch_assoc();

      if($stmt = mysqli_prepare($conn, "UPDATE users SET verified = 1 WHERE rndid = ? AND email = ?")) {
        // Set the parameter
        mysqli_stmt_bind_param($stmt, "ss", $row['accountid'], $row['email']);
        // Execute the statement
        if(mysqli_stmt_execute($stmt)) {
          $verified = true;
        }
      }
      ?>
        <?php echoNavbar() ?>
        <div class="mainspace" style="justify-content: center">

          <?php
          if ($verified) {
            echo '<h1 style="position: relative; bottom: 7vmin">You are verified!</h1>';


          }
          ?>



        </div>
      </div>
        <?php echoFooter() ?>
      </body>
      </html>
