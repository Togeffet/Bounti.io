<?php
            session_start();
            include_once '../../../unimportant.php';
            if ($_SESSION['verified'] == 0) { // User isn't verified
              echo '<script type="text/javascript">location.href = "settings.php?s=v";</script>';
              exit;
            }

            $fullnameinput = "";
            $firstnameinput = "";
            $lastnameinput = "";
            $emailinput = "";
            $picinput = "";


            // Create connection
            $conn = new mysqli($dblocation, $dbuser, $dbpass, $dbname);

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // collect value of input field
                $firstname = $_REQUEST['firstname'];
                $lastname = $_REQUEST['lastname'];
                $email = $_REQUEST['email'];
                $pic = $_REQUEST['profpic'];

                $oldpass = $_REQUEST['oldpass'];
                $newpass = $_REQUEST['newpass'];
                $newpass2 = $_REQUEST['newpass2'];



                if ($oldpass && $newpass && $newpass2) {
                    $getpass = "SELECT * FROM users WHERE id = " . $_SESSION["sessionID"];
                    if ($passresult = $conn->query($getpass)) {
                        $passrow = $passresult->fetch_assoc();
                        $existing = $passrow["password"];

                        if (password_verify($oldpass, $existing)) {
                            if ($newpass === $newpass2) {
                                $pass = password_hash($newpass, PASSWORD_BCRYPT);
                                $passinput = "password = '" . $pass . "'";
                            } else {
                                echo 'nah man';
                            }
                        } else {
                            echo 'Password not verified';
                        }
                    } else {
                    echo 'Unable to get query';
                    }
                } else {
                    echo 'Oldpass not there';
                }


                if ($firstname) { // If they're changing their first name
                  if ($lastname) { // If they're also changing their last name
                    $fullname = $firstname . " " . $lastname;

                  } else {
                    $fullname = $firstname . " " . $_SESSION['sessionLast'];
                  }

                  $fullnameinput = "fullname = '" . $fullname . "', ";

                    $firstnameinput = "firstname = '" . $firstname . "'";
                    $sql = "UPDATE notifications SET sender = '" . $fullname . "' WHERE senderid = " . $_SESSION['sessionID'];
                    $conn->query($sql);
                    $sql = "UPDATE bounties SET authorname = '" . $fullname . "' WHERE authorid = " . $_SESSION['sessionID'];
                    $conn->query($sql);
                    $sql = "UPDATE reviews SET sender = '" . $fullname . "' WHERE senderid = " . $_SESSION['sessionID'];
                    $conn->query($sql);

                    if ($lastname || $email || $pic || $passinput) { // If they're changing another setting
                        $firstnameinput .= ", "; // Add a comma to the query
                    }
                }

                if ($lastname) { // If they're changing their last name
                  $lastnameinput = "lastname = '" . $lastname . "'";

                  if ($firstname) { // If they're also changing their first name
                    // Do nothing, they've already done it up above(??)
                  } else {
                    $fullname = $_SESSION['sessionFirst']. " " . $lastname;

                    $fullnameinput = "fullname = '" . $fullname . "', ";

                    $sql = "UPDATE notifications SET sender = '" . $fullname . "' WHERE senderid = " . $_SESSION['sessionID'];
                    $conn->query($sql);
                    $sql = "UPDATE bounties SET authorname = '" . $fullname . "' WHERE authorid = " . $_SESSION['sessionID'];
                    $conn->query($sql);
                    $sql = "UPDATE reviews SET sender = '" . $fullname . "' WHERE senderid = " . $_SESSION['sessionID'];
                    $conn->query($sql);
                  }



                    if ($email || $pic || $passinput) { // If they're changing another setting
                        $lastnameinput .= ", "; // Add a comma to the query
                    }
                }


                if ($email) {
                    $emailinput = "email = '" . $email . "'";



                    if ($pic || $passinput) { // If they're changing another setting
                        $emailinput .= ", "; // Add a comma to the query
                    }
                }
                if ($pic) {
                    $picinput = "img = '" . $pic . "'";
                    $sql = "UPDATE reviews SET senderpic = '" . $pic . "' WHERE senderid = " . $_SESSION['sessionID'];
                    $conn->query($sql);
                    $sql = "UPDATE messages SET senderpic = '" . $pic . "' WHERE senderid = " . $_SESSION['sessionID'];
                    $conn->query($sql);


                    if ($passinput) { // If they're changing another setting
                        $picinput .= ", "; // Add a comma to the query
                    }
                }






                $sql = "UPDATE users
                        SET " . $fullnameinput . $firstnameinput . $lastnameinput . $emailinput . $picinput . $passinput .
                        " WHERE id = " . $_SESSION["sessionID"] . ";";

                echo $sql;

                if ($conn->query($sql) === TRUE) {
                    $sql = "SELECT * FROM users WHERE id = " . $_SESSION["sessionID"];

                    $result = $conn->query($sql);

                    $row = $result->fetch_assoc();

                    $_SESSION["sessionName"] = $row["fullname"];
                    $_SESSION["sessionFirst"] = $row["firstname"];
                    $_SESSION["sessionLast"] = $row["lastname"];

                    $_SESSION["sessionEmail"] = $row["email"];
                    $_SESSION["sessionGrade"] = $row["grade"];
                    $_SESSION["sessionIMG"] = $row["img"];

                    //echo '<script type="text/javascript">location.href = "settings.php?s=y";</script>';
                    $result = 'Settings updated';
                    echo json_encode($result);

                } else {
                    //echo '<script type="text/javascript">location.href = "settings.php?s=n";</script>';
                    $result = 'Something went wrong';
                    echo json_encode($result);
                }
            }
            $conn->close();
            ?>
