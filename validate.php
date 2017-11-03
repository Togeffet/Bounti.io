<?php header('Content-Type: application/json');
session_start();


function userExists() {
  $userInput = $_GET['user'];
    $servername = "localhost";
    $username = "root";
    $password = "loolyhead";
    $dbname = "usersnew";

    $conn = new mysqli($servername, $username, $password, $dbname);

      $sql = "SELECT * FROM users WHERE username = '" . $userInput . "'";

      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
        echo $userInput;
        echo 'This exists';
        echo $result->num_rows;
        echo $sql;
        $_SESSION['exist'] = 'Yes';
    } else {
      echo 'It doesn\'t wtf';
      echo $result->num_rows;
      echo $sql;
      echo $userInput;
      $_SESSION['exist'] = 'No';
    }
}
?>
<div><?php userExists() ?></div>
