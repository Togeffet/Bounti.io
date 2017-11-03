<?php
session_start();

include 'scripts.php';
include_once '../../../unimportant.php';

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
        mysql_close($conn);
        exit();
      }
    }
  }
}

require_once '../vendor/autoload.php';


//ini_set('display_errors',1);
//error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - Add Payment Method</title>
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
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
          \Stripe\Stripe::setApiKey($secretKey);

          if ($_POST['formSubmitted'] == 'cc') { // If they're creating a credit card

            $account = \Stripe\Account::retrieve($_SESSION['stripeAcct']);
            $account->external_accounts->create(array("external_account" => $_POST['stripeToken']));

            if ($_SESSION['extAcctMade'] == 0) {
              $stmt = mysqli_prepare($conn, 'UPDATE users SET extacctmade = 1 WHERE rndid = ?');
              // Set the parameter
              mysqli_stmt_bind_param($stmt, "s", $_SESSION['rndid']);
              // Execute the statement
              mysqli_stmt_execute($stmt);

              $_SESSION['extAcctMade'] = 1;
            }

            echo '<script type="text/javascript">location.href="' . $siteRoot . '/managepaymentmethods"</script>';
            //print_r($account);
          } else if ($_POST['formSubmitted'] == 'ba') { // If they're creating a bank account
            // Something something whipcrack, behind my back, black attack

            $account = \Stripe\Account::retrieve($_SESSION['stripeAcct']);
            $account->external_accounts->create(array("external_account" => $_POST['stripeToken']));

            if ($_SESSION['extAcctMade'] == 0) {
              $stmt = mysqli_prepare($conn, 'UPDATE users SET extacctmade = 1 WHERE rndid = ?');
              // Set the parameter
              mysqli_stmt_bind_param($stmt, "s", $_SESSION['rndid']);
              // Execute the statement
              mysqli_stmt_execute($stmt);

              $_SESSION['extAcctMade'] = 1;
            }

            echo '<script type="text/javascript">location.href="' . $siteRoot . '/managepaymentmethods"</script>';

          } else {
            echo '<script type="text/javascript">showError("Something went wrong")</script>';
          }


          /*if (isset($_SESSION['stripeAcct'])) { // If the user has a stripe account
            $customer = \Stripe\Customer::retrieve($_SESSION['stripeAcct']);
            $customer->sources->create(array(
              "source" => $_POST['stripeToken'])
            );
          }*/

        } ?>
        <div class="mainspace">
          <h1 style="margin-bottom: 2vmin">Add Pay Out Method</h1>
          <p class="comingSoonBlurb" style="margin-bottom: 5vmin">Bounti.io creates a scrambled token with your information and securely sends
            it over https to our payment processor, Stripe. We don't store any payment information on our servers, so you never have to worry.</p>
          <div class="coolBackground" id="ccordc">
            <h1>Debit Card</h1>
          </div>

          <form action="<?php echo $siteRoot ?>/createpayoutmethod.php" method="POST" id="cc-form">
            <span class="payment-errors"></span>


            <!--div class="formRow">
              <div class="formItem">
                <label>Name on Card</label>
                <input type="text" size="35" class="card-holder" required>
              </div>
            </div-->


            <div class="formRow">
              <div class="formItem">
                <label>Card Number</label>
                <input type="text" size="20" class="card-number" required onchange="verifyCCNum(this.value)">
              </div>
            </div>

            <div class="formRow">
              <div class="formItem" style="width: auto">
                <label>Expiration (MM/YY)</label>
                <div class="formRow">
                  <input style="width: 5vmin" type="text" size="2" required class="card-expiry-month">
                  <p> / </p>
                    <input style="width: 5vmin" type="text" size="2" required class="card-expiry-year">
                  </div>
              </div>

              <div class="formItem" style="width: auto">
                <label>CVC</label>
                  <input style="width: 6vmin" type="text" size="4" required class="card-cvc">
              </div>
            </div>



            <input type="submit" class="submit" value="Add Card">
          </form>

          <div class="coolBackground" id="ba">
            <h1>Bank Account</h1>
          </div>
          <form action="<?php echo $siteRoot ?>/createpayoutmethod.php" method="POST" id="ba-form">

            <div class="formRow">
              <div class="formItem">
                <label>Account Holder Name</label>
                <input type="text" size="35" class="account-name" required>
              </div>
            </div>

            <div class="formRow">
              <div class="formItem">
                <label>Account Number</label>
                <input type="text" size="20" class="account-number" required>
              </div>
            </div>

            <div class="formRow">
              <div class="formItem">
                <label>Routing Number</label>
                <input type="text" size="20" class="routing-number" required>
              </div>
            </div>



            <input type="submit" class="submit" value="Add Bank Account">
          </form>



        </div>
      <?php echoFooter() ?>
</body>
