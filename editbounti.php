
<?php
 session_start();
 if (!($_SESSION["loggedIn"])) {
   $_SESSION['wantedPage'] = $_SERVER['REQUEST_URI'];
   echo '<script type="text/javascript">location.href = "' . $siteRoot . '/loginpage";</script>';
 }
 include_once '../../../unimportant.php';
 include 'scripts.php';
 //ini_set('display_errors',1);
 //error_reporting(E_ALL);

 // Create connection
$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);
// Check connection
if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}
 ?>
 <!DOCTYPE html>
 <html>

     <head>
         <meta charset="UTF-8">
         <title>Bounti.io - Edit Bounti</title>
         <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,700|Roboto:100,300,500,800" rel="stylesheet">
         <!--<link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>-->
         <link rel="stylesheet" href="<?php echo $siteRoot ?>/css/jquery-ui.min.css" />
         <link rel="stylesheet" href="<?php echo $siteRoot ?>/convstyle.css" />
         <link rel="icon" href="<?php echo $siteRoot ?>/img/bouti_title_logo_new.png" />
         <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-3.1.0.min.js"></script>
         <script type="text/javascript" src="<?php echo $siteRoot ?>/js/jquery-ui.min.js"></script>
         <!--<script type="text/javascript" src="js/materialize.min.js"></script>-->
         <script src="<?php echo $siteRoot ?>/convjs.js"></script>
     </head>

     <body>
         <?php echoNavbar() ?>
         <?php // Update if user is tryna
           if ($_SERVER["REQUEST_METHOD"] == "POST") {
             try {


               //$uploaddir = 'documents/'; // Directory where files are saved
               //$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

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

               if (isset($_POST['id']) && ($reward >= 100) && ($fee >= 100)) {


                 if($stmt = mysqli_prepare($conn, "SELECT * FROM bounties WHERE rndid = ? AND authorid = ?")) {
                   mysqli_stmt_bind_param($stmt, "ss", $_POST['id'], $authorid);
                   // Execute the statement
                   mysqli_stmt_execute($stmt);
                   // Get the results
                   $result = $stmt->get_result();
                   $editrow = $result->fetch_assoc();

                 }

                 $rndid = $editrow['rndid'];

                 $uploaddir ='/var/www/documents/' . $rndid . '/original/'; // Directory where files are saved
                 $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

                 $titleOutput = '';
                 $bioOutput = '';
                 $pagesOutput = '';
                 $rewardOutput = '';
                 $duedateOutput = '';
                 $minscoreOutput = '';
                 $mingradeOutput = '';


                 if ($editrow['title'] != $title) {
                   // Prepare statement
                   $stmt = mysqli_prepare($conn, 'UPDATE bounties SET title = ?, success = 0 WHERE rndid = ? AND authorid = ?');
                   // Set the parameter
                   mysqli_stmt_bind_param($stmt, "sss", $title, $_POST['id'], $authorid);
                   // Execute the statement
                   mysqli_stmt_execute($stmt);
                 }

                 if ($editrow['bio'] != $bio) {
                   // Prepare statement
                   $stmt = mysqli_prepare($conn, 'UPDATE bounties SET bio = ?, success = 0 WHERE rndid = ? AND authorid = ?');
                   // Set the parameter
                   mysqli_stmt_bind_param($stmt, "sss", $bio, $_POST['id'], $authorid);
                   // Execute the statement
                   mysqli_stmt_execute($stmt);
                 }

                 if ($editrow['pages'] != $pages) {
                   // Prepare statement
                   $stmt = mysqli_prepare($conn, 'UPDATE bounties SET pages = ?, success = 0 WHERE rndid = ? AND authorid = ?');
                   // Set the parameter
                   mysqli_stmt_bind_param($stmt, "sss", $pages, $_POST['id'], $authorid);
                   // Execute the statement
                   mysqli_stmt_execute($stmt);
                 }

                 if ($editrow['stripeamount'] != $reward) {
                   // Prepare statement
                   $stmt = mysqli_prepare($conn, 'UPDATE bounties SET stripeamount = ?, fee = ?, total = ?, reward = ?, success = 0 WHERE rndid = ? AND authorid = ?');
                   // Set the parameter
                   mysqli_stmt_bind_param($stmt, "iiiiss", $reward, $fee, $total, $originalReward, $_POST['id'], $authorid);
                   // Execute the statement
                   mysqli_stmt_execute($stmt);
                 }

                 if ($editrow['duedate'] != $duedate) {
                   // Prepare statement
                   $stmt = mysqli_prepare($conn, 'UPDATE bounties SET duedate = ?, success = 0 WHERE rndid = ? AND authorid = ?');
                   // Set the parameter
                   mysqli_stmt_bind_param($stmt, "sss", $duedate, $_POST['id'], $authorid);
                   // Execute the statement
                   mysqli_stmt_execute($stmt);
                 }

                 if ($editrow['min_score'] != $minscore) {
                   // Prepare statement
                   $stmt = mysqli_prepare($conn, 'UPDATE bounties SET min_score = ?, success = 0 WHERE rndid = ? AND authorid = ?');
                   // Set the parameter
                   mysqli_stmt_bind_param($stmt, "iss", $minscore, $_POST['id'], $authorid);
                   // Execute the statement
                   mysqli_stmt_execute($stmt);
                 }

                 if ($editrow['mingrade'] != $mingrade) {
                   // Prepare statement
                   $stmt = mysqli_prepare($conn, 'UPDATE bounties SET mingrade = ?, success = 0 WHERE rndid = ? AND authorid = ?');
                   // Set the parameter
                   mysqli_stmt_bind_param($stmt, "iss", $mingrade, $_POST['id'], $authorid);
                   // Execute the statement
                   mysqli_stmt_execute($stmt);
                 }

                 if (!($_FILES['userfile']['name'])) {
                   // Okay cool, no file here
                 } else {
                   $ext = pathinfo($_FILES['userfile']['tmp_name'], PATHINFO_EXTENSION);
                   if ($ext != 'docx') {
                     throw new Exception('Must be a .docx file');
                   }
                   if ($_FILES['userfile']['size'] > 33554432) {
                     throw new Exception('File must be smaller than 32MB');
                   }
                   if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) { // If it gets uploaded correctly


                         exec('doc2pdf "' . $uploadfile .'" 2>&1', $output);
                         $info = new SplFileInfo($uploadfile);
                         $ext = $info->getExtension();

                         $rawfilename = basename($uploadfile, ("." . $ext));

                         $paper_file = $rawfilename . ".pdf";
                         $save_to = $uploaddir . $editrow['rndid'] .'.jpg';

                         $img = new imagick();
                         $img->setResolution(100,100);
                         $img->setSize(618,800);
                         $img->readImage($uploaddir . $paper_file . '[0]');
                         //set new format
                         $img->setImageFormat('jpg');

                         //save image file
                         $img->writeImage($save_to);

                         rename($uploadfile, ($uploaddir . $editrow['rndid']. '.docx')); // Rename uploaded document
                         rename($save_to, ($uploaddir . $editrow['rndid']. '.jpg')); // Rename the image to overwrite
                         unlink($uploaddir . $paper_file);


                         //echo '<script type="text/javascript">location.href = "https://bounti.io/fullbounti/'.$editrow['rndid'].'";</script>';
                       } else {
                         throw new Exception('There was an error uploading file');
                       }
                     }
               }

                echo '<script type="text/javascript">location.href = "' . $siteRoot . '/fullbounti/'.$editrow['rndid'].'";</script>';
              } catch (Exception $e) {
                echo '<script type="text/javascript">showError("'.$e->getMessage().'")</script>';
              }
            }

          ?>

          <?php // Get bounti info
          if (isset($_GET['id'])) {

            if($stmt = mysqli_prepare($conn, 'SELECT * FROM bounties WHERE rndid = ? AND authorid = ?')) {
              // Set the parameter
              mysqli_stmt_bind_param($stmt, "ss", $_GET['id'], $_SESSION['rndid']);
              // Execute the statement
              mysqli_stmt_execute($stmt);
              // Get the results
              $result = $stmt->get_result();
              $numRows = mysqli_num_rows($result);
              $row = $result->fetch_assoc();

              /*if($row['authorid'] != $_SESSION['sessionID']) {
                //echo 'This ain\'t yours';
              } else {
                //echo 'This is yours';
              }*/
              //
              // Getting selected grade level
              $anygrade = ''; $a = ''; $b = ''; $c = ''; $d = ''; $f = '';
              $minGrade = $row['min_score'];
              if($minGrade == -2) {
                $anygrade = ' selected';
              } else if ($minGrade == 50) {
                $f = ' selected';
              } else if ($minGrade == 60) {
                $d = ' selected';
              } else if ($minGrade == 70) {
                $c = ' selected';
              } else if ($minGrade == 80) {
                $b = ' selected';
              } else if ($minGrade == 90) {
                $a = ' selected';
              }

              // Getting selected minimum education
              $hs = ''; $col = ''; $grad = ''; $mast =''; $doc = '';
              $minEd = $row['mingrade'];
              if ($minEd == 1) {
                $hs = ' selected';
              } else if ($minEd == 2) {
                $col = ' selected';
              } else if ($minEd == 3) {
                $grad = ' selected';
              } else if ($minEd == 4) {
                $mast = ' selected';
              } else {
                $doc = 'selected';
              }

              $reward = $row['stripeamount'];
              $reward /= 100;
              $fee = $row['fee'];
              $fee /= 100;
              $total = $row['total'];
              $total /= 100;


            }


          }
           ?>

          <?php if ($numRows > 0) {
            $stripeReward = $row['stripeamount'] * .01;
            echo '<div class="mainspace">
              <div class="uploadform">
                 <div class="floatleft">
                     <form action="' . $siteRoot . '/editbounti.php" enctype="multipart/form-data" method="POST">
                     <input type="hidden" name="id" value="'.$row['rndid'].'" />
                      <div class="formRow">
                        <div class="formItem">
                         <label for="title">Title</label>
                         <input name="title" id="title" type="text" value="' . $row['title'] . '" />
                         </div>
                         </div>
                         <div class="formRow">
                        <div class="formItem">
                         <label for="bio">Bio</label>
                         <input name="bio" id="bio" type="text" rows="3" value="' .$row['bio'] . '" />
                         </div>
                         </div>
                         <div class="formRow">
                           <div class="formItem">
                             <label for="pages">Pages</label>
                             <input name="pages" id="pages" type="number" value="' .$row['pages'] . '" min="1" />
                           </div>
                           <div class="formItem">
                             <label for="reward">Reward</label>
                             <div class="formRow">
                               <p id="money">$</p>
                               <input name="reward" id="reward" type="number" min=".50" step=".01" max="2500.00" value="' . $stripeReward . '" /><br>
                              </div>
                           </div>
                         </div>

                         <div class="formRow">
                           <div class="formItem">
                             <label for="date">Due Date</label>
                             <input name="date" id="date" type="text" maxlength="10" value="'.$row['duedate'].'" required />
                           </div>
                         </div>


                         <div class="formRow">
                           <div class="formItem">
                           <label for="minscore">Minimum Grade Level</label>
                           <select name="minscore">
                               <option value="-2"'.$anygrade . '>Any grade</option>
                               <option value="50"' . $f .'>F</option>
                               <option value="60"' . $d .'>D</option>
                               <option value="70"' . $c .'>C</option>
                               <option value="80"' . $b .'>B</option>
                               <option value="90"' . $a .'>A</option>
                           </select>
                          </div>
                        </div>

                        <div class="formRow">
                          <div class="formItem">
                         <label for="grade">Minimum Education</label>
                         <select name="grade">
                             <option value="1"'.$hs.'>Highschool</option>
                             <option value="2"'.$col .'>College</option>
                             <option value="3"'.$grad . '>Graduate</option>
                             <option value="4"'.$mast .'>Masters</option>
                             <option value="5"'.$doc .'>Doctorate</option>
                         </select>
                         </div>
                         </div>



                         <input type="hidden" name="MAX_FILE_SIZE" value="16777215" />
                         <input id="fileupload" name="userfile" type="file" onchange="alertFileName()" />
                         <div class="centerme" style="text-align: right;">
                           <table style="width: 100%" id="rewardTable">
                             <tr style="font-weight: 300">
                               <td><img src="' . $siteRoot . '/img/help-circle.png" id="rewardHelp" /><div id="rewardTip" class="tooltiptext">How much the Bounti Hunter will earn</div></td>
                               <td>Reward:</td>
                               <td id="rewardWorth">'.money_format('$%i', $stripeReward).'</td>
                             </tr>
                             <tr>
                               <td><img src="' . $siteRoot . '/img/help-circle.png" id="feesHelp" /><div id="feeTip" class="tooltiptext">Bounti.io\'s cut added to the payment processor\'s fee</div></td>
                               <td style="font-weight: 300">Fees:</td>
                               <td id="feesWorth" style="font-weight: 300">'.money_format('$%i', $fee).'</td>
                             </tr>
                             <tr>
                               <td></td>
                               <td>You pay:</td>
                               <td id="totalWorth">'.money_format('$%i', $total).'</td>
                             </tr>
                           </table>
                         </div>
                         <div class="centerme">
                          <input id="submit" type="submit" value="Update" />


                         <div id="delBounti"><a onclick="deleteBounti(\'' .$row['rndid'] . '\')" id="deleteBounti">delete bounti</a></div>
                         </div>
                 </div>
                     <label for="fileupload" id="dropplease">
                         <p>Click to change file</p>
                         <p id="fileName"></p>
                     </label>


                 <!--</div>-->
                 </form>



             </div>';
           } else {
             echo '<div class="mainspace" style="justify-content: center; align-items: center">
             <h1>This Bounti doesn\'t exist</h1>
             <a href="' . $siteRoot . '/bounties"><h1 style="font-size: 3vmin">Click here to view more</h1></a>';
           }
           ?>


           </div>
</div>
<?php echoFooter(); ?>


        <script type="text/javascript">$('#date').datepicker({minDate: +1, maxDate: '+1y', showAnim: 'slideDown'});</script>
     </body>
 </html>
