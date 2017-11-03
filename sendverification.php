<?php
session_start();
if (!($_SESSION["loggedIn"])) {
    //echo '<script type="text/javascript">location.href = "index.php?e=n";</script>';
    echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
}
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
        <div class="mainspace" style="justify-content: center">

<?php
/*ini_set('display_errors',1);
error_reporting(E_ALL);*/

include_once '../../../unimportant.php';
require_once "../../../usr/share/php/Mail.php";
include_once '../../../usr/share/php/Mail/mime.php';

$text = "Follow the link in this email to verify your account";
$html_message = "<!DOCTYPE>
<html>
  <head>
    <meta http-equiv='content-type' content='text/html; charset=UTF-8'>
  </head>
<body style='font-family: sans-serif; color: black'>
<div style='width: 600px'>
<img src='img/bountiheader.png' style='width: 400px; height: auto; margin-left: 100px; margin-right: 100px' />
<h1 align='center' style='font-size: 32px; color: black; text-decoration: none'>Welcome to Bounti.io!</h1>
<p style='font-size: 18px'>".$_SESSION['sessionName'].", you're so close, all you need to do now is click the button below to verify your email!
Then you'll be free to begin getting your bounties reviewed or get paid
for reviewing others'.</p>
<br><br>
<table width='600px'>
<tr>
<td style='text-align: center'>
<a style='border-radius: 4px; margin-top: 10px; padding: 7px; padding-left: 12px; padding-right: 12px;
          box-shadow: 0 8px 9px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
          background-color: #E0E0E0; color: black; text-decoration: none;
          cursor: pointer; text-decoration: none; font-size: 18px' href='http://bounti.io/verify/".
          $_SESSION['sessionCode']."'>
  Verify email
</a>
</td>
</tr>
</table>
<br><br>
</div>
</body>
</html>";

$headers["From"] = 'support@bounti.io';
$headers["To"] = $_SESSION['sessionEmail'];
$to = $_SESSION['sessionEmail'];
$headers["Subject"] = "Verify your Bounti.io account";
$headers["Content-Type"] = 'text/html; charset=UTF-8';
$headers["Content-Transfer-Encoding"]= "8bit";


$mime = new Mail_mime;
$mime->addHTMLImage("img/bountiheader.png");
$mime->setTXTBody($text);
$mime->setHTMLBody($html_message);
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
  echo("<p>" . $mail->getMessage() . "</p>");
 } else {
  echo("<h1>Verification email sent</h1><h1 style='font-size: 3vmin'>Click the link in the email we just sent to ".$_SESSION['sessionEmail']."
  to complete your account. </p>");
 }
?>

</div>
<?php echoFooter()?>
</body>
</html>
