<?php
session_start(); // Get session variables
include 'scripts.php';
if($_SESSION["loggedIn"]) { // If the user is logged in, take them to bounties
    echo '<script type="text/javascript">location.href = "' . $siteRoot . '/bounties";</script>';
} else if ($_COOKIE['loggedIn']) {
    echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
}
?>
<!DOCTYPE html>
<html>

    <head>
        <meta name="verifyownership" content="ac5d8be5074ee949067588c356832732"/>
        <meta charset="UTF-8">
        <title>Bounti.io - Welcome</title>
        <meta name="description" content="Looking for someone to proofread your paper? Choose your price.
        Good English skills? Get paid to review papers. Sign up to become a Bounti Hunter today.">
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
        <script>page = "index"</script>
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>

    <body>

        <?php echoNavbar() ?>

        <div class="mainspace homepage">
            <h1>Get your paper reviewed.</h1>
            <h1>Choose your price.</h1>
            <img src="<?php echo $siteRoot ?>/img/placeholder_paper.png" class="indexPicture" />

            <h1>Good English skills?</h1>
            <h1>Get paid to review papers.</h1>



        </div>

        <?php echoFooter() ?>

    </body>
</html>
