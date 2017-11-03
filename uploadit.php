<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);
$uploaddir = 'img/profpics/'; // Directory where files are saved
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
$userid = 3;

if (!($_FILES['userfile']['name'])) {
  echo 'There is no userpic';
} else {
  if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
    $info = new SplFileInfo($uploadfile);
    $ext = $info->getExtension();


    $save_to = $uploaddir . $userid . '.' . $ext;

    $img = new imagick();
    $img->readImage($uploadfile);
    $img->resizeImage(250,250,Imagick::FILTER_LANCZOS,1);

    //set new format
    //$img->setImageFormat('jpg');

    //save image file
    $img->writeImage($save_to);

    //rename($uploadfile, ($uploaddir . $userid. '.docx'));
    unlink($uploadfile);

    echo '<img src="' . $save_to . '" />';
  }
}
?>
