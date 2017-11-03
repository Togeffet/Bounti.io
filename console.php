<?php
  session_start();

  include 'scripts.php';
  include_once '../../../unimportant.php';

  if ($_SESSION['rndid'] != "user_58a7c1f501628") {
    echo 'You\'re not Master Franklin, you shouldn\'t be here';
    echo '<script type="text/javascript">location.href = "' . $siteRoot . '/bounties";</script>';
    exit();
  } else {

  // Create connection
  $conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);
  // Check connection
  if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
  }

  $stmt = mysqli_prepare($conn, 'SELECT username, fullname, img FROM users ORDER BY id DESC LIMIT 1000');
  // Set the parameter
  mysqli_stmt_bind_param($stmt, "s", $address);
  // Execute the statement
  mysqli_stmt_execute($stmt);
  // Get the results
  $result = $stmt->get_result();
  echo 'Hello, Master Franklin. It\'s good to see you. There are now ' . mysqli_num_rows($result) . ' users.';
  echo '<table>';
  while ($row = $result->fetch_assoc()) {
    echo '<tr>
            <td><a href="'.$siteRoot.'/account/'.$row['username'].'/">' . $row['username'] . '</a></td>
            <td>' . $row['fullname'] . '</td>
            <td>' . $row['img'] . '</td>
          </tr>';
  }
  echo '</table>';

}

 ?>
