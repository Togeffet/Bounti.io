<?php

if ($_SESSION["loggedIn"]) {
  if (isset($_SESSION['wantedPage'])) {
    echo '<script type="text/javascript">location.href = "'.$_SESSION['wantedPage'].'";</script>';
  } else {
    echo '<script type="text/javascript">location.href = "' . $siteRoot . '/bounties";</script>';
  }
}
include 'scripts.php';

//ini_set('display_errors',1);
//error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - Reset Password</title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery.form.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
        <script>var page = "resetpassword"</script>
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>

    <body>

        <?php echoNavbar() ?>
        <div class="mainspace">
          <h1>Recover Password</h1>

          <form id="resetPass" style="margin-top: 6vmin">
            <input type="hidden" value="<?php echo $_GET['id']; ?>" name="id" />
              <div class="formRow">
                <div class="formItem">
                  <label for="newpass">New password<img id="passwordOkayYes" /><div id="validAccountDetails"></div></label>
                  <input type="password" id="newpassword" name="newpass" required />
                </div>
              </div>
              <div class="formRow">
                <div class="formItem">
                  <label for="newpass2">Retype new password</label>
                  <input type="password" name="newpass2" required />
                </div>
              </div>
          <input type="submit" id="submit" value="Update password" style="position: relative; top: 1vmin" disabled />
        </form>

        </div>
        <?php echoFooter() ?>
      </body>
    </html>
