<?php

ini_set('display_errors',1);
error_reporting(E_ALL);
include_once '../../../unimportant.php';

require_once '../vendor/autoload.php';

\Stripe\Stripe::setApiKey($secretKey);


try {
  // Create account
  /*$json = \Stripe\Account::create(array(
    "managed" => true,
    "country" => "US",
    "email" => "frankos98@yahoo.com",
  ));
  $account = json_decode($json);

  $acctID = $json['id'];
  echo '<p>';
  echo $acctID . "\n";




  //print_r($json['verification']['fields_needed']);

  for ($i = 0; $i < count($json['verification']['fields_needed']); $i++) {
    echo $json['verification']['fields_needed'][$i] . "\n";
  }
  echo '</p>';
  // Retrieve account details
  $account = \Stripe\Account::retrieve($acctID);

  $account->legal_entity->first_name = "Test";
  $account->legal_entity->last_name = "Boy";

  $account->legal_entity->type = "individual";

  //$account->legal_entity->ssn_last_4 = "1234";


  $account->legal_entity->dob->day = "25";
  $account->legal_entity->dob->month = "5";
  $account->legal_entity->dob->year = "1998";

  $account->tos_acceptance->date = time();
  $account->tos_acceptance->ip = $_SERVER["REMOTE_ADDR"];
*/
/*
  // Create a bank account
  $account->external_account = array(
    "object" => "bank_account",
    "country" => "US",
    "currency" => "usd",
    "account_holder_name" => "Frankie Fanelli",
    "account_holder_type" => "individual",
    "routing_number" => "110000000",
    "account_number" => "000123456789", // Must be checking
  );
  */
  /*
  //Needed after the first couple thousand dollars
  $account->legal_entity->address->city = "Le Roy";
  $account->legal_entity->address->line1 = "1009 Frances Avenue";
  $account->legal_entity->address->postal_code = "61752";
  $account->legal_entity->address->state = "IL";
  */
  //After $20,000 they need full SSN

  //$account->save();

  $customer = \Stripe\Customer::create(array(
    "source" => "tok_19VmirDVrLFTEpzlw5TiqqEv",
    "description" => "Customer for example@stripe.com"
  ));


echo 'CUSTOMER: ';
  print_r($customer);

/*
  $accountDetails = \Stripe\Account::retrieve($acctID);

  $account = json_decode($accountDetails);

  //print_r($json['verification']['fields_needed']);

  for ($i = 0; $i < count($accountDetails['verification']['fields_needed']); $i++) {
    echo $accountDetails['verification']['fields_needed'][$i] . "\n";
  }*/




//echo $json['verification']['fields_needed'][0];

} catch(\Stripe\Error\Card $e) {
  // Since it's a decline, \Stripe\Error\Card will be caught
  $body = $e->getJsonBody();
  $err  = $body['error'];

  print('Status is:' . $e->getHttpStatus() . "\n");
  print('Type is:' . $err['type'] . "\n");
  print('Code is:' . $err['code'] . "\n");
  // param is '' in this case
  print('Param is:' . $err['param'] . "\n");
  print('Message is:' . $err['message'] . "\n");
} catch (\Stripe\Error\RateLimit $e) {
  // Too many requests made to the API too quickly
  echo 'Too many requests';
} catch (\Stripe\Error\InvalidRequest $e) {
  // Invalid parameters were supplied to Stripe's API
  echo 'Invalid parameters';
} catch (\Stripe\Error\Authentication $e) {
  // Authentication with Stripe's API failed
  // (maybe you changed API keys recently)
  echo 'Authentication failed';
} catch (\Stripe\Error\ApiConnection $e) {
  // Network communication with Stripe failed
  echo 'Network failure';
} catch (\Stripe\Error\Base $e) {
  // Display a very generic error to the user, and maybe send
  // yourself an email
  echo 'Something went wrong, we\'re working to fix this';
} catch (Exception $e) {
  // Something else happened, completely unrelated to Stripe
  echo 'Something went wrong';
}




/*define('CLIENT_ID', $developmentID);
  define('API_KEY', 'sk_test_hwpMAb6MjFsGXyIwkZAazB04');
  define('TOKEN_URI', 'https://connect.stripe.com/oauth/token');
  define('AUTHORIZE_URI', 'https://connect.stripe.com/oauth/authorize');



if (isset($_GET['code'])) { // Redirect w/ code
    $code = $_GET['code'];
    $token_request_body = array(
      'client_secret' => API_KEY,
      'grant_type' => 'authorization_code',
      'client_id' => CLIENT_ID,
      'code' => $code,
    );

    $path = '../cacert.pem';

    $req = curl_init(TOKEN_URI);
        curl_setopt($req, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($req, CURLOPT_CAINFO, $path);
        curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($req, CURLOPT_POST, true );
        curl_setopt($req, CURLOPT_POSTFIELDS, http_build_query($token_request_body));

    // TODO: Additional error handling
    $respCode = curl_getinfo($req, CURLINFO_HTTP_CODE);
    $resp = json_decode(curl_exec($req), true);
    curl_close($req);

    print_r($resp);


  } else if (isset($_GET['error'])) { // Error
    echo $_GET['error_description'];

  } else { // Show OAuth link
    $authorize_request_body = array(
      'response_type' => 'code',
      'scope' => 'read_write',
      'client_id' => CLIENT_ID
    );
    $url = AUTHORIZE_URI . '?' . http_build_query($authorize_request_body);
    echo "<a href='$url'>Connect with Stripe</a>";
  }*/
?>
