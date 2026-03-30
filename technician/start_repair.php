<?php
session_start();
require '../config/connect.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pending.php");
    exit;
}
$repair_id = (int)$_POST['repair_id'];
$user_id   = $_SESSION['user_id'];
$check = $conn->query("
    SELECT status
    FROM repair_requests
    WHERE id = $repair_id
")->fetch_assoc();
if (!$check || $check['status'] !== 'pending') {
    header("Location: pending.php");
    exit;
}
$conn->query("
    UPDATE repair_requests
    SET status = 'in_progress'
    WHERE id = $repair_id
");
header("Location: progress.php");
exit;
