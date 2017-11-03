<?php
session_start();
include 'scripts.php';
include_once '../../../unimportant.php';
$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - Verify Your Account</title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>

    <body>
        <?php echoNavbar() ?>
        <div class="mainspace">
          <h1>Coming Soon</h1>
          <p class="comingSoonBlurb">Have an idea for a feature you'd like to see added? Submit it using the
           <a href="<?php echo $siteRoot?>/feedback">feedback form</a>.</p>

          <div id="paymentMethods" class="comingSoonDiv">
            <div class="flexColumn spaceBetween alignCenter" style="width: 100%">

              <div class="coolBackground comingSoon noHover">
                  <p>Lowered minimum reward amount</p>
              </div>

              <div class="coolBackground comingSoon noHover">
                  <p>Bitcoin payments</p>
              </div>

              <div class="coolBackground comingSoon noHover">
                  <p>A help/FAQ page</p>
              </div>

            </div>
          </div>

        </div>
        <?php echoFooter()?>
    </body>
</html>
