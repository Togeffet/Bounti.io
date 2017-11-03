<?php
session_start();
include 'scripts.php';

if (!($_SESSION["loggedIn"])) {
  if (!($_SESSION["loggedIn"])) {
    $_SESSION['wantedPage'] = $_SERVER['REQUEST_URI'];
    echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
  }
}

?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - Bounti Successful</title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>
    <body>
      <?php echoNavbar() ?>
      <div class="mainspace" style="justify-content:center">
          <h1>Bounti completed!</h1>
          <div style="width: 100vmin">
            <h1 style="font-size: 4vmin">Well done, partner! If the author finds no trouble with your work, your reward will be deposited in your default account over the next few days.</h1>
          </div>
          <a href="<?php echo $siteRoot ?>/transactions" style="margin-top: 5vmin"><div class="coolBackground" style="width: 60vmin; text-align: center"><h1 style="font-size: 3vmin; cursor: pointer">Transaction history</h1></div></a>
          <a href="<?php echo $siteRoot ?>/managepaymentmethods" style="margin-top: 3vmin"><div class="coolBackground" style="width: 60vmin; text-align: center"><h1 style="font-size: 3vmin; cursor: pointer">Manage payment methods</h1></div></a><br>
      </div>
      <?php echoFooter() ?>
    </body>
</html>
