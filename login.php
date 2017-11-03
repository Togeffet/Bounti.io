<?php
session_start();
include 'scripts.php';

include_once '../../../unimportant.php';

$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

/* check connection */
if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

unset($_SESSION['exists']); // Gets rid of errormessage var from login screen

if($_COOKIE['loggedIn']) { // If there is a cookie
  // Prepare the statement
  if($stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?")) {
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "s", $_COOKIE['id']);
    // Execute the statement
    mysqli_stmt_execute($stmt);
    // Get the results
    $result = $stmt->get_result();

    if ($result->num_rows > 0) { // If the cookie matches a current user
        $row = $result->fetch_assoc(); // Grab the row's info
        $_SESSION["sessionID"] = $row["id"];
        $_SESSION["sessionUser"] = $row["username"];
        $_SESSION["sessionName"] = $row["fullname"];
        $_SESSION["sessionFirst"] = $row["firstname"];
        $_SESSION["sessionLast"] = $row["lastname"];
        $_SESSION["sessionEmail"] = $row["email"];
        $_SESSION["sessionGrade"] = $row["grade"];
        $_SESSION["sessionIMG"] = $row["img"];
        $_SESSION["loggedIn"] = TRUE;
        $_SESSION["collecting"] = $row["iscollecting"];
        $_SESSION['gradeLetter'] = $row['gradeletter'];
        $_SESSION['score'] = $row['score'];

        // Refresh the cookie
        setcookie("id", $row["id"], time() + (86400 * 30), "/");
        setcookie("loggedIn", TRUE, time() + (86400 * 30), "/");

        if (isset($_SESSION['wantedPage'])) {
          echo '<script type="text/javascript">location.href = "'.$_SESSION['wantedPage'].'";</script>';
        } else {
          echo '<script type="text/javascript">location.href = "bounties.php";</script>';
        }
      } else {
        echo '<script type="text/javascript">location.href = "loginpage.php?e=n";</script>';
      }
  }

} // End cookie shit


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // collect value of input field
    $username = $_REQUEST['username'];
    $pass = $_REQUEST['password'];
    $keepLogged = $_REQUEST['checkbox'];

    $hash = password_hash($pass, PASSWORD_BCRYPT);

    if($stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?")) {
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "s", $username);
      // Execute the statement
      mysqli_stmt_execute($stmt);
      // Get the results
      $result = $stmt->get_result();

      if (mysqli_num_rows($result) > 0) {

        $row = $result->fetch_assoc();

        $existingHash = $row["password"];

        if (password_verify($pass, $existingHash)) {
          if($keepLogged) {
            // Come back to this later and do this: https://stackoverflow.com/questions/1354999/keep-me-logged-in-the-best-approach
            setcookie("id", $row["id"], time() + (86400 * 30), "/");
            setcookie("loggedIn", TRUE, time() + (86400 * 30), "/");

          } else if (!$keepLogged) {
            $_SESSION["sessionID"] = $row["id"];
            $_SESSION["sessionUser"] = $row["username"];
            $_SESSION["sessionName"] = $row["fullname"];
            $_SESSION["sessionFirst"] = $row["firstname"];
            $_SESSION["sessionLast"] = $row["lastname"];
            $_SESSION["sessionEmail"] = $row["email"];
            $_SESSION["sessionGrade"] = $row["grade"];
            $_SESSION["sessionIMG"] = $row["img"];
            $_SESSION["loggedIn"] = TRUE;
            $_SESSION["collecting"] = $row["iscollecting"];
            $_SESSION['gradeLetter'] = $row['gradeletter'];
            $_SESSION['score'] = $row['score'];
          }

          if (isset($_SESSION['wantedPage'])) {
            echo '<script type="text/javascript">location.href = "'.$_SESSION['wantedPage'].'";</script>';
          } else {
            echo '<script type="text/javascript">location.href = "bounties.php";</script>';
          }
        } else {
          // Password was wrong
            echo '<script type="text/javascript">location.href = "loginpage.php";</script>';
        }
      }
    }
  }

$conn->close();
?>
