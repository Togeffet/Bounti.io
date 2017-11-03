<?php
session_start();
if (!($_SESSION["loggedIn"])) {
  $_SESSION['wantedPage'] = $_SERVER['REQUEST_URI'];
  echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
}
include 'scripts.php';
ini_set('display_errors',1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>Bounti.io - Upload</title>
        <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
        <!--<link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>-->
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/css/jquery-ui.min.css" />
        <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
        <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
        <script src='https://www.google.com/recaptcha/api.js'></script>
        <!--<script type="text/javascript" src="js/materialize.min.js"></script>-->
        <script src="<?php echo $siteRoot ?>/convjs.js"></script>
    </head>

    <body>
        <?php echoNavbar() ?>

        <?php
          if (($_SESSION['payMethodMade'] == 0) || ($_SESSION['verified'] == 0)) { // If they haven't created a payment method or been verified yet
            echo '
            <div class="uploadDarken">
              <div class="taskList">
                <h1>To upload, you must:</h1>
                <div class="flexRow spaceBetween">
                  <p>Verify email</p>'.
                  ($_SESSION['verified'] == 1 ? '<img src="' . $siteRoot . '/img/check.png" class="green" />' : '<img src="' . $siteRoot . '/img/xsmall.png" class="red" />').
                  '</div>
                <div class="flexRow spaceBetween">
                  <p><a href="' . $siteRoot . '/createpaymentmethod">Provide a payment method</a></p>'.
                  (($_SESSION['payMethodMade'] == 1) ? '<img src="' . $siteRoot . '/img/check.png" class="green" />' : '<img src="' . $siteRoot . '/img/xsmall.png" class="red" />').
                  '</div>
              </div>
            </div>';
          }
        ?>

        <?php
        if($_SERVER['REQUEST_METHOD'] == "POST") {
          try {
            if (($_SESSION['payMethodMade'] == 1) && ($_SESSION['verified'] == 1)) { // If they're verified and have a payment method
              if (isset($_POST['reward']) && $_POST['reward'] >= 1) {


                $originalReward = $_POST['reward'];
                $reward = round($originalReward, 2, PHP_ROUND_HALF_DOWN);


                if ($reward < 20) {
                  $fee = 1;
                }
                else {
                  $fee = round(($reward / 10), 0);
                }

                $reward = $reward * 100;
                $fee = $fee * 100;
                $total = $reward + $fee;

                //echo $reward . ' Fee: ' . $fee . ' Total: ' . $total;

                include_once '../../../unimportant.php';
                // Create connection
                $conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

                // check connection
                if (mysqli_connect_errno()) {
                  printf("Connect failed: %s\n", mysqli_connect_error());
                  exit();
                }
                $rndid = uniqid('bounti_');

                $oldmask = umask(0);
                mkdir('/var/www/documents/' . $rndid, 0777);
                if (is_dir('/var/www/documents/' . $rndid)) {
                  mkdir('/var/www/documents/' . $rndid . '/original', 0777);
                  mkdir('/var/www/documents/' . $rndid . '/revised', 0777);
                }

                $uploaddir ='/var/www/documents/' . $rndid . '/original/'; // Directory where files are saved
                $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

                // collect value of input field
                $title = $_POST['title'];
                $bio = $_POST['bio'];
                $pages = $_POST['pages'];
                $author = $_SESSION["sessionUser"];

                $minscore = $_POST['minscore'];
                $authorname = $_SESSION["sessionName"];
                $authorid = $_SESSION['rndid'];

                $duedate = $_POST['date'];

                $uploaddate = time();
                $mingrade = $_POST['grade'];



                $secret = '6Ld0ww4UAAAAALZCeIIaiMMWHp0PWWMIAT4ZdGuu';
                //get verify response data
                //$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
                //$responseData = json_decode($verifyResponse);
                //if($responseData->success) {
                  $ext = pathinfo($_FILES['userfile']['tmp_name'], PATHINFO_EXTENSION);
                  /*if ($ext != '.docx') {
                    throw new Exception('Must be a .docx file');
                  }*/
                  if ($_FILES['userfile']['size'] > 33554432) {
                    throw new Exception('File must be smaller than 32MB');
                  }
                  if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) { // If it gets uploaded correctly

                    if($stmt = mysqli_prepare($conn, "INSERT INTO bounties (rndid, title, author, reward, stripeamount, fee, total, bio, pages, min_score, authorname, authorid, duedate, timestamp, mingrade) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                      // Set the parameter
                      mysqli_stmt_bind_param($stmt, "sssdiiisiissssi", $rndid, $title, $author, $originalReward, $reward, $fee, $total, $bio, $pages, $minscore, $authorname, $authorid, $duedate, $uploaddate, $mingrade);
                      // Execute the statement
                      if(mysqli_stmt_execute($stmt)) { // If it successfully gets inserted into database
                        // Get the paper that was just uploaded
                          $paperid = $rndid;

                          // Actually upload it
                          exec('doc2pdf "'.$uploadfile .'" 2>&1', $output);
                          $info = new SplFileInfo($uploadfile);
                          $ext = $info->getExtension();

                          $rawfilename = basename($uploadfile, ("." . $ext));

                          $paper_file = $rawfilename . ".pdf";
                          $save_to = $uploaddir . $paperid . '.jpg';

                          $img = new imagick();
                          $img->setResolution(100,100);
                          $img->setSize(618,800);
                          $img->readImage($uploaddir . $paper_file . '[0]');
                          //set new format
                          $img->setImageFormat('jpg');

                          //save image file
                          $img->writeImage($save_to);

                          rename($uploadfile, ($uploaddir . $paperid. '.docx'));
                          unlink($uploaddir . $paper_file);
                          umask($oldmask);



                          //echo '<script type="text/javascript">showError("Paper uploaded")</script>';
                          echo '<script type="text/javascript">location.href = "' . $siteRoot . '/fullbounti/'.$paperid.'";</script>';

                      } else {
                          throw new Exception('Something went wrong');
                      }
                    } else {
                        throw new Exception('Something went wrong');
                    }
                  } else {
                    var_dump($_FILES['userfile']['error']);
                    throw new Exception('Document wasn\'t uploaded');
                  }
                /*} else {
                  throw new Exception('Captcha wasn\'t verified');
                }*/
              } else {
                throw new Exception('Reward must be more than one dollar');
              }
            } else { // If they got through the amazing security of .darken
              throw new Exception('You must be verified and have a payment method set up');
            }
          } catch (Exception $e){
            echo '<script>showError("'.$e->getMessage().'")</script>';

          }
        }

         ?>
        <div class="mainspace">
            <div class="uploadform">
                <div class="floatleft">
                    <form action="<?php echo $siteRoot ?>/uploadpaper.php" enctype="multipart/form-data" method="POST">
                      <div class="formRow">
                        <div class="formItem">
                          <label for="title">Title</label>
                          <input name="title" id="title" type="text" required />
                        </div>
                      </div>
                      <div class="formRow">
                        <div class="formItem">
                          <label for="bio">Bio</label>
                          <input name="bio" id="bio" type="text" required />
                        </div>
                      </div>
                      <div class="formRow">
                        <div class="formItem">
                          <label for="pages">Pages</label>
                          <input name="pages" id="pages" type="number" value="1" min="1" required />
                        </div>
                        <div class="formItem">
                          <label for="reward">Reward</label>
                          <div class="formRow">
                            <p id="money">$</p>
                            <input name="reward" id="reward" type="number" min="1.00" step=".01" max="2500" value="1.00" required />
                          </div>
                        </div>
                      </div>
                      <div class="formRow">
                        <div class="formItem">
                          <label for="date">Due Date</label>
                          <input name="date" id="date" type="text" maxlength="10" value="" required />
                        </div>
                      </div>
                      <div class="formRow">
                        <div class="formItem">
                          <label for="minscore">Minimum Grade Level</label>
                            <select name="minscore">
                              <option value="-2">Any grade</option>
                              <option value="50">F</option>
                              <option value="60">D</option>
                              <option value="70">C</option>
                              <option value="80">B</option>
                              <option value="90">A</option>
                            </select>
                          </div>
                        </div>
                        <div class="formRow">
                          <div class="formItem">
                            <label for="grade">Minimum Education</label>
                            <select name="grade">
                                <option value="1">Highschool</option>
                                <option value="2">College</option>
                                <option value="3">Graduate</option>
                                <option value="4">Masters</option>
                                <option value="5">Doctorate</option>
                            </select>
                          </div>
                        </div>
                        <div class="centerme" style="text-align: right;">
                          <table style="width: 100%" id="rewardTable">
                            <tr style="font-weight: 300">
                              <td><img src="<?php echo $siteRoot ?>/img/help-circle.png" id="rewardHelp" /><div id="rewardTip" class="tooltiptext">How much the Bounti Hunter will earn</div></td>
                              <td>Reward:</td>
                              <td id="rewardWorth">$1.00</td>
                            </tr>
                            <tr>
                              <td><img src="<?php echo $siteRoot ?>/img/help-circle.png" id="feesHelp" /><div id="feeTip" class="tooltiptext">Bounti.io's cut added to the payment processor's fee</div></td>
                              <td style="font-weight: 300">Fees:</td>
                              <td id="feesWorth" style="font-weight: 300">$1.00</td>
                            </tr>
                            <tr>
                              <td></td>
                              <td>You pay:</td>
                              <td id="totalWorth">$2.00</td>
                            </tr>
                          </table>
                        </div>
                        <!--div class="centerme">
                          <div class="g-recaptcha" data-sitekey="6Ld0ww4UAAAAALhGP1EWkX2eKfCW5EHs-rRLb2aG"></div>
                        </div-->

                        <input type="hidden" name="MAX_FILE_SIZE" value="16777215" />
                        <input id="fileupload" name="userfile" type="file" required onchange="alertFileName()"/>
                        <input id="submit" type="submit" value="Submit" />

                </div>
                <!--<div class="dropfile" id="drophere" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <!--<img src="documents/'.$row['id'].'.jpg" class="paperPreview" />-->
                    <label for="fileupload" id="dropplease">
                        <p>Click to choose .docx file</p>
                        <!--p style="font-size: 3vmin">Or paste Google Drive link</p-->
                        <p id="fileName"></p>
                    </label>


                <!--</div>-->
                </form>


            </div>


          </div>




        </div>
        <?php echoFooter(); ?>
        <script type="text/javascript">$('#date').datepicker({minDate: +1, maxDate: '+1y', showAnim: 'slideDown'});</script>

    </body>
</html>
