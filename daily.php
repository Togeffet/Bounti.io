<?php

include_once '../../unimportant.php';

$conn = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);

$stmt = mysqli_prepare($conn,
'UPDATE users AS U
INNER JOIN bounties AS B
  ON B.reviewer = U.rndid
SET U.score = U.score - 10
WHERE B.reviewer IS NOT NULL AND STR_TO_DATE(duedate, "%m/%d/%Y") <= NOW() AND success = 0');
// Execute the statement
mysqli_stmt_execute($stmt);

$stmt = mysqli_prepare($conn, 'UPDATE bounties SET success = -2 WHERE success = 0');
mysqli_stmt_execute($stmt);

$rndid = uniqid('notif_');

$stmt = mysqli_prepare($conn,
'INSERT INTO notifications (rndid, recipient, senderid, paperid, messagetype)
  SELECT ?, U.rndid, A.rndid, B.rndid, "pu" FROM bounties AS B
  INNER JOIN users AS U
    ON U.rndid = B.reviewer
  INNER JOIN users AS A
    ON A.rndid = B.authorid
  WHERE B.reviewer IS NOT NULL AND STR_TO_DATE(duedate, "%m/%d/%Y") <= NOW() AND success = 0');

mysqli_stmt_bind_param($stmt, "s", $rndid);

mysqli_stmt_execute($stmt);
?>
