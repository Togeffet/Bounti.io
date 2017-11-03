<?php
session_start();
include 'scripts.php';
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - 404 Error</title>
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
          <h1 style="position: relative; bottom: 7vmin">404 not found :(</h1>
          <a href="<?php echo $siteRoot ?>/bounties" style="margin-top: 5vmin"><div class="coolBackground" style="width: 60vmin; text-align: center"><h1 style="font-size: 3vmin; cursor: pointer">Click here to go back home</h1></div></a>
      </div>
      <?php echoFooter() ?>
    </body>
</html>
