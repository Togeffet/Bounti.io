<?php
session_start();
include 'scripts.php';
if($_SESSION["loggedIn"]) { // If the user is logged in, take them to bounties
    echo '<script type="text/javascript">location.href = "' . $siteRoot . '/bounties";</script>';
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - Create Account</title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/css/jquery-ui.min.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
        <script src='https://www.google.com/recaptcha/api.js'></script>
        <script>var page = "createaccount"</script>
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>
    <body>
        <?php echoNavbar() ?>
        <div class="mainspace">

            <h1>Create Account</h1>
                <form action='<?php echo $siteRoot ?>/register.php' method='post' accept-charset='UTF-8' enctype="multipart/form-data" style="margin-bottom: 4vmin" id="createAccountForm">

                  <div class="formRow">
                    <div class="formItem">
                      <label for='username'>Username<img id="usernameOkayYes" /></label>
                      <input type='text' name='username' id='newusername'  maxlength="25" required />
                    </div>
                  </div>

                  <div class="formRow">
                    <div class="formItem">
                      <label for='fullname'>First Name</label>
                      <input type='text' name='firstname' id='newfullname' size="15" maxlength="15" required />
                    </div>
                  </div>

                  <div class="formRow">
                    <div class="formItem">
                      <label for='fullname'>Last Name</label>
                      <input type='text' name='lastname' id='newfullname' size="20" maxlength="20" required />
                    </div>
                  </div>

                  <div class="formRow">
                    <div class="formItem">
                      <label for='email'>Email<img id="emailOkayYes" /></label>
                      <input type='text' name='email' id='newemail' maxlength="254" required/>
                    </div>
                  </div>

                  <div class="formRow">
                    <div class="formItem">
                      <label for='password'>Password<img id="passwordOkayYes" /><div id="validAccountDetails"></div></label>
                      <input autocomplete="new-password" type='password' name='password' id='newpassword' maxlength="50" required />
                    </div>
                  </div>
                  <label for="month" style="width: 100%; text-align: left;">Date of birth</label>
                  <div class="formRow" style="margin-top: 1vmin">

                    <div class="formItem">
                      <label for="month" style="font-size: 1.5vmin">MM</label>
                      <input name="month" id="month" type="text" maxlength="2" required />
                    </div>
                    <div class="formItem">
                    <label for="month" style="font-size: 1.5vmin">DD</label>
                    <input name="day" id="day" type="text" maxlength="2" required />
                    </div>
                    <div class="formItem" style="width: auto">
                      <label for="month" style="font-size: 1.5vmin; width: 6vmin">YYYY</label>
                      <input name="year" id="year" type="text" maxlength="4" required />
                    </div>
                  </div>

                  <div class="formRow">
                    <div class="formItem">
                      <label for='grade'>Current education</label>
                      <select name="grade" id="grade" required>
                        <option value="High School">High School</option>
                        <option value="College">College</option>
                        <option value="Graduate">Graduate</option>
                        <option value="Masters">Masters</option>
                        <option value="Doctorate">Doctorate</option>
                      </select>
                    </div>
                  </div>

                  <div class="formRow">
                    <div class="formItem">
                      <label for='img'>Profile Picture</label>
                      <input type="hidden" name="MAX_FILE_SIZE" value="16777215" />
                      <input id="img" name="userfile" type="file" />
                    </div>
                  </div>

                  <div class="centerme">
                    <div class="g-recaptcha" data-sitekey="6Ld0ww4UAAAAALhGP1EWkX2eKfCW5EHs-rRLb2aG"></div>
                    <div style="width: 50vmin">
                      <p style="font-family: 'Roboto',sans-serif; font-weight: 300; text-align: center">By creating your account, you are agreeing to the <a href="<?php echo $siteRoot ?>/useragreement.html">User Agreement</a>, <a href="<?php echo $siteRoot ?>/privacypolicy.html">Privacy Policy</a>, as well as the <a href="https://stripe.com/us/connect/legal" style="color: rgba(0,0,0,0.7)">Stripe Connect Platform Agreement</a>.</p>
                    </div>

                    <input id='submit' class='checkValuesSubmit' type='submit' name='submit' value='Create Account' onclick='checkAccountValues()' disabled />
                  </div>
                </form>

        </div>
        <?php echoFooter() ?>
        <script type="text/javascript">$('#date').datepicker({maxDate: 'today', showAnim: 'slideDown'});</script>
    </body>
</html>
