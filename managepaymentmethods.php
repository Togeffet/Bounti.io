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
\Stripe\Stripe::setApiKey($secretKey);

?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - Manage Payment Methods</title>
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
      if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['amount1']) && isset($_POST['amount2']) && isset($_POST['accountid'])) {
        $amount1 = $_POST['amount1'];
        $amount2 = $_POST['amount2'];

        $customer = \Stripe\Customer::retrieve($_SESSION['custAcct']);
        $bank_account = $customer->sources->retrieve($_POST['accountid']);

        // verify the account
        $bank_account->verify(array('amounts' => array($amount1, $amount2)));

        if ($bank_account->data->status == 'verified') {
          if ($_SESSION['payMethodMade'] == 0) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET paymethodmade = 1 WHERE rndid = ?");
            // Set the parameter
            mysqli_stmt_bind_param($stmt, "s", $_SESSION['rndid']);
            // Execute the statement
            mysqli_stmt_execute($stmt);

            $_SESSION['payMethodMade'] = 1;
          }
          echo '<script type="text/javascript">showError(\'Account verified!\')</script>';
        } else {
          echo '<script type="text/javascript">showError(\'Incorrect amounts\')</script>';
        }
      }
      ?>
      <div class="mainspace">

        <h1>Manage payment methods</h1>

        <div id="paymentMethods">
          <div class="flexRow spaceBetween alignCenter" style="width: 100%">
            <h3>Payment Methods</h3>
            <a href="<?php echo $siteRoot ?>/createpaymentmethod">
              <img src="<?php echo $siteRoot ?>/img/plus.png" style="cursor: pointer; width: 3.5vmin; height: 3.5vmin" />
            </a>
          </div>

          <?php
          $customer = \Stripe\Customer::retrieve($_SESSION['custAcct']);
          $customer['default_source'];


          $noBanks = false;
          $bankAccounts = \Stripe\Customer::retrieve($_SESSION['custAcct'])->sources->all(
          array(
            'limit'=>10,
            'object' => "bank_account",
          ));

          if (!empty($bankAccounts['data'])) {
            //print_r($bankAccounts);
            foreach ($bankAccounts['data'] as $bankAccount) {
              $default = '';
              if ($bankAccount['id'] == $customer['default_source']) {
                $default = " default";
              }
              echo '
              <div class="coolBackground'.$default.' paymentMethod" id="'.$bankAccount['id'].'">
                <div class="flexRow">
                  <img src="' . $siteRoot . '/img/bank.png" />

                  <div class="flexItem">
                    <p>'.$bankAccount['bank_name'].'</p>
                    <span>&#183; &#183; &#183; &#183; '.$bankAccount['last4'].' &nbsp; &nbsp | &nbsp; &nbsp; '.$bankAccount['account_holder_name'].'</span>
                  </div>
                </div>
                <div class="flexItem"  id="paymentButtons'.$bankAccount['id'].'" style="display: none">
                  <span style="margin-top: 1vmin">Status: '.$bankAccount['status'].'</span>
                  <div class="flexRow spaceAround">
                    <div class="paymentButton" onclick="deletePaymentMethod(\''.$bankAccount['id'].'\')" style="background-color: #C62828">Delete</div>';
                    if ($bankAccount['status'] != 'verified') {
                      echo '<div class="paymentButton" onclick="verifyBankAccount(\''.$bankAccount['id'].'\')" style="background-color: #E0E0E0;color: black;">Verify</div>';
                    } else {
                      echo '<div class="paymentButton" onclick="changeDefaultPayment(\''.$bankAccount['id'].'\')" style="background-color: #E0E0E0;color: black;">Make default</div>';
                    }
                  echo '</div>
                </div>
              </div>';
            }
          } else {
            $noBanks = true;
          }

          $creditCards = \Stripe\Customer::retrieve($_SESSION['custAcct'])->sources->all(array(
            'limit'=>10,
            'object' => 'card',
          ));
          if (!empty($creditCards['data'])) {
            foreach ($creditCards['data'] as $card) {
              $default = '';
              if ($card['id'] == $customer['default_source']) {
                $default = " default";
              }
              echo '
              <div class="coolBackground'.$default.' paymentMethod" id="'.$card['id'].'">
                <div class="flexRow">
                  <img src="' . $siteRoot . '/img/credit-card.png" />

                  <div class="flexItem">
                    <p>'.$card['brand'].'</p>
                    <span>&#183; &#183; &#183; &#183; '.$card['last4'].'</span>'. //' &nbsp; &nbsp | &nbsp; &nbsp; '.$card['name'].'</span>
                  '</div>
                </div>
                <div class="flexItem" id="paymentButtons'.$card['id'].'" style="display: none">
                  <span style="margin-top: 1vmin">Expiry: '.$card['exp_month'] . '/' .$card['exp_year'].'</span>
                  <div class="flexRow spaceAround">
                    <div class="paymentButton" onclick="deletePaymentMethod(\''.$card['id'].'\')" style="background-color: #C62828">Delete</div>
                    <div class="paymentButton" onclick="changeDefaultPayment(\''.$card['id'].'\')" style="background-color: #E0E0E0;color: black;">Make default</div>
                  </div>
                </div>
              </div>';
            }
          } else if ($noBanks) {
            echo '<div class="centerme"><h3 style="font-size: 2vmin">Click the \'+\' above to add a payment method</h3></div>';
          }


            //echo $bankAccount['id'] . " " . $bankAccount['last4'] . $bankAccount['bank_name'] . $bankAccount['status'];


          ?>
        </div>


        <div id="paymentMethods">
          <div class="flexRow spaceBetween alignCenter" style="width: 100%">
            <h3>Pay Out Methods</h3>
            <a href="<?php echo $siteRoot ?>/createpayoutmethod">
              <img src="<?php echo $siteRoot ?>/img/plus.png" style="cursor: pointer; width: 3.5vmin; height: 3.5vmin" />
            </a>
          </div>

          <?php
          $noBankAccts = false;
          $bankAccounts = \Stripe\Account::retrieve($_SESSION['stripeAcct'])->external_accounts->all(array(
            'limit'=>10,
            'object' => 'bank_account'));


            if (!empty($bankAccounts['data'])) { // If the user has a bank account
              foreach ($bankAccounts['data'] as $bankAccount) {
                $default = '';
                if ($bankAccount['default_for_currency']) {
                  $default = " default";
                }
                echo '
                <div class="coolBackground'.$default.' paymentMethod" id="'.$bankAccount['id'].'">
                  <div class="flexRow">
                    <img src="' . $siteRoot . '/img/bank.png" />

                    <div class="flexItem">
                      <p>'.$bankAccount['bank_name'].'</p>
                      <span>&#183; &#183; &#183; &#183; '.$bankAccount['last4'].' &nbsp; &nbsp | &nbsp; &nbsp; '.$bankAccount['account_holder_name'].'</span>
                    </div>
                  </div>
                  <div class="flexItem"  id="paymentButtons'.$bankAccount['id'].'" style="display: none">
                    <span style="margin-top: 1vmin">Status: '.$bankAccount['status'].'</span>
                    <div class="flexRow spaceAround">
                      <div class="paymentButton" onclick="deletePaymentMethod(\''.$bankAccount['id'].'\')" style="background-color: #C62828">Delete</div>';
                      /*if ($bankAccount['status'] != 'verified') {
                        echo '<div class="paymentButton" onclick="verifyBankAccount(\''.$bankAccount['id'].'\')" style="background-color: #E0E0E0;color: black;">Verify</div>';
                      } else {*/
                        echo '<div class="paymentButton" onclick="changeDefaultExtAccount(\''.$bankAccount['id'].'\')" style="background-color: #E0E0E0;color: black;">Make default</div>';
                      //}
                echo '</div>
                  </div>
                </div>';
              }
            } else { // if the user doesn't have a bank account
              $noBankAccts = true;
            }

            $creditCards = \Stripe\Account::retrieve($_SESSION['stripeAcct'])->external_accounts->all(array(
              'limit'=>10,
              'object' => 'card'
            ));
            if (!empty($creditCards['data'])) { // if the user has at least one credit card
              foreach ($creditCards['data'] as $card) {
                $default = '';
                if ($card['default_for_currency']) {
                  $default = " default";
                }
                echo '
                <div class="coolBackground'.$default.' paymentMethod" id="'.$card['id'].'">
                  <div class="flexRow">
                    <img src="' . $siteRoot . '/img/credit-card.png" />

                    <div class="flexItem">
                      <p>'.$card['brand'].'</p>
                      <span>&#183; &#183; &#183; &#183; '.$card['last4'].'</span>'.//' &nbsp; &nbsp | &nbsp; &nbsp; '.$card['name'].'</span>
                    '</div>
                  </div>
                  <div class="flexItem" id="paymentButtons'.$card['id'].'" style="display: none">
                    <span style="margin-top: 1vmin">Expiry: '.$card['exp_month'] . '/' .$card['exp_year'].'</span>
                    <div class="flexRow spaceAround">
                      <div class="paymentButton" onclick="deletePaymentMethod(\''.$card['id'].'\')" style="background-color: #C62828">Delete</div>
                      <div class="paymentButton" onclick="changeDefaultExtAccount(\''.$card['id'].'\')" style="background-color: #E0E0E0;color: black;">Make default</div>
                    </div>
                  </div>
                </div>';
              }
            } else if ($noBankAccts) { // if the user has no accounts
              echo '<div class="centerme"><h3 style="font-size: 2vmin">Click the \'+\' above to add a pay out method</h3></div>';
            }

          ?>


        </div>
      </div>
    </div>
  </div>
      <?php echoFooter() ?>
      <script>$(document).mouseup(function(e) {
        var container = $(".taskList");
        if (!container.is(e.target) // if the target of the click isnt the container...
          && container.has(e.target).length === 0) // ... nor a descendant of the container
        {
          removeDarken();
        };
      });
    </script>
    </body>
    </html>
