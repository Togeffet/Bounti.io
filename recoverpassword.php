<?php

/*if ($_SESSION["loggedIn"]) {
  if (isset($_SESSION['wantedPage'])) {
    echo '<script type="text/javascript">location.href = "'.$_SESSION['wantedPage'].'";</script>';
  } else {
    echo '<script type="text/javascript">location.href = "bounties.php";</script>';
  }
}*/
include 'scripts.php';

//ini_set('display_errors',1);
//error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - Recover Password</title>
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
        <div class="mainspace">
          <h1>Recover Password</h1>
          <div class="centerme" style="width: 50vmin; margin-top: 6vmin">
            <p style="font-family: 'Roboto',sans-serif; font-weight: 300; text-align: center">We'll send you a link to recover your password.</p>
          </div>
          <form onsubmit="recoverPass(event)" id="recoverPassForm">
            <div class="formRow">
              <div class="formItem">
                <label>Account Email Address</label>
                <input type="text" class="emailToSendTo" />
              </div>
            </div>
          <input type="submit" class="submit" value="Send email" style="position: relative; top: 1vmin" />
        </form>

        </div>
        <?php echoFooter() ?>
      </body>
    </html>
