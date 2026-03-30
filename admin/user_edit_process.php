<?php
require '../config/connect.php';
$id         = (int)$_POST['id'];
$fullname   = trim($_POST['fullname']);
$department = trim($_POST['department']);
$role_id    = (int)$_POST['role_id'];
$password   = trim($_POST['password'] ?? '');
if ($password !== '') {
    $stmt = $conn->prepare("
        UPDATE users 
        SET fullname=?, department=?, role_id=?, password=?
        WHERE id=?
    ");
    $stmt->bind_param("ssisi", $fullname, $department, $role_id, $password, $id);
} else {
    $stmt = $conn->prepare("
        UPDATE users 
        SET fullname=?, department=?, role_id=?
        WHERE id=?
    ");
    $stmt->bind_param("ssii", $fullname, $department, $role_id, $id);
}
$stmt->execute();
$stmt->close();
header("Location: users.php");
exit;
