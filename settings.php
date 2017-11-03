<?php
session_start();
if (!($_SESSION["loggedIn"])) {
  $_SESSION['wantedPage'] = $_SERVER['REQUEST_URI'];
  echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
}
include_once '../../../unimportant.php';
include 'scripts.php';

$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

// Check connection
if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

$stmt = mysqli_prepare($conn, 'SELECT * FROM users WHERE rndid = ?');
// Set the parameter
mysqli_stmt_bind_param($stmt, "s", $_SESSION['rndid']);
// Execute the statement
mysqli_stmt_execute($stmt);
// Get the results
$result = $stmt->get_result();

$user = $result->fetch_assoc();

$first = $user['firstname'];
$last = $user['lastname'];
$email = $user['email'];
$img = $user['img'];
$month = substr($user['dob'], 0, 2);
$day = substr($user['dob'], 3, 2);
$year = substr($user['dob'], 6, 4);
$checked = $user['emailnotif'];

?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - Settings</title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery.form.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
        <script>var page = "settings"</script>
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>
    <body>
      <?php echoNavbar() ?>


        <div class="mainspace">

          <h1 style="margin-bottom: 4vmin">Settings</h1>
          <div class="coolBackground" id="name">
            <h1>Name</h1>
          </div>
          <form method="POST" id="name-form">
            <input type="hidden" name="settingsupdate" value="name" />

            <div class="formRow">
              <div class="formItem">
                <label>First</label>
                <input type="text" size="35" name="first" placeholder="<?php echo $first ?>" />
              </div>
            </div>
            <div class="formRow">
              <div class="formItem">
                <label>Last</label>
                <input type="text" size="35" name="last" placeholder="<?php echo $last ?>" />
              </div>
            </div>



            <input type="submit" class="submit" value="Update Name">
          </form>


          <div class="coolBackground" id="email">
            <h1>Email</h1>
          </div>
          <form method="POST" id="email-form">
            <input type="hidden" name="settingsupdate" value="email" />

            <div class="formRow">
              <div class="formItem">
                <label>Email</label>
                <input type="text" size="35" name="email" placeholder="<?php echo $email ?>" />
              </div>
            </div>

            <div id="checkboxandlabel">
              <p>Email Notifications:</p>
              <input type="hidden" name="notif-checkbox" value='0' />
              <input id='checkbox' type='checkbox' name='notif-checkbox' value='1' <?php echo $checked==1 ? 'checked' : '' ?>>
              <label for='checkbox'></label>
            </div>

            <input type="submit" class="submit" value="Update Email">
          </form>

          <div class="coolBackground" id="dob">
            <h1>Date of Birth</h1>
          </div>
          <form method="POST" id="dob-form">
            <input type="hidden" name="settingsupdate" value="name" />

            <label for="month" style="width: 100%; text-align: left;">Date of birth</label>
            <div class="formRow" style="margin-top: 1vmin">

              <div class="formItem">
                <label for="month" style="font-size: 1.5vmin">MM</label>
                <input name="month" id="month" type="text" maxlength="2" placeholder="<?php echo $month  ?>" required />
              </div>
              <div class="formItem">
                <label for="day" style="font-size: 1.5vmin">DD</label>
                <input name="day" id="day" type="text" maxlength="2" placeholder="<?php echo $day  ?>" required />
              </div>
              <div class="formItem" style="width: auto">
                <label for="year" style="font-size: 1.5vmin; width: 6vmin">YYYY</label>
                <input name="year" id="year" type="text" maxlength="4" placeholder="<?php echo $year  ?>" required />
              </div>
            </div>

            <input type="submit" class="submit" value="Update Birthday">
          </form>

          <div class="coolBackground" id="passWord">
            <h1>Password</h1>
          </div>

          <form method="POST" id="passWord-form">
            <input type="hidden" name="settingsupdate" value="password" />

            <div class="formRow">
              <div class="formItem">
                <label>Current Password</label>
                <input type="password" size="35" name="currentpass" required />
              </div>
            </div>

            <div class="formRow">
              <div class="formItem">
                <label>New Password<img id="passwordOkayYes" /><div id="validAccountDetails"></div></label>
                <input type="password" size="20" name="newpass" id="changepassword" required />
              </div>
            </div>

            <input id="passSubmit" type="submit" class="submit" disabled value="Update Password">
          </form>

          <div class="coolBackground" id="profpic">
            <h1>Profile Picture</h1>
          </div>
          <form method="POST" enctype="multipart/form-data" id="profpic-form">
            <input type="hidden" name="settingsupdate" value="profpic" />
            <div class="formRow">
              <div class="formItem">
                <label for='img'>Profile Picture</label>
                <input type="hidden" name="MAX_FILE_SIZE" value="16777215" />
                <input id="img" name="userfile" type="file" />
              </div>
            </div>

            <input type="submit" class="submit" value="Update Profile Picture">
          </form>

          <p class="text"><a style="color: black" href="<?php echo $siteRoot ?>/managepaymentmethods">Manage Payment Methods</a></p>
          <p class="text" style="margin-top: 2vmin; margin-bottom: 4vmin"><a style="color: black" href="<?php echo $siteRoot ?>/transactions">Transactions</a></p>




</div>
            <?php echoFooter(); ?>

    </body>
</html>
