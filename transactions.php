<?php
session_start();

include_once '../../../unimportant.php';
include 'scripts.php';

if (!($_SESSION["loggedIn"])) {
  $_SESSION['wantedPage'] = $_SERVER['REQUEST_URI'];
  echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
  exit();
} else {
  $conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);
  // Check connection
  if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
  }

  if($stmt = mysqli_prepare($conn, 'SELECT code FROM supertopsecret WHERE accountid = ?')) {
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['rndid']);
    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
      $result = $stmt->get_result();
      $userCode = $result->fetch_assoc();
      if ($_SESSION['sessionCode'] != $userCode['code']) { // Code is changed
        logOut();
        $_SESSION['wantedPage'] = $_SERVER['REQUEST_URI']; // TODO: GET THIS TO WORK
        echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
        mysqli_close($conn);
        exit();
      }
    }
  }
}

require_once '../vendor/autoload.php';


//ini_set('display_errors',1);
//error_reporting(E_ALL);
\Stripe\Stripe::setApiKey($secretKey);
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - Transactions</title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
        <script type="text/javascript">
          Stripe.setPublishableKey('<?php echo $publishableKey ?>');
        </script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>

    <body>
      <?php echoNavbar() ?>
      <?php
      $charges = \Stripe\Charge::all(array(
        "customer" => $_SESSION['custAcct'],
        'limit' => 20
      ));

      $transfers = \Stripe\Transfer::all(array(
        "destination" => $_SESSION['stripeAcct'],
        'limit' => 20
      ));

      //$balance = \Stripe\BalanceTransaction::all(array("transfer" => $_SESSION['stripeAcct']));
      //print_r($balance);
      ?>
      <div class="mainspace">

        <h1>Transaction History</h1>

        <div id="paymentMethods">
          <div class="flexRow spaceBetween alignCenter" style="width: 100%">
            <h3>To you</h3>
          </div>

          <?php
          if (!empty($transfers['data'])) {

            foreach ($transfers['data'] as $transfer) {
              $amount = $transfer['amount'];
              if ($amount < 2000) { // If it's less than 20
                $amount -= 100; // Subtract 100
                $amount /= 100; // Get it out of cents and into dollars
                $amount = number_format($amount, 2, '.', ','); // Format it correctly
              } else {
                $amount = $amount - (floor($amount / 1000) * 100); // Subtract what would be the fee
                $amount /= 100; // Get it out of cents and into dollars
                $amount = number_format($amount, 2, '.', ','); // Format it correctly
              }

              echo '
              <div class="coolBackground transaction" id="'.$transfer['application_id'].'">
                <div class="flexRow spaceBetween">
                  <div class="flexItem">
                    <p>Amount: $'.$amount.'</p>
                    <span>Status: '.$transfer['status'].' '. (!empty($transer['failure_message']) ? 'Error message: ' . $transfer['failure_message'] : '') .'</span>
                  </div>
                  <span>Created: <script type="text/javascript">convertTimestamp('.$transfer['date'].')</script></span>
                </div>
              </div>';

            }
          } else {
            echo 'There\'s nothing here';
          }
          ?>
        </div>

        <div id="paymentMethods">
          <div class="flexRow spaceBetween alignCenter" style="width: 100%">
            <h3>From you</h3>
          </div>

          <?php
          if (!empty($charges['data'])) {
            //print_r($bankAccounts);
            foreach ($charges['data'] as $charge) {
              $amount = $charge['amount'];
              $amount /= 100; // Get it out of cents and into dollars
              $amount = number_format($amount, 2, '.', ','); // Format it correctly

              echo '
              <div class="coolBackground transaction" id="'.$charge['application_id'].'">
                <div class="flexRow spaceBetween">
                  <div class="flexItem">
                    <p>Amount: $'.$amount.'</p>
                    <span>Status: '.$charge['status'].' '. (!empty($charge['failure_message']) ? 'Error message: ' . $charge['failure_message'] : '') .'</span>
                  </div>
                  <span>Created: <script type="text/javascript">convertTimestamp('.$charge['created'].')</script></span>
                </div>
              </div>';

            }
          } else {
            echo 'There\'s nothing here';
          }
          ?>
        </div>

    </div>
    <?php echoFooter() ?>
  </body>
</html>
