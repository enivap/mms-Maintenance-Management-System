<?php
session_start();
require '../config/connect.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: users.php");
    exit;
}
$username   = trim($_POST['username'] ?? '');
$password   = trim($_POST['password'] ?? '');
$fullname   = trim($_POST['fullname'] ?? '');
$department = trim($_POST['department'] ?? '');
$role_id    = (int)($_POST['role_id'] ?? 0);
if (
    $username === '' ||
    $password === '' ||
    $fullname === '' ||
    $department === '' ||
    $role_id === 0
) {
    die('ข้อมูลไม่ครบถ้วน');
}
$check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    die('Username นี้ถูกใช้งานแล้ว');
}
$check->close();
$stmt = $conn->prepare("
    INSERT INTO users 
        (username, password, fullname, department, role_id)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "ssssi",
    $username,
    $password,
    $fullname,
    $department,
    $role_id
);
if ($stmt->execute()) {
    header("Location: users.php?success=1");
    exit;
} else {
    die('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
}
