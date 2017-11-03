<?php
include 'scripts.php';
if ($_SESSION["loggedIn"]) {
  if (isset($_SESSION['wantedPage'])) {
    echo '<script type="text/javascript">location.href = "'.$_SESSION['wantedPage'].'";</script>';
    unset($_SESSION['wantedPage']);
  } else {
    echo '<script type="text/javascript">location.href = "'.$siteRoot.'/bounties";</script>';
  }
}


//ini_set('display_errors',1);
//error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - Login</title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>

    <body>

        <?php echoNavbar() ?>
        <?php
        include_once '../../../unimportant.php';

        $conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

        /* check connection */
        if (mysqli_connect_errno()) {
          printf("Connect failed: %s\n", mysqli_connect_error());
          exit();
        }

        if($_COOKIE['loggedIn']) { // If there is a cookie
          // Prepare the statement
          if($stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE rndid = ?")) {
            // Set the parameter
            mysqli_stmt_bind_param($stmt, "s", $_COOKIE['rndid']);
            // Execute the statement
            mysqli_stmt_execute($stmt);
            // Get the results
            $result = $stmt->get_result();

            if ($result->num_rows > 0) { // If the cookie matches a current user
                $row = $result->fetch_assoc(); // Grab the row's info
                $_SESSION["sessionID"] = $row["id"];
                $_SESSION['rndid'] = $row['rndid'];

                $_SESSION["sessionUser"] = $row["username"];
                $_SESSION["sessionName"] = $row["fullname"];
                $_SESSION["sessionFirst"] = $row["firstname"];
                $_SESSION["sessionLast"] = $row["lastname"];
                $_SESSION["sessionEmail"] = $row["email"];

                $_SESSION["sessionGrade"] = $row["grade"];
                $_SESSION['sessionGradeNum'] = $row['gradenum'];

                $_SESSION["sessionIMG"] = $row["img"];
                $_SESSION["loggedIn"] = TRUE;
                $_SESSION["collecting"] = $row["iscollecting"];


                $_SESSION['gradeLetter'] = $row['gradeletter'];
                $_SESSION['score'] = $row['score'];

                $_SESSION['stripeAcct'] = $row['stripeacct'];
                $_SESSION['custAcct'] = $row['custacct'];

                $_SESSION['extAcctMade'] = $row['extacctmade'];
                $_SESSION['payMethodMade'] = $row['paymethodmade'];

                $token = bin2hex(random_bytes(12));

                $stmt = mysqli_prepare($conn, 'UPDATE supertopsecret SET code = ? WHERE accountid = ?');
                // Set the parameter
                mysqli_stmt_bind_param($stmt, "ss", $token, $_SESSION['rndid']);
                // Execute the statement
                mysqli_stmt_execute($stmt);

                $_SESSION['sessionCode'] = $token;

                // Refresh the cookie
                setcookie("rndid", $row["rndid"], time() + (86400 * 30), "/");
                setcookie("loggedIn", TRUE, time() + (86400 * 30), "/");

                if (isset($_SESSION['wantedPage'])) {
                  echo '<script type="text/javascript">location.href = "'.$_SESSION['wantedPage'].'";</script>';
                  unset($_SESSION['wantedPage']);
                  exit();
                } else {
                  echo '<script type="text/javascript">location.href = "'.$siteRoot.'/bounties";</script>';
                }
              } else {
                echo '<script type="text/javascript">showError("Something went wrong")</script>';
              }
          }

        } // End cookie shit


        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // collect value of input field
            $username = $_POST['username'];
            $pass = $_POST['password'];

            if (isset($_POST['checkbox'])) {
              $keepLogged = $_POST['checkbox'];
            } else {
              $keepLogged = false;
            }

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
                    setcookie("rndid", $row["rndid"], time() + (86400 * 30), "/");
                    setcookie("loggedIn", TRUE, time() + (86400 * 30), "/");
                  }

                    $_SESSION["sessionID"] = $row["id"];
                    $_SESSION['rndid'] = $row['rndid'];

                    $_SESSION["sessionUser"] = $row["username"];
                    $_SESSION["sessionName"] = $row["fullname"];
                    $_SESSION["sessionFirst"] = $row["firstname"];
                    $_SESSION["sessionLast"] = $row["lastname"];
                    $_SESSION["sessionEmail"] = $row["email"];
                    $_SESSION["sessionGrade"] = $row["grade"];
                    $_SESSION['sessionGradeNum'] = $row['gradenum'];
                    $_SESSION["sessionIMG"] = $row["img"];
                    $_SESSION["loggedIn"] = TRUE;
                    $_SESSION["collecting"] = $row["iscollecting"];
                    $_SESSION['gradeLetter'] = $row['gradeletter'];
                    $_SESSION['score'] = $row['score'];

                    $_SESSION['stripeAcct'] = $row['stripeacct'];
                    $_SESSION['custAcct'] = $row['custacct'];

                    $_SESSION['extAcctMade'] = $row['extacctmade'];
                    $_SESSION['payMethodMade'] = $row['paymethodmade'];

                    $token = bin2hex(random_bytes(12));

                    $stmt = mysqli_prepare($conn, 'UPDATE supertopsecret SET code = ? WHERE accountid = ?');
                    // Set the parameter
                    mysqli_stmt_bind_param($stmt, "ss", $token, $_SESSION['rndid']);
                    // Execute the statement
                    mysqli_stmt_execute($stmt);

                    $_SESSION['sessionCode'] = $token;



                  if (isset($_SESSION['wantedPage'])) {
                    echo '<script type="text/javascript">location.href = "'.$_SESSION['wantedPage'].'";</script>';
                    unset($_SESSION['wantedPage']);
                    exit();
                  } else {
                    echo '<script type="text/javascript">location.href = "'.$siteRoot.'/bounties";</script>';
                  }
                } else {
                  // Password was wrong

                    echo '<script type="text/javascript">
                    showError("Incorrect password");
                    document.getElementById("username").value = "'.$username.'";</script>';
                }
              } else { // Username doesn't exist
                echo '<script type="text/javascript">showError("Username doesn\'t exist")</script>';
              }
            }
          }

        $conn->close();
         ?>

        <div class="mainspace">
            <h1>Login</h1>
          <form action='<?php echo $siteRoot ?>/loginpage.php' method='post' accept-charset='UTF-8'>
            <div class="formRow">
              <div class="formItem">
                <label for='username'>Username:</label>
                <input type='text' name='username' id='username' autofocus maxlength="50" onchange="usernameCheck(document.getElementById('username').value)" required />
              </div>
            </div>

            <div class="formRow">
              <div class="formItem">
                <label for='password'>Password:</label>
                <input type='password' name='password' id='password' maxlength="50" required />
              </div>
            </div>

                <div id="checkboxandlabel">
                  <p>Keep me logged in:</p>
                  <input id='checkbox' type='checkbox' name='checkbox' value='yes'>
                  <label for='checkbox'></label>
                </div>


          <input id='submit' type='submit' name='Submit' value='Submit' />


          </form>
          <a class="regLink" href="<?php echo $siteRoot ?>/recoverpassword">Forgot your password?</a>
        </div>
        <?php echoFooter() ?>

      </body>
  </html>
