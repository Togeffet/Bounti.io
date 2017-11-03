<?php
session_start();
include 'scripts.php';
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Help - Bounti.io</title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <!--<link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>-->
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <!--<script type="text/javascript" src="js/materialize.min.js"></script>-->
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>

    <body>
      <?php echoNavbar() ?>
      <div class="mainspace" style="justify-content:center">
          <h1 style="position: relative; bottom: 7vmin">Coming soon&#8482;</h1>
      </div>

      <?php echoFooter() ?>
    </body>
</html>
