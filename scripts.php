
<?php
session_start();
require("../../../unimportant.php");
//$conn = new mysqli($dblocation, $dbuser, $dbpass, $dbname);

function datePassed($date) {
  if(!ctype_digit($date))
      $date = strtotime($date);

  $diff =  (time() - 21600) - $date;

  if($diff > 0) {
    return true;
  } else {
    return false;
  }
}

function time2str($ts)
{
    if(!ctype_digit($ts))
        $ts = strtotime($ts);

    $diff = time() - $ts;
    if($diff == 0)
        return 'now';
    elseif($diff > 0)
    {
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 60) return 'just now';
            if($diff < 120) return '1 minute ago';
            if($diff < 3600) return floor($diff / 60) . ' minutes ago';
            if($diff < 7200) return '1 hour ago';
            if($diff < 86400) return floor($diff / 3600) . ' hours ago';
        }
        if($day_diff == 1) return 'Yesterday';
        if($day_diff < 7) return $day_diff . ' days ago';
        if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
        if($day_diff < 60) return 'last month';
        return date('F Y', $ts);
    }
    else
    {
        $diff = abs($diff);
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 120) return 'in a minute';
            if($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
            if($diff < 7200) return 'in an hour';
            if($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
        }
        if($day_diff == 1) return 'Tomorrow';
        if($day_diff < 4) return date('l', $ts);
        if($day_diff < 7 + (7 - date('w'))) return 'next week';
        if(ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
        if(date('n', $ts) == date('n') + 1) return 'next month';
        return date('F Y', $ts);
    }
}

/*function time2str($ts) {
    if(!ctype_digit($ts)) {
      $ts = strtotime($ts);
    }

    $diff = time() - $ts;
    if($diff == 0) {
      return 'now';
    }
    else if ($diff > 0) {
        $day_diff = floor($diff / 86400);
        if ($day_diff == 0) {
            if($diff < 60) return 'just now';
            if($diff < 120) return '1 minute ago';
            if($diff < 3600) return floor($diff / 60) . ' minutes ago';
            if($diff < 7200) return '1 hour ago';
            if($diff < 86400) return floor($diff / 3600) . ' hours ago';
        }
        if($day_diff == 1) return 'Yesterday';
        if($day_diff < 7) return $day_diff . ' days ago';
        if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
        if($day_diff < 60) return 'last month';
        return date('F Y', $ts);
    }
}*/


function notVerified() {
  if(!($_SESSION['verified']) || ($_SESSION['verified'] == 0)) { // If there is no session variable, or it's 0
    require("../../../unimportant.php");
    // Create connection
    $conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);
    // Test connection
    if (mysqli_connect_errno()) {
      printf("Connect failed: %s\n", mysqli_connect_error());
      exit();
    }
    // Prepare the statement
    if ($stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE rndid = ?")) {
      // Set the parameter
      mysqli_stmt_bind_param($stmt, "s", $_SESSION['rndid']);
      // Execute the statement
      mysqli_stmt_execute($stmt);
      // Get the results
      $result = $stmt->get_result();
      $row = $result->fetch_assoc(); // See if user is verified or not
      if ($row['verified'] == 0) { // If they aren't, show them the banner
        echo '<div id="notVerified"><p>' . $_SESSION['sessionEmail'] . ' has not been verified yet.
              <a onclick="sendVerification()">Resend verification email</a></p><img onclick="hideBanner()" src="' . $siteRoot . '/img/xsmall.png" /></div>';
              $_SESSION['verified'] = 0;
      } else { // If they are, don't show banner and say they're verified
        $_SESSION['verified'] = 1;
      }
    }
    $conn->close();
  }
}

