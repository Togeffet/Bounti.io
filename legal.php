<?php
session_start();
include 'scripts.php';
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Legal</title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>
    <body>
      <?php echoNavbar() ?>
      <div class="mainspace">
          <h1>Legal</h1>
          <a href="<?php echo $siteRoot ?>/termsandconditions.html" style="margin-top: 5vmin"><div class="coolBackground" style="width: 60vmin; text-align: center"><h1 style="font-size: 3vmin; cursor: pointer">Terms and Conditions</h1></div></a><br>
          <a href="<?php echo $siteRoot ?>/privacypolicy.html"><div class="coolBackground" style="width: 60vmin; text-align: center"><h1 style="font-size: 3vmin; cursor: pointer">Privacy Policy</h1></div></a>
      </div>
      <?php echoFooter() ?>
    </body>
</html>
