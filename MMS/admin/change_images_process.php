<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    exit('Unauthorized');
}
if (!isset($_FILES['image'], $_POST['target'])) {
    header("Location: change_images.php");
    exit;
}
$allowed = ['login.jpg', 'admin.jpg', 'technician.jpg', 'qaqc.jpg', 'informant.jpg'];
$target  = $_POST['target'];
if (!in_array($target, $allowed)) {
    exit('Invalid file');
}
$uploadDir = '../uploads/others/';
$tmpName   = $_FILES['image']['tmp_name'];
$info = getimagesize($tmpName);
if ($info === false) {
    exit('File is not image');
}
move_uploaded_file($tmpName, $uploadDir . $target);
header("Location: change_images.php");
exit;