function echoNavbar() {
  require("../../../unimportant.php");
  if (isset($_SESSION["loggedIn"])) { // If the person is logged in
    echo '
    <!--Hello curious person! Here, you\'ll find the culmination of my freshmen year at college.-->
    <!--(This means the code is ugly, so please don\'t judge me!)-->
    <!--If you see anything that could be fixed, please email me or submit feedback!-->
    <!--Also, I hope that maybe you can learn something from this code! Although you should probably go to better-coded websites-->
    <!--And please don\'t steal any of this stuff, I worked hard for it (not that you\'d really want to/should, anyway)-->

      <div id="navbar">
        <a href="' . $siteRoot . '/index" class="navlogo">
          <img id="logo" src="' . $siteRoot . '/img/bounti_logo_black.png" />
          <p id="navName">Bounti.io <span>&nbsp;beta</span></p>
        </a>
        <a class="navlink" href="' . $siteRoot . '/feedback">Feedback</a>
        <a id="homeImg" href="' . $siteRoot . '/bounties"><img src="' . $siteRoot . '/img/home.png" /></a>
        <a id="uploadImg" href="' . $siteRoot . '/uploadpaper"><img src="' . $siteRoot . '/img/upload.png" /></a>
        <a id="messagesImg" onclick="showMessages(\''.$_SESSION['rndid'].'\', event)"><img src="' . $siteRoot . '/img/email-outline.png" id="mailbox" /></a>
        <div class="accountNavDiv">
          <img src="' . $siteRoot . '/'.$_SESSION['sessionIMG'].'" id="account" />
        </div>
        <div id="notif"></div>
        <div class="messagesDropDown"><div class="centerme">loading...</div></div>
        <div class="accountDropDown">
            <a href="' . $siteRoot . '/account/'.$_SESSION['sessionUser'].'">My Account</a>
            <a href="' . $siteRoot . '/notifications">Notifications</a>
            <a href="' . $siteRoot . '/settings">Settings</a>
            <a href="' . $siteRoot . '/logout">Logout</a>
        </div>
    </div>';
    notVerified();
    echo '<div id="errorBox" onmouseover="keepError()" onmouseout="hideError()">
      <p></p>
      <img src="' . $siteRoot . '/img/xsmall.png" />
    </div>';
  } else {
    echo '
    <!--Hello curious person! Here, you\'ll find the culmination of my freshmen year at college.-->
    <!--(This means the code is ugly, so please don\'t judge me!)-->
    <!--If you see anything that could be fixed, please email me or submit feedback!-->
    <!--Also, I hope that maybe you can learn something from this code! Although you should probably go to better-coded websites-->
    <!--And please don\'t steal any of this stuff, I worked hard for it (not that you\'d really want to/should, anyway)-->

    <div id="navbar">
            <a href="' . $siteRoot . '/index" class="navlogo">
              <img id="logo" src="' . $siteRoot . '/img/bounti_logo_black.png" />
              <p id="navName">Bounti.io <span>&nbsp;beta</span></p>
            </a>
            <a class="navlink" href="' . $siteRoot . '/createaccount">Create Account</a>
            <a class="navlink" href="' . $siteRoot . '/loginpage">Login</a>
          </div>
          <div id="errorBox" onmouseover="keepError()" onmouseout="hideError()">
            <p></p>
            <img src="' . $siteRoot . '/img/xsmall.png" />
          </div>';
  }
}

function echoFlippaNavbar() {
  require("../../../unimportant.php");
  if (isset($_SESSION["loggedIn"])) { // If the person is logged in
    echo '
    <!--Hello curious person! Here, you\'ll find the culmination of my freshmen year at college.-->
    <!--(This means the code is ugly, so please don\'t judge me!)-->
    <!--If you see anything that could be fixed, please email me or submit feedback!-->
    <!--Also, I hope that maybe you can learn something from this code! Although you should probably go to better-coded websites-->
    <!--And please don\'t steal any of this stuff, I worked hard for it (not that you\'d really want to/should, anyway)-->

      <div id="flippaNav">
        <a href="' . $siteRoot . '/index" class="navlogo">
          <img id="logo" src="' . $siteRoot . '/img/bounti_logo_black.png" />
          <p id="navName">Bounti.io <span>&nbsp;beta</span></p>
        </a>
        <a class="navlink" href="' . $siteRoot . '/feedback">Feedback</a>
        <a id="homeImg" href="' . $siteRoot . '/bounties"><img src="' . $siteRoot . '/img/home.png" /></a>
        <a id="uploadImg" href="' . $siteRoot . '/uploadpaper"><img src="' . $siteRoot . '/img/upload.png" /></a>
        <a id="messagesImg" onclick="showMessages(\''.$_SESSION['rndid'].'\', event)"><img src="' . $siteRoot . '/img/email-outline.png" id="mailbox" /></a>
        <div class="accountNavDiv">
          <img src="' . $siteRoot . '/'.$_SESSION['sessionIMG'].'" id="account" />
        </div>
        <div id="notif"></div>
        <div class="messagesDropDown"><div class="centerme">loading...</div></div>
        <div class="accountDropDown">
            <a href="' . $siteRoot . '/account/'.$_SESSION['sessionUser'].'">My Account</a>
            <a href="' . $siteRoot . '/notifications">Notifications</a>
            <a href="' . $siteRoot . '/settings">Settings</a>
            <a href="' . $siteRoot . '/logout">Logout</a>
        </div>
    </div>';
    notVerified();
    echo '<div id="errorBox" onmouseover="keepError()" onmouseout="hideError()">
      <p></p>
      <img src="' . $siteRoot . '/img/xsmall.png" />
    </div>';
  } else {
    echo '
    <!--Hello curious person! Here, you\'ll find the culmination of my freshmen year at college.-->
    <!--(This means the code is ugly, so please don\'t judge me!)-->
    <!--If you see anything that could be fixed, please email me or submit feedback!-->
    <!--Also, I hope that maybe you can learn something from this code! Although you should probably go to better-coded websites-->
    <!--And please don\'t steal any of this stuff, I worked hard for it (not that you\'d really want to/should, anyway)-->

    <div id="flippaNav">
            <a href="' . $siteRoot . '/index" class="navlogo">
              <img id="logo" src="' . $siteRoot . '/img/bounti_logo_black.png" />
              <p id="navName">Bounti.io <span>&nbsp;beta</span></p>
            </a>
            <a class="navlink" href="' . $siteRoot . '/createaccount">Create Account</a>
            <a class="navlink" href="' . $siteRoot . '/loginpage">Login</a>
          </div>
          <div id="errorBox" onmouseover="keepError()" onmouseout="hideError()">
            <p></p>
            <img src="' . $siteRoot . '/img/xsmall.png" />
          </div>';
  }
}

function echoFooter() {
  require("../../../unimportant.php");
  echo '<div class="footer">
    <table>
    <tr>
      <td><a href="https://www.bounti.io/account">Account</a></td>
		  <td><a href="https://www.bounti.io/messages">Messages</a></td>
      <td><a href="https://www.bounti.io/help">Help</a></td>
    </tr>
    <tr>
      <td><a href="https://www.bounti.io/history">History</a></td>
		  <td><a href="https://www.bounti.io/bounties">Bounties</a></td>
      <td><a href="https://www.bounti.io/useragreement.html">User Agreement</a></td>
    </tr>
    <tr>
      <td><a href="https://www.bounti.io/notifications">Notifications</a></td>
		  <td><a href="https://www.bounti.io/submitbounti">Turn In Bounti</a></td>
      <td><a href="https://www.bounti.io/privacypolicy.html">Privacy Policy</a></td>
    </tr>
    <tr>
      <td><a href="https://www.bounti.io/uploadpaper">Upload Paper</a></td>
		  <td><a href="https://www.bounti.io/transactions">Transactions</a></td>
      <td><a href="https://www.bounti.io/feedback">Feedback</a></td>
    </tr>
    <tr>
      <td><a href="https://www.bounti.io/settings">Settings</a></td>
		  <td><a href="https://www.bounti.io/managepaymentmethods">Payment Methods</a></td>
      <td><a href="https://www.bounti.io/aboutme">About Me</a></td>
    </tr>
    <tr>
      <td><a href="https://www.bounti.io/logout">Logout</a></td>
		  <td><a href="#hi there :)"></a></td>
      <td><a href="https://www.bounti.io/comingsoon">Coming Soon</a></td>
    </tr>' .
    '</table>
      <p>Bounti.io - Public Beta v0.1 &nbsp; | &nbsp; &#169;'.date('Y').' Bounti.io</p>
  </div>';
}

function echoFlippaFooter() {
  require("../../../unimportant.php");
  echo '<div class="footer flippaFooter">
    <table>
    <tr>
      <td><a href="https://www.bounti.io/account">Account</a></td>
		  <td><a href="https://www.bounti.io/messages">Messages</a></td>
      <td><a href="https://www.bounti.io/help">Help</a></td>
    </tr>
    <tr>
      <td><a href="https://www.bounti.io/history">History</a></td>
		  <td><a href="https://www.bounti.io/bounties">Bounties</a></td>
      <td><a href="https://www.bounti.io/useragreement.html">User Agreement</a></td>
    </tr>
    <tr>
      <td><a href="https://www.bounti.io/notifications">Notifications</a></td>
		  <td><a href="https://www.bounti.io/submitbounti">Turn In Bounti</a></td>
      <td><a href="https://www.bounti.io/privacypolicy.html">Privacy Policy</a></td>
    </tr>
    <tr>
      <td><a href="https://www.bounti.io/uploadpaper">Upload Paper</a></td>
		  <td><a href="https://www.bounti.io/transactions">Transactions</a></td>
      <td><a href="https://www.bounti.io/feedback">Feedback</a></td>
    </tr>
    <tr>
      <td><a href="https://www.bounti.io/settings">Settings</a></td>
		  <td><a href="https://www.bounti.io/managepaymentmethods">Payment Methods</a></td>
      <td><a href="https://www.bounti.io/aboutme">About Me</a></td>
    </tr>
    <tr>
      <td><a href="https://www.bounti.io/logout">Logout</a></td>
		  <td><a href="#hi there :)"></a></td>
      <td><a href="https://www.bounti.io/comingsoon">Coming Soon</a></td>
    </tr>' .
    '</table>
      <p>Bounti.io - Public Beta v0.1 &nbsp; | &nbsp; &#169;'.date('Y').' Bounti.io</p>
  </div>';
}

function logOut() {
  if($stmt = mysqli_prepare($conn, 'UPDATE supertopsecret SET code = "" WHERE accountid = ?')) {
    // Set the parameter
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['rndid']);
    // Execute the statement
    mysqli_stmt_execute($stmt);
  }

  $_SESSION = array(); // Clears all session variables

  $past = time() - 3600; // Clears all cookies
  foreach ( $_COOKIE as $key => $value )
  {
      setcookie( $key, $value, $past, '/' );
  }
}

?>
