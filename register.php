<?php
session_start();

include 'scripts.php';
include_once '../../../unimportant.php';
require_once '../vendor/autoload.php';
\Stripe\Stripe::setApiKey($secretKey);


//ini_set('display_errors',1);
//error_reporting(E_ALL);

// Create connection
$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

  if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
  }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // collect value of input field

    $username = preg_replace('/\s+/', '', $_POST['username']);
    $fullname = $_POST['firstname'] . ' ' . $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $grade = $_POST['grade'];

    $month = $_POST['month'];
    $day = $_POST['day'];
    $year = $_POST['year'];

    if (strlen($month) == 1) {
      $month = '0' . $month;
    }

    if (strlen($day) == 1) {
      $day = '0' . $day;
    }

    $dob = $month . '/' . $day . '/' . $year;


    if ($grade == 'High School') {
      $gradenum = 1;
    } else if ($grade == 'College') {
      $gradenum = 2;
    } else if ($grade == 'Graduate') {
      $gradenum = 3;
    } else if ($grade == 'Masters') {
      $gradenum = 4;
    } else if ($grade == 'Doctorate') {
      $gradenum = 5;
    } else {
       $gradenum = 1;
    }




    $password = $_REQUEST['password'];
    $pass = password_hash($password, PASSWORD_BCRYPT);

    $rndid = uniqid('user_');
    $time = time();

    // Prepare statement
    $stmt = mysqli_prepare($conn, 'INSERT INTO users (rndid, username, fullname, firstname, lastname, email, password, grade, gradenum, dob, timestamp)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "ssssssssisi", $rndid, $username, $fullname, $firstname, $lastname, $email, $pass, $grade, $gradenum, $dob, $time);


    $secret = '6Ld0ww4UAAAAALZCeIIaiMMWHp0PWWMIAT4ZdGuu';
    //get verify response data
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
    $responseData = json_decode($verifyResponse);
    if($responseData->success) { // If captcha is good
      mysqli_stmt_execute($stmt);

        $id = $rndid;

        // Prepare statement
        $stmt = mysqli_prepare($conn, 'SELECT * FROM users WHERE rndid = ?');
        // Set the parameter
        mysqli_stmt_bind_param($stmt, "s", $id);
        // Execute the statement
        mysqli_stmt_execute($stmt);

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $_SESSION['rndid'] = $row['rndid'];
        $_SESSION["sessionUser"] = $row["username"];
        $_SESSION["sessionName"] = $row["fullname"];
        $_SESSION["sessionFirst"] = $row['firstname'];
        $_SESSION["sessionLast"] = $row['lastname'];
        $_SESSION["sessionEmail"] = $row["email"];
        $_SESSION["sessionGrade"] = $row["grade"];
        $_SESSION['sessionGradeNum'] = $row['gradenum'];
        $_SESSION["loggedIn"] = TRUE;
        $_SESSION["collecting"] = $row["iscollecting"];
        $_SESSION['extAcctMade'] = $row['extacctmade'];
        $_SESSION['payMethodMade'] = $row['paymethodmade'];



        $_SESSION['gradeLetter'] = $row['gradeletter'];
        $_SESSION['score'] = $row['score'];

        $alias = 'img/profpics/'; // Alias to directory
        $uploaddir = '/var/www/profilepictures/'; // Real directory where images are stored
        $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
        $userid = $row['rndid'];

        if (!($_FILES['userfile']['name'])) {
          $img = 'img/default.png';
        } else {
          if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {


            $info = new SplFileInfo($uploadfile);
            $ext = $info->getExtension();

            $aliasSaveTo = $alias . $userid . '.' . $ext;
            $save_to = $uploaddir . $userid . '.' . $ext;

            $img = new imagick();
            $img->readImage($uploadfile);
            $img->resizeImage(250,0,Imagick::FILTER_LANCZOS,1);

            //set new format
            //$img->setImageFormat('jpg');

            //save image file
            $img->writeImage($save_to);

            //rename($uploadfile, ($uploaddir . $userid. '.docx'));
            unlink($uploadfile);
            $img = $aliasSaveTo;

          }

        }

        // Prepare statement
        $stmt = mysqli_prepare($conn, 'UPDATE users SET img = ? WHERE rndid = ?');
        // Set the parameter
        mysqli_stmt_bind_param($stmt, "ss", $img, $row['rndid']);
        // Execute the statement
        mysqli_stmt_execute($stmt);


        $_SESSION['sessionIMG'] = $img;



        $rndid = uniqid('notif_');
        $messageType = 'om';

        // Prepare statement
        $stmt = mysqli_prepare($conn, 'INSERT INTO notifications (rndid, recipient, messagetype, timestamp)
          VALUES (?, ?, ?, ?)');
        // Set the parameter
        mysqli_stmt_bind_param($stmt, "sssi", $rndid, $row['rndid'], $messageType, $time);
        // Execute the statement
        mysqli_stmt_execute($stmt);


        $token = bin2hex(random_bytes(12));

        // Prepare statement
        $stmt = mysqli_prepare($conn, 'INSERT INTO supertopsecret (accountid, code, email)
          VALUES (?, ?, ?)');
        // Set the parameter
        mysqli_stmt_bind_param($stmt, "sss", $row['rndid'], $token, $row['email']);
        // Execute the statement
        mysqli_stmt_execute($stmt);

        $_SESSION['sessionCode'] = $token;


        // Create Stripe account
        try {
          // Create account
          $json = \Stripe\Account::create(array(
            "managed" => true,
            "country" => "US",
            "email" => $email,
          ));
          $account = json_decode($json);

          $acctID = $json['id'];

          // Retrieve account details
          $account = \Stripe\Account::retrieve($acctID);

          $account->legal_entity->first_name = $firstname;
          $account->legal_entity->last_name = $lastname;

          $account->legal_entity->type = "individual";

          //$account->legal_entity->ssn_last_4 = "1234";


          $account->legal_entity->dob->day = $day;
          $account->legal_entity->dob->month = $month;
          $account->legal_entity->dob->year = $year;

          $account->tos_acceptance->date = time();
          $account->tos_acceptance->ip = $_SERVER["REMOTE_ADDR"];


          /*
          //Needed after the first couple thousand dollars
          $account->legal_entity->ssn_last_4 = "1234";
          $account->legal_entity->address->city = "Le Roy";
          $account->legal_entity->address->line1 = "1009 Frances Avenue";
          $account->legal_entity->address->postal_code = "61752";
          $account->legal_entity->address->state = "IL";
          */
          //After $20,000 they need full SSN

          $account->save();

          $customer = \Stripe\Customer::create(array(
            //"source" => $_POST['stripeToken'],
            "description" => "Customer for " . $userid,
            "email" => $email
          ));

          $custID = $customer['id'];

          // Prepare statement
          $stmt = mysqli_prepare($conn, 'UPDATE users SET stripeacct = ?, custacct = ? WHERE rndid = ?');
          // Set the parameter
          mysqli_stmt_bind_param($stmt, "sss", $acctID, $custID, $row["rndid"]);
          // Execute the statement
          mysqli_stmt_execute($stmt);

          $_SESSION['stripeAcct'] = $acctID;
          $_SESSION['custAcct'] = $custID;

          echo '<script type="text/javascript">location.href = "' . $siteRoot . '/sendverification";</script>';


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


        //echo '<script type="text/javascript">location.href = "sendverification.php";</script>';

    } else { // Captcha was no good
      echo '<script type="text/javascript">location.href = "' . $siteRoot . '/createaccount";</script>';
    }
}
$conn->close();
?>
