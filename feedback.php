<?php
session_start();
if (!($_SESSION["loggedIn"])) {
  $_SESSION['wantedPage'] = $_SERVER['REQUEST_URI'];
  echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
}
include 'scripts.php';

//ini_set('display_errors',1);
//error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html>

  <head>
    <meta charset="UTF-8">
    <title>Bounti.io - Feedback</title>
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
    <?php
    if($_SERVER["REQUEST_METHOD"] == "POST") {
      //include_once '../vendor/stripe/stripe-php/init.php';
      include '../../../unimportant.php';

      require_once "../../../usr/share/php/Mail.php";
      include_once '../../../usr/share/php/Mail/mime.php';

      $text = $_POST['body'] . "\n\n\nSent by: " . $_SESSION['sessionUser'] . ' (' . $_SESSION['rndid'] . ')';

      $headers["From"] = 'support@bounti.io';
      $headers["To"] = 'franklin.fanelli@bounti.io';
      $to = 'franklin.fanelli@bounti.io';
      $headers["Subject"] = $_POST['subject'];
      $headers["Content-Type"] = 'text/html; charset=UTF-8';
      $headers["Content-Transfer-Encoding"]= "8bit";


      $mime = new Mail_mime;
      $mime->setTXTBody($text);
      $mimeparams=array();


      // It refused to change to UTF-8 even if the header was set to this, after adding the following lines it worked.

      $mimeparams['text_encoding']="8bit";
      $mimeparams['text_charset']="UTF-8";
      $mimeparams['html_charset']="UTF-8";
      $mimeparams['head_charset']="UTF-8";


      $body = $mime->get($mimeparams);
      $headers = $mime->headers($headers);
      $page_content = "Mail now.";

      $host = "email-smtp.us-east-1.amazonaws.com";
      $port = "587";

      // SMTP server name, port, user/passwd
      $smtpinfo["host"] = $host;
      $smtpinfo["port"] = $port;
      $smtpinfo["auth"] = true;
      $smtpinfo["username"] = $emailUser;
      $smtpinfo["password"] = $emailPass;


      // Create the mail object using the Mail::factory method
      $mail = Mail::factory('smtp', $smtpinfo);

      $mail->send($to, $headers, $body);

      if (PEAR::isError($mail)) {
        $message = 'Something went wrong :(';
        $data = ['message' => 'Something went wrong', 'sent' => false];
      } else {
        $message = 'Thank you! :)';
        $data = ['message' => 'Thank you! :)', 'sent' => true];
      }
      echo '<script>showError("'.$message.'")</script>';
        //echo json_encode($data);

    }




      /*\Stripe\Stripe::setApiKey("sk_test_hwpMAb6MjFsGXyIwkZAazB04");

      \Stripe\Account::create(array(
        "managed" => true,
        "country" => "US",
        "email" => "bob@example.com"
      ));*/


    ?>
    <div class="mainspace">
      <h1>Feedback</h1>
      <p class="feedbackBlurb">Any comments, questions, concerns, bugs you've found, or ideas you'd like to share?
      I strive to make this something that gets used, so please, let me hear what you have to say!</p>

      <form name="feedback" action="<?php echo $siteRoot ?>/feedback.php" method='post' accept-charset='UTF-8'>

        <div class="formRow">
          <div class="formItem">
            <label for="subject">Subject</label>
            <input type="text" name="subject"></input>
          </div>
        </div>

        <div class="formRow">
          <div class="formItem">
            <label for="body">Body</label>
            <textarea name="body" rows="8"></textarea>
          </div>
        </div>

        <input id='submit' type='submit' name='Submit' value='Submit' />

      </form>

    </div>
    <?php echoFooter() ?>
  </body>
</html>
