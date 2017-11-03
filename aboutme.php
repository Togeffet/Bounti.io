<?php
session_start();
include 'scripts.php';
?>
<!DOCTYPE html>
<html>

  <head>
    <meta charset="UTF-8">
    <title>Bounti.io - About me</title>
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
      <h1>About me</h1>
        <div id="aboutme">

          <div>
            <h1>Frankie Fanelli</h1>
            <br>Frank is currently a sophomore at Southern Illinois University, majoring in
            computer science. He has interned in State Farm's IT/Systems internship twice, and has made quite a few
            websites in his day. Feel free to contact him about anything! You should totally hire him, he'd make a
            great addition to your company (and I hear he makes a killer cappuccino)! Check the LinkedIn link below for
            more info. 
          </div>
          <img src="<?php echo $siteRoot ?>/img/me.jpg" />
        </div>

        <div id="contactinfo">
          <a href="mailto:franklin.fanelli@bounti.io"><img src="<?php echo $siteRoot ?>/img/email.png" /></a>
          <a href="https://www.linkedin.com/in/frank-fanelli-81102895"><img src="<?php echo $siteRoot ?>/img/linkedin.png" /></a>
          <a href="https://github.com/Togeffet"><img src="<?php echo $siteRoot ?>/img/github_small.png" /></a>
        </div>
    </div>
    <script type="text/javascript"
      src="https://d1zazrti94enmy.cloudfront.net/assets/flippaPromoBar-e2bdeab54e3ccc7e6aea1e72704704a68a5ac7a2523ffd0510897d287223d401.js"
      id="flippa-promo-bar"
      data-listing-id="9040427">
    </script>
      <?php echoFooter() ?>
  </body>
</html>
